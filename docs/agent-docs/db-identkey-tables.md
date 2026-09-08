---
name: db-identkey-tables
description: "Kmcharacters, Kmcs, Kmdescr, Kmcharheading - identification key schema, verified against models/*.php and raw SQL usage"
---

# Database Schema: Identification Key Tables

Verified against Doctrine models in `models/*.php` plus raw SQL in `classes/KeyManager.php`,
`classes/KeyMatrixEditor.php`, `classes/TaxonomyEditorManager.php` and `classes/TaxonomyCleaner.php`
(2026-07-31), since some of these tables are queried with raw SQL rather than through the (partial)
Doctrine mapping.

## Kmcharacters (`models/Kmcharacters.php`, table `kmcharacters`)

**Correction**: the prior memory's column names (`character_name`, `character_type`, `imageurl`,
`sequencenumber`, per-row `language`) do not exist.

PK: `cid` (identity, unsigned). Real columns: `charname` (not `character_name`), `chartype` (default
`'UM'`, not `character_type`), `defaultlang` (default `'English'` - one language per character
definition, not a per-row `language` column), `difficultyrank` (default 1), `units`, `description`,
`notes`, `display`, `helpurl`, `enteredby`, `sortsequence`, `initialtimestamp`, `hid` (`ManyToOne` FK to
`Kmcharheading`). There is no `imageurl` column.

## Kmcs (`models/Kmcs.php`, table `kmcs`)

Matches the prior memory closely - this one verified clean. PK is the composite `(cs, cid)`, no
surrogate `csid`. Columns: `cs` (string state code), `charstatename`, `implicit`, `notes`, `description`,
`illustrationurl`, `stateid`, `sortsequence`, `initialtimestamp`, `enteredby`, `cid` (`ManyToOne` FK to
`Kmcharacters`).

Example: `cid`=(a "Sunlight requirement" character), `cs`="1" `charstatename`="Full sun"; `cs`="2"
"Partial shade"; `cs`="3" "Full shade".

## Kmdescr (`models/Kmdescr.php`, table `kmdescr`) - taxon-to-character-state assignments

**Note on verification method**: the Doctrine model for this table is a thin, mostly read-only mapping
(`tid`, `cid`, `cs`, `seq` only - all four marked `@ORM\Id`). The additional columns below are not in the
Doctrine mapping but are confirmed live via raw SQL in `classes/KeyManager.php` and
`classes/TaxonomyEditorManager.php`, which both `INSERT INTO kmdescr (TID, CID, CS, Modifier, X, TXT,
Seq, Notes, Inherited)` and separately `INSERT INTO kmdescr (TID, CID, CS, Source)`.

Confirmed real columns: `TID` (FK to `taxa.TID`), `CID`, `CS` (composite FK to `kmcs.cid`+`cs`), `Seq`,
`Modifier`, `X` (numeric value for range/numeric characters), `TXT` (free text value), `Notes`,
`Inherited` (whether the trait was inherited from a parent taxon rather than entered directly - see
`TaxonomyCleaner.php`/`TaxonomyEditorManager.php`, which `DELETE FROM kmdescr WHERE inherited IS NOT
NULL` when re-deriving inherited traits), `Source`.

**Correction / deletion**: the prior memory also listed `PseudoTrait`, `Frequency`, and `DateEntered`
columns. No occurrence of these anywhere in the PHP codebase (grepped across all `.php` files) - deleted
as unverifiable.

**Primary key**: the Doctrine model marks `(tid, cid, cs, seq)` as the full composite key (four columns,
not three) - `seq` is part of the key, not just an ordering column. This matches `KeyManager.php`'s
`INSERT IGNORE` pattern, which always supplies a `Seq` value alongside `TID`/`CID`/`CS`.

## Kmcharheading (`models/Kmcharheading.php`, table `kmcharheading`)

**Correction**: the prior memory's column names (`headingid`, `cid`, `heading_text`, `sequencenumber`)
do not exist, and the FK direction was backwards - `Kmcharheading` does not reference `Kmcharacters`;
`Kmcharacters.hid` references `Kmcharheading`.

PK: composite `(hid, langid)`. Real columns: `hid`, `headingname` (not `heading_text`), `language`
(default `'English'`), `notes`, `sortsequence`, `initialtimestamp`, `langid`.

## Gotcha: Character-State Structure Lives Here, Not in Taxaenumtree

The identification key's taxon-to-character-state matching is entirely represented by `Kmdescr` joined
to `Kmcs`/`Kmcharacters` - not by `Taxaenumtree`. See [[db-taxonomy-tables]] for the verification that
`Taxaenumtree` is taxonomic-hierarchy-only. For how `Kmdescr`/`Kmcs`/`Kmcharacters` feed the actual key
filter UI and `IdentManager::setTaxa()`, see [[ident-settaxa-flow]] and [[ident-key-editing]].

## See also

[[db-taxonomy-tables]], [[ident-key-editing]], [[ident-key-filter-flow]], [[ident-settaxa-flow]],
[[php-manager-classes]]
