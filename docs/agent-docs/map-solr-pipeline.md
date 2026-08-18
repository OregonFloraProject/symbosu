---
name: map-solr-pipeline
description: "SOLR search path for collections/map - PHP query building in solrSearch.php, security filtering, response parsing, fallback conditions"
---

As of commit `ea3c24f29` (2026-04-29, "Move SOLR execution to backend; deprecate JS query builders"), SOLR query building, execution, and response parsing happen server-side in `spatial/rpc/solrSearch.php`. Confirmed still true in the current tree. See [[arch-cql-deprecated]] for the related CQL dead-code cleanup from the same change.

## Correction: the JS builder functions are not commented out

`js/symb/collections.map.index.OregonFlora.js` tags each old query-builder function with a `// Deprecated: moved to spatial/rpc/solrSearch.php` comment line, but the function bodies themselves are live, uncommented code — not commented out as a prior version of this memory claimed. Verified by call-site search:

- **Actually dead** (no callers anywhere in the repo): `getCollectionParams`, `prepareTaxaDataAsync`, `isFamilyName`, `prepareTaxaParamsAsync`, `getTextParams`, `getGeographyParams`, `buildSOLRQString`, `getRecordCountFromSOLR`, `loadPointsFromSOLR`.
- **Mislabeled — still called**: `convertSOLRResponse(res, host)` and the `SOLR_TYPE_TO_SYMBIOTA_TYPE` map it uses are tagged "Deprecated" but are called directly from `collections/map/index.php` (`searchCollections()`) to reshape `solrSearch.php`'s GeoJSON response into `{taxaArr, collArr, recordArr}` for the frontend. Do not remove these as dead code.
- `renderOccurrenceRows` (same file) is unrelated to SOLR and still runs client-side to paint the occurrence list table.

## Query assembly (`spatial/rpc/solrSearch.php`)

Browser POSTs raw form data; PHP builds and executes the query server-side. Input fields include `db[]`, `taxa`/`taxontype`/`usethes`, `country`/`state`/`county`/`local`/`collector`/`collnum`, date range, `catnum`, boolean flags (`typestatus`, `hasimages`, `hasgenetic`, `includecult`, `excludeinat`), and geometry (`polycoords`, or `pointlat`/`pointlong`/`radius`/`pointunits`, or the rectangle corners).

Assembled query:
```
q=(collid:(...)) AND (...taxa...) AND (...text...) AND (decimalLatitude:[* TO *] AND decimalLongitude:[* TO *] AND sciname:[* TO *])
&fq=geo:"Intersects(POLYGON((...)))"
```
Defaults to `q=(sciname:[* TO *])` if no clauses are built.

## Security filtering (verified in `solrSearch.php` and `classes/SOLRManager.php`)

1. `ProfileManager::refreshUserRights()` then `$solrManager->getCanReadRareSpp()` determine `$canReadRareSpp`.
2. Count query runs first against the **unsecured** `$solrQ` to get `$fullCount`.
3. `SOLRManager::checkQuerySecurity($solrQ)` returns `$solrQSecure` — appends `AND (localitySecurity:0)` (SOLR field name; MySQL side calls the equivalent field `recordSecurity`, see [[map-module]]) when the user cannot read rare species.
4. If restricted, a second count query with `$solrQSecure` gives `hiddenFound = fullCount - partialCount`.
5. The main data query (`wt=geojson&geojson.field=geo`, capped at `MAX_RECORD_COUNT = 20000` rows — this cap has been present since the endpoint was first added, it was not removed) uses `$solrQSecure`.

### Bug: the `query` field in the JSON response is unsecured

`solrSearch.php` sets `$geojson['query'] = 'q=' . $solrQ . ...` — built from the **unsecured** `$solrQ`, not `$solrQSecure`, even though the actual record data returned (`$geojson` features) was fetched with the secured query. The frontend stores this `query` value verbatim (as `solrqstring`) for the "copy link" button and for KML export. This is the root cause of the KML-export security bypass documented in [[map-click-kml-flow]] — read that file for the full consequence chain.

## Response shape

GeoJSON features are mapped (by `convertSOLRResponse`, see above) to `taxaArr[tid] = {sn, tid, family, color}`, `collArr[collid] = {name, collid, color}`, and `recordArr[] = {occid, collid, lat, lng, tid, type, catalogNumber, eventdate, sciname, id}`. `CollType` maps `"Observations"` &rarr; `"observation"`, `"Preserved Specimens"` &rarr; `"specimen"`.

On error, `solrSearch.php` catches `\Throwable` and returns `{"error": true, "message": ...}` as JSON (HTTP 200) rather than throwing. The frontend (`collections/map/index.php`, `searchCollections()`) checks `response.error` and calls `alert(response.message)` — this failure path is visible to the user, not silent.

## When SOLR is bypassed

Controlled by `$USE_SOLR_SEARCH` in `collections/map/index.php`, computed once at page load: `false` when `$MAP_SOLR_SEARCH_FLAG !== 1`, or when a checklist search (`clid`) is present (checklist/vouchered data may not be fully indexed in SOLR). This is a static configuration decision made before any query runs — there is no code path that detects a live SOLR outage and falls back to MySQL mid-request. A prior version of this memory described such a "silent runtime fallback" as a gotcha; no such mechanism exists in the current code, so that claim has been removed rather than kept with a caveat.

## Related
[[map-module]], [[map-click-kml-flow]], [[arch-cql-deprecated]], [[arch-security-permissions]]
