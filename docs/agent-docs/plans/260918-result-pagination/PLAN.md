# Plan: Paginate identify and explore result lists

Status: approved
Request: imageGallery.jsx uses pagination to limit the number of pictures the user can see. Bring that system over to identify.jsx and explore.jsx.

## Approach

Extract the pager + page-size control that `common/imageGallery.jsx` already uses (`react-responsive-pagination` plus a 20/50/100 `<select>`) into a shared `common/resultPagination.jsx` component. In `identify.jsx` and `explore.jsx`, hold `page` and `countLimit` in state, derive the full ordered list of displayable taxa, slice it to the current page, and pass the sliced results into the existing `IdentifySearchContainer` / `ExploreSearchContainer`. Render the shared pager below the results. Reset `page` to 1 whenever a new query result set arrives or the page size changes, so the index never exceeds the new page count. The containers in `searchResults.jsx` stay unchanged because the pages feed them an already-sliced result set with the same `{ familySort, taxonSort }` shape.

Scope confirmed with user: paginate all taxa results in both list and grid views, use the numeric pager plus page-size select, render the control below the results.

## Affected Code

- `js/react/src/common/resultPagination.jsx` (new): shared pager + page-size control, adapted from `imageGallery.jsx` lines 93-118 with the collapse behavior removed.
- `js/react/src/identify/identify.jsx`: add `page`/`countLimit` state, derive and pass paged results, render `ResultPagination` under `IdentifySearchContainer`, reset page on new results / sort / page-size change.
- `js/react/src/explore/explore.jsx`: same, plus filter the ordered list by `currentTids` before slicing so pagination counts only displayed taxa.

## Data Model and Contracts

`ResultPagination` (function component) props:

- `page: number` (1-based current page)
- `totalPages: number` (ceil of visible count / countLimit)
- `countLimit: number` (current page size)
- `onPageChange: (page: number) => void`
- `onCountLimitChange: (nextSize: number) => void`
- `pageSizes?: number[]` (default `[20, 50, 100]`)

Rendering contract: returns `null` when `totalPages < 1`; otherwise renders the pager and the page-size select.

Page-level derivation contract (both pages): the ordered displayable list is flattened from the same structure the container renders, so page windows match visual order. The rebuilt `{ familySort, taxonSort }` keeps the original result object shape, so `searchResults.jsx` needs no change.

- identify: order = `Object.entries(familySort)` in insertion order, each family's results concatenated. All taxa display (no `currentTids` filter).
- explore: order = `taxonSort` filtered by `currentTids` when `sortBy === 'taxon'`; otherwise `Object.entries(familySort)` order with each family's results filtered by `currentTids`. When `currentTids` is empty the visible list is empty and the pager is hidden.

## Libraries

- react-responsive-pagination@^2.14.0 (already in `js/react/package.json`): pager component; verified API `current`, `total`, `onPageChange` from the installed `dist/index.d.ts`. Context7 does not index this package, so the plan mirrors the in-repo usage in `imageGallery.jsx`.

## Risks

- Current page exceeds new page count after a filter shrinks results: reset `page` to 1 in the query-result handler and on page-size change.
- Pager rendered with zero results: `ResultPagination` returns `null` when `totalPages < 1`, guarding `react-responsive-pagination` against `total={0}`.
- Duplicate or reordered taxa across pages: derive the flat order from the container's own source (`familySort` / `taxonSort`) so slicing matches rendered order; rebuild the grouped shape from the slice.
- explore's `currentTids` filter is applied inside the container today: the page must apply the same filter before slicing, otherwise page counts include hidden taxa and pages look sparse.
