---
name: db-checklist-tables
description: "Fmchecklists, Fmchklsttaxalink, Fmprojects, Fmdynamicchecklists, Fmvouchers - checklist/flora schema, verified against models/*.php"
---

# Database Schema: Checklist Tables

Verified against Doctrine models in `models/*.php` (2026-07-31). The prior version of this memory
(authored 2026-07-16) got most column names on these tables wrong; corrections below.

## Fmchecklists (`models/Fmchecklists.php`, table `fmchecklists`)

PK: `clid` (identity). Real columns: `name`, `title`, `locality` (**not** `location`), `publication`,
`abstract`, `authors`, `type` (default `'static'`), `politicaldivision`, `dynamicsql`, `parent`,
`parentclid` (self-referencing FK, plus a `Fmchklstchildren` join table for the child side), `notes`,
`latcentroid`/`longcentroid` (**not** `centerLat`/`centerLon`), `pointradiusmeters`, `footprintwkt`,
`percenteffort`, `access` (default `'private'`), `defaultsettings`, `iconurl`, `headerurl`,
`sortsequence`, `expiration`, `datelastmodified`, `initialtimestamp`, `uid`.

Notable: `type` and `dynamicsql` live directly on `Fmchecklists` - a checklist's static/dynamic behavior
is a property of the checklist row itself, not a separate parallel table (see gotcha below).
Hardcoded constants on the model: `CLID_RARE_ALL=14948`, `CLID_RARE_OR=14802`, `CLID_GARDEN_ALL=54`,
`PID_GARDEN_ALL=3`, `PID_VENDOR_ALL=4`.

## Fmchklsttaxalink (`models/Fmchklsttaxalink.php`, table `fmchklsttaxalink`)

**Correction**: no `infourl` or `sortsequence` column (both fabricated in the prior version), and the
denormalized family column is `familyoverride`, not `family`.

Real columns: `morphospecies`, `familyoverride`, `habitat`, `abundance`, `notes`, `explicitexclude`,
`source`, `nativity`, `endemic`, `invasive`, `internalnotes`, `dynamicproperties`, `initialtimestamp`,
`clid`, `tid`.

## Gotcha: Fmchklsttaxalink's Primary Key Includes `morphospecies`, Not Just `(clid, tid)`

The Doctrine mapping marks `morphospecies`, `clid`, and `tid` all as `@ORM\Id`, making the composite key
`(morphospecies, clid, tid)` rather than the `(clid, tid)` pair the prior memory assumed. In practice this
means a single checklist can carry more than one row for the same `tid`, distinguished by
`morphospecies` - e.g. two entries for one taxon logged under different informal/morphospecies labels.
Code that treats `(clid, tid)` as unique (e.g. an upsert keyed only on those two columns) can silently
collide or duplicate. Confirmed in `models/Fmchklsttaxalink.php`.

## Fmprojects (`models/Fmprojects.php`, table `fmprojects`)

**Correction**: the prior memory's column names (`projectname`, `projectdescription`, `clid`, `initials`,
`lastupdated`) do not exist on this table.

Real columns: PK `pid`; `projname` (not `projectname`), `displayname`, `managers` (comma-separated, still
not normalized - this part of the original claim holds), `briefdescription`, `fulldescription` (not
`projectdescription`), `notes`, `occurrencesearch`, `ispublic`, `dynamicproperties`, `parentpid`,
`sortsequence`. There is no `clid` FK linking a project directly to a checklist on this table.

## Fmdynamicchecklists / Fmdyncltaxalink (saved dynamic taxa lists)

**Correction**: the prior memory described `Fmdynamicchecklists` as a rules-based "generated variant" of
a specific `Fmchecklists` row (with `clid`, `taxonstatusfilter`, and lat/long bounding-box columns). None
of that is accurate.

Real shape, confirmed in `models/Fmdynamicchecklists.php` and `classes/DynamicChecklistManager.php`:
- **Fmdynamicchecklists** (table `fmdynamicchecklists`): PK `dynclid`. Columns: `name`, `details`, `uid`,
  `type`, `notes`, `expiration` (a datetime - these rows expire and get purged, see
  `DynamicChecklistManager.php` line 131: `DELETE FROM fmdynamicchecklists WHERE expiration < NOW()`),
  `initialtimestamp`. **No `clid`, no `taxonstatusfilter`, no lat/long columns.**
- **Fmdyncltaxalink** (table `fmdyncltaxalink`, model `models/Fmdyncltaxalink.php`): simple join table,
  composite PK `(dynclid, tid)`, plus `initialtimestamp`.

This is an ephemeral "save a taxa list from a search result" mechanism (used by
`DynamicChecklistManager::class`), independent of the static/dynamic distinction that lives on
`Fmchecklists.type`. It is not a generator that re-derives its taxa list from stored geographic/status
rules at query time.

## Fmvouchers (`models/Fmvouchers.php`, table `fmvouchers`)

**Correction**: no surrogate `voucherid`, and no `collectiondate`/`habitat` columns.

Real columns: composite PK `(tid, clid)`; `occid`, `editornotes`, `preferredimage`, `notes`,
`initialtimestamp`.

## Gotcha: "Static vs Dynamic Checklist" Is Not Two Tables

The prior gotcha claimed `Fmchecklists` = static checklists and `Fmdynamicchecklists` = generated
checklists, as if they were two forms of the same concept that code must branch on. That framing is
wrong. `Fmchecklists` itself carries a `type` column (default `'static'`) and a `dynamicsql` column, so
a single checklist row can be static or dynamic. `Fmdynamicchecklists`/`Fmdyncltaxalink` are an unrelated,
expiring "saved search result" pair of tables (see above) - they are not the dynamic counterpart of a
specific `Fmchecklists` row. Code branching on checklist type should inspect `Fmchecklists.type` /
`dynamicsql`, and should not conflate that with `Fmdynamicchecklists`. See `classes/ExploreManager.php`
(`getType()` delegates to the checklist model) and [[php-manager-classes]] for the manager that fronts
this.

## Other supporting join tables (seen but not detailed here)

`Fmchklstchildren` (`clid`, `clidchild` - parent/child checklist nesting), `Fmchklstprojlink` (`pid`,
`clid`, `clnameoverride`, `sortSequence`, `mapchecklist`, `notes` - links projects to checklists).

## See also

[[db-taxonomy-tables]], [[db-occurrence-tables]], [[php-manager-classes]], [[php-flow-checklist-taxa]],
[[taxo-acceptance-status]]
