---
name: taxo-authority
description: What taxauthid/taxauthority means in Symbiota/OregonFlora, why taxon lookups silently fail across authorities, and that there is no admin UI to edit taxauthority rows
---

# Taxonomic Authority (`taxauthid` / `taxauthority`)

`taxauthority` (see `models/Taxauthority.php`, schema in `config/schema/3.0/db_schema-3.0.sql`) is
a small lookup table: `taxauthid`, `name`, `isprimary`, `description`, `editors`, `contact`,
`email`, `url`, `notes`, `isactive`. Every install seeds `taxauthid = 1` with `isprimary = 1`
(e.g. "Central Thesaurus").

**Why it exists**: `taxa` is a single shared, de-duplicated pool of scientific names. Different
taxonomic frameworks can classify the same name pool differently (accepted vs. synonym, different
parent/hierarchy placement). Rather than duplicate `taxa` rows per framework, all framework-specific
opinion lives in `taxstatus`, which has a composite PK `(tid, tidaccepted, taxauthid)` — the same
`tid` can have multiple `taxstatus` rows, one per authority.

- `taxstatus.tid` = the name being classified
- `taxstatus.tidaccepted` = which name this authority considers accepted (equals `tid` if this row
  is the accepted name itself)
- `taxstatus.parenttid` = this authority's hierarchy placement
- `taxstatus.taxauthid` = which authority's opinion this row represents

**Why: taxon lookups silently fail.** Nearly every taxonomy-related manager
(`TaxonomyDisplayManager`, `TaxonomyEditorManager`, `IdentManager` — confirmed hardcoding
`taxauthid = 1` in `setTaxa()`'s base join, see [[ident-settaxa-flow]] — and others) filters
`taxstatus` by `taxauthid`, defaulting to whichever row has `isprimary = 1` (usually
`taxauthid = 1`). A taxon can exist in `taxa` and even in `taxstatus`, but if its `taxstatus` row
is under a different (non-primary) `taxauthid`, every tool that assumes the default authority will
fail to find it — this looks like "missing data" but is actually an authority-scope mismatch.
Separately, OregonFlora's own "Oregon Vascular Plant" checklist restriction (`fmchklsttaxalink`,
`clid = 1`) is an independent filter layered on top of `taxauthid` — a taxon can be under the right
`taxauthid` but simply not linked into that checklist.

**How to apply**: When a taxon "can't be found" in the Tax Tree Viewer
(`taxa/taxonomy/taxonomydisplay.php`) or similar tools despite existing in `taxa`/`taxstatus`,
check `SELECT taxauthid FROM taxstatus WHERE tid = <tid>` against the tool's active `taxauthid`
(usually hardcoded/defaulted to 1) before assuming a data or search-string bug.

**Gap found**: There is no admin page anywhere in the codebase (including `sitemap.php`) to create
or edit `taxauthority` rows themselves (name/description/contact/url, or which authority is
primary). `TaxonomyDisplayManager::setTaxonomyMeta()` (`classes/TaxonomyDisplayManager.php:497`)
only reads it. Managing authorities requires a direct DB `INSERT`/`UPDATE` against `taxauthority`.

Related: [[taxo-acceptance-status]], [[ident-settaxa-flow]], [[php-flow-search]],
[[db-taxonomy-tables]].
