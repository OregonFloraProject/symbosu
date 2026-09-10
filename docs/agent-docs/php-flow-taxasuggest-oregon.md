---
name: php-flow-taxasuggest-oregon
description: "How the 'Restrict autosuggest to Oregon vascular plant taxa' checkbox on the map and spatial search forms restricts taxon autosuggest to checklist clid=1"
---

# Restrict autosuggest to Oregon vascular plant taxa

The checkbox (`collections/map/index.php:2248`, `spatial/index.php:246`) only affects the taxon
**autosuggest** widget. It is never POSTed with the search form and does not filter the search
results themselves.

## Client side: `js/symb/api.taxonomy.taxasuggest.js`

1. `initTaxaSuggest()` runs on `$(document).ready` and attaches a jQuery UI autocomplete to the
   `#taxa` input via `runGenericTaxaSuggest("taxa", "taxontype")` (line 118).
2. On each keystroke, the autocomplete `source` callback reads the checkbox state and sends it to
   the RPC endpoint (lines 71-88):
   ```js
   let oregontaxa = ($("#oregonvascplant").is(':checked')) ? 1 : 0
   $.getJSON(acUrl, { term: extractLast(request.term), oregontaxa: oregontaxa, t: () => $("#taxontype").val() }, response);
   ```
   `acUrl` resolves to `clientRoot + /rpc/taxasuggest.php` (`acUrlBase`, line 1).
3. Requests trigger only after 4+ typed characters (`minLength` handled in the `search` callback,
   lines 90-99). `t` carries the taxon-type dropdown value (default 2 = scientific name).

## Server side: `rpc/taxasuggest.php` -> `classes/TaxonSearchSupport.php`

1. `rpc/taxasuggest.php:15,26` reads `oregontaxa` from the request and calls
   `TaxonSearchSupport::setOregonTaxa()` (line 202), storing it in the private `$oregonTaxa`.
2. `getTaxaSuggest()` (line 25) dispatches: `getTaxaSuggestByRank()` if `rankLow`/`rankHigh` were
   passed, otherwise `getTaxaSuggestByType()`. The map call sends only `t=2`, so the type path runs.
3. Every SQL branch of `getTaxaSuggestByType()` (lines 30-150) conditionally injects the same two
   clauses when `$this->oregonTaxa` is truthy:
   ```sql
   LEFT JOIN `fmchklsttaxalink` as cl ON <alias>.tid = cl.tid
   ...
   AND (cl.clid = 1)
   ```
   `clid = 1` is the Oregon vascular plant checklist, so suggestions are filtered to taxa that
   appear on it. The alias varies: `t` for the `taxa` table branches, `v` for the `taxavernaculars`
   branch (common-name / any-name search types).
4. `getTaxaSuggestByRank()` (lines 152-173) implements the identical restriction for callers that
   pass `ranklow`/`rankhigh` instead of a taxon type.

## Other places the same filter appears

- `classes/RpcTaxonomy.php::getTaxaSuggest()` (line 55) — the newer Laravel-side implementation for
  `taxa/taxonomy/rpc/gettaxasuggest.php`, with the same `AND (cl.clid = 1)` predicate and an
  `oregonVascPlant` flag; `taxa/taxonomy/taxonomydisplay.php` passes `oregon` through it.
- `js/symb/spatial.module.js:49` reads the same `oregonvascplant` checkbox on `spatial/index.php`.

## Will not search scope for the map page

The `#taxa` autosuggest is loaded on the map page via
`<script src="../../js/symb/api.taxonomy.taxasuggest.js">` (`collections/map/index.php:2142`);
`clientRoot` is exposed inline just above so `acUrl` resolves correctly (line 312).

Related: [[php-flow-search]], [[map-module]], [[db-checklist-tables]], [[taxo-acceptance-status]]