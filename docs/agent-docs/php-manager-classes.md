---
name: php-manager-classes
description: "Roster of PHP backend manager classes in /classes/, the two coexisting manager patterns (legacy MySQLi vs Doctrine wrapper), and non-obvious coupling between managers"
---

# PHP Manager Classes (`/classes/`)

`/classes/` holds 100+ manager/model classes. There is no single uniform "manager pattern" — two
styles coexist:

## Pattern A: legacy `Manager` base class (majority, ~60+ classes)

`classes/Manager.php` is the shared base: raw MySQLi (`$this->conn`, via
`MySQLiConnectionFactory::getCon()`), a `$this->id` primary key, `cleanInStr()`/`cleanOutStr()`
escaping helpers, and `logOrEcho()` for verbose/log-mode output. Subclasses (e.g.
`ProfileManager`, `KeyDataManager`, `IdentManager`, `DwcArchiverBaseManager`, `ChecklistManager`)
build raw SQL strings against `$this->conn` and expose getters/setters. This is the older,
dominant style and covers most of the codebase (profile/auth, key data, DwC-A export, image
processing, occurrence editing).

## Pattern B: Doctrine "wrapper" managers (newer, 5 classes)

`TaxaManager`, `ExploreManager`, `InventoryManager`, `IdentManager` (partially — it also extends
`Manager`), and `ArticlesManager` follow a distinct shape:
- Constructor `__construct($id = -1)` loads a Doctrine entity via
  `SymbosuEntityManager::getEntityManager()->getRepository(...)->find($id)` into `$this->model`.
- Static `fromModel($model)` wraps an already-fetched Doctrine entity (used when the caller already
  ran a query and just needs the wrapper's getters, e.g. after a `createQueryBuilder()` result).
- Instance getters mostly delegate straight to `$this->model->getX()`, occasionally adding
  computed/joined data (e.g. `TaxaManager::getCharacteristics()`, `ExploreManager::getVouchers()`
  which runs its own Doctrine query joining `Fmvouchers`/`Omoccurrences`/`Omcollections`).

These are the classes actually driving the React-facing RPC endpoints under `/taxa/rpc/`,
`/checklists/rpc/`, `/projects/rpc/`, `/ident/rpc/` — see [[php-flow-taxon-lookup]],
[[php-flow-search]], [[php-flow-checklist-taxa]], [[ident-key-filter-flow]],
[[ident-settaxa-flow]].

RPC endpoints are plain PHP scripts (not a shared dispatcher/framework): each defines its own
top-level functions and does `if (array_key_exists("someParam", $_GET)) { ... }` branching, then
`echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE)`. There is no shared
`queryType` convention or `{status, data}` envelope across endpoints — param names and response
shape are decided per-endpoint (e.g. `taxa/rpc/api.php` dispatches on `search`/`taxon`/`family`/
`genus`/`synonym` GET keys; `checklists/rpc/api.php` dispatches on `clid`/`dynclid`).

## Roster (confirmed classes, non-exhaustive)

- **TaxaManager** (`classes/TaxaManager.php`) — taxon profile data: sciname, vernaculars,
  synonyms, images, descriptions, garden-plant attributes. Owns a large table of hardcoded
  characteristic-ID (CID) constants — see gotcha below.
- **IdentManager** (`classes/IdentManager.php`) — builds the interactive ID key's taxa query
  (`setTaxa()`) and the per-key characteristic list (`getCharacteristics()`). See
  [[ident-settaxa-flow]].
- **ExploreManager** (`classes/ExploreManager.php`) — wraps a `Fmchecklists`/`Fmdynamicchecklists`
  model for checklist metadata (title, abstract, authors, centroid, vouchers). Does **not** itself
  query checklist taxa; taxa listing is delegated to `IdentManager::setTaxa()`/`getTaxa()` (see
  [[php-flow-checklist-taxa]]). Has the getTitle/getIntro inversion gotcha below.
- **InventoryManager** (`classes/InventoryManager.php`) — thin wrapper over an `Fmprojects` model
  (projname, managers, checklists, description). Project *editing* (add/delete project, add/delete
  manager, add/delete checklist) lives in the separate, non-Doctrine
  **InventoryProjectManager** (`classes/InventoryProjectManager.php`), not in `InventoryManager`.
- **OccurrenceMapManager** (`classes/OccurrenceMapManager.php`, extends `OccurrenceManager`) —
  builds the mapping SQL query, `getMappingData()`, `getCoordinateMap()`, `getRecordCnt()`,
  `writeKMLFile()`. See [[map-module]] for the SOLR/MySQL flow.
- **ProfileManager** (`classes/ProfileManager.php`, extends `Manager`) — auth: `authenticate()`,
  `register()`, password hashing/reset, `setUserRights()`.
- **ImageProcessor** (`classes/ImageProcessor.php`) — CyVerse batch image import
  (`processCyVerseImages()`), file loading, header parsing.
- **KeyManager / KeyEditorManager / KeyMatrixEditor / KeyCharAdmin / KeyDataManager** — the
  identification-key admin/editor family; see [[ident-key-editing]] and
  [[ident-key-filter-flow]].
- **TaxonomyEditorManager / TaxonomyDisplayManager** — taxon acceptance status and the tax-tree
  viewer; see [[taxo-acceptance-status]] and [[taxo-authority]].
- **DwcArchiver* family** (`DwcArchiverBaseManager`, `DwcArchiverCore`, `DwcArchiverOccurrence`,
  `DwcArchiverMedia`, etc.) — Darwin Core Archive export. There is no single `DwCAArchiveManager`
  class; the export logic is split across this family, each `extends Manager`.
- **GardenSearchManager** and **OSUTaxaManager** — garden-plant search/attribute logic (sunlight,
  moisture, height/width, etc. via CID lookups); there is no separate `GardenManager` class.

**Not real** — the following classes were referenced in the prior version of this memory but do
not exist anywhere in `/classes/`: `RareManager`, `CollectionManager`, `DwCAArchiveManager`,
`GardenManager`. "Rare plant" and "garden" behavior lives in `OSUTaxaManager`,
`GardenSearchManager`, and constants like `getRareClid()`/`getGardenClid()` in
`classes/Functional.php`, not in dedicated manager classes.

## Gotcha: ExploreManager getTitle()/getIntro() field inversion

**Confirmed real**, and it has a live consequence: `ExploreManager::getTitle()` returns
`$this->model->getName()` and `ExploreManager::getIntro()` returns `$this->model->getTitle()` —
the two are swapped relative to their method names (`classes/ExploreManager.php:42-47`, with an
inline comment acknowledging it: "these next two seem wrong, but the function names make more
sense than the model/DB names").

This isn't just a naming quirk — `checklists/rpc/api.php::buildResult()` does
`$result["title"] = $checklistObj->getTitle()` and `$result["intro"] = $checklistObj->getIntro()`,
so the JSON payload sent to the React checklist page really does contain the checklist's `name`
field under the `title` key and its `title` field under the `intro` key.

## Gotcha: hardcoded characteristic IDs (CIDs)

**Partially confirmed, details corrected.** `TaxaManager.php` (not `Functional.php`) defines a
large block of `private static $CID_*` properties (`classes/TaxaManager.php:15-54`), e.g.
`$CID_SUNLIGHT = 680`, `$CID_MOISTURE = 683`, `$CID_SUMMER_MOISTURE = 682`,
`$CID_LIFESPAN = 136`, plus ~20 more (width, height, flower color, bloom months, wildlife support,
habitat, ecoregion, conservation status, etc.). The literal `680` (sunlight) and `683` (moisture)
also appear hardcoded independently in `GardenSearchManager.php`, `OSUTaxaManager.php`, and
`CommonSearchManager.php`.

**CID 695 is not "duration/lifecycle"** — no `695` reference exists anywhere in `TaxaManager.php`,
`IdentManager.php`, `GardenSearchManager.php`, `OSUTaxaManager.php`, or `CommonSearchManager.php`.
The closest real constant is `$CID_LIFESPAN = 136`. Drop the CID-695 claim.

`js/react/src/common/constants.js` does **not exist** — there is a `js/react/src/taxa/constants/`
directory instead; the CID values are not verified to be duplicated there.

Separately, `classes/Functional.php` defines its own small set of hardcoded IDs via functions, not
CIDs: `getGardenClid()` returns 54, `getRegionCid()` returns 208, `getNurseryCid()` returns 209,
`getVendorPid()` returns 4, `getRareClid()` exists too. `getNurseryCid()`/`getRegionCid()` are used
in `IdentManager::setTaxa()`'s vendor-attribute branch (see [[ident-settaxa-flow]]).

Related: [[db-taxonomy-tables]], [[db-checklist-tables]], [[db-identkey-tables]],
[[react-taxa]], [[react-explore]], [[react-identify]], [[arch-page-entry-points]],
[[map-module]].
