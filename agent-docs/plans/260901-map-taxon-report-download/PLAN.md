# Plan: Map Taxon Report Download

Status: approved
Request: Add a taxon-report download control to `collections/map/index.php`. The control must call `spatial/rpc/solrSearch.php` with `download=csv` or `download=docx`; the endpoint must collect distinct `tidinterpreted` values from the Solr result, create an ephemeral dynamic checklist and its taxon links, then export the checklist as CSV or DOCX through logic shared with `ident/rpc/api.php`.

## Approach

Extract checklist data assembly and export dispatch from `ident/rpc/api.php` into a shared identification module. Extend the existing Solr helper pipeline with a secured taxon-ID download mode that reuses the map query construction, deduplicates returned `tidinterpreted` values, and passes them to a new `DynamicChecklistManager` method. Add a single map toolbar button with a CSV/DOCX selector that submits the current search form to `solrSearch.php`, while preserving the existing JSON response path for normal map searches.

## Affected Code

- `collections/map/index.php`: add the taxon-report format control and submit the current map criteria to the download endpoint.
- `spatial/rpc/solrSearch.php`: validate `download=csv|docx`, run the taxon-only Solr mode, create the dynamic checklist, and stream the selected report; retain JSON handling for normal searches.
- `spatial/shared/solrSearchHelpers.php`: expose shared secured Solr result handling for distinct `tidinterpreted` values.
- `classes/DynamicChecklistManager.php`: add a method that creates the standard expiring dynamic-checklist row and inserts unique taxon links from a validated TID list.
- `ident/shared/checklistApi.php`: hold shared checklist-data assembly and CSV/DOCX export dispatch extracted from the identification RPC endpoint.
- `ident/rpc/api.php`: become a thin endpoint that uses the shared checklist module without changing existing `clid`, `dynclid`, JSON, or legacy export behavior.

## Data Model and Contracts

- `POST spatial/rpc/solrSearch.php` accepts the existing map search fields plus `download=csv` or `download=docx`.
- Download mode queries Solr with the same security-filtered query used for map features, requests only `tidinterpreted`, removes duplicate and invalid IDs, and observes the existing `MAX_RECORD_COUNT = 20000` result cap.
- `DynamicChecklistManager::createDynamicChecklistFromTids(array $tidList): int` creates one `fmdynamicchecklists` row with constant report name/details, the current user ID, and the existing seven-day expiration convention, then inserts `(dynclid, tid)` rows into `fmdyncltaxalink`.
- A successful download streams the existing checklist CSV or Word2007 DOCX format. `download=docx` maps to the existing Word export routine, while `ident/rpc/api.php` continues accepting its existing `export=word` parameter.
- Invalid download values or Solr/checklist failures return the endpoint's JSON error shape; normal map searches continue returning the existing JSON GeoJSON shape.

## Libraries

- `doctrine/orm@2.7.3`: existing checklist entity lookup and `IdentManager` taxon assembly used by the shared export flow.
- `phpoffice/phpword@^1.3.0`: existing Word2007 DOCX writer used by the shared checklist export.
- `SOLRManager`: existing project-local Solr client and security filtering; no new external Solr dependency.

## Risks

- Repeated Solr occurrence documents could create duplicate checklist links: deduplicate and validate TIDs before insertion.
- A report could expose restricted taxa if it uses the unsecured query: derive download TIDs from the same secured query as map features.
- Sending the existing JSON header before a binary report could corrupt download responses: set response headers only in the selected response branch.
- Empty or oversized searches could produce an unusable checklist: preserve the Solr cap and return a clear error when no valid TIDs are found.
- Shared extraction could change existing identification exports: retain the current result shape and run PHP syntax checks plus focused endpoint-flow verification.

## Code Review
- Skipped by user.
