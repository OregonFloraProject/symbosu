---
name: map-module
description: "Occurrence mapping subsystem hub - entry point, PHP classes, RPC endpoints, frontend JS, security model, config"
---

Entry point: `collections/map/index.php`. Three layers: PHP backend (`OccurrenceMapManager`), RPC endpoints (`collections/map/rpc/` and `spatial/rpc/`), Leaflet-based JS frontend.

For SOLR query building/response detail, see [[map-solr-pipeline]]. For the marker-click-to-popup and KML export flows, see [[map-click-kml-flow]].

## PHP backend

`classes/OccurrenceMapManager.php` extends `OccurrenceManager` (`classes/OccurrenceManager.php`, see [[php-manager-classes]]).

- `__construct($occIds = null)` — stores `$occIds` and calls `setGeoSqlWhere()` immediately.
- `buildMapSqlQuery($start, $limit, $select)` — builds the shared SELECT against `omoccurrences o` (+ paleo WITH clause if `ACTIVATE_PALEO`), always adds `(ts.taxauthid = 1 OR ts.taxauthid IS NULL)`.
- `getCoordinateMap($start = 0)` — main MySQL-path data fetch (used by `index.php` on page load and `collections/map/rpc/searchCollections.php`); returns `{taxaArr, collArr, recordArr}` — the same shape the SOLR path produces (see [[map-solr-pipeline]]), not a `[collection][occid]` map.
- `getMappingData($recLimit, $extraFieldArr, $occIds)` — used by `collections/map/leafletmap.php` (simple/taxon maps); returns `[sciname][occid]` with instcode, collcode, colltype, catnum, ocatnum, inat, collector, lat, lng. When `$occIds` is passed it skips security filtering entirely (see Gotcha below).
- `setGeoSqlWhere()` (private) — builds `$this->sqlWhere`; applies the role-based security filter (see below); if there's no search term but `$this->occIds` is set (constructor-supplied), sets `sqlWhere = 'WHERE o.occid IN(...)'` with **no security filter**.
- `writeKMLFile($recLimit, $extraFieldArr)` — streams KML directly via `buildMapSqlQuery()`; see [[map-click-kml-flow]].

`getOccurrenceArr()` and `createShape()`, previously documented here, do not exist in the current file — removed.

## RPC endpoints

- `collections/map/rpc/searchCollections.php` — MySQL-path AJAX endpoint, calls `getCoordinateMap()`.
- `collections/map/rpc/getCoordinates.php`, `getTaxa.php`, `postMap.php`, `changemaprecordpage.php` — supporting endpoints.
- `spatial/rpc/solrSearch.php` — SOLR-path endpoint; see [[map-solr-pipeline]].

## Frontend JS (verified present)

- `js/symb/collections.map.index.js` — core map UI, Leaflet, jQuery UI tabs/accordion; `openRecord()`/`openPopup()` live here.
- `js/symb/collections.map.index.OregonFlora.js` — OregonFlora extensions; see [[map-solr-pipeline]] for which functions here are dead vs. still called.
- `js/symb/wktpolygontools.js`, `MapShapeHelper.js`, `localitySuggest.js`.
- `js/leaflet.OregonFlora/leaflet.OregonFlora.js` — Leaflet customizations (overlays, clustering); layer data files under `js/leaflet.OregonFlora/layers/`.

### Overlay layer architecture (`leaflet.OregonFlora.js`)

Overlays lazy-load: an empty `L.layerGroup()` is registered in the layer control, data is fetched on first `overlayadd`, and `clearLayers()` runs on `overlayremove` (data stays cached in memory).

- **Counties** (`layers/oregon.counties.json`) — on by default, fetched immediately.
- **Ecoregions** (`layers/ecoregions.kml`) — off by default, lazy-loaded, parsed with `DOMParser` + `L.KML`.
- **Land ownership** — `addLandOwnershipOverlays(map, clientRoot)` iterates a `{label, file, color}` array: BLM (`#f5e723`), USFS (`#23f52e`), State of OR (`#23e7f5`), Local Govt (`#A9D5B8`), Other Federal (`#1A9B8E`), Tribal (`#ceabe0`), Private (`PrivateOwnership2.geojson`, fetched in chunks, `#fff`). Popup shows `FeeTitleHolder` (or fallback) + "Land Owner: {label}". All off by default.
- `groupLandLayers(map, groups)` — pure DOM manipulation (no new Leaflet control) that nests the land layer `<label>`s under a synthetic parent checkbox in the existing layer control, re-applied after every `control._update`.

Clustering uses `L.markerClusterGroup` via a custom `LeafletSingleClusterMapGroup` class (`leaflet.OregonFlora.js`) that groups markers by taxon, collection, and portal simultaneously so points can be recolored/toggled by any of the three groupings; see [[map-click-kml-flow]] for how markers are built from search results.

## SOLR vs. MySQL path selection

Decided once per page load in `collections/map/index.php` from `$USE_SOLR_SEARCH = ($MAP_SOLR_SEARCH_FLAG === 1) ? 1 : 0`, forced to `0` if a checklist (`clid`) search term is present. This is a static config check, not a live SOLR-health fallback — see [[map-solr-pipeline]] for the corrected record of what happens when SOLR itself errors (it is not silent).

## Locality / rare-species security

Enforced per-record, not per-column-redaction: a record is either included or excluded from results, based on role vs. a security field. There is no precision-reduction or coordinate-shifting redaction in this codebase — a record with restricted locality is fully hidden from users who lack the right, not fuzzed.

Roles, from `OccurrenceMapManager::setGeoSqlWhere()`:
- `SuperAdmin`, `CollAdmin`, `RareSppAdmin`, `RareSppReadAll` — see all records, no filter.
- `RareSppReader` or `CollEditor` — `(o.CollId IN (their collections) OR o.recordSecurity = 0)`.
- Everyone else (including anonymous) — `o.recordSecurity = 0` only.

## Gotcha: security column renamed, one legacy manager not updated

Schema patch `config/schema/3.0/patches/db_schema_patch-3.3.sql` renamed `omoccurrences.localitySecurity` &rarr; `recordSecurity` (values: 0 = none, 1 = hidden locality, 5 = hide full record). `OccurrenceMapManager.php` uses the current name. The SOLR index still exposes the field as `localitySecurity` (lowercase; see `SOLRManager::checkQuerySecurity()` in [[map-solr-pipeline]]) — a separate, intentional naming split between the two systems. But `classes/MapInterfaceManager.php` (last touched 2019, still wired into `collections/map/csvdownloadhandler.php` and `collections/map/rpc/changemaprecordpage.php`) still queries `o.LocalitySecurity` — the pre-3.3 column name. Whether this still works depends on whether the renamed column was left with a compatibility alias; it was not found in the schema files checked. Treat any locality-security logic touched through those two endpoints as unverified until checked against the live DB.

## Config (`symbini.php` / `config/symbini_template.php`, verified)

- `$MAP_SOLR_SEARCH_FLAG` — 1 enables the SOLR path.
- `$MAPPING_BOUNDARIES` — `"maxLat;maxLng;minLat;minLng"` project bounding box.
- `$EXTERNAL_PORTAL_HOSTS` — federated portals for cross-portal search (defaults to `[]` in `index.php` if unset).
- `$SOLR_URL` — SOLR endpoint used by `solrSearch.php`.

## Limits

- Default record limit is 20,000 on both paths: `collections/map/index.php` defaults `recordlimit` to 20000 for the MySQL path; `spatial/rpc/solrSearch.php` hard-caps SOLR results at `MAX_RECORD_COUNT = 20000` (present since the endpoint was introduced — not removed).
- `collections/map/index.php` sets `max_execution_time` to 180 seconds.

## Related
[[map-solr-pipeline]], [[map-click-kml-flow]], [[db-occurrence-tables]], [[php-manager-classes]], [[arch-page-entry-points]], [[arch-security-permissions]], [[arch-cql-deprecated]]

**Why:** Verified against the repo 2026-07-31; the 2026-07-16 bulk-authored version of this memory (and its two siblings, map_solr.md and map_click_kml_flow.md) contained unverifiable/wrong method names, line numbers, and a fabricated redaction mechanism. See [[map-solr-pipeline]] and [[map-click-kml-flow]] for what replaced them.
