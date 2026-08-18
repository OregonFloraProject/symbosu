---
name: arch-legacy-patterns
description: "Legacy Symbiota code coexisting with Doctrine, the real mysqli read/write connection split, and image URL path resolution"
---

# Legacy Patterns

## Legacy Symbiota code coexists with Doctrine

Older pages and classes use raw SQL (via `mysqli`, see below) directly, while newer code paths use
Doctrine entities in `/models/` through manager classes (see [[php-manager-classes]]). Both styles
are live in production simultaneously; there is no single migration boundary. When working on a
feature, check whether the specific page/manager you are touching already uses Doctrine or raw
`mysqli` before adding a third approach.

## Gotcha: DB connection selection is a real read/write split, but it is mysqli, not PDO

`config/dbconnection.php` defines `MySQLiConnectionFactory` with two named connection types,
`"readonly"` and `"write"`, both currently pointing at the same host/credentials in this config
file but modeled as separate connections:

```php
class MySQLiConnectionFactory {
  static $SERVERS = array(
    array('type' => 'readonly', 'host' => ..., ...),
    array('type' => 'write', 'host' => ..., ...),
  );
  public static function getCon($type) { ... }       // returns a mysqli connection
  public static function getConParams($type) { ... }  // returns raw params (used by Doctrine's PDO setup)
}
```

Callers get a connection with `MySQLiConnectionFactory::getCon("readonly")` or `getCon("write")`
- confirmed in `classes/Database.php`, `classes/ChecklistFGExportManager.php`,
`classes/CommonSearchManager.php`, `classes/FieldGuideManager.php`, and 100+ other call sites.
Doctrine itself gets its PDO connection params via `getConParams("write")`
(`config/SymbosuEntityManager.php`), i.e. Doctrine always writes through the "write" credentials.

### Correction from the prior (unverified) version of this memory
There is no `$readPDO` / `$writePDO` pair anywhere in the codebase (grepped, zero hits). The real
split is `MySQLiConnectionFactory::getCon('readonly')` / `getCon('write')`, and the connections
returned are `mysqli` objects, not PDO objects (PDO only shows up as the driver Doctrine's own
`EntityManager::create()` uses internally).

## Gotcha: image URL path resolution

`classes/Functional.php` defines `resolve_img_path($dbPath)`, which prefixes a stored image path
with `$IMAGE_ROOT_URL` and/or `$IMAGE_DOMAIN` (globals sourced from `$MEDIA_ROOT_URL`/
`$MEDIA_DOMAIN` via aliasing in `config/symbbase.php`) unless the path already starts with
`http` or already contains that root/domain.

### Correction from the prior (unverified) version of this memory
There is no `Functional::getImagePath()` method (`Functional.php` has no class wrapper at all -
it's a plain function file) and no `/content/images/{specimens,fieldPhotos,vouchers,taxa}/`
directory structure in the repo. The actual `content/` tree (verified via `ls content/`) contains
`collections/`, `collicon/`, `dwca/`, `geoJSON/`, `geolocate/`, `imglib/`, `lang/`, `logs/`,
`ootd/`, `sitemaps/`, `slideshow/` - image storage lives under `content/imglib/` and
`content/collections/`, not a `content/images/` tree with per-type subfolders.

## Related
[[arch-overview]], [[php-manager-classes]], [[arch-security-permissions]]
