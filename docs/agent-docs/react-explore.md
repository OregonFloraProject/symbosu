---
name: react-explore
description: js/react/src/explore — checklist explore view (read-only) and vendor/garden management variant (editable)
---

## Files (verified against js/react/src/explore)
| File | Purpose |
|------|---------|
| `explore.jsx` | `ExploreApp`: read-only checklist browse with filtering/sorting |
| `explore-vendor.jsx` | `ExploreApp` (vendor variant): same + inline editing, CRUD, CSV bulk upload |
| `sidebar.jsx` | `SideBar`: filter controls + statistics |
| `sidebar-vendor.jsx` | `SideBarVendor`: same + add/remove plant buttons |
| `viewOpts.jsx` | Radio/checkbox controls for view/sort/detail |
| `previewModal.jsx` | Modal to preview checklist data before opening full explore |
| `vendorUploadModal.jsx` | Modal for CSV upload of plant inventory (uses Papa Parse client-side) |

## API Calls
| File | Method | Endpoint | Purpose |
|------|--------|----------|---------|
| `explore.jsx` | GET | `/checklists/rpc/api.php?clid=&pid=&dynclid=&search=&name=&synonyms=` | Load + search |
| `explore.jsx` | GET | `/ident/rpc/api.php?export=csv\|word&...` | Export |
| `explore-vendor.jsx` | GET | `/checklists/rpc/api.php?clid=&pid=` | Load (no dynclid) |
| `explore-vendor.jsx` | POST | `/checklists/rpc/api-vendor.php?update=info&...` | Edit metadata |
| `explore-vendor.jsx` | POST | `/checklists/rpc/api-vendor.php?update=spp&action=preview\|rewrite\|add\|delete\|edit` | CRUD species |
| `previewModal.jsx` | GET | `/checklists/rpc/api.php?clid=&pid=` | Preview data |

## api-vendor.php: 5 spp actions and their frontend triggers
| Action | PHP function | Triggered from | Frontend method |
|--------|-------------|----------------|-----------------|
| `add` | `addOneSPP()` | "Add X?" button in sidebar | `handleAddSPP` (`sidebar-vendor.jsx`) -> `updateSPP` |
| `delete` | `deleteSPP()` | minus-circle per taxon row | delete onClick (`common/searchResults.jsx`) -> `updateSPP` |
| `edit` | `editSPP()` | Save in notes textarea per taxon | `saveEditSPP` (`common/searchResults.jsx`) -> `updateSPP` |
| `preview` | `previewSPP()` | "Upload and Preview" in modal | `processUpload` (`vendorUploadModal.jsx`) -> `previewSPPlist` |
| `rewrite` | `rewriteSPP()` | "Submit this list." in modal | `handleSubmit` (`vendorUploadModal.jsx`) -> `updateSPPlist` |

- `add`, `delete`, `edit` all route through `updateSPP()` in `explore-vendor.jsx` (branches on `obj.action`)
- `preview` and `rewrite` are separate methods (`previewSPPlist`/`updateSPPlist`) with different data flows
- **Verified key mismatch**: `checklists/rpc/api-vendor.php` returns `{"status": N}` (or `{"status": "success"|"error"|"notfound"}`) for these actions, but `explore-vendor.jsx:342` checks `jres.success` — a genuine mismatch, so no success feedback is shown to the user on these operations
- `deleteSPP()` and `addOneSPP()` receive params from the URL query string (not the POST body); `previewSPP()` receives a JSON body via `$_REQUEST['upload']`

## Key Patterns
- URL params (`cl`/`clid`, `pid`, `dynclid`) parsed on mount; `cl` supported as a legacy alias
- `sortResults()` organizes taxa by family (`familySort` object) and a flat `taxonSort` array
- Export URLs rebuilt whenever filters change
- `currentTids` drives display; the full taxon list loads once, filtering only updates `currentTids`
- `fixedTotals` vs `totals`: fixed = original counts; totals = post-filter counts — same pattern used in [[react-identify]] and [[react-checklist-special]]

## Editing Access Control (explore-vendor.jsx / checklistvendor.php)
Editing is gated by the same condition checked independently in two places, both verified:
`$IS_ADMIN || (array_key_exists("ClAdmin", $USER_RIGHTS) && in_array($clid, $USER_RIGHTS["ClAdmin"]))`
- Page gate: `checklists/checklistvendor.php:6` — redirects to the login page if not authorized, before rendering the editable bundle
- API gate (the real enforcement, since the endpoint accepts raw requests): `checklists/rpc/api-vendor.php:586` — re-checks per `update` action (info edit, spp add/delete/edit, CSV preview/rewrite)
- A further UI-level toggle sits on top of both: `isEditing.info` state in `explore-vendor.jsx`'s `toggleEditing()` just switches the "Info" section between display/input; spp add/delete/edit have no toggle and fire immediately on click
- `checklistvendor.php` has no inbound link anywhere in the codebase (grepped all PHP/JS/JSX) — reachable only via a direct URL `checklists/checklistvendor.php?cl=<clid>&pid=<pid>` by a user who already has `ClAdmin` rights for that clid

## Gotcha: showNotes exists only on the unmerged `show-notes` branch
On `dev` there is no `showNotes` prop, and notes visibility is gated solely by `isEditable`.
The branch `show-notes` (1 commit ahead of `dev`, `918e739c5` "Add note for Grow Native vendor
checklist (display and export)") adds it: `explore.jsx:579` passes `showNotes={this.getPid() === 4}`,
and `common/searchResults.jsx:318` gates on `this.props.isEditable == true || this.props.showNotes`.

Grepping only the `dev` working tree makes this look fabricated. It is unmerged work, not a
phantom. Check `git grep showNotes show-notes` before concluding it does not exist. pid 4 is the
vendor project, the same magic number used in [[react-inventory]].

## Gotchas
- `state.searchText` and `state.filters.searchText` both exist in `explore.jsx` — a TODO comment notes the redundancy
- `explore-vendor.jsx` is the only file in the codebase that uses `httpPost.js` (verified: no other `httpPost` import elsewhere)
- `GARDEN_CLID = 54` hardcoded in `explore-vendor.jsx`
- Direct DOM manipulation to show/hide success messages in `explore-vendor.jsx` (bypasses React state)
- Supports flexible CSV column names for upload: sciname, sci_name, scientific_name, etc.
- CSV export for vendor: `/ident/rpc/api.php?export=vendorcsv`

## Related
[[react-frontend]], [[react-common]], [[react-checklist-special]], [[react-identify]], [[php-manager-classes]]
