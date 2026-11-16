# Tasks: Group family and genus checklist search

Status: in progress
Plan: PLAN.md
Approval: The user explicitly auto-approved plan and tasks for autonomous execution.

## Wave 1
- [ ] T1: Return independently limited family and genus groups
  - Files: `pages/rpc/taxonomic-checklist.php`, `classes/TaxonomicChecklistRepository.php`; testing worker owns `tests/TaxonomicChecklistRepositoryTest.php`.
  - Do: Extract existing database lookup into a repository; reuse the accepted-taxon/checklist/rank restrictions and existing labels. Execute separate family-prefix and genus-prefix queries with shared constraints, distinct results and independent limits of 20. Constrain available PDF families before limiting. Keep controller thin, remove mode validation and return both arrays even for invalid-length queries. Preserve allowlist failure behavior.
  - Tests: Add a dependency-free PHP regression script using an isolated fake entity manager/query builder, checking group shape, query constraints, independent limits, label mapping, and empty allowlist. Verify live HTTP responses for missing/short/oversized queries and family/genus prefixes.
  - Done when: Focused PHP tests and syntax checks pass; live API returns both groups without a mode parameter, each capped at 20 and linked only to available reports.
- [ ] T2: Render independently collapsible grouped suggestions
  - Files: `js/react/src/taxonomic-checklist/main.jsx`, `js/react/src/less/taxonomic-checklist.less`; testing worker owns `js/react/tests/taxonomic-checklist.test.cjs`.
  - Do: Remove modes and send query only. Render Family and Genus sections in that order with accessible independent arrow toggles, five visible links initially and a More button revealing all fetched matches in its group. Keep sections expanded initially and reset local section state for a new search. Preserve debounce, errors, stale-response rejection, report links and editorial content. Ensure mouse and keyboard interaction inside the widget does not trigger premature blur dismissal. Style controls to fit the existing dropdown.
  - Tests: Add focused dependency-free component regression checks using installed Babel/React tooling as practical, covering request contract, grouped rendering, independent collapse/More, state reset, empty groups, stale responses and errors. Test live browser desktop/mobile behavior at localhost:8000 with screenshots; use routed fixtures for 20-result groups where necessary.
  - Done when: Focused checks and changed-file lint pass; browser confirms both groups, five-to-20 More behavior, independent collapse, keyboard usability, PDF targets, reset and error handling.

## Final verification and documentation
- Testing worker runs available project lint, typecheck, all practical tests and `npm run build`; records missing checks and baseline failures separately.
- Codebase-explorer updates existing docs for the touched modules and endpoint, then documentation and completed task status are committed.
