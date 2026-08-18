---
name: meta-memory-provenance
description: "Which vintages of this project's memory corpus are trustworthy; the 2026-07-16 bulk pass was fabricated and was rewritten on 2026-07-31"
---

# Memory Provenance

This corpus was reorganized and fact-checked against the repo on 2026-07-31. The audit found a
sharp split by vintage.

## The 2026-07-16 bulk pass was written without reading the code

Roughly 2,400 lines across `architecture_overview`, `database_schema`, `manager_classes`,
`data_flow_patterns`, `page_entry_points`, and `gotchas` were plausible-sounding and substantially
false. Verified examples:

- **Invented DB columns.** `Omoccurrences` was documented with `tid`, `latitude`, `longitude`,
  `protected`, `collector`, `determiner`. The real columns are `tidinterpreted`, `decimalLatitude`,
  `decimalLongitude`, `recordSecurity`, `recordedBy`, `identifiedBy`. Six for six wrong.
- **Invented methods.** Not one of the ~20 documented manager methods existed
  (`TaxaManager::getTaxon`, `ExploreManager::buildResult`, `OccurrenceMapManager::searchOccurrences`, …).
  Four documented manager classes did not exist either.
- **An invented API convention.** A `queryType` GET param and a `{status, data}` response envelope
  were described across two separate memories. No RPC endpoint uses either.
- **Invented infrastructure.** `$readPDO`/`$writePDO` (real: `MySQLiConnectionFactory::getCon()`),
  `Functional::getImagePath()` (real: `resolve_img_path()`), and the `USER_RIGHTS` strings
  `canEditCollection`/`canManageProject`/`isAdmin` (real keys: `CollAdmin`, `CollEditor`, `ClAdmin`,
  `ClCreate`, `ProjAdmin`, `KeyEditor`, `KeyAdmin`, `TaxonProfile`, `Taxonomy`).
- **A wrong count repeated everywhere.** "11 Webpack bundles" — there are 15.

## Memories written during actual code work verified accurate

`ident-key-editing`, `ident-settaxa-flow`, `taxo-acceptance-status`, and `taxo-authority`
(written 2026-07-28/30 while working in the code) held up on inspection. Only line-number
citations had drifted.

**Why:** memory written as a byproduct of reading code records what was read. Memory written as a
standalone documentation exercise reconstructs what a codebase of this shape *probably* looks like,
and a Symbiota-derived PHP app has a very guessable shape. The fabrications were all plausible,
which is what made them dangerous.

**How to apply:** write memories while in the code, citing what was actually read. Treat any
uncited architectural claim as a hypothesis. When a memory names a column, method, or endpoint,
that name is checkable in seconds — check it before acting on it.

## Verification traps found during the audit

- **Grep the module, not one file.** A claim that `pid === 4` gates the vendor identify link was
  nearly deleted as fabricated after grepping `inventory/main.jsx`. It is real, in
  `inventory/table.jsx:169`. See [[react-inventory]].
- **Grep other branches before declaring something fabricated.** The `showNotes` prop does not
  exist on `dev` and looks invented. It is real on the unmerged `show-notes` branch. See [[react-explore]].
- **Duplicate commit hashes exist.** `314f92aaf` and `ea3c24f29` share a message and date; only
  `ea3c24f29` is reachable from `dev`. Confirm with `git merge-base --is-ancestor` before citing.

Related: [[arch-overview]], [[php-manager-classes]], [[db-occurrence-tables]], [[arch-security-permissions]]
