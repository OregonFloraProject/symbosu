# Tasks: Map Taxon Report Download

Status: in progress
Plan: PLAN.md

## Wave 1
- [x] T1: Extract shared checklist data and export flow (commit e7d2966)
  - Files: `ident/shared/checklistApi.php`, `ident/rpc/api.php`
  - Do: Move the checklist result assembly currently defined in `ident/rpc/api.php` into the new shared module, including its existing imports, empty result shape, `get_data()` behavior, slider normalization, and export dispatcher. Keep `ident/rpc/api.php` as a thin GET endpoint that preserves `clid`, `dynclid`, JSON, `word`, `csv`, and `vendorcsv` behavior. Make the shared data function use its `$params` argument consistently when checking optional `pid` values so it can be called by the Solr endpoint.
  - Tests: none: the repository has no PHP unit-test harness and this flow requires configured Doctrine/MySQL fixtures; run `php -l` against both PHP files.
  - Done when: Existing identification JSON and export request contracts remain intact, the shared module can be included by another endpoint, and both files pass PHP syntax validation.

- [x] T2: Add dynamic checklist creation from TIDs (commit a3617ca)
  - Files: `classes/DynamicChecklistManager.php`
  - Do: Add `createDynamicChecklistFromTids(array $tidList): int`. Validate positive numeric TIDs, remove duplicates, create an expiring `fmdynamicchecklists` row using constant map-report name/details and the existing seven-day expiration and user-ID conventions, then insert one `(dynclid, tid)` row for each TID. Return the new ID only after successful checklist creation and preserve the manager's existing raw-MySQLi conventions and error reporting.
  - Tests: none: the method writes to configured database tables and no database-backed unit-test harness exists; run `php -l classes/DynamicChecklistManager.php` and inspect the generated SQL contract.
  - Done when: Empty or invalid-only input does not create a checklist, valid input creates exactly one checklist and unique taxon links, and the method passes PHP syntax validation without changing existing creation methods.

- [x] T3: Add map taxon-report controls (commit 3463042)
  - Files: `collections/map/index.php`
  - Do: Add one taxon-report format selector for `csv` and `docx` plus a button in the existing occurrence download toolbar. Implement the page's download handler so it copies the current `params-form` fields into a temporary POST form targeting `../../spatial/rpc/solrSearch.php`, adds `download` with the selected format, opens the streamed file in a new tab, and does not interfere with normal map form submission or KML/specimen downloads.
  - Tests: none: the control is inline legacy JavaScript/PHP and the repository has no browser test harness for this page; run PHP syntax validation and inspect the rendered form/handler contract.
  - Done when: The control submits all current map criteria, sends exactly `download=csv` or `download=docx`, supports repeated checkbox fields, and leaves existing toolbar controls unchanged.

## Wave 2
- [x] T4: Orchestrate secured Solr taxon-report downloads (commit 433ccb9)
  - Files: `spatial/shared/solrSearchHelpers.php`, `spatial/rpc/solrSearch.php`
  - Do: Extend the existing Solr pipeline with a download mode that reuses the same query construction, applies the same rare-species security query, requests only `tidinterpreted`, removes duplicate and invalid TIDs, and honors the existing 20,000-record cap. In `solrSearch.php`, validate `download=csv|docx`, create the dynamic checklist through `DynamicChecklistManager::createDynamicChecklistFromTids()`, obtain its checklist data through the shared identification module, and dispatch `csv` or `docx` to the shared exporter (`docx` maps to the existing Word2007 routine). Set JSON headers only for normal responses or download errors, and preserve the current normal-search response shape.
  - Tests: none: verification requires live Solr, MySQL, and PHPWord services and no integration harness is present; run PHP syntax validation on both files and perform static checks for secured-query use, TID deduplication, format validation, and normal-search preservation.
  - Done when: A valid download request streams the requested checklist report, restricted Solr records cannot contribute TIDs, repeated occurrence TIDs produce one checklist link each, empty results return the endpoint's JSON error shape, and ordinary map searches behave as before.
