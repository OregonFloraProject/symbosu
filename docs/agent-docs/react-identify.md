---
name: react-identify
description: js/react/src/identify — interactive plant identification tool with discrete + numeric range filters
---

## Files
- `identify.jsx` — `IdentifyApp`: main class component, orchestrates everything
- `sidebar.jsx` — `SideBar`: filter UI + statistics; `SideBarHeading` + `SidebarAccordion` sub-components

## API Calls
1. `../glossary/rpc/getterms.php` — glossary terms
2. `{clientRoot}/ident/rpc/api.php?clid=&pid=&dynclid=&search=&name=&attr[]=&range[]=` — taxa + characteristics; returns `{ taxa, characteristics, totals, projName, title, authors, abstract, lat, lng }`

## Key Patterns
- `Promise.all()` for glossary + taxa fetch, to avoid a race condition when attaching glossary tooltips
- After each query, stale filter cids are pruned from `filters.attrs` and `filters.sliders`
- Range filters are encoded as paired `range[]` params: `cid-n-min` and `cid-x-max`
- Export URLs include the full filter state and are rebuilt on every filter change; word export includes `showcommon=1`
- `MOBILE_BREAKPOINT = 576` (verified at `identify.jsx:22`)
- `cl`/`clid` both supported for legacy compatibility; redirects to `/projects/` if neither is present

## Result Pagination
- State: `page: 1`, `countLimit: 20` (`identify.jsx:52-53`); `handlePageChange(page)` sets `page`, `handleCountLimitChange(nextSize)` sets `countLimit` and resets `page` to 1 (`identify.jsx:583-588`)
- `getVisibleTaxa()` flattens `searchResults.familySort` in `Object.entries` order (`identify.jsx:589-595`); `getPagedResults()` slices that flat list and rebuilds `{ familySort, taxonSort }` grouped by `result.family` (`identify.jsx:596-608`)
- Page resets to 1 in `onSearchResults` (`identify.jsx:429`) and `onSortByChanged` (`identify.jsx:563`)
- Renders `ResultPagination` below `IdentifySearchContainer` inside the `searchResults.taxonSort.length > 0` branch; `totalPages` is `Math.ceil(getVisibleTaxa().length / countLimit)` (`identify.jsx:770-785`)
- `common/searchResults.jsx` is unchanged

## Differences from checklist-special
- Uses a `sliders` filter key (not `ranges`) — see [[react-checklist-special]]
- Uses `sliderOld.jsx` (not the newer `slider.jsx`) — see [[react-common]]
- Sidebar accordion is per-characteristic-group (`SidebarAccordion`)
- No canned searches, no carousel

## Gotchas
- `SideBar` uses `UNSAFE_componentWillReceiveProps()` (deprecated React lifecycle) for mobile detection (verified at `sidebar.jsx:133`)
- `fixedTotals` vs `totals` follows the same pattern as [[react-explore]]: fixed = original counts, totals = post-filter
- Filter pruning happens after each query to keep filter state valid

## Related
[[react-frontend]], [[react-common]], [[react-explore]], [[react-checklist-special]], [[ident-key-filter-flow]]
