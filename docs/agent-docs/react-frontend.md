---
name: react-frontend
description: "Comprehensive overview of js/react/src — structure, stack, build, and gotchas for the OregonFlora React layer"
---

## Overview

React frontend for OregonFlora (oregonflora.org), embedded in a PHP/Symbiota CMS. Webpack compiles multiple independent React apps + LESS stylesheets from `js/react/src/`. JS output -> `js/react/dist/`, CSS output -> `css/compiled/`.

## Tech Stack

- React 16.14 (legacy `ReactDOM.render`, class-based root components)
- Webpack 5 + Babel (preset-react, preset-env, class-properties plugin)
- LESS (compiled by a separate Webpack config from JS) - see [[react-less]]
- HTTP: custom `XMLHttpRequest` wrappers (`httpGet.js`, `httpPost.js`) - see [[react-common]]
- Maps: `@react-google-maps/api` + legacy `react-google-maps`
- Icons: FontAwesome (SVG core, registered per-file, not centralized)
- Carousel: `react-slick`, patched via patch-package (`postinstall: patch-package` in `js/react/package.json`, `react-slick` pinned to `^0.30.3`)
- Tables: `react-table` v7
- CSV: `papaparse`
- UI: `@blueprintjs/core` (minimal usage)

## Directory Structure (verified against js/react/src)

```
src/
├── header/             — main.jsx (sitewide nav)
├── footer/              — main.jsx (sitewide footer)
├── home/                 — main.jsx, whatsnew.jsx, newsletters.jsx
├── taxa/                — main.jsx, search.jsx, taxa-garden.jsx, taxa-rare.jsx, utils.js
│   ├── components/
│   └── constants/
├── explore/              — checklist explore + vendor nursery variant
├── identify/             — identify.jsx, sidebar.jsx
├── checklist-special/    — main.jsx (garden + rare), rare-policy.jsx
│   └── components/
├── inventory/            — main.jsx, table.jsx
├── taxonomic-checklist/   - main.jsx (grouped family/genus report search)
├── common/               — shared utilities and UI components — [[react-common]]
│   └── sidebar/
└── less/                 — LESS source for all pages — [[react-less]]
```

## Build

- `npm run build` (production, minified) / `npm run dev` (watch mode), run from `js/react/` (`js/react/package.json`)
- `webpack.config.js` exports two config objects: `reactConfig` (JS) and `lessConfig` (LESS)

## Webpack bundle count (verified against js/react/webpack.config.js)

**17 JS entry points**, all but `header` itself declare `dependOn: 'header'`:
`header`, `footer`, `home`, `newsletters`, `whatsnew`, `checklist-special`, `inventory`, `identify`, `taxa`, `taxa-search`, `taxa-garden`, `taxa-rare`, `taxa-unified`, `taxonomic-checklist`, `explore`, `explore-vendor`, `rare-policy`.

**8 LESS entry points** (separate config, own entry map): `theme`, `header`, `footer`, `garden`, `rare`, `taxa`, `inventory`, `taxonomic-checklist`.

Any memory elsewhere claiming "11 Webpack bundles" is wrong. The current configuration has 17 JS bundles + 8 LESS bundles (25 total Webpack entries across the two configs).

## PHP/React Integration Pattern

Props passed from PHP -> React via `data-props`-style JSON attributes on DOM mount points, read at `ReactDOM.render()` time. `clientRoot` (URL path string) is passed to virtually every component for building API/asset URLs. `googleMapKey` and `userName` also passed this way. See [[arch-page-entry-points]] for the full PHP template <-> bundle map.

## Key Architectural Patterns

1. Monolithic class-based root components — one large class component per page owns all state and API calls; no Redux/Context.
2. Sidebar + Results layout — `identify`, `explore`, `checklist-special` all use left filter sidebar + right results grid.
3. Filter state model: `{ searchText, attrs, sliders/ranges, checklist }` — `identify` uses `sliders`; `checklist-special` uses `ranges`.
4. TID-based virtual filtering — full taxon list loaded once; filter queries return matching `tids`; display layer hides non-matching cards without reloading.
5. `dangerouslySetInnerHTML` used throughout for PHP-rendered HTML content (descriptions, news). `glossary.js` calls the global `DOMPurify.sanitize()` (loaded by the PHP page, not imported/bundled).

## Conventions

- `.jsx` for React files; `.js` for pure utilities
- `PascalCase` for component files, `camelCase` for utilities and entry files
- `defaultProps` on most class components
- FontAwesome icons imported and registered at module top level per-file (not centralized)

## Taxonomic Checklist grouped search

`js/react/src/taxonomic-checklist/main.jsx` defines the hook-based `TaxonomicChecklist`
component mounted by `pages/taxonomic-checklist.php` at `react-taxonomic-checklist-app`.
It reads `clientRoot` from `react-header`'s `data-props` and appends the compiled
`taxonomic-checklist.css` stylesheet. Existing editorial content and download links remain
in this component.

- One query-only fetch to `${clientRoot}/pages/rpc/taxonomic-checklist.php?query=...` returns
  `{family:[{label,family}],genus:[{label,family}]}`. Each group has its own server-side
  20-result limit. See [[arch-page-entry-points]] for validation and query restrictions.
- The dropdown renders Family then Genus. Both sections start expanded with up to five
  links each. More reveals all fetched matches in its own group, up to 20, without another
  request. Collapsing a section preserves its More state until the next search.
- Each heading contains an independent arrow button exposing `aria-expanded` and
  `aria-controls`; its results container uses `hidden` when collapsed.
- If one group is empty while the other has results, it displays `No family matches.` or
  `No genus matches.`. If both are empty, the dropdown is hidden.
- Links retain server-provided labels and open
  `${clientRoot}/pages/pdfs/family/${encodeURIComponent(match.family)}.pdf` in a new tab
  with `rel="noreferrer"`.
- Input changes immediately hide previous results and invalidate outstanding responses.
  Nonblank input triggers a 500ms debounce; the search button and input focus search
  immediately. Searches trim input and accept lengths 3 through 100.
- Each `loadSuggestions()` call resets both sections to expanded/five, including searches
  triggered by input focus or the search button. Blank input changes hide results without
  scheduling a fetch.
- Focus moving between controls inside the search wrapper keeps suggestions open.
  Focus leaving the wrapper schedules dismissal after 150ms; focus returning inside cancels
  it. Dismissal invalidates outstanding responses.
- A request counter rejects stale successes and failures. Failed requests hide results and
  show `Search is temporarily unavailable.` in an `aria-live="polite"` paragraph for four
  seconds. Unmount cleanup clears debounce, blur and error timers and invalidates requests.

### Focused verification

- From the repository root: `php tests/TaxonomicChecklistRepositoryTest.php`.
  `tests/TaxonomicChecklistRepositoryTest.php` uses an isolated fake entity manager/query
  builder to check response shape, label mapping, separate queries and limits, prefix and
  accepted/checklist/authority constraints, PDF allowlist filtering and empty-list short
  circuiting. It does not connect to the database.
- From `js/react`: `node --test tests/taxonomic-checklist.test.cjs`.
  `js/react/tests/taxonomic-checklist.test.cjs` contains six tests using installed Babel
  tooling and a custom hook/render harness with controlled fetches and timers. Coverage
  includes query-only requests, group order/PDF links, independent five-to-20 More and
  collapse/reset behavior, effective visibility, empty groups, stale responses, errors,
  focus boundaries and unmount cleanup.
- The grouped-search verification record in
  `docs/agent-docs/plans/261116-taxonomic-checklist-grouped-search/TASKS.md` records both
  focused suites and targeted ESLint passing, plus desktop/mobile browser checks with live
  searches and explicitly simulated 20-result groups.
- The final post-commit `npm run build` succeeded after both implementation commits.
  `/tmp/opencode/taxonomic-checklist-final-build.log` records successful JS and LESS builds
  with an outdated `caniuse-lite` warning. The task verification record separates broader
  pre-existing ESLint failures and the unavailable typecheck from the passing focused checks.

## Notable Gotchas

1. React 16 — no concurrent mode, legacy `ReactDOM.render`
2. Mixed paradigm — root components are class-based; some function components with hooks mixed in (e.g. `taxa-rare.jsx`, `common/sidebar/slider.jsx`)
3. `sliderOld.jsx` is legacy — `identify` uses it; `checklist-special` uses new `slider.jsx` — see [[react-common]]
4. `checklist-special/main.jsx` serves both garden and rare pages, branching on a `pageType` prop
5. `DOMPurify` is a global dep — loaded by the PHP page, not bundled
6. `react-slick` is patched (patch-package) for custom lazy-load behavior
7. Duplicate `onSearch` logic (synonym-check API call) in `header/main.jsx` and `home/main.jsx` — both have warning comments about the duplication
8. `explore-vendor.jsx` is the only place in the codebase that uses `httpPost.js` — see [[react-explore]]
9. No routing library — navigation via `window.location`; PHP handles URL routing

## Gotcha: React bundle load order depends on header, enforced two ways

Verified against `js/react/webpack.config.js` and page templates (e.g. `taxa/index.php`):
- Every JS entry point except `header` itself declares `dependOn: 'header'` in the Webpack config, so Webpack's runtime chunk ordering requires the header bundle.
- Independently, PHP page templates `include("$SERVER_ROOT/header.php")` near the top of the page (e.g. `taxa/index.php:13`), which emits the `<script src=".../dist/header.js">` tag before the page's own bundle script tag appears later in the same template (e.g. `taxa/index.php:37` for `taxa.js`).
- Both mechanisms independently guarantee header loads first; there is no separate manual bundle-ordering step to maintain.
