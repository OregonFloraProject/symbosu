# Tasks: Group family and genus checklist search

Status: complete
Plan: PLAN.md
Approval: The user explicitly auto-approved plan and tasks for autonomous execution.

## Wave 1
- [x] T1: Return independently limited family and genus groups (commit 00ed1164a)
  - Files: `pages/rpc/taxonomic-checklist.php`, `classes/TaxonomicChecklistRepository.php`; testing worker owns `tests/TaxonomicChecklistRepositoryTest.php`.
  - Do: Extract existing database lookup into a repository; reuse the accepted-taxon/checklist/rank restrictions and existing labels. Execute separate family-prefix and genus-prefix queries with shared constraints, distinct results and independent limits of 20. Constrain available PDF families before limiting. Keep controller thin, remove mode validation and return both arrays even for invalid-length queries. Preserve allowlist failure behavior.
  - Tests: Add a dependency-free PHP regression script using an isolated fake entity manager/query builder, checking group shape, query constraints, independent limits, label mapping, and empty allowlist. Verify live HTTP responses for missing/short/oversized queries and family/genus prefixes.
  - Done when: Focused PHP tests and syntax checks pass; live API returns both groups without a mode parameter, each capped at 20 and linked only to available reports.
- [x] T2: Render independently collapsible grouped suggestions (commit e40664a4a)
  - Files: `js/react/src/taxonomic-checklist/main.jsx`, `js/react/src/less/taxonomic-checklist.less`; testing worker owns `js/react/tests/taxonomic-checklist.test.cjs`.
  - Do: Remove modes and send query only. Render Family and Genus sections in that order with accessible independent arrow toggles, five visible links initially and a More button revealing all fetched matches in its group. Keep sections expanded initially and reset local section state for a new search. Preserve debounce, errors, stale-response rejection, report links and editorial content. Ensure mouse and keyboard interaction inside the widget does not trigger premature blur dismissal. Style controls to fit the existing dropdown.
  - Tests: Add focused dependency-free component regression checks using installed Babel/React tooling as practical, covering request contract, grouped rendering, independent collapse/More, state reset, empty groups, stale responses and errors. Test live browser desktop/mobile behavior at localhost:8000 with screenshots; use routed fixtures for 20-result groups where necessary.
  - Done when: Focused checks and changed-file lint pass; browser confirms both groups, five-to-20 More behavior, independent collapse, keyboard usability, PDF targets, reset and error handling.

## Final verification and documentation
- Testing worker runs available project lint, typecheck, all practical tests and `npm run build`; records missing checks and baseline failures separately.
- Codebase-explorer updates existing docs for the touched modules and endpoint, then documentation and completed task status are committed.

## Verification results
- T1: `php tests/TaxonomicChecklistRepositoryTest.php` passed. PHP syntax checks passed for the controller, repository and regression script. Live HTTP checks passed for missing, whitespace, short and oversized queries plus `Ros`, `Ast` and `Car` prefixes; responses contained both arrays and only PDF-allowlisted families.
- Project ESLint: `./node_modules/.bin/eslint . --format json` from `js/react` reported 843 errors and 218 warnings across 101 files. The changed checklist component had zero diagnostics; broader failures are outside this task. Log: `/tmp/opencode/oregonflora-eslint.json`.
- API PHPUnit: `./vendor/bin/phpunit --configuration phpunit.xml --testdox` from `api` passed, one test and one assertion through HTTP.
- Typecheck skipped: no typecheck script or TypeScript configuration exists.
- Root `tests/SplitSciNameTest.php` skipped: it constructs `TaxonomyEditorManager`, whose database-connection requirements were not established. Database-backed checks for this task use application HTTP endpoints only.
- T2: Targeted ESLint passed. `node --test tests/taxonomic-checklist.test.cjs` from `js/react` passed all six focused tests, including effective visibility, independent five-to-20 expansion, collapse/reset, stale responses, errors, focus boundaries and unmount cleanup.
- Integration build: `npm run build` succeeded, with a stale Browserslist database warning. Log: `/tmp/opencode/taxonomic-checklist-build.log`.
- Browser: Playwright verification passed at `http://localhost:8000/pages/taxonomic-checklist.php` using real API searches and explicitly simulated 20-result fixtures. Desktop/mobile checks confirmed independent sections and More controls, keyboard usability, PDF links, empty-group behavior, dismissal, stale-response handling, errors and no horizontal overflow.
- Screenshots: `/tmp/opencode/taxonomic-desktop-viewport.png`, `/tmp/opencode/taxonomic-desktop-fixture.png`, `/tmp/opencode/taxonomic-mobile-fixture.png`, `/tmp/opencode/taxonomic-mobile-empty-group.png`.
- Required post-commit gate: PHP syntax checks passed for all three PHP files; targeted ESLint passed for the component and Node tests; PHP repository checks and all six Node tests passed again. Final `npm run build` exited zero after both task commits. Log: `/tmp/opencode/taxonomic-checklist-final-build.log`. Build warning: `caniuse-lite` data is 19 months old.

## Documentation
- Updated `AGENTS.md` (also reflected through the existing `CLAUDE.md` symlink), `docs/agent-docs/arch-page-entry-points.md`, `docs/agent-docs/react-frontend.md` and `docs/agent-docs/react-less.md` with the current grouped endpoint, controls, constraints and regression commands.
- Preserved the pre-existing untracked `docs/agent-docs/REVIEW.md`.
