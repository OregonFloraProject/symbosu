# Tasks: Convert the Taxonomic Checklist page to React

Status: complete
Plan: PLAN.md

## Wave 1
- [x] T1: Relocate the family/genus search endpoint (commit ab65bd3f2)
  - Files: `pages/rpc/taxonomic-checklist.php`, `pages/rpc/taxonomic-checklist-search.php`
  - Do: Move the existing JSON search implementation to `taxonomic-checklist-search.php` without changing its query behavior or response contract. Leave the original path available for the page-shell integration in T2.
  - Tests: none (the repository has no React/PHP unit-test suite configured; PHP lint and endpoint contract checks will be performed during verification).
  - Done when: The new endpoint preserves both search modes, validation and JSON response shape, and both changed PHP paths pass `php -l`.

## Wave 2
- [x] T2: Render the checklist page through a React bundle (commit 39fd8f9b7)
  - Files: `pages/taxonomic-checklist.php`, `js/react/src/taxonomic-checklist/main.jsx`, `js/react/webpack.config.js`
  - Do: Turn the page into a shared-header/footer shell with a React mount point and bundle include. Migrate existing editorial content, links, download buttons, family/genus search, suggestions and family-PDF selection to the React page. Use the relocated endpoint from T1 and keep stale requests from replacing newer search results.
  - Tests: none (the React package has no unit-test runner or existing tests; lint, type-check where available, build, and manual endpoint/page checks will verify the behavior).
  - Done when: The original page content and links render in React, the search supports both modes and shows the same selected-family PDF link, the bundle builds, and the PHP shell passes `php -l`.
