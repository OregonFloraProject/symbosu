---
name: db-occurrence-tables
description: "Omoccurrences, Omcollections - specimen/occurrence schema (Darwin Core based), verified against models/*.php"
---

# Database Schema: Occurrence Tables

Verified against Doctrine models in `models/*.php` (2026-07-31). The prior version of this memory
(authored 2026-07-16) used a small set of ad-hoc column names; the real schema is a full Darwin Core
occurrence record and several of the assumed column names do not exist.

## Omoccurrences (`models/Omoccurrences.php`, table `omoccurrences`)

PK: `occid` (identity, unsigned).

**Corrections** (columns the prior memory got wrong or invented):
- There is **no `tid` column**. The taxon FK is `tidinterpreted` (`ManyToOne` to `Taxa`, joined on
  `TID`). `sciname` and `scientificName` also exist directly on the row as denormalized text (Darwin
  Core convention), separate from `tidinterpreted`.
- There is **no `latitude`/`longitude`**. The real columns are `decimalLatitude` / `decimalLongitude`
  (Darwin Core names).
- There is **no `protected`** flag. Locality redaction uses `localitySecurity` (0 = no security, 1 =
  hidden locality - same convention as `Taxa.securitystatus`) plus `localitySecurityReason`.
- There is **no `collector`/`determiner`**. These are `recordedBy` (collector(s)) and `identifiedBy`
  (who identified it), per Darwin Core.
- There is **no `photographs`** or **`pointintersect`** column on this model.
- `collid` is a `ManyToOne` to `Omcollections` (joined on `CollID`), not a plain int FK.

Confirmed real columns (partial list, this table is wide - see `models/Omoccurrences.php` for the
complete Darwin Core field set): `dbpk`, `basisOfRecord` (default `'PreservedSpecimen'`),
`occurrenceID`, `catalogNumber`, `otherCatalogNumbers`, `ownerInstitutionCode`, `institutionCode`,
`collectionCode`, `family`, `scientificName`, `sciname`, `genus`, `specificEpithet`, `taxonRank`,
`infraspecificEpithet`, `scientificNameAuthorship`, `taxonRemarks`, `identifiedBy`, `dateIdentified`,
`typeStatus`, `recordedBy`, `recordNumber`, `eventDate`, `latestDateCollected`, `year`/`month`/`day`,
`habitat`, `substrate`, `fieldNotes`, `occurrenceRemarks`, `informationWithheld`, `dataGeneralizations`,
`cultivationStatus` (0 = wild, 1 = cultivated), `locality`, `localitySecurity`,
`localitySecurityReason`, `decimalLatitude`, `decimalLongitude`, `geodeticDatum`,
`coordinateUncertaintyInMeters`, `footprintWKT`, `georeferencedBy`, `georeferenceProtocol`,
`georeferenceRemarks`, `minimumElevationInMeters`/`maximumElevationInMeters`, `disposition`,
`storageLocation`, `modified`, `processingstatus`, `recordEnteredBy`, `dateEntered`,
`dateLastModified`, `collid` (FK), `tidinterpreted` (FK).

## Omcollections (`models/Omcollections.php`, table `omcollections`)

PK: `collid` (identity, unsigned).

**Corrections**: there is no `institution` column (the model has an `iid` `ManyToOne` FK to a separate
`Institutions` table instead) and no `description` column (the real column is `fulldescription`).

Real columns: `institutioncode`, `collectioncode`, `collectionname`, `collectionid`, `datasetname`,
`fulldescription`, `homepage`, `individualurl`, `contact`, `email`, `latitudedecimal`/
`longitudedecimal`, `icon`, `colltype` (default `'Preserved Specimens'`), `managementtype` (default
`'Snapshot'`), `publicedits`, `collectionguid`, `securitykey`, `guidtarget`, `rightsholder`, `rights`,
`usageterm`, `publishtogbif`, `publishtoidigbio`, `aggkeysstr`, `dwcaurl`, `bibliographiccitation`,
`accessrights`, `sortseq`, `initialtimestamp`, `iid` (FK to `Institutions`).

## Gotcha: Column Names Follow Darwin Core, Not the Simplified Names Used Elsewhere in This Codebase's Docs

Any SQL/DQL written against `omoccurrences` must use the real Darwin Core-derived names -
`decimalLatitude`/`decimalLongitude` for coordinates, `tidinterpreted` for the resolved taxon FK,
`localitySecurity` for the redaction flag, `recordedBy`/`identifiedBy` for collector/determiner. This
matters directly for spatial and taxon-filtered queries; see [[map-module]] and [[map-solr-pipeline]] for
how the map/SOLR layer names these same fields in its query builders, and cross-check before assuming a
shorter/simpler name exists on the raw table.

## See also

[[db-taxonomy-tables]], [[db-checklist-tables]], [[map-module]], [[map-solr-pipeline]],
[[map-click-kml-flow]], [[php-manager-classes]]
