---
name: react-inventory
description: js/react/src/inventory — dual-view project browser (chooser list vs single project detail) with Google Maps
---

## Files
- `main.jsx` — `InventoryChooser` (project list), `InventoryDetail` (single project), `ChecklistTable`, `ProjectMap`
- `table.jsx` — `Table`: reusable react-table v7 component with filtering/sorting/pagination

## Entry Point Logic
- `?pid=<id>` -> renders `InventoryDetail`
- `?search=<term>` -> redirects to external `search.php`
- No params -> renders `InventoryChooser`

## API Calls
- `InventoryDetail`: `./rpc/api.php?pid={pid}` (relative path)
- `InventoryChooser`: `${clientRoot}/projects/rpc/api.php` (absolute, clientRoot-prefixed path — inconsistent with Detail)

## Hardcoded pid Logic (verified against inventory/main.jsx)
- `pid === 1` -> shows the map image / no-map class toggle
- `pid === 1 || pid === 2` -> shows the interactive map section
- `pid === 3` -> different checklist text pointing to the Grow Natives page
- `Table` sorts on `longcentroid` (E-W geographic sort) and filters on the `name` column
- `Table`'s `pageSize` is fixed at 100 (verified `table.jsx:55`)

- `pid !== 4` -> gates the identify-page link, so vendors get no identify link. This one lives in
  `inventory/table.jsx:169`, not `main.jsx`, with the inline comment
  `/* Don't link to identify pages for vendors */`. Grepping only `main.jsx` misses it.

pid 4 is the vendor project. The same magic number drives vendor behavior in
[[react-explore]]; there is no shared constant for it.

## Gotchas
- State mutation bug: `let newArr = this.state.projects` (reference, not copy) in `toggleProjectDisplay`
- Memory leak: a window resize listener is added in `componentDidMount` but never removed in `componentWillUnmount`
- Silent errors: API errors are only logged to console, no user-facing feedback
- API path inconsistency: `InventoryDetail` uses a relative path, `InventoryChooser` uses a clientRoot-prefixed absolute path
- Map markers navigate via `location.href` directly (no React Router)
- `dangerouslySetInnerHTML` used for `briefDescription` and `fullDescription`

## Related
[[react-frontend]], [[react-common]], [[arch-page-entry-points]]
