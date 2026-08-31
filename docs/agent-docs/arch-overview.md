---
name: arch-overview
description: "OregonFlora top-level architecture, tech stack, and PHP/React integration pattern"
---

# OregonFlora Architecture Overview

## Identity
OregonFlora is a Symbiota portal (open-source biodiversity collection platform) customized for
Oregon vascular plants. Basis repo: BioKIC/Symbiota.

## Tech Stack

**Backend**: PHP, MySQL (via `mysqli`, see [[arch-legacy-patterns]]), Doctrine ORM 2.7.3
(`composer.json`), Composer.

**Frontend**: React 16.14, Webpack 5, LESS compiled to CSS. See [[react-frontend]] and
[[react-less]] for the frontend build in detail.

**Build tooling**: `js/react/package.json` npm scripts (`npm run build` / `npm run dev`), Composer
for PHP deps.

## Architecture Pattern

Hybrid: legacy Symbiota-style PHP templates with direct SQL coexist alongside newer Doctrine
ORM + React pages that talk over JSON RPC endpoints. See [[arch-legacy-patterns]] for the
specifics of where each style is used and the resulting gotchas.

## Separation of Concerns

1. **Data layer** - Doctrine entity classes in `/models/` (e.g. Taxa, Fmchecklists,
   Omoccurrences), lazy-loaded proxies generated to `/temp/proxies/` (see [[arch-build-deploy]]).
2. **Business logic layer** - PHP manager classes in `/classes/` (TaxaManager, IdentManager,
   InventoryManager, ExploreManager, OccurrenceMapManager, etc). See [[php-manager-classes]].
3. **Presentation layer** - PHP templates render an HTML shell with a React mount div; RPC
   endpoints under `<module>/rpc/api.php` expose manager data as JSON; React components consume
   them. Full page -> bundle -> RPC routing table: [[arch-page-entry-points]].

## Key Directories

| Directory | Purpose |
|-----------|---------|
| `/config/` | `symbini.php`, `dbconnection.php`, `SymbosuEntityManager.php` (Doctrine bootstrap) |
| `/classes/` | PHP manager classes, utilities, domain logic |
| `/models/` | Doctrine entity classes (ORM annotations) |
| `/js/react/` | React source (`src/`), Webpack config, build output (`dist/`) |
| `/css/compiled/` | Compiled LESS output |
| `/taxa/`, `/garden/`, `/rare/`, `/projects/`, `/checklists/`, `/collections/`, `/ident/` | Module directories, each typically with its own `rpc/` subfolder |
| `/webservices/` | Global RPC endpoints (autofill/autocomplete scripts) |
| `/temp/` | Doctrine proxies, API caches, uploads |

## Request Lifecycle (React page)

```
User -> PHP template (e.g. /taxa/index.php)
    -> renders HTML shell with a mount div (e.g. <div id="react-taxa-app"></div>)
    -> loads the page's Webpack bundle (e.g. /js/react/dist/taxa.js)
    -> React component calls its module's RPC endpoint (e.g. /taxa/rpc/api.php)
    -> PHP manager (e.g. TaxaManager) fetches data via Doctrine or mysqli
    -> RPC script echoes json_encode($result) directly (no envelope/status wrapper observed)
    -> React renders UI; further interaction triggers more RPC calls
```

Confirmed by reading `taxa/rpc/api.php`, `checklists/rpc/api.php`, `projects/rpc/api.php`,
`home/rpc/api.php`: none of them dispatch on a `queryType` parameter or wrap output in a
`{status, data}` envelope. Each dispatches on the presence of specific GET params (`taxon`,
`search`, `clid`, `dynclid`, `pid`, `canned`, etc) and echoes the result array directly. Do not
trust "queryType=..." style examples for this codebase's RPC calls; see
[[arch-page-entry-points]] for the verified per-endpoint parameter names.

## Configuration System

Three-tier: checked-in templates (`symbini_template.php`, `dbconnection_template.php`),
gitignored environment copies (`symbini.php`, `dbconnection.php`), and runtime feature flags
(e.g. `SOLR_MODE`, `RPG_FLAG` referenced in `taxa/rpc/api.php`).

## Notable Decisions

| Decision | Trade-off |
|----------|-----------|
| Hybrid modernization instead of full rewrite | Two ways to do most things; see [[arch-legacy-patterns]] |
| Doctrine ORM for newer entities | Requires proxy pre-generation in production, see [[arch-build-deploy]] |
| Webpack multi-entry build, one bundle per page | More build config, but smaller per-page payload |
| Manager classes as a business-logic layer | Extra indirection between Doctrine and JSON output |

## Gotcha: SOLR fallback to MySQL

If `SOLR_MODE` is enabled but the SOLR service is down, the map module falls back to MySQL
queries, which are slower. See [[map-solr-pipeline]] for the SOLR query pipeline and its bypass
conditions.

## Gotcha: hardcoded characteristic IDs

Numeric characteristic IDs (CIDs) are hardcoded in PHP for things like sunlight requirements;
changing the `kmcharacters` table requires corresponding code changes. See [[db-identkey-tables]].
