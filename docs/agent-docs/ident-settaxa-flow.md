---
name: ident-settaxa-flow
description: How IdentManager::setTaxa() builds the identification key's taxa query (joins, search, attr filters, checklist membership, vendor/trinomial handling)
---

# IdentManager::setTaxa() query flow

Entry point: `ident/rpc/api.php:76-91` builds an `IdentManager`, calls setters from request params
(`setClid`, `setDynClid`, `setTaxonFilter`, `setRelevanceValue`, `setAttrsFromParams`,
`setSearchTerm`/`setSearchName`, `setThumbnails`), then `setTaxa()`; result read via `getTaxa()`.

`setTaxa()` (`classes/IdentManager.php:122-362`) builds one Doctrine DQL query against `Taxa`,
only runs if `$this->clid || $this->dynClid`:

1. **Base joins** — left-join `Taxavernaculars` (common names), inner-join `Taxstatus` (family,
   accepted status), filtered to `taxauthid = 1`, vernacular language English/Basename only.
2. **Search filter** — branches on `searchName`: `commonname` joins `Taxavernaculars` again with
   LIKE match, optionally also matching synonyms via a `tidaccepted` subquery when
   `searchSynonyms` is set; `sciname` mirrors this against `t.sciname`; if neither name field
   specified, matches both (used by Natives page).
3. **Character-state attr filters** (`classes/IdentManager.php:208-232`) — for each `{cid: [states]}`
   in `$this->attrs`, either collects it as a vendor filter (if `cid == getNurseryCid()`, which
   returns `209`) or inner-joins `Kmdescr` with a per-character alias, filtering by `cid`/`cs`.
4. **Checklist membership** — three mutually exclusive paths: `dynClid` set -> join
   `Fmdyncltaxalink`; `clType == 'dynamic'` -> join `Omoccurrences` (marked "not finished/not in
   use" in code); otherwise (normal static checklist) -> join `Fmchklsttaxalink` on `clid`. If
   vendor attrs were requested, adds a second `Fmchklsttaxalink` join (`clk2`) with an OR condition
   matching either binomials directly in a vendor checklist, or trinomials whose parent binomial is
   in checklist 54 (Natives, matches `getGardenClid()` in `classes/Functional.php`) while the
   trinomial is in the vendor checklist.
5. **Family/genus filter** if `taxonFilter` set.
6. **Execute + reshape** — runs the query ordered by family/sciname/vernacular sortsequence, then
   collapses the flat row-per-vernacular-name result into one entry per taxon, merging
   English/Basename vernacular names into a `vernacular` sub-array.
7. **Post-processing** — stores into `$this->taxa`, optionally calls `setThumbnailUrls()`
   (`classes/IdentManager.php:369+`; one query per taxon against the `images` entity, `LIMIT 1`,
   explicitly documented in an inline comment as intentional to avoid pulling all images per taxon)
   if `showThumbnails`, then `$em->flush()`.

After `setTaxa()`, the caller also calls `getCharacteristics()` (`classes/IdentManager.php:392+`)
separately to fetch `Kmdescr` character/state data for the key's filter sidebar.

Also used by `checklists/rpc/api.php`'s `buildResult()`/`buildDynResult()` for plain checklist
taxa listing (not just the identification key) — see [[php-flow-checklist-taxa]].

Related: [[ident-key-editing]] covers how `kmcs`/`kmdescr` data gets edited, not how it's queried
for display. [[ident-key-filter-flow]] covers the request flow around this method.
[[php-manager-classes]], [[db-identkey-tables]], [[db-checklist-tables]].
