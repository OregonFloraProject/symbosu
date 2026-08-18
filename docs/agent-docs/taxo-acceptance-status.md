---
name: taxo-acceptance-status
description: How TaxonomyEditorManager derives whether a taxon is accepted, not accepted, unassigned, or in conflict
---

Taxon acceptance status (`getIsAccepted()`) is computed from the `taxstatus` table, not stored as a
simple flag. Logic lives in `TaxonomyEditorManager::setTaxon()` in `classes/TaxonomyEditorManager.php`
(method starts at line 50, derivation logic spans roughly lines 75-146), invoked from
`taxa/taxonomy/taxoneditor.php`.

**Values** (`$isAccepted`, default -1):
- `1` = accepted
- `0` = not accepted (synonym)
- `-1` = not yet assigned (no taxstatus row processed)
- `-2` = in conflict (contradictory taxstatus rows)

**Derivation**: queries `taxstatus` joined to `taxa` on `tidaccepted`, filtered by `taxauthid`
(taxonomic authority) and `tid`. Loops over all returned rows:
- Row where `tid == tidaccepted` (self-referencing) -> taxon is accepted (`1`), unless a prior row
  already said `0`, in which case `-2`.
- Row where `tid != tidaccepted` -> taxon is a synonym of another taxon (`0`), also populates
  `acceptedArr[$tidAccepted]` with the accepted taxon's sciname/author/notes. If a prior row
  already said `1`, contradiction -> `-2`.

A single `tid` can have multiple `taxstatus` rows (e.g. under conflicting taxonomic treatments);
disagreement among them is what produces the "in conflict" (`-2`) state, not a separate conflict
field.

**Self-healing**: if no `taxstatus` row exists at all for a `tid`+`taxauthid` (orphaned from the
table), `setTaxon()` auto-repairs by locating a plausible parent (species/genus/kingdom/organism
rank fallback chain) and inserting a new self-referencing `taxstatus` row, setting status to `1`
(accepted).

**Mutating status**: write methods `submitAddAcceptedLink()`, `removeAcceptedLink()`,
`submitChangeToAccepted()`, `submitChangeToNotAccepted()` (all confirmed in
`classes/TaxonomyEditorManager.php`) modify `taxstatus` rows directly; the editor page re-runs
`setTaxon()` afterward to recompute the in-memory status from the DB.

Related: [[db-taxonomy-tables]] for the `taxstatus`/`taxa` table shapes, [[taxo-authority]] for how
`taxauthid` scopes which rows are considered, [[php-flow-search]] for how the same `taxstatus`
table drives search/synonym resolution.
