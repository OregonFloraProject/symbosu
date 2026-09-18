# Tasks: Unified Taxa Profile Page

Status: complete

Conventions for all tasks:
- Every new component copies its markup, logic, and comments verbatim from the named old source; only genuine divergences are parameterized per plan.md.
- Never modify an old entry file (`main.jsx`, `taxa-garden.jsx`, `taxa-rare.jsx`) or any file under `js/react/src/taxa/components/` or `js/react/src/common/` — they are read-only references.
- The project has no unit test infrastructure and no frontend lint. Per spec, verification is the webpack build: `npm run build` in `js/react/` (node_modules already installed).
- Existing shared imports to reuse as-is: `common/ImageGallery`, `common/ImageModal`, `common/Loading`, `common/glossary`, `common/taxaUtils`, `taxa/components/DescriptionTabs`, `MapItem`, `SynonymItem`, `SideBarSectionVendor`, `SideBarSectionLookalikesTable`, `SideBarSectionSpeciesList`, `taxa/utils.js`, `taxa/constants`, `checklist-special` `ExplorePreviewModal`.

## Wave 1

- [x] T1: Shared data hooks (commit 5dca94e)
  - Satisfies: AC-4, AC-5
  - Files: create `js/react/src/taxa/shared/useSlideshowCount.js`, `js/react/src/taxa/shared/useGlossary.js`, `js/react/src/taxa/shared/useTaxonApi.js`
  - Do: Three hooks. `useSlideshowCount()` returns `[slideshowCount, updateViewport]` with the byte-identical 5/4/3 breakpoint logic and `window.addEventListener('resize', ...)` (copy `main.jsx:58-67`; note rare also calls `updateViewport()` after data load, so expose the function too). `useGlossary()` fetches `../glossary/rpc/getterms.php`, JSON-parses, sets state, logs and continues on error (copy `taxa-rare.jsx:140-149`). `useTaxonApi(tid, type)` fetches `./rpc/api.php?taxon=${tid}` plus `&type=${type}` when given, returns `{ data, apiError, isLoading }` semantics matching each old page: parse JSON, error flag on throw, loading cleared in finally (copy the fetch shapes from `main.jsx:68-216`, `taxa-garden.jsx:73-198`, `taxa-rare.jsx:62-155`).
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: the three files exist, import cleanly in a scratch render, and `npm run build` in `js/react/` passes.

- [x] T2: Page shell and hero image (commit cf9a700)
  - Satisfies: AC-4, AC-5
  - Files: create `js/react/src/taxa/shared/TaxaPageShell.jsx`, `js/react/src/taxa/shared/ProfileHeroImage.jsx`
  - Do: `TaxaPageShell` renders Loading + `.print-header` + `.row.print-start` (title block + Print button) + `.row.mt-2.main-wrapper` 8/4 grid per plan.md props `{ containerClassName, wrapperClassName, mainClassName, pageTitle, titleBlock, sidebar, isLoading, clientRoot, children }`. Copy structure verbatim from `TaxaMainComponents.jsx:63-86` (chooser), `TaxaMainComponents.jsx:186+` (detail), `taxa-garden.jsx:204-223`, `taxa-rare.jsx:180-198`. The print-header shows `{pageTitle}` + `<br/>` + `{window.location.href}` in all cases. `ProfileHeroImage({ image, alt })` renders `<figure><div className="img-main-wrapper"><img id="img-main" src={image.url} alt={alt}/></div><figcaption>{image.photographer}</figcaption></figure>` (copy `taxa-rare.jsx:210-216`).
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: both files exist and `npm run build` passes.

- [x] T3: Unified image gallery (commit 160c7ef)
  - Satisfies: AC-4, AC-5
  - Files: create `js/react/src/taxa/shared/TaxaImageGallery.jsx`
  - Do: Wraps `common/ImageGallery` + `common/ImageModal` with encapsulated modal state. Two modes: single-basis (garden: one gallery, modal toggles `{currImage, isOpen}` — copy `taxa-garden.jsx:57-64,239-259`) and dual-basis (core/rare: two galleries, modal images switch on basis — copy `taxa-rare.jsx:167-171,224-256` and `TaxaMainComponents.jsx:144-155,261-281`). Do NOT pass `imageCount` to `ImageGallery` on any variant (dead prop; `ImageGallery` derives counts from `images.length` — the spec-mandated garden quirk fix). Preserve each variant's title/altname strings exactly.
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: file exists, exposes props covering garden/core/rare usage, and `npm run build` passes.

- [x] T4: Unified sidebar section (commit 6876b41)
  - Satisfies: AC-4, AC-5
  - Files: create `js/react/src/taxa/shared/RelatedBorderedItem.jsx`, `js/react/src/taxa/shared/SidebarSection.jsx`
  - Do: `RelatedBorderedItem({ value, rankId, variant })` with the two anchor variants: rare style `<a className="related-link" target="_blank" rel="noreferrer">` (`SideBarSection.jsx:77-104`) vs main style bare `<a>` (`SideBarSectionForMain.jsx:115-142`). `SidebarSection({ title, items, variant, classes, isTaxaRare, glossary, rankId, clientRoot })` per plan.md: shared shell (showItem filter, `sidebar-section` + `d-none`-when-empty + `mb-4` (rare variant) vs `mb-5` + classes (main variant), `h3.text-light-green`, trailing dashed-border span) and per-key dispatch covering: `Related`, `status` (OrderedObjectBorderedItem for rare variant — copy `SideBarSection.jsx:47-75`; conservation_status object branch for main variant — copy `SideBarSectionForMain.jsx:14-51`), `synonyms`/`Synonyms` -> `SynonymItem` with the `isTaxaRare` bottom placement (`SideBarSection.jsx:134-152`), `webLinks` -> SingleBorderedItem (`SideBarSectionForMain.jsx:94-113`), `More info` -> MoreInfoItem with PDF-aware buttons (`SideBarSectionForMain.jsx:53-92`), fallback -> the variant-appropriate BorderedItem (rare: glossary-tooltipped KEY_NAMES labels, no py-2 — `SideBarSection.jsx:12-38`; main: raw labels, py-2 + border-item ul class — `SideBarSectionForMain.jsx:10-51`).
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: both files exist, dispatch covers every key used by the three old pages, and `npm run build` passes.

- [x] T5: PHP flag and route wiring (commit cb61bd3)
  - Satisfies: AC-2, AC-6
  - Files: modify `config/symbini.php`, `config/symbini_template.php`, `taxa/index.php`, `taxa/garden.php`, `taxa/rare.php`
  - Do: Add `$TAXA_UNIFIED_FLAG = 0;` to the OregonFlora feature flags section of both config files (next to `$RPG_FLAG`, line ~170). In each PHP page, wrap the existing bundle script include in `if (isset($TAXA_UNIFIED_FLAG) && $TAXA_UNIFIED_FLAG === 1) { load js/react/dist/taxa-unified.js with filemtime cache-bust } else { existing include unchanged }`. In `rare.php` the conditional must sit inside the existing `$RPG_FLAG === 1` gate so the login redirect and gating are untouched. Mount divs stay as-is. Old bundles keep loading when the flag is off.
  - Tests: none (no PHP test infrastructure; verified by php -l syntax check on the five files)
  - Done when: `php -l` passes on all five files, the flag defaults to 0, and with the flag off each page's emitted script tag is byte-identical to before.

## Wave 2

- [x] T6: Core variant component (commit 28f46e3)
  - Satisfies: AC-3, AC-4
  - Files: create `js/react/src/taxa/unified/UnifiedTaxaCore.jsx`
  - Do: Function component receiving `{ tid, defaultTitle, clientRoot, synonym }`, wired to T1 hooks (no `type` param). Copy the core data mapping verbatim from `main.jsx:85-216` into the hook result shape: parent/child Related URLs, moreInfo building (rare/garden profile buttons via `getRareTaxaPage`/`getGardenTaxaPage`), taxalinks filtering (ipni/usda) into weblinks JSX array (`main.jsx:119-157`), Status conservation rows. Render: `rankId <= RANK_GENUS` (and null while loading) -> chooser layout (copy `TaxaChooser` render `TaxaMainComponents.jsx:54-130` incl. SppItem grid; SppItem copy goes in this file or a sibling in `unified/`); else detail layout (copy `TaxaDetail` render `TaxaMainComponents.jsx:156-300` incl. ambiguous-synonyms h2 handling, hero via T2, DescriptionTabs, dual-basis gallery via T3, sidebar via T4 `variant="main"`, MapItem). Title side-effect: `defaultTitle + ' ' + sciName` (chooser uses family fallback: `main.jsx` chooser title logic `TaxaMainComponents.jsx:56-58`).
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: file exists, imports resolve, and `npm run build` passes.

- [x] T7: Garden variant component (commit 0cbcec3)
  - Satisfies: AC-3, AC-4
  - Files: create `js/react/src/taxa/unified/UnifiedTaxaGarden.jsx`
  - Do: Function component `{ tid, defaultTitle, clientRoot }` using T1 hooks with `type=garden` plus the two chained fetches copied verbatim from `taxa-garden.jsx:150-173`: canned native groups (`${clientRoot}/garden/rpc/api.php?canned=true`, matched against `res.checklists`) and vendor availability (`${clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid=${tid}`). Data mapping verbatim: plantType/sizeMaturity/moisture/plantFacts/growthMaintenance (`taxa-garden.jsx:84-147`). Render copied from `taxa-garden.jsx:200-341` via T2 shell (garden classes: `container mx-auto pl-4 pr-4 pt-5`, h1 vernacular + h2 italic sciName), hero via T2, garden description paragraph with `addGlossaryTooltips`, single-basis gallery via T3 (no imageCount), native groups block with preview modal (`taxa-garden.jsx:262-321`, ExplorePreviewModal import from checklist-special), sidebar via T4 (variant 'rare' style) for Plant Facts / Growth and Maintenance, existing `SideBarSectionVendor` for Commercial Availability, Core profile page button via `getTaxaPage`. Title side-effect: `${defaultTitle} ${sciName}`.
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: file exists, imports resolve, and `npm run build` passes.

- [x] T8: Rare variant component (commit 6d84b4c)
  - Satisfies: AC-3, AC-4
  - Files: create `js/react/src/taxa/unified/UnifiedTaxaRare.jsx`
  - Do: Function component `{ tid, defaultTitle, clientRoot, synonym }` using T1 hooks with `type=rare`. Data mapping verbatim from `taxa-rare.jsx:62-152`: profile-8 Summary + first non-8/9 description filtering, checkNullThumbnailUrl on both bases, context/surveyManage shaping, elevationToString, lookalikes/associatedSpecies, accessRestricted, legacyFactSheetUrl. Render copied from `taxa-rare.jsx:179-302` via T2 shell (rare classes: `container mx-auto py-5`, `profile-type` div + hr, h1 italic sciName + h2 vernacular), apiError alert, hero with herbarium fallback (`images[0] ?? herbariumImages[0]` logic at `taxa-rare.jsx:173-177`) via T2, DescriptionTabs in `.taxa-prose`, dual-basis gallery via T3, sidebar via T4 (variant 'rare', `isTaxaRare` on Context), MapItem with needsPermission, Survey & Manage, Look-Alikes (existing SideBarSectionLookalikesTable), Associated species (existing SideBarSectionSpeciesList), Core profile page + legacy fact-sheet buttons. Title side-effect: `${defaultTitle} - ${sciName} - Rare Plant Profile`.
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: file exists, imports resolve, and `npm run build` passes.

## Wave 3

- [x] T9: Unified entry, webpack slot, and build (commit f449ece)
  - Satisfies: AC-1, AC-3, AC-5
  - Files: create `js/react/src/taxa/main-unified.jsx`, modify `js/react/webpack.config.js`
  - Do: `main-unified.jsx` copies the bootstrap tail (`main.jsx:233-257`): read `react-header` data-props, locate whichever mount div exists (`react-taxa-app` / `react-taxa-garden-app` / `react-taxa-rare-app`), `getUrlQueryParams`, normalize `tid`->`taxon` ONLY for core and rare routes, `search` redirect to `./search.php`, else `window.location = '/'`. Variant detection from `window.location.pathname`: contains `garden.php` -> garden, `rare.php` -> rare, else core; render the matching Wave 2 component with `{ tid, defaultTitle, clientRoot, synonym }`. Webpack: add `'taxa-unified': { import: path.join(SRC_DIR, 'taxa', 'main-unified.jsx'), dependOn: 'header' }` following the existing entry style.
  - Tests: none (no unit test infrastructure; verified via webpack build)
  - Done when: `npm run build` in `js/react/` succeeds and produces `js/react/dist/taxa-unified.js` alongside the unchanged old bundles.

- [x] T10: Playwright testing plan (commit d8b1485)
  - Satisfies: AC-7
  - Files: create `docs/agent-docs/specs/260918-unified-taxa-page/playwright-plan.md`
  - Do: A runnable manual/automated test plan for the user to execute later against a running site with the unified flag on: per-route (core/garden/rare) parity checks against the old pages (flag off in another session or another server), covering title side-effect, h1/h2 order, hero image, description tabs, both carousel bases + modal open/close, sidebar sections incl. vendor/lookalikes/associated species, MapItem restricted overlay, core-profile cross buttons, redirects (no tid -> `/`, `search` param -> search page, `tid=` alias on core/rare but not garden), genus/family chooser layout on core, resize breakpoints (1200/992) slideshow counts, print button fires `window.print`, flag on/off toggle behavior per page, and the removed `imageCount` (no visual difference in garden gallery). List concrete selectors (mount div ids, `#img-main`, `.sidebar-section`, `.taxa-slideshows`, `.print-trigger`) and sample tids per route where known.
  - Tests: none (the deliverable IS the test plan)
  - Done when: the plan covers every AC-4 parity item and all edge cases from spec.md, with concrete steps.

## Coverage
- AC-1: T9
- AC-2: T5
- AC-3: T9, T6, T7, T8
- AC-4: T1, T2, T3, T4, T6, T7, T8
- AC-5: T1, T2, T3, T4, T9
- AC-6: T5
- AC-7: T10
