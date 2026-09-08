---
name: arch-security-permissions
description: Permissions are enforced only in PHP via the USER_RIGHTS session array; verified right-key names and shape
---

# Security & Permissions

## Enforcement is PHP-only

There is no DB-level (e.g. row-level or grant-based) enforcement of collection, checklist, or
project permissions. Every gate found in the codebase is a PHP-level check against session state
before a manager runs a query or a page renders admin controls. If an RPC endpoint has a bug that
skips the check, the underlying query itself will not stop it.

## Real shape of `$USER_RIGHTS`

`$USER_RIGHTS` (from `$_SESSION`) is **not** a flat array of permission-string constants. It is an
associative array keyed by right name, where most values are arrays of IDs the user holds that
right for (collection IDs, checklist IDs, project IDs). Checks combine a global `$IS_ADMIN` /
`$isAdmin` flag with `array_key_exists()` and, for scoped rights, `in_array($id, $USER_RIGHTS[...])`.

Verified real right keys (grepped across the repo, not invented):

| Right key | Scope | Example check site |
|---|---|---|
| `CollAdmin` | array of collection IDs | `misc/general_template.php`, `collections/admin/specupload.php` |
| `CollEditor` | array of collection IDs | `misc/general_template.php`, `sitemap.php` |
| `ClAdmin` | array of checklist IDs | `checklists/checklistadmin.php`, `sitemap.php` |
| `ClCreate` | boolean-ish presence | `checklists/checklistadmin.php` |
| `ProjAdmin` | array of project IDs | `checklists/checklistadmin.php` |
| `KeyEditor` | boolean-ish presence | `ident/key-v1.php`, `ident/tools/editor.php` |
| `KeyAdmin` | boolean-ish presence | `ident/admin/index.php`, `ident/admin/chardetails.php`, `sitemap.php` |
| `TaxonProfile` | boolean-ish presence | `sitemap.php` |
| `Taxonomy` | boolean-ish presence | `sitemap.php` |

Typical check pattern:
```php
if ($IS_ADMIN || (array_key_exists('CollAdmin', $USER_RIGHTS) && in_array($collid, $USER_RIGHTS['CollAdmin']))) {
    $isAdmin = true;
}
```

### Correction from the prior (unverified) version of this memory
The previously recorded right strings `canEditCollection`, `canManageProject`, `isAdmin` (as an
array member checked with `in_array('isAdmin', USER_RIGHTS)`), and `canViewCollection` do **not**
appear anywhere in the codebase and have been dropped. `isAdmin`/`IS_ADMIN` is a separate global
boolean flag (`config/symbbase.php: $isAdmin = $IS_ADMIN;`), not a `USER_RIGHTS` entry.

## Protected species / locality redaction

Locality data for protected species is redacted at the map/occurrence layer; see
[[map-module]] for the redaction mechanism (owned by the map-domain memory, not duplicated here).

## Related
[[arch-overview]], [[arch-legacy-patterns]], [[map-module]]
