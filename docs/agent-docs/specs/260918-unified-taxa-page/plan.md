# Plan: Unified Taxa Profile Page

Status: approved
Spec: spec.md

## Approach

Add a new webpack entry `taxa-unified` that builds `js/react/src/taxa/main-unified.jsx` into `js/react/dist/taxa-unified.js`. That entry detects its variant from the served route (`pathname` contains `garden.php` -> garden, `rare.php` -> rare, otherwise core) and renders one of three variant components (`unified/UnifiedTaxaCore|Garden|Rare.jsx`) composed of new shared building blocks in `js/react/src/taxa/shared/`. A new config constant `$TAXA_UNIFIED_FLAG = 0` (symbini.php flag section, following `$RPG_FLAG`) makes the three PHP pages load the unified bundle instead of the old ones; flag off restores the old bundles with zero code path changes. Every shared component is a NEW file whose markup and logic are copied verbatim from the old files; no old file is modified except the three PHP pages' script-include block and the two config files.

## Affected Code

New React files (all satisfy AC-4 parity by copying from the named old source):

- `js/react/src/taxa/main-unified.jsx`: bootstrap + variant detection + dispatch (AC-1, AC-3)
  - Copy of the identical bootstrap tail in `main.jsx:233-257` / `taxa-garden.jsx:347-360` / `taxa-rare.jsx:305-329`, generalized: find whichever mount div exists (`react-taxa-app` / `react-taxa-garden-app` / `react-taxa-rare-app`), normalize `tid`->`taxon` only on the core and rare routes (the old garden page does not normalize, and parity requires skipping it there), `search` redirect, else render variant component
- `js/react/src/taxa/shared/useSlideshowCount.js`: the byte-identical `updateViewport` logic (5/4/3 by 1200/992 breakpoints) as a hook with `resize` listener (AC-4, AC-5; source `main.jsx:58-67`)
- `js/react/src/taxa/shared/useGlossary.js`: `../glossary/rpc/getterms.php` fetch, log-and-continue on error (AC-4, AC-5; source `taxa-rare.jsx:140-149`)
- `js/react/src/taxa/shared/useTaxonApi.js`: `./rpc/api.php?taxon=${tid}` with optional `type=garden|rare`, error flag, loading flag (AC-4, AC-5; sources `main.jsx:85`, `taxa-garden.jsx:77`, `taxa-rare.jsx:68`)
- `js/react/src/taxa/shared/TaxaPageShell.jsx`: Loading + `.print-header` + `.row.print-start` (title block + Print button) + `.row.mt-2.main-wrapper` 8/4 grid; props parameterize the class drift: `containerClassName` (core `container mx-auto py-5 taxa-detail` / garden `container mx-auto pl-4 pr-4 pt-5` / rare `container mx-auto py-5`), `wrapperClassName` (core chooser `row-cols-sm-2`), `mainClassName` (rare/core `pr-4`), `pageTitle`, `titleBlock` (per-variant h1/h2 JSX), `sidebar`, `isLoading`, `clientRoot` (AC-4, AC-5; sources `TaxaMainComponents.jsx:63-86,186+`, `taxa-garden.jsx:204-223`, `taxa-rare.jsx:180-198`)
- `js/react/src/taxa/shared/ProfileHeroImage.jsx`: `<figure><div className="img-main-wrapper"><img id="img-main"/>...<figcaption>{photographer}` (AC-4, AC-5; sources `TaxaMainComponents.jsx:220-226`, `taxa-garden.jsx:227-233`, `taxa-rare.jsx:210-216`)
- `js/react/src/taxa/shared/TaxaImageGallery.jsx`: `ImageGallery` + `ImageModal` with encapsulated modal state; supports single-basis (garden) and dual-basis (core/rare) modes. The dead `imageCount` prop is NOT passed: `ImageGallery` derives its counts from `images.length` internally, so omitting it is rendering-identical while removing the garden quirk (`imageCount={this.state.length}` -> undefined) (AC-4, AC-5; sources `taxa-garden.jsx:239-259`, `taxa-rare.jsx:224-256`, `TaxaMainComponents.jsx:144-155,261-281`, verified against `common/imageGallery.jsx`)
- `js/react/src/taxa/shared/RelatedBorderedItem.jsx`: both existing variants behind a `variant` prop, since the rare and main versions differ in anchor attributes (`className="related-link" target="_blank" rel="noreferrer"` vs bare `<a>`) and DOM parity requires both (AC-4, AC-5; sources `SideBarSection.jsx:77-104`, `SideBarSectionForMain.jsx:115-142`)
- `js/react/src/taxa/shared/SidebarSection.jsx`: ONE unified sidebar section. Shared shell: `showItem` filter, `sidebar-section` wrapper + `d-none`-when-empty, `h3.text-light-green` title, trailing dashed-border span. Per-key item dispatch covering both old variants: `Related` -> `RelatedBorderedItem`, `status` -> ordered-status item, `synonyms`/`Synonyms` -> `SynonymItem` (including the `isTaxaRare` bottom placement), `webLinks` -> single-bordered list, `More info` -> more-info buttons (PDF-aware), fallback -> `BorderedItem`. Genuine DOM divergences stay as internal `variant` ('rare' | 'main') branches: outer spacing (`mb-4` vs `mb-5` + `classes` prop), and `BorderedItem` flavor (rare: glossary-tooltipped `KEY_NAMES` label, no `py-2`; main: raw label, `py-2` + `border-item` class, inline `conservation_status` branch). Props: `{ title, items, variant, classes, isTaxaRare, glossary, rankId, clientRoot }`. Used by all three variants (garden: Highlights/Plant Facts/Growth; rare: Context/Survey & Manage; core: Context/Web links) (AC-4, AC-5; sources `SideBarSection.jsx` + `SideBarSectionForMain.jsx`)
- `js/react/src/taxa/unified/UnifiedTaxaCore.jsx`: core variant; `rankId <= RANK_GENUS` chooser branch (SppItem grid, no sidebar MapItem) and species detail branch, including the moreInfo/taxalinks/weblinks building logic from `main.jsx:105-176` (AC-3, AC-4)
- `js/react/src/taxa/unified/UnifiedTaxaGarden.jsx`: garden variant; native-groups canned preview (`ExplorePreviewModal` wiring), vendor availability chained calls, garden description paragraph (AC-3, AC-4)
- `js/react/src/taxa/unified/UnifiedTaxaRare.jsx`: rare variant; profile-8 Summary filtering, surveyManage mapping, lookalikes/associated species sections, `Core profile page` + legacy fact-sheet buttons (AC-3, AC-4)

Modified files:

- `js/react/webpack.config.js`: new entry `taxa-unified: { import: path.join(SRC_DIR, 'taxa', 'main-unified.jsx'), dependOn: 'header' }` (AC-1)
- `taxa/index.php`, `taxa/garden.php`: wrap the existing script include in `if ($TAXA_UNIFIED_FLAG === 1) { include taxa-unified.js with filemtime cache-bust } else { existing include }` (AC-2, AC-6)
- `taxa/rare.php`: same conditional, placed inside the existing `$RPG_FLAG === 1` gate so the RPG login redirect behavior is untouched (AC-2, AC-6)
- `config/symbini.php` + `config/symbini_template.php`: `$TAXA_UNIFIED_FLAG = 0;` in the OregonFlora feature flags section next to `$RPG_FLAG` (AC-6)

Deliverable document:

- `docs/agent-docs/specs/260918-unified-taxa-page/playwright-plan.md` (AC-7)

## Data Model and Contracts

- Variant detection: `window.location.pathname` contains `garden.php` -> `'garden'`; contains `rare.php` -> `'rare'`; otherwise `'core'` (default branch, covers `/taxa/index.php` and `/taxa/`).
- Mount divs: unchanged per route (`react-taxa-app` / `react-taxa-garden-app` / `react-taxa-rare-app`); the unified bootstrap locates whichever exists.
- Props into each variant component: `{ tid, defaultTitle, clientRoot, synonym }`, identical to the old entry components.
- RPC contract: unchanged. Same endpoints, same `type=garden`/`type=rare` params, same raw (non-enveloped) JSON.
- TaxaPageShell props: `{ containerClassName, wrapperClassName, mainClassName, pageTitle, titleBlock, sidebar, isLoading, clientRoot, children }`.

## Libraries

No new libraries. All code is copied verbatim from in-repo usage of `react@16.14` (hooks), `react-slick`, `react-tabs`, `react-scroll`, `react-fontawesome`, and `slick-carousel` CSS. Because the requirement is byte-level parity with existing in-repo React 16 code and no new library APIs are introduced, in-repo source is the authoritative API reference for this feature; no external doc fetch is needed.

## Risks

- DOM drift breaking parity: mitigation is copy-verbatim implementation, and the Playwright plan includes per-route DOM/class comparison steps against the old pages.
- Garden quirk (dead `imageCount` prop): removed on the unified page; `ImageGallery` never reads it, so the rendered output is identical. Old pages keep their quirk untouched.
- Rare PHP interplay: the unified bundle load must sit inside the existing `$RPG_FLAG` gate so the redirect behavior is identical in both flag states.
- Build environment: `js/react` needs `npm install` (postinstall `patch-package`) before `npm run build`; verification will install if `node_modules` is missing.
- Route-detection edge (URLs without `.php`): core is the default branch, so any unexpected path still renders the core layout exactly as `/taxa/index.php` would.
