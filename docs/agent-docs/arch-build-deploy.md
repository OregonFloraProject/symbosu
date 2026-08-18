---
name: arch-build-deploy
description: "Doctrine proxy generation, frontend build steps, and release process"
---

# Build & Deploy

## Doctrine Proxy Generation

`config/SymbosuEntityManager.php` configures Doctrine with:
```php
$config->setProxyDir("$SERVER_ROOT/temp/proxies");
$config->setProxyNamespace("Symbosu\Proxies");
if ($IS_DEV) {
  $config->setAutoGenerateProxyClasses(true);
} else {
  $config->setAutoGenerateProxyClasses(false);
}
```
So proxy auto-generation is on in dev and **off in production** - proxies must exist on disk
under `/temp/proxies/` before a production request needs them, or Doctrine will fail to resolve
`Symbosu\Proxies\...` classes.

A `cli-config.php` exists at the repo root and wires up `SymbosuEntityManager::getEntityManager()`
for Doctrine's console runner, so the standard Doctrine CLI proxy-generation command
(`vendor/bin/doctrine orm:generate-proxies`) is reachable from the repo root. No deployment script
or doc in this repo was found that actually invokes it, so treat "run it after `composer install`"
as an operational inference from the config, not a documented step.

## Frontend Build

`js/react/package.json` scripts:
- `npm run build` -> `NODE_ENV=production webpack` (after cleaning `dist/`)
- `npm run dev` -> `NODE_ENV=development webpack` (watch mode)

Webpack config (`js/react/webpack.config.js`) runs two configs in one pass: the JS bundle build
(output to `js/react/dist/`) and a LESS-to-CSS build (output to `css/compiled/`). See
[[arch-page-entry-points]] for the full bundle list and [[react-less]] for the LESS side.

## Release Process

From `docs/release-protocol.md` (this repo tracks upstream Symbiota releases):
1. Merge the release-candidate branch into `master` after resolving conflicts.
2. Bump the version in `config/symbbase.php` following SemVer, no period between `v` and the
   number.
3. Apply any non-backwards-compatible DB schema changes via the `/dev` patch directory.
4. Open a PR into `master`; merge with "Create a merge commit" (squash merge is explicitly
   disallowed because it complicates subsequent merges).
5. Draft a GitHub release on `BioKIC/Symbiota` tagged to match the `symbbase.php` version.
6. Deploy to portal(s) for beta testing.

## Deployment Layout (from Doctrine config, verified)

- Code root contains `/config/` (gitignored env-specific `symbini.php`, `dbconnection.php`),
  `/temp/proxies/` (Doctrine proxy classes), `/js/react/dist/` (built bundles), `/css/compiled/`
  (built CSS).
- Doctrine's second-level cache uses `ApcuCache` in production and `ArrayCache` in dev
  (`SymbosuEntityManager::getMetaConfig()`), so APCu availability is a production dependency for
  Doctrine metadata/query caching paths that use it.

## Related
[[arch-overview]], [[arch-page-entry-points]], [[react-frontend]]
