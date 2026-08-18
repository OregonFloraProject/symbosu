---
name: react-checklist-special
description: "js/react/src/checklist-special — dual-purpose app for Grow Natives (garden) and Rare Plant Guide pages, plus a rare-policy access request form"
---

## Files
| File | Purpose |
|------|---------|
| `main.jsx` | `SpecialChecklistApp`: single class component serving BOTH garden and rare pages, branching on a `pageType` prop |
| `rare-policy.jsx` | Standalone function component (uses `fetch`, not `httpGet`/`httpPost`) for sensitive rare-data access request form |
| `components/gardenCarousel.jsx` | react-slick carousel for canned searches |
| `components/gardenCredits.jsx` | Sponsor logos (Metro, NRCS) |
| `components/cannedSearches.jsx` | `CannedSearchContainer` + `CannedSearchResult` preset plant combination cards |
| `components/infographicDropdownGarden.jsx` | Collapsible hero banner for garden page (class) |
| `components/infographicDropdownRare.jsx` | Collapsible hero banner for rare page (function + hooks) |
| `components/sortOptions.jsx` | View/sort controls (function) |
| `components/aboutDropdown.jsx` | Rare Plant Guide info section with partner links |

## API Calls
1. `{clientRoot}/{pageType}/rpc/api.php?clid=&pid=` — taxa + characteristics
2. `{clientRoot}/garden/rpc/api.php?canned=true` — canned searches (garden only)
3. `../glossary/rpc/getterms.php` — glossary terms
4. `{apiUrl}?clid=&pid=&search=&attr[]=&range[]=` — filtered results (returns `{ tids }` only)

## Key Differences from identify module
| Feature | identify | checklist-special |
|---------|----------|-------------------|
| Filter key | `sliders` | `ranges` |
| Slider component | `sliderOld.jsx` | `slider.jsx` (Blueprint.js) |
| Canned searches | None | Yes (carousel cards) |
| Sort default | sciName | vernacularName (garden), sciName (rare) |

## Key Behavior
- `onGroupFilterClicked()` — nursery state multi-select, hardcoded at `characteristics[5].characters[1]` (verified `main.jsx:400`, comment there notes it's "as hardcoded in garden/rpc/api.php")
- `getIncludedHeritageLists()` — extracts NatureServe rankings from hardcoded `cid === 244` (verified `main.jsx:34`)
- `slideshowCount` state drives the responsive carousel size and is recalculated on resize (verified `main.jsx:116,445`)

## rare-policy.jsx
- Status states (verified at `rare-policy.jsx:90-95`): `loading`, `sent`, `error`, `accessRequested`, `accessGranted`, `ready`
- GETs and POSTs against `{clientRoot}/profile/rpc/api.php` (verified `rare-policy.jsx:126,321`) — the only file in `checklist-special/` using `fetch` directly instead of the shared `httpGet`/`httpPost` wrappers
- Back link reads a `refurl` query param

## Gotchas
- Hardcoded `characteristics[5].characters[1]` for nursery states, and hardcoded `cid 244` for heritage rankings — both brittle if the checklist's characteristic ordering changes upstream
- Two-stage init: data loads, then glossary tooltips attach, then any canned search from the URL is applied
- `Object.assign` used for immutable state updates

## Related
[[react-frontend]], [[react-common]], [[react-identify]], [[react-explore]]
