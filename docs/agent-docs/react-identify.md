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
