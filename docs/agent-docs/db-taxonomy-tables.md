---
name: db-taxonomy-tables
description: "Taxa, Taxstatus, Taxavernaculars, Taxalinks, Taxadescrstmts/Taxadescrblock, Taxaenumtree - core taxonomic schema, verified against models/*.php"
---

# Database Schema: Taxonomy Tables

Verified against Doctrine models in `models/*.php` (2026-07-31). The prior version of this memory
(authored 2026-07-16) asserted several columns that do not exist; those are corrected below.

## Taxa (`models/Taxa.php`, table `taxa`)

PK: `tid` (int, unsigned, identity).

Real columns: `kingdomname`, `rankid`, `sciname` (unique with `rankid`+`author`), `unitind1`/`unitname1`,
`unitind2`/`unitname2`, `unitind3`/`unitname3` (the parsed name parts - genus/species/infraspecies),
`author`, `phylosortsequence`, `status` (maps to SQL column `statusNotes`), `source`, `notes`, `hybrid`,
`securitystatus` (0 = no security, 1 = hidden locality), `modifiedtimestamp`, `initialtimestamp`,
`modifieduid`, `nomenclaturalStatus`.

**Correction**: the prior memory claimed `Taxa` has `parentTid`, `acceptedTid`, and `uncertain` columns.
None of these exist on `taxa`. Parent/accepted-taxon relationships live entirely in `Taxstatus` and
`Taxaenumtree`, not on `Taxa` itself.

Relationships confirmed in the model: `Taxa` has a mapped `OneToMany` to `Taxavernaculars` (ordered by
`sortsequence`) and to `Images`.

## Taxstatus (`models/Taxstatus.php`, table `taxstatus`)

**Correction**: no surrogate `tsid` primary key. The PK is the composite
`(tidaccepted, tid, taxauthid)`.

Real columns: `tidaccepted`, `tid`, `taxauthid`, `parenttid`, `family`, `unacceptabilityreason`, `notes`,
`sortsequence` (default 50), `initialtimestamp`. **There is no `taxonomicstatus` column and no `source`
column** - both were fabricated in the prior version.

`taxauthid` scopes every row to a taxonomic authority (see [[taxo-authority]] for the hardcoded-`taxauthid=1`
gotcha). Confirmed via `TaxaManager::populateAcceptedSynonyms()` / `populateSynonyms()` in
`classes/TaxaManager.php`, both of which hardcode `ts.taxauthid = 1`.

**Convention** (confirmed by comment in `TaxaManager.php` line 593-594): `tid = X, tidaccepted = X` means
X is the accepted name; `tid = synonymTid, tidaccepted = acceptedTid` means synonymTid is a synonym of
acceptedTid.

## Taxavernaculars (`models/Taxavernaculars.php`, table `taxavernaculars`)

PK: `vid` (identity). Columns: `vernacularname`, `language` (default `'English'`), `source`, `notes`,
`username`, `isupperterm`, `sortsequence` (default 50), `initialtimestamp`, `langid`, `tid` (FK to Taxa).

## Taxalinks (`models/Taxalinks.php`, table `taxalinks`)

PK: `tlid`. Columns: `tid`, `url`, `title`, `sortsequence`.

**Correction**: the prior memory invented `description` and `linktype` columns. Neither exists - the
model only exposes `url`, `title`, `sortsequence`.

## Taxadescrstmts / Taxadescrblock (long-form descriptions)

Long-form taxon text is split across two tables, not one:

- **Taxadescrblock** (`models/Taxadescrblock.php`, table `taxadescrblock`): PK `tdbid`. Holds `tid` (FK
  to Taxa), `tdprofileid`, `caption`, `source`, `sourceurl`, `language`, `displaylevel`. This is the
  table that actually links a description block to a taxon and to a source/language.
- **Taxadescrstmts** (`models/Taxadescrstmts.php`, table `taxadescrstmts`): PK `tdsid`. Holds `tdbid`
  (FK to Taxadescrblock, not `tid` directly), `heading`, `statement`, `displayheader`, `sortsequence`.

**Correction**: the prior memory described a single `Taxadescrstmts` table with `tid`, `description`,
`caption`, `source` columns. None of `tid`, `description`, `caption`, `source` exist on
`taxadescrstmts` - `tid`/`caption`/`source` live one level up on `taxadescrblock`, and the actual text
column is `statement`, not `description`. Confirmed by `TaxaManager.php` line 372, which selects
`tdb.tdbid, tdb.caption, tdb.language, tdb.source, tdb.sourceurl, ... tds.tdsid, tds.heading,
tds.statement, tds.displayheader` by joining the two tables.

## Taxaenumtree (`models/Taxaenumtree.php`, table `taxaenumtree`)

**Correction**: no surrogate `eeid` primary key. The PK is the composite `(tid, parenttid, taxauthid)`,
where `tid` and `parenttid` are `ManyToOne` references to `Taxa` and `taxauthid` references
`Taxauthority`. There are no `cid` or `csid` columns.

Purpose: taxonomic hierarchy only (kingdom -> phylum -> ... -> species), scoped per `taxauthid`.

## Gotcha: Taxaenumtree Does Not Serve the Identification Key

The prior memory claimed `Taxaenumtree` is used for two purposes: the taxonomic hierarchy *and* the
identification key structure (character/state matching). This is **false** and was deleted. The model
has no `cid`/`csid` columns to represent characters or states, and a repo-wide check of the two key-editing
classes (`classes/KeyManager.php`, `classes/KeyMatrixEditor.php`) shows every `taxaenumtree` join uses
only `tid`/`parenttid`/`taxauthid` to walk the taxonomic tree (e.g. to find all taxa under a family for
matrix editing) - never to represent characters or states. The identification key's actual
character-to-taxon structure lives entirely in `Kmdescr` (see [[db-identkey-tables]] and
[[ident-settaxa-flow]]). Do not assume `taxaenumtree` rows encode key-filter state.

## Gotcha: Vernacular Language Is Stored As a Full Word, Not an ISO Code

The prior memory's example query filtered `Taxavernaculars` with `language = 'en'`. The actual column
default and stored values are full language names (e.g. `'English'`), not ISO 639-1 codes. Confirmed by
`models/Taxavernaculars.php` (`private $language = 'English';`) and by `glossary/index.php` line 18,
which explicitly converts an incoming `'en'` parameter to `'English'` before querying:
`if($language == 'en') $language = 'English';`. Code that queries vernaculars by language code must do
the same conversion, or the filter silently matches nothing.

## Gotcha: Synonym Resolution Required in Search

Confirmed pattern (`classes/TaxaManager.php` `populateSynonyms()`/`populateAcceptedSynonyms()`): a search
against `Taxa.sciname` alone misses synonyms. Finding synonyms/accepted-name pairs requires joining
`Taxstatus` on `tid`/`tidaccepted` and filtering `taxauthid = 1` (the hardcoded primary authority - see
[[taxo-authority]]). Use `TaxaManager` rather than raw `Taxa` queries when synonym resolution matters; see
[[php-flow-search]] and [[taxo-acceptance-status]] for the fuller accepted/synonym derivation logic.

## See also

[[db-checklist-tables]], [[db-occurrence-tables]], [[db-identkey-tables]], [[taxo-acceptance-status]],
[[taxo-authority]], [[php-manager-classes]], [[php-flow-search]]
