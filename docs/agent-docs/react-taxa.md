---
name: react-taxa
description: "js/react/src/taxa — taxon profile pages for species/genus (main), rare, garden, search results, and the flag-gated unified page"
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
| `main-unified.jsx` | Unified entry; dispatches to core/garden/rare variant by `window.location.pathname` |
| `unified/UnifiedTaxaCore.jsx` | Unified species/genus profile variant |
| `unified/UnifiedTaxaGarden.jsx` | Unified garden profile variant |
| `unified/UnifiedTaxaRare.jsx` | Unified rare profile variant |
| `shared/TaxaPageShell.jsx` | Shared page shell used by all unified variants |
| `shared/ProfileHeroImage.jsx` | Shared hero image block for unified variants |
| `shared/TaxaImageGallery.jsx` | Shared image gallery for unified variants |
| `shared/RelatedBorderedItem.jsx` | Shared bordered related-item row for unified variants |
| `shared/SidebarSection.jsx` | Shared sidebar with `rare` and `main` variants |
| `shared/useTaxonApi.js` | Shared taxon fetch hook for unified variants |
| `shared/useGlossary.js` | Shared glossary fetch hook for unified variants |
| `shared/useSlideshowCount.js` | Shared slideshow count hook for unified variants |

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

## Unified Page (flag-gated)

`main-unified.jsx` is a separate entry from `main.jsx`, `taxa-garden.jsx`, and `taxa-rare.jsx`.
The old entries and everything they import are unchanged. Variant dispatch lives in
`main-unified.jsx` via `window.location.pathname`: paths containing `garden.php` load
`UnifiedTaxaGarden`, paths containing `rare.php` load `UnifiedTaxaRare`, all other paths load
`UnifiedTaxaCore`. Shared UI and hooks live in `shared/` (`TaxaPageShell`, `ProfileHeroImage`,
`TaxaImageGallery`, `RelatedBorderedItem`, `SidebarSection` with `rare|main` variants,
`useTaxonApi`, `useGlossary`, `useSlideshowCount`). `/taxa/index.php`, `/taxa/garden.php`, and
`/taxa/rare.php` load `dist/taxa-unified.js` instead of their legacy bundle when
`$TAXA_UNIFIED_FLAG` is 1 (`config/symbini.php` and `config/symbini_template.php`; `rare.php` checks it inside the existing
`$RPG_FLAG` gate). The unified bundle is `taxa-unified` (`dependOn: 'header'`, builds
`dist/taxa-unified.js`). Deliberate behavior delta on the unified page only: the dead
`imageCount` prop is omitted (`common/imageGallery.jsx` never reads it).

## Related
[[react-frontend]], [[react-common]], [[react-explore]] (garden/vendor overlap), [[arch-page-entry-points]]
