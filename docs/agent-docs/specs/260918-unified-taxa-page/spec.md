# Spec: Unified Taxa Profile Page

Status: verified
Request: "I want to refactor taxa, taxa-garden, taxa-rare to be one taxa page, with shared components. taxa, taxa-garden, taxa-rare have many duplicated components that can be simplified into singular components. The new, unified taxa page: Should display with different layout depending on route /index.php, /garden.php, /rare.php. Its components are now made up from shared components. Should have absolutely no change in function, layout compared to the old separated pages. Should be a new page, main-unified.jsx, separated from taxa, taxa-garden, taxa-rare. This is to make sure we can revert back to the old taxa pages as the unified page goes through user testing. Slot the unified one to js/react/webpack.config.js configs. Spec-driven development specific: The React frontend of this project does not have lint. Test development work through build, then draft a playwright testing plan, so I can test it later."

## Overview

The three taxon profile pages (core `main.jsx`, garden `taxa-garden.jsx`, rare `taxa-rare.jsx`) duplicate large amounts of markup and logic: bootstrap, viewport/slideshow count, page shell, hero image, gallery wiring, and sidebar sections. This feature builds ONE new unified page, `main-unified.jsx`, composed of new shared components, that renders the core, garden, or rare layout depending on the served route. The old pages remain untouched and instantly revertable via a server-side config constant, so the unified page can go through user testing with zero risk.

## Decisions (from interview)

- Serving: a PHP config constant (following the existing `$RPG_FLAG` pattern in `config/symbini.php`) switches all three routes (`/taxa/index.php`, `/taxa/garden.php`, `/taxa/rare.php`) to the unified bundle at once. Flag off (default) = old pages. Flip back to revert.
- Old files: the refactor creates only NEW files. `main.jsx`, `taxa-garden.jsx`, `taxa-rare.jsx`, and every file they import stay byte-identical.

## User Stories

- As a developer, I want one unified taxa page composed of shared components, so that the duplicated markup and logic across the three profile pages is maintained in one place going forward.
- As a site admin, I want a single server-side config constant that switches all three taxon routes to the unified page, so that I can enable it for user testing and revert instantly by flipping it back.
- As a visitor, I want the unified page to look and behave identically to the old page on each route, so that user testing introduces no visible or functional change.

## Acceptance Criteria

- AC-1: A new React entry `js/react/src/taxa/main-unified.jsx` exists and is slotted into `js/react/webpack.config.js` as a new bundle (e.g. `taxa-unified`), `dependOn: 'header'`, producing `js/react/dist/taxa-unified.js` via the project's build command.
- AC-2: With the config constant off (the default), `/taxa/index.php`, `/taxa/garden.php`, `/taxa/rare.php` behave exactly as before: same mount divs, same old bundles, and the old React entry files plus every file they import are byte-identical to before this feature.
- AC-3: With the config constant on, all three routes load the unified bundle, and the unified app renders the core, garden, or rare layout matching the route it is served from (`/taxa/index.php` -> core layout, `/taxa/garden.php` -> garden layout, `/taxa/rare.php` -> rare layout).
- AC-4: Behavior and layout parity per route. For each variant, the unified page reproduces the old page's rendered DOM (element structure, class names, ordering) and function, including: data fetching (`taxa/rpc/api.php` with `type=garden` / `type=rare` where the old pages send it), glossary fetch from `../glossary/rpc/getterms.php`, garden's extra native-groups and vendor calls, rare's profile-8 Summary description filtering, image carousel/modal wiring and basis toggling, hero image markup, sidebar sections and their content, title side-effect on `document.title`, print button, and the tid/search query-param redirects.
- AC-5: The unified page is composed of new shared components under `js/react/src/taxa/shared/` (bootstrap, slideshow-count hook, glossary hook, page shell, hero image, image gallery, unified sidebar section), reused across all three variants; page-specific logic (core's genus/family chooser branch, garden's native groups and vendor sections, rare's summary filter and survey/manage mapping) lives in the unified page's variant sections. No old file is modified.
- AC-6: The three PHP pages read the config constant and conditionally mount the unified bundle (with cache-busting `filemtime`) when it is on, following the existing `$RPG_FLAG` constant pattern in `config/symbini.php` (constant also added to `config/symbini_template.php` with the same default).
- AC-7: A Playwright testing plan is delivered at `docs/agent-docs/specs/260918-unified-taxa-page/playwright-plan.md`, covering per-route parity checks (core/garden/rare), the flag on/off toggle, redirect cases, and image modal interactions, so the user can execute it later against a running site.

## Edge Cases

- Missing or invalid `tid` (`tid === -1`): unified page redirects to `/`, same as the old pages.
- URL carries a `search` query param: redirects to the search flow, same as the old pages.
- Core variant with `rankId <= RANK_GENUS` (genus/family): renders the chooser layout, same as `TaxaChooser` in the old core page.
- Rare route when `RPG_FLAG` is off: the existing rare.php login redirect still applies in BOTH unified-flag states.
- Window resized across the 1200px and 992px breakpoints: slideshow image count updates, same as old pages.
- Known garden quirk fixed: the old garden page passes a dead `imageCount={this.state.length}` (undefined) prop; `ImageGallery` ignores `imageCount` entirely and derives counts from `images.length`, so the unified page omits the prop on all three variants with zero visual or functional difference. The old garden page keeps its quirk untouched.
- Empty image arrays: rare's fallback to herbarium images (`images[0] ?? herbariumImages[0]`) is preserved; core/garden keep their existing fallbacks.

## Non-Goals

- Modifying the old entry files (`main.jsx`, `taxa-garden.jsx`, `taxa-rare.jsx`) or any file they import.
- Making the unified page the default, or removing the old pages/bundles.
- Changes to PHP RPC endpoints, `TaxaManager`, or the API contract.
- New or changed LESS/CSS: the unified page reuses the existing compiled stylesheet classes as-is.
- Executing the Playwright tests (plan document only; the user runs it later).
- Fixing existing behavior quirks beyond removing the dead `imageCount` prop on the unified page; for everything else parity outranks cleanup.

## Open Questions

- Q-1: Exact name and default of the config constant (proposed: `$TAXA_UNIFIED_FLAG = 0` in `config/symbini.php` and `config/symbini_template.php`). (blocks: nothing; resolvable in Phase 2)

## Verification
Environment limits (recorded, not failures): no PHP interpreter, no headless browser, no jsdom in this container, so browser-driven end-to-end runs are deferred to `playwright-plan.md` (user runs it later). No TypeScript and no unit test framework exist in the project (both skipped with reason everywhere). An `eslint.config.mjs` does exist in `js/react/` (no `lint` npm script); all 12 new files pass `npx eslint` with 0 errors (1 warning: unused `synonym` prop in `UnifiedTaxaRare.jsx`, matching the old `TaxaRareApp` signature).

- AC-1: pass (`npm run build` exit 0; `dist/taxa-unified.js` fresh at 199006 B; entry `taxa-unified` with `dependOn: 'header'` in `webpack.config.js:74-77`; built bundle contains all three mount ids and the garden/rare route strings)
- AC-2: pass (`git status`/`git diff` empty for `main.jsx`, `taxa-garden.jsx`, `taxa-rare.jsx`, `taxa/components/`, `common/`; flag-off `else` branches additions-only vs baseline; `$TAXA_UNIFIED_FLAG = 0` in both configs)
- AC-3: pass (`main-unified.jsx:10-16` variant detection, `:18-27` mount lookup, `:35-37` tid normalization on core/rare only, `:41` tid===-1 guard, `:39-40`/`:51-52` fallbacks; all three variant files present)
- AC-4: pass (per-task parity spot-checks vs old sources in T1-T4 and T6-T9 test reports; `imageCount` omission verified rendering-identical against `common/imageGallery.jsx`; one real divergence found and fixed: missing `tid === -1` guard, corrected in `main-unified.jsx:41`)
- AC-5: pass (8 files in `taxa/shared/`, 3 in `taxa/unified/`; git log shows old files untouched by T1-T10)
- AC-6: pass (flag-gated `taxa-unified.js` with `filemtime` in all three PHP pages; mount divs unchanged; `rare.php` keeps the `$RPG_FLAG` gate and login redirect)
- AC-7: pass (`playwright-plan.md` exists; covers every AC-4 item and spec edge case incl. `R-TID-INVALID`; results table maps checks to ACs)
