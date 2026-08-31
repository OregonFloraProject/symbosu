---
name: arch-page-entry-points
description: "Verified table of PHP pages, their Webpack bundles, React entry files, mount divs, and RPC endpoints"
---

# Page Entry Points

All entries below were verified against `js/react/webpack.config.js` and the actual PHP files on
2026-07-31 (repo state at that date). Where the previous version of this memory was wrong, the
correction is noted.

## Webpack Bundles

`js/react/webpack.config.js` defines **15 React entry bundles** (not 11 as previously recorded),
all `dependOn: 'header'` except `header` itself:

`header`, `footer`, `home`, `newsletters`, `whatsnew`, `checklist-special`, `inventory`,
`identify`, `taxa`, `taxa-search`, `taxa-garden`, `taxa-rare`, `explore`, `explore-vendor`,
`rare-policy`.

Output goes to `js/react/dist/`. A separate `lessConfig` in the same webpack config compiles 7
LESS entries (`theme`, `header`, `footer`, `garden`, `rare`, `taxa`, `inventory`) to
`/css/compiled/`; see [[react-less]] for the LESS side.

`header` has no `dependOn` and is implicitly the shared base; every other bundle declares
`dependOn: 'header'` in the webpack config, confirming it must load first.

## Page -> Template -> Bundle -> RPC table

| Page | PHP file | Mount div | Bundle (`dist/*.js`) | React entry | RPC endpoint(s) actually called |
|---|---|---|---|---|---|
| Home | `/index.php` | `react-home-app` | `home` | `js/react/src/home/main.jsx` | `home/rpc/api.php` (no params; always returns `{news, events}`) |
| Newsletters archive | `/newsletters/index.php` | - | `newsletters` | `js/react/src/home/newsletters.jsx` | - |
| News/events | `/pages/news-events.php` | - | `whatsnew` | `js/react/src/home/whatsnew.jsx` | - |
| Taxon profile (main) | `/taxa/index.php` | `react-taxa-app` | `taxa` | `js/react/src/taxa/main.jsx` | `taxa/rpc/api.php` |
| Taxon profile (garden variant) | `/taxa/garden.php` | - | `taxa-garden` | `js/react/src/taxa/taxa-garden.jsx` | `taxa/rpc/api.php?type=garden` |
| Taxon profile (rare variant) | `/taxa/rare.php` | - | `taxa-rare` | `js/react/src/taxa/taxa-rare.jsx` | `taxa/rpc/api.php?type=rare` (gated by `RPG_FLAG`) |
| Taxon search results | `/taxa/search.php` | `react-taxa-search-app` | `taxa-search` | `js/react/src/taxa/search.jsx` | `taxa/rpc/api.php?search=...` |
| Grow Natives (garden) main | `/garden/index.php` | - | `checklist-special` | `js/react/src/checklist-special/main.jsx` | `garden/rpc/api.php` |
| Rare Plant Guide main | `/rare/index.php` | - | `checklist-special` | `js/react/src/checklist-special/main.jsx` | `rare/rpc/api.php` |
| Rare policy page | `/rare/policy.php` | - | `rare-policy` | `js/react/src/checklist-special/rare-policy.jsx` | - |
| Inventory/project browser | `/projects/index.php` | `react-inventory-app` | `inventory` | `js/react/src/inventory/main.jsx` | `projects/rpc/api.php?pid=...` or `?search=...` |
| Checklist browse (read-only) | `/checklists/checklist.php` | `react-explore-app` | `explore` | `js/react/src/explore/explore.jsx` | `checklists/rpc/api.php?clid=...` |
| Checklist vendor management (editable) | `/checklists/checklistvendor.php` | `react-explore-vendor-app` | `explore-vendor` | `js/react/src/explore/explore-vendor.jsx` | `checklists/rpc/api.php` |
| Interactive plant key | `/ident/key.php` | `react-identify-app` | `identify` | `js/react/src/identify/identify.jsx` | `ident/rpc/api.php` (params `clid`, `dynclid`) |
| Occurrence/collection map | `/collections/map/index.php` | (none - not React) | none | - | Legacy jQuery/JS: `js/symb/collections.map.index.js` + `js/symb/collections.map.index.OregonFlora.js`. See [[map-module]] |

### Corrections from the prior (unverified) version of this memory
- There are **15** Webpack bundles, not 11.
- The interactive plant key is served from `/ident/key.php`, not `/checklists/dynamicmap.php`.
  Its RPC endpoint is `/ident/rpc/api.php`, not `/checklists/rpc/api.php`. `checklists/dynamicmap.php`
  exists in the repo but does not load the `identify` bundle.
- `taxa-garden` and `taxa-rare` are served from dedicated files `/taxa/garden.php` and
  `/taxa/rare.php`, not from `/taxa/index.php?special=garden|rare`.
- The vendor-editable checklist view is served from a dedicated file
  `/checklists/checklistvendor.php`, not from `/checklists/checklist.php?vendor=1`.
- `/projects/detail.php` does not exist; `projects/index.php` is the only project page found and
  dispatches on `pid` in `projects/rpc/api.php`.
- RPC endpoints do **not** use a `queryType` GET parameter and do **not** wrap responses in a
  `{status: success|error, data: ...}` envelope. Each endpoint dispatches on the presence of
  specific params and echoes `json_encode($result)` directly. Verified by reading
  `taxa/rpc/api.php`, `checklists/rpc/api.php`, `projects/rpc/api.php`, `home/rpc/api.php`,
  `garden/rpc/api.php`.

## RPC Endpoint Summary (verified dispatch params)

| Endpoint | Dispatches on | Notes |
|---|---|---|
| `taxa/rpc/api.php` | `search`, `taxon` (+ optional `type=rare\|garden`), `family`, `genus`, `synonym` | `type=rare` also requires `$RPG_FLAG === 1` |
| `checklists/rpc/api.php` | `clid`, `dynclid` | Used by both the read-only explore bundle and (per its file comment) the vendor bundle |
| `ident/rpc/api.php` | `clid`, `dynclid` | Backs the `identify` bundle |
| `projects/rpc/api.php` | `search`, `pid` | |
| `garden/rpc/api.php` | `canned=true` (canned search carousel) | Other query modes exist but were not further enumerated |
| `rare/rpc/api.php` | taxa-search style params (function `getTaxa($params)` present) | |
| `rare/rpc/autofillsearch.php` | autocomplete | File confirmed to exist |
| `webservices/autofillsearch.php`, `webservices/autofillsciname.php`, `webservices/autofillvernacular.php` | autocomplete | All three confirmed to exist (prior memory listed only one generic "global autocomplete" endpoint) |
| `home/rpc/api.php` | none - always returns `{news, events}` | No query-parameter dispatch at all |

## Gotcha: RPC response format is not standardized

Every RPC script observed builds a plain PHP array and does
`echo json_encode($result, JSON_NUMERIC_CHECK | JSON_INVALID_UTF8_SUBSTITUTE)` with no common
envelope, no `status` field, and no consistent error shape. Frontend code consuming these
endpoints must handle raw, per-endpoint response shapes rather than a uniform contract.

## Gotcha: pagination parameters are not standardized

Different RPC endpoints and manager methods use different pagination conventions (e.g. `page`
found in checklist result building vs `limit`/`offset`-style access elsewhere); there is no single
pagination parameter naming convention enforced across endpoints. Check the specific endpoint
before assuming `limit`/`offset` or `page`/`pageSize`.

## Related
[[arch-overview]], [[react-frontend]], [[react-less]], [[map-module]], [[php-manager-classes]]
