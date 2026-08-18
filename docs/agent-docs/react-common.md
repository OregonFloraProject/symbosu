---
name: react-common
description: "js/react/src/common — shared utilities, UI components, search widget, filter sidebar, search results, sidebar filter types"
---

## Utility Files (.js)
| File | Exports | Notes |
|------|---------|-------|
| `httpGet.js` | `httpGet(url)` -> Promise\<string\> | XMLHttpRequest wrapper |
| `httpPost.js` | `httpPost(url, mapParamString)` -> Promise\<string\> | XHR POST wrapper; only used by `explore-vendor.jsx` — see [[react-explore]] |
| `queryParams.js` | `getUrlQueryParams(url)`, `addUrlQueryParam(key, val)` | URL query string helpers |
| `taxaUtils.js` | `getTaxaPage`, `getRareTaxaPage`, `getGardenTaxaPage`, `getGardenPage`, `getImageDetailPage`, `getCommonNameStr`, `sortByTaxon`, `getChecklistPage`, `getIdentifyPage` | All return URL strings except the last two |
| `glossary.js` | `addGlossaryTooltips(text, glossary)` | Injects `<span class="glossary">` into HTML; calls the global `DOMPurify.sanitize()` (not imported) |
| `imageCarouselUtils.js` | `getOnDemandLazySlides(spec)` | Custom lazy-load helper for the patched react-slick |

## UI Components (.jsx)
| File | Export | Used by |
|------|--------|---------|
| `search.jsx` | `SearchWidget` (class) | header, home, explore, identify, checklist-special |
| `filterSidebar.jsx` | `SideBar` (class) | checklist-special |
| `searchResults.jsx` | `SearchResultContainer`, `SearchResult`, `CardSearchContainer`, `CardSearchResult`, `ExploreSearchResult`, `ExploreSearchContainer`, `IdentifySearchResult`, `IdentifySearchContainer`, `VendorUploadContainer` | explore, identify, checklist-special (variants per context) |
| `viewOpts.jsx` | `ViewOpts` (class); statics `DEFAULT_SEARCH_TEXT=''`, `DEFAULT_CLID=-1` | explore, identify |
| `loading.jsx` | `Loading` (function) | most pages |
| `searching.jsx` | `Searching` (function) | explore, identify, checklist-special |
| `pageHeader.jsx` | `PageHeader` (function) | most pages |
| `crumbBuilder.jsx` | `CrumbBuilder` (function) | taxa, inventory |
| `iconButton.jsx` | `IconButton`, `CancelButton` | multiple |
| `filterModal.jsx` | `FilterModal` (class) | identify, checklist-special (mobile filter drawer) |
| `modal.jsx` | `ImageModal` (class) | taxa |
| `imageCarousel.jsx` | `ImageCarousel` (function) | taxa |
| `imageModalCarousel.jsx` | `ImageModalCarousel` | taxa (dual-slider, nav1/nav2) |
| `formFields.jsx` | `TextField` (class) | explore-vendor |
| `textarea.jsx` | `TextareaField` | extends `TextField`, explore-vendor |
| `helpButton.jsx` | `HelpButton` (class) | deprecated (see gotcha below) |

## sidebar/ Sub-components
| File | Export | Purpose |
|------|--------|---------|
| `featureSelector.jsx` | `FeatureSelector` (function) | Dispatcher: renders `checkboxList`, `slider`, `sliderOld`, `selectDropdown`, or `groupFilter` based on a `display` prop; a `useNewSlider` prop switches slider version |
| `checkboxList.jsx` | `CheckboxList` (function) | Checkbox attribute filters, with optional external links |
| `slider.jsx` | `Slider` (function, hooks) | New Blueprint.js `RangeSlider`; used by checklist-special |
| `sliderOld.jsx` | `PlantSlider` (class) | Legacy slider; used by identify |
| `selectDropdown.jsx` | `SelectDropdown` (function) | HTML select for exclusive choices |
| `groupFilter.jsx` | `GroupFilter` (function) | Button group for category filtering |

## Key Patterns
- No Context API anywhere — all state passed via prop drilling
- `SearchWidget` throttles via `lodash.throttle` at 500ms (verified `search.jsx:2,51`); closes suggestions on `onBlur` using `e.relatedTarget`
- `dangerouslySetInnerHTML` used throughout for charstate name/subheading HTML
- Feature selector element IDs generated via `.replace(/[^a-z]/g, '')` (strips all non-alpha characters)

## Gotchas
- `DOMPurify` is a global (loaded by the PHP page, not imported here)
- `helpButton.jsx` is explicitly marked deprecated in a comment: it requires Bootstrap's JS to function, but Bootstrap JS has been removed from the site (verified `helpButton.jsx:16-17`)
- Two slider implementations coexist: `sliderOld.jsx` (identify) vs `slider.jsx` (checklist-special) — see [[react-identify]], [[react-checklist-special]]
- FontAwesome icons registered per-file, not centralized

## Related
[[react-frontend]], [[react-header]], [[react-explore]], [[react-identify]], [[react-checklist-special]], [[react-inventory]], [[react-taxa]]
