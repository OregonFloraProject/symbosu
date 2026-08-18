---
name: php-flow-search
description: "Real taxon search flow (taxa/rpc/api.php's searchTaxa) and how synonym resolution via Taxstatus makes both accepted and synonym names findable"
---

# Search Request Flow

`taxa/rpc/api.php`'s `searchTaxa($searchTerm)` (dispatched from `?search=...`) is a two-stage
Doctrine query, not the four-stage (exact → prefix → vernacular → fuzzy/SOUNDEX) pipeline
described in older notes — there is **no SOUNDEX/LEVENSHTEIN fuzzy-match stage** anywhere in this
function:

1. **Exact match probe**: query `Taxa` for `t.sciname = :search`, and separately query
   `Taxavernaculars` joined to `Taxstatus` for a coarse vernacular-name match
   (`v.vernacularname LIKE '%...%'`, resolved through `ts.tidaccepted`).
2. **Branch**: if the exact-sciname query returned exactly one row *and* the vernacular query
   returned zero rows, treat it as a unique match — wrap it with `TaxaManager::fromModel()`,
   serialize via `taxaManagerToJSON($tm, "default", true)`, and additionally call `getTaxStatus()`
   to check whether it's a synonym; if so, attach `tidaccepted` to the response so the frontend can
   redirect straight to the accepted taxon's profile.
3. **Otherwise**: run a broader `sciname LIKE '%...%'` search (again resolved through
   `Taxstatus.tidaccepted`) and return the coarse result list for an autocomplete/search-results
   UI.

`checklists/rpc/autofillsearch.php` (and its `garden/rpc/`, `rare/rpc/` counterparts) is a
separate, simpler autocomplete endpoint: builds a dynamic Doctrine query on `Taxa.sciname LIKE`,
optionally inner-joined to `Fmchklsttaxalink` (by `clid`) or `Fmdyncltaxalink` (by `dynclid`) to
scope suggestions to one checklist.

## Synonym Resolution Pattern

Both `searchTaxa()`'s queries route through `Taxstatus.tidaccepted` rather than matching `Taxa`
directly, so a search for either the accepted name or one of its synonyms resolves to the same
accepted `tid`:

```
Taxstatus:
  tid=456 (synonym)         tidaccepted=789 (Salvia apiana)
  tid=789 (Salvia apiana)   tidaccepted=789 (self)
```

This is why `taxa/rpc/api.php` always joins/subqueries through `Taxstatus` rather than filtering
`Taxa` alone — see also [[taxo-authority]] for how `taxauthid` scopes which `Taxstatus` row counts
as "the" accepted mapping, and [[taxo-acceptance-status]] for how accepted/synonym/conflict status
is derived from the same table.

Related: [[php-manager-classes]], [[php-flow-taxon-lookup]], [[db-taxonomy-tables]],
[[react-header]], [[react-taxa]].
