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
├── common/               — shared utilities and UI components — [[react-common]]
│   └── sidebar/
└── less/                 — LESS source for all pages — [[react-less]]
```

## Build

- `npm run build` (production, minified) / `npm run devstart` (watch mode), run from `js/react/`
- `webpack.config.js` exports two config objects: `reactConfig` (JS) and `lessConfig` (LESS)

## Webpack bundle count (verified against js/react/webpack.config.js)

**15 JS entry points**, all but `header` itself declare `dependOn: 'header'`:
`header`, `footer`, `home`, `newsletters`, `whatsnew`, `checklist-special`, `inventory`, `identify`, `taxa`, `taxa-search`, `taxa-garden`, `taxa-rare`, `explore`, `explore-vendor`, `rare-policy`.

**7 LESS entry points** (separate config, own entry map): `theme`, `header`, `footer`, `garden`, `rare`, `taxa`, `inventory`.

Any memory elsewhere claiming "11 Webpack bundles" is wrong — correct as of this verification (2026-07-31) is 15 JS bundles + 7 LESS bundles (22 total Webpack entries across the two configs).

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
