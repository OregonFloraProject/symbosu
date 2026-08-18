---
name: php-flow-taxon-lookup
description: "Real request flow for a taxon profile page, from taxa/index.php through taxa/rpc/api.php's getTaxon()/taxaManagerToJSON()"
---

# Taxon Profile Request Flow

Entry point: `taxa/index.php`, React bundle `taxa` (`js/react/src/taxa`).

1. React calls `taxa/rpc/api.php` with a `taxon` GET param (not `tid`/`queryType` — verified in
   `taxa/rpc/api.php`'s dispatch block: `if (array_key_exists("taxon", $_GET) && is_numeric(...))`
   , also branching on `type=rare`/`type=garden` for the Rare Plant Guide and Grow Natives pages).
2. `getTaxon($tid, $queryType = "default")` fetches the Doctrine `Taxa` entity via
   `$em->getRepository("Taxa")->find($tid)`, wraps it with `TaxaManager::fromModel($taxaModel)`,
   and passes it to `taxaManagerToJSON()`.
3. `taxaManagerToJSON()` (`taxa/rpc/api.php:149+`) assembles the response by calling `TaxaManager`
   getters directly — `getGardenDescription()`, `getGardenId()`, `getTaxalinks()`,
   `getImagesByBasisOfRecord()` (only for ranks below family/genus, `rankid > 180`), and — only
   when `$queryType !== 'default'` — `getCharacteristics($queryType)`.
4. For minimal-data callers (search results, SPP listings) it instead calls `setSingleImage()` /
   `getImage()`, falling back to a sibling taxon's (`getSpp()`) image if the taxon has none of its
   own.
5. Response is raw `json_encode($result, ...)` — no `{status, data}` envelope, no `queryType`
   wrapper key.

There is no separate "synonym click → new RPC call" step distinct from a normal taxon lookup: a
synonym is just another `tid`; `taxa/rpc/api.php` also has a `synonym` dispatch branch
(`checkSynonym($_GET["synonym"])`, function defined further down in the same file) used
specifically to resolve a synonym `tid` to its accepted taxon.

Related: [[php-manager-classes]], [[php-flow-search]], [[db-taxonomy-tables]], [[react-taxa]],
[[arch-page-entry-points]].
