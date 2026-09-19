# Tasks: Paginate identify and explore result lists

Status: complete
Plan: PLAN.md

## Wave 1

- [x] T1: Create shared ResultPagination component (commit e4dc1a319)
  - Files: `js/react/src/common/resultPagination.jsx` (new)
  - Do: Create a function component that ports the pager and page-size control from `js/react/src/common/imageGallery.jsx` lines 93-118, without the collapse/expand logic. Import `Pagination` from `react-responsive-pagination` and the FontAwesome chevron icons the same way `imageGallery.jsx` does (optional, only if needed for styling). Props: `page` (number), `totalPages` (number), `countLimit` (number), `onPageChange` (page), `onCountLimitChange` (nextSize), `pageSizes` (default `[20, 50, 100]`). Render the `Pagination` control (`total={totalPages}`, `current={page}`, `onPageChange`) plus a center-ish `<select value={countLimit} onChange>` listing `pageSizes`, calling `onCountLimitChange(Number(e.target.value))`. Return `null` when `totalPages < 1`. Use the same inline flex layout as the gallery control (spacer, pager centered, select right). Export as default. Match code style: no comments except a short one if non-obvious, no em dashes.
  - Tests: none. Reason: `js/react` has no test runner (no jest config, no `test` script).
  - Done when: `npm run build` succeeds from `js/react`, the component returns `null` for `totalPages < 1`, and its prop signature matches the contract in `PLAN.md`.

## Wave 2

- [x] T2: Add pagination to identify.jsx (commit 7147cffde)
  - Files: `js/react/src/identify/identify.jsx`
  - Do: Import `ResultPagination` from `../common/resultPagination.jsx`. Add `page: 1` and `countLimit: 20` to state. Add methods `handlePageChange(page) { this.setState({ page }); }`, `handleCountLimitChange(nextSize) { this.setState({ countLimit: nextSize, page: 1 }); }`, `getVisibleTaxa()` and `getPagedResults()`. `getVisibleTaxa()` flattens `this.state.searchResults.familySort` in `Object.entries` order (family order preserved, each family's results concatenated). `getPagedResults()` slices the flat list from `(page - 1) * countLimit` for `countLimit` items and rebuilds `{ familySort, taxonSort }` from the slice, grouping the slice by `result.family`. Bind the two handlers in the constructor. In `onSearchResults`, set `page: 1` alongside `searchResults`. In `onSortByChanged`, set `page: 1`. In `render`, pass `searchResults={this.getPagedResults()}` to `IdentifySearchContainer` and render `<ResultPagination page={this.state.page} totalPages={Math.ceil(this.getVisibleTaxa().length / this.state.countLimit)} countLimit={this.state.countLimit} onPageChange={this.handlePageChange} onCountLimitChange={this.handleCountLimitChange} />` immediately after the `IdentifySearchContainer` inside the existing `searchResults.taxonSort.length > 0` branch. Keep all existing behavior (no-results messages, mobile scroll) intact.
  - Tests: none. Reason: `js/react` has no test runner.
  - Done when: `npm run build` succeeds from `js/react`; the full result set is split across pages of 20 by default, the pager and 20/50/100 select render below the results, changing page size returns to page 1, and a new search/sort resets to page 1.

- [x] T3: Add pagination to explore.jsx (commit 3b18ab529)
  - Files: `js/react/src/explore/explore.jsx`
  - Do: Import `ResultPagination` from `../common/resultPagination.jsx`. Add `page: 1` and `countLimit: 20` to state. Add methods `handlePageChange(page) { this.setState({ page }); }`, `handleCountLimitChange(nextSize) { this.setState({ countLimit: nextSize, page: 1 }); }`, `getVisibleTaxa()` and `getPagedResults()`. `getVisibleTaxa()` returns `[]` when `this.state.currentTids` is empty; otherwise, when `this.state.sortBy === 'taxon'`, filter `this.state.searchResults.taxonSort` to results whose `tid` is in `currentTids`; otherwise flatten `this.state.searchResults.familySort` in `Object.entries` order and filter each result by `currentTids`. `getPagedResults()` slices the flat list from `(page - 1) * countLimit` for `countLimit` items and rebuilds `{ familySort, taxonSort }` from the slice, grouping the slice by `result.family`. Bind the two handlers in the constructor. In `onSearchResults(tids)`, set `page: 1` alongside `currentTids`. In `onSortByChanged`, set `page: 1`. In `render`, pass `searchResults={this.getPagedResults()}` to `ExploreSearchContainer` and render `<ResultPagination page={this.state.page} totalPages={Math.ceil(this.getVisibleTaxa().length / this.state.countLimit)} countLimit={this.state.countLimit} onPageChange={this.handlePageChange} onCountLimitChange={this.handleCountLimitChange} />` immediately after `ExploreSearchContainer`. Keep the existing `currentTids`, `showNotes`, `showTaxaDetail`, export URL, and no-results behavior intact.
  - Tests: none. Reason: `js/react` has no test runner.
  - Done when: `npm run build` succeeds from `js/react`; only taxa in `currentTids` are paginated, family and taxon sort both page correctly, the pager and 20/50/100 select render below the results, page size change returns to page 1, a empty `currentTids` hides the pager, and a new search/sort resets to page 1.
