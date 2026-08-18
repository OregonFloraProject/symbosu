---
name: react-header
description: "js/react/src/header — sitewide navigation header with search, dropdowns, scroll animation, mobile menu"
---

## Files
- `main.jsx` — single file, entire header app (`HeaderApp` class root, `HeaderDropdown`/`HeaderDropdownItem`/`HeaderButtonBar`/`HeaderButton` sub-components)

## Navigation
`DROPDOWNS` constant defines 6 menus (verified): Explore, Resources, Publications, About, Contribute, Profile (Profile is dynamic based on login state).

## API Calls
1. `/webservices/autofillsearch.php?q=...&clid=...` — autocomplete, via the shared `SearchWidget` (see [[react-common]]), throttled 500ms
2. `/taxa/rpc/api.php?synonym={taxonId}` — check if a synonym maps to a unique accepted taxon

## Key Behavior
- `onSearch(searchObj)` — synonym resolution (calls #2 above) then navigates; the same logic is duplicated verbatim in `home/main.jsx` — see [[react-home]]. Both files carry a warning comment about the duplication.
- Scroll handling animates header height 100px -> 60px based on scroll position; logo cross-fade opacity computed as `(headerHeight - 60) / 40`.
- Mobile breakpoint is `window.innerWidth < 992` (Bootstrap `lg`), verified at `header/main.jsx:200`.
- `componentDidMount()` tags dropdown entries with `currentPage`/`currentAncestor` flags by comparing against the current URL, and registers scroll/resize listeners.

## Gotchas
- Dropdown close uses `onBlur` + `e.relatedTarget` check
- Synonym resolution is a 2-step flow: check uniqueness via API, then navigate with params
- Commented-out old array-based dropdown structure and an old HTML search form are preserved in the file rather than deleted

## Related
[[react-frontend]], [[react-common]], [[react-home]], [[arch-page-entry-points]]
