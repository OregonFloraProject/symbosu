---
name: react-taxa
description: "js/react/src/taxa — taxon profile pages for species/genus (main), rare, garden, and search results"
---

## Files (verified against js/react/src/taxa)
| File | Purpose |
|------|---------|
| `main.jsx` | `TaxaApp`: species/genus profile; branches on `rankId` to `TaxaChooser` or `TaxaDetail` |
| `search.jsx` | `TaxaSearchResults`: search results page; auto-redirects on a single match |
| `taxa-rare.jsx` | `TaxaRareApp`: function component with hooks (the only hooks-based file in `taxa/`) |
| `taxa-garden.jsx` | `TaxaApp` (garden variant): garden plant profile |
| `utils.js` | `sortKeyedCharObject`, `csRangeToString`, `checkNullThumbnailUrl` |
| `constants/index.js` | `RANK_FAMILY=140`, `RANK_GENUS=180`, `CLID_RARE_ALL=14948`, `KEY_NAMES`, `SUB_KEY_LIST_ORDERS` |
| `components/TaxaMainComponents.jsx` | `TaxaChooser` (genus/family) + `TaxaDetail` (species) |
| `components/DescriptionTabs.jsx` | Tabbed descriptions with glossary integration (react-tabs) |
| `components/MapItem.jsx` | Distribution map; shows a restricted overlay when `needsPermission=true` |
| `components/SideBarSection.jsx` | Sidebar for rare profiles (includes glossary) |
| `components/SideBarSectionForMain.jsx` | Sidebar for main profiles (simpler, no glossary) |
| `components/SideBarSectionVendor.jsx` | Vendor/nursery availability sidebar |
| `components/SideBarSectionLookalikesTable.jsx` | Two-column lookalikes table |
| `components/SideBarSectionSpeciesList.jsx` | Associated species list |
| `components/SynonymItem.jsx` | Expandable synonyms + misapplied names, max 3 shown |
| `components/utils.js` | Component-local helpers (separate from `taxa/utils.js`) |

## API Calls
| File | Endpoint | Returns |
|------|----------|---------|
| `main.jsx` | `./rpc/api.php?taxon={tid}` | Full taxa data |
| `main.jsx` | `../glossary/rpc/getterms.php` | Glossary terms |
| `taxa-rare.jsx` | `./rpc/api.php?taxon={tid}&type=rare` | + conservation, ecoregion, counties, etc. |
| `taxa-garden.jsx` | `./rpc/api.php?taxon={tid}&type=garden` | + gardenDescription, characteristics |
| `taxa-garden.jsx` | `{clientRoot}/garden/rpc/api.php?canned=true` | Native group checklists |
| `taxa-garden.jsx` | `{clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid={tid}` | Commercial vendors |
| `search.jsx` | `./rpc/api.php?search={term}` | Array of matching taxa |

## Key Patterns
- `rankId` determines the render path: `RANK_GENUS` or lower -> `TaxaChooser`; higher -> `TaxaDetail`
- Images have 3 basis categories (`HumanObservation`, `PreservedSpecimen`, `LivingSpecimen`); `TaxaDetail` toggles between `HumanObservation` and `PreservedSpecimen`
- `checkNullThumbnailUrl()` mutates the image array in place to add fallback thumbnails
- `acceptedSynonyms` is rendered as a grid when there are multiple entries (ambiguous taxa)
- `SynonymItem` separates entries by `nomenclaturalStatus === 'misapplied'`
- Distribution map link: `{clientRoot}/collections/map/leafletmap.php?usethes=1&taxa={tid}&gridSizeSetting=30` (verified in `components/MapItem.jsx`)
- Distribution map image: `{clientRoot}/images/maps/{tid}.jpg`

## Gotchas
- `taxa-rare.jsx` filters descriptions by profile ID: profile 8 is used for the RPG summary, and both profile 8 and profile 9 are excluded from the general `taxonDescriptions` list (verified at `taxa-rare.jsx:81-87`)
- Garden profile has two modal types: an image modal and a preview modal for native groups

## Related
[[react-frontend]], [[react-common]], [[react-explore]] (garden/vendor overlap), [[arch-page-entry-points]]
