---
name: php-flow-checklist-taxa
description: "Checklist taxa listing actually goes through IdentManager::setTaxa()/getTaxa(), not a direct ExploreManager/Fmchklsttaxalink query"
---

# Checklist Taxa Listing Flow

Entry: `checklists/rpc/api.php`, dispatched on `clid` (static checklist, `buildResult()`) or
`dynclid` (dynamic checklist, `buildDynResult()`).

The key fact this flow gets wrong in older notes: **`ExploreManager` does not itself run the taxa
query.** `buildResult($params)`:

1. Loads the `Fmchecklists` model by `clid`, wraps it with `ExploreManager::fromModel()` for
   metadata only (title/intro/authors/abstract/locality/centroid — subject to the
   getTitle()/getIntro() inversion, see [[php-manager-classes]]).
2. Constructs an **`IdentManager`**, calls `setClid($clid)`, `setOrderBySciname(true)`,
   `setIncludeChecklistNotes(true)`, optionally `setSearchTerm()`/`setSearchName()`/
   `setSearchSynonyms()` from request params, or `setThumbnails(true)` on the initial page load.
3. Calls `$identManager->setTaxa()` then `getTaxa()` — the same query-building method documented
   in [[ident-settaxa-flow]], which joins `Fmchklsttaxalink` on `clid` (for a normal static
   checklist) among other paths.
4. Merges in voucher data per-taxon via `ExploreManager::getVouchers()` (a single query joining
   `Fmvouchers` → `Omoccurrences` → `Omcollections`, filtered by `clid` — replaced a slower
   per-taxon voucher query per an inline code comment dated 2024-04-19).
5. Renames the `image` key to `thumbnail` per row, and computes `TaxaManager::getTaxaCounts()`
   totals.

`buildDynResult($params)` is the `Fmdynamicchecklists` equivalent: wraps the dynamic-checklist
model, then calls `IdentManager::setDynClid($dynclid)` + `setTaxa()`/`getTaxa()` the same way (this
routes through `IdentManager::setTaxa()`'s `Fmdyncltaxalink` join path). Notably it parses lat/lng
out of the checklist's *title* string by splitting on whitespace (`explode(" ", $title)`), with an
inline comment questioning why there's no dedicated lat/lng field for dynamic checklists.

Response is a flat JSON object (`clid`, `title`, `intro`, `taxa[]`, `tids[]`, `totals`) — no
pagination params (`page`/`pageSize`) are handled server-side; `IdentManager::setTaxa()` returns
the full result set for the checklist in one query.

Related: [[ident-settaxa-flow]], [[php-manager-classes]], [[db-checklist-tables]],
[[react-explore]].
