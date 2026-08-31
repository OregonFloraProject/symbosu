---
name: map-click-kml-flow
description: "Map dot click-to-popup flow and KML export flow for collections/map/index.php search results, including a verified KML security bypass"
---

## 1. Clicking a map dot opens the individual record popup

Submitting the map search form (`collections/map/index.php`) calls `searchCollections()`, which POSTs to `spatial/rpc/solrSearch.php` (or the MySQL path — see [[map-solr-pipeline]] and [[map-module]]) and gets back `recordArr`, records shaped `{occid, collid, lat, lng, tid, type, catalogNumber, id, ...}`.

`genMapGroups(records, tMap, cMap, origin)` (`collections/map/index.php`) loops `recordArr` and builds one `L.marker` per record (specimen vs. observation icon by `record.type`), each registered with three `LeafletSingleClusterMapGroup` instances (`js/leaflet.OregonFlora/leaflet.OregonFlora.js`) — by taxon, collection, and portal — and fed into `L.markerClusterGroup` for spatial clustering (see [[map-module]]). Clicking an individual marker fires its `click` handler.

`openRecord(record)` (`js/symb/collections.map.index.js`):
```js
function openRecord(record) {
   let url = record.host ?
      `${record.host}/collections/individual/index.php?occid=${record.occid}` :
      "../individual/index.php?occid=" + record.occid
   openPopup(url);
}
```
If the record came from a federated/cross-portal search (`record.host` set), the link points at that portal; otherwise it's relative to the current site.

`openPopup(urlStr)` (same file) opens `individual/index.php?occid=<occid>` via `window.open` (not a Leaflet popup), sized to ~95% of the opener's width (capped 1400px), 600px tall.

**Chain:** search submit &rarr; SOLR/MySQL result &rarr; `genMapGroups()` builds markers &rarr; click &rarr; `openRecord()` &rarr; `openPopup()` opens `individual/index.php?occid=X` in a new window.

## Correction: the record list panel is not a separate PHP partial anymore

A prior version of this memory described the record list panel as loaded from a file `collections/map/occurrencelist.php` via a `getOccurenceRecords()` call. That file does not exist in the current tree. `collections/map/index.php` itself contains the comment `// JGM TODO: This and the #occurrencelist div below replaces getOccurrenceRecords() (which calls occurrencelist.php)` — confirming that mechanism was removed. The record list and its download toolbar (including the KML form) are now rendered directly inside `collections/map/index.php`, and each page of rows is painted client-side into `#occurrencelist tbody` by `renderOccurrenceRows()` (`js/symb/collections.map.index.OregonFlora.js`) from data already in memory.

## 2. KML export after a search

The KML button is a plain HTML form embedded in `collections/map/index.php`:
```php
<form name="fullquerykmlform" action="kmlhandler.php" method="post" target="_blank">
    <input name="reclimit" ... />
    <input name="searchvar" ... />
    <input id="solrqstring" name="solrqstring" type="hidden" />
</form>
```
It POSTs to `collections/map/kmlhandler.php` in a new tab. The `solrqstring` hidden field is populated by JS as `response.query` — the value returned by `solrSearch.php` (see the bug below).

`collections/map/kmlhandler.php`:
```php
$occIds = getOccIdsFromSOLR($solrqString, $recLimit);
$mapManager = new OccurrenceMapManager($occIds);
$mapManager->writeKMLFile($recLimit, $kmlFields);
```
`getOccIdsFromSOLR()` (`collections/download/solr.php`) POSTs the query string to `$SOLR_URL/select` with `fl=occid&wt=json` and returns the occid list — it applies no security filtering of its own.

`OccurrenceMapManager::writeKMLFile($recLimit, $extraFieldArr)` (`classes/OccurrenceMapManager.php`) streams KML via `buildMapSqlQuery()`, using `$this->sqlWhere` set at construction time. It sends KML MIME headers and writes XML directly with `fwrite`, chunking the SQL query at 40,000 rows (`$KML_CHUNK_SIZE`) up to a 3,000,000-row cap (`$KML_RECORD_CAP`). Each `<Placemark>` gets `<ExtendedData>` (collection/institution code, catalog number, a `DataSource` note, `RecordURL` pointing at `individual/index.php?occid=<occid>` — the same detail page the map-dot popup opens) and a `<Point>`.

**Chain:** KML button submit &rarr; `kmlhandler.php` resolves `solrqstring` to occids via `getOccIdsFromSOLR()` &rarr; `new OccurrenceMapManager($occIds)` &rarr; `writeKMLFile()` &rarr; `buildMapSqlQuery()` &rarr; streamed KML.

## Gotcha: KML export bypasses locality/rare-species security

Verified end to end. The on-screen map search *is* properly security-filtered (see [[map-solr-pipeline]]: the actual result features come from `$solrQSecure`). But the KML export path is not:

1. `spatial/rpc/solrSearch.php` builds its `query` response field from the **unsecured** `$solrQ`, not the security-filtered `$solrQSecure` (see [[map-solr-pipeline]] for the exact line).
2. That unsecured string is what JS writes into the `solrqstring` hidden input and POSTs to `kmlhandler.php`.
3. `getOccIdsFromSOLR()` (`collections/download/solr.php`) runs it against SOLR verbatim — no security clause, no rare-species check.
4. `kmlhandler.php` passes those occids into `new OccurrenceMapManager($occIds)`. Its constructor calls `setGeoSqlWhere()` (`classes/OccurrenceMapManager.php`), which — because there's no active search term, only `$this->occIds` — takes the `else if ($this->occIds)` branch and sets `sqlWhere = 'WHERE o.occid IN(...)'` with **no `recordSecurity` check at all**.
5. `writeKMLFile()` &rarr; `buildMapSqlQuery()` uses that unfiltered `sqlWhere` directly.

Net effect: any occurrence matched by the original search — including locality-restricted/rare-species records that were correctly excluded from the on-screen map and record list for the current user — is included in the KML download, with full coordinates. This reproduces for any search whose result set flows occids back through `solrqstring` (in practice, any SOLR-path search, since `solrqstring` is always populated when `$USE_SOLR_SEARCH` is on).

This replaces a vaguer, unverified claim ("some legacy endpoints skip redaction") from a prior version of this memory with the actual mechanism and the two files responsible: `spatial/rpc/solrSearch.php` (wrong variable used for the `query` field) and `classes/OccurrenceMapManager.php::setGeoSqlWhere()` (occids branch has no security clause).

## Note: a separate, unrelated KML path exists

The standalone "Spatial Query" tool (`spatial/index.php` &rarr; `spatial/rpc/datadownloader.php` &rarr; `SpatialModuleManager::writeKMLFromGeoJSON()`, `classes/SpatialModuleManager.php`) also exports KML, working from GeoJSON directly. Confirmed present; shares no code with the map's KML export above, and was not checked for the same security bypass.

## Related
[[map-module]], [[map-solr-pipeline]], [[arch-security-permissions]], [[arch-cql-deprecated]]
