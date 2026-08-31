# OregonFlora Codebase Memory Index

Naming: `<domain>-<topic>.md`, kebab-case, `name:` frontmatter always equals the filename
(wikilinks resolve against `name:`). Domains: `arch-`, `db-`, `php-`, `react-`, `map-`, `ident-`, `taxo-`.

## Architecture & Routing
- [Architecture Overview](docs/agent-docs/arch-overview.md) — Hybrid PHP/React on a Symbiota base, Doctrine ORM, request lifecycle
- [Page Entry Points](docs/agent-docs/arch-page-entry-points.md) — Single owner of the page → template → bundle → RPC table; 15 Webpack bundles
- [Build & Deploy](docs/agent-docs/arch-build-deploy.md) — Doctrine proxy generation to `temp/proxies`, LESS/Webpack build steps
- [Security & Permissions](docs/agent-docs/arch-security-permissions.md) — `$USER_RIGHTS` structure, real right keys, `$IS_ADMIN`
- [Legacy Patterns](docs/agent-docs/arch-legacy-patterns.md) — MySQLi vs Doctrine coexistence, `MySQLiConnectionFactory` read/write split, `resolve_img_path()`
- [CQL Deprecated](docs/agent-docs/arch-cql-deprecated.md) — CQL is dead code since `ea3c24f29`

## Database Schema
- [Taxonomy Tables](docs/agent-docs/db-taxonomy-tables.md) — Taxa, Taxstatus, Taxavernaculars, Taxalinks, Taxadescrblock/stmts, Taxaenumtree
- [Checklist Tables](docs/agent-docs/db-checklist-tables.md) — Fmchecklists (`type`/`dynamicsql`), Fmchklsttaxalink, Fmprojects, Fmvouchers
- [Occurrence Tables](docs/agent-docs/db-occurrence-tables.md) — Omoccurrences, Omcollections; `tidinterpreted`/`decimalLatitude`/`recordSecurity`
- [Ident Key Tables](docs/agent-docs/db-identkey-tables.md) — Kmcharacters, Kmcs, Kmdescr (4-col PK), Kmcharheading

## PHP Backend
- [Manager Classes](docs/agent-docs/php-manager-classes.md) — Which managers exist, responsibilities, the two real dispatch patterns
- [Flow: Taxon Lookup](docs/agent-docs/php-flow-taxon-lookup.md) — `taxa/rpc/api.php?taxon=<tid>`
- [Flow: Search](docs/agent-docs/php-flow-search.md) — 2-stage exact-then-LIKE; synonym resolution
- [Flow: Checklist Taxa](docs/agent-docs/php-flow-checklist-taxa.md) — Delegates to `IdentManager::setTaxa()`

## Identification Key
- [Key Filter Flow](docs/agent-docs/ident-key-filter-flow.md) — `ident/key.php?clid=` → `identify.js`
- [Key Editing](docs/agent-docs/ident-key-editing.md) — kmcs character-state edits: chardetails.php vs editor.php/matrixeditor.php
- [setTaxa Flow](docs/agent-docs/ident-settaxa-flow.md) — How the key's taxa query is built

## Taxonomy
- [Acceptance Status](docs/agent-docs/taxo-acceptance-status.md) — Derived from taxstatus rows, not a stored flag
- [Tax Authority](docs/agent-docs/taxo-authority.md) — `taxauthid` scoping; hardcoded to 1 throughout `TaxaManager`

## Mapping
- [Map Module](docs/agent-docs/map-module.md) — Hub: entry point, architecture, security roles
- [SOLR Pipeline](docs/agent-docs/map-solr-pipeline.md) — Server-side since `ea3c24f29`; `MAX_RECORD_COUNT = 20000`
- [Click & KML Flow](docs/agent-docs/map-click-kml-flow.md) — Marker popup; KML export, incl. the unsecured-query export bug

## React Frontend
- [React Frontend](docs/agent-docs/react-frontend.md) — Hub: stack, build, 15 bundles all `dependOn: 'header'`
- [header](docs/agent-docs/react-header.md) — Sitewide nav: scroll animation, dropdowns, mobile menu, search
- [footer](docs/agent-docs/react-footer.md) — Stateless footer, dynamic copyright year
- [home](docs/agent-docs/react-home.md) — Carousel/search, news/events, newsletters
- [taxa](docs/agent-docs/react-taxa.md) — species/genus, rare, garden, search results
- [explore](docs/agent-docs/react-explore.md) — Checklist browse + vendor management; `showNotes` lives on an unmerged branch
- [identify](docs/agent-docs/react-identify.md) — Interactive plant key UI
- [checklist-special](docs/agent-docs/react-checklist-special.md) — Grow Natives + Rare Plant Guide
- [inventory](docs/agent-docs/react-inventory.md) — Project browser; `pid !== 4` gates the identify link (`table.jsx`)
- [common](docs/agent-docs/react-common.md) — Shared utils, SearchWidget, FilterSidebar, sidebar filter components
- [less](docs/agent-docs/react-less.md) — LESS sources, 7 style entries, filemtime cache-busting

## Meta
- [Memory Provenance](docs/agent-docs/meta-memory-provenance.md) — Which vintages of this corpus are trustworthy, and why
