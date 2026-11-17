# Plan: Group family and genus checklist search

Status: approved
Request: Replace family/genus search modes with one search returning separate collapsible Family and Genus sections, five initial results per section and More revealing up to 20. Update the RPC response, test at localhost:8000, and do not access MariaDB manually. The user explicitly auto-approved the plan and tasks for autonomous execution.

## Approach
Search both groups in one HTTP request. Keep the existing prefix search, accepted Oregon vascular checklist constraints, query length limits, debounce, stale-response protection and family PDF links. Render Family then Genus, initially expanded, with independent accessible arrow buttons and More controls. Each new query resets both groups to expanded with five visible matches. Empty groups display a short empty message when the other group has matches; entirely empty responses retain the existing hidden-suggestions behavior. Keep the database lookup in `pages/rpc/taxonomic-checklist.php`; the endpoint validates input, reads the PDF allowlist, builds the two queries and returns JSON.

## Affected Code
- `js/react/src/taxonomic-checklist/main.jsx`: Consume grouped results and replace mode controls with independent result sections.
- `js/react/src/less/taxonomic-checklist.less`: Style section buttons, arrows and More controls within the existing dropdown.
- `pages/rpc/taxonomic-checklist.php`: Validate input, return grouped empty results, read the allowlist and implement separate family/genus queries directly in the endpoint.
- `classes/TaxonomicChecklistRepository.php`: Removed; query logic remains in the RPC endpoint.
- `tests/TaxonomicChecklistRepositoryTest.php`: Removed; this project does not keep tests for this endpoint.
- `js/react/tests/taxonomic-checklist.test.cjs`: Exercises rendered search interactions.
- Existing architecture/frontend docs: Record the endpoint contract and grouped UI.

## Data Model and Contracts
- Request: `GET /pages/rpc/taxonomic-checklist.php?query=<prefix>`; mode is unnecessary.
- Success, including invalid-length/empty queries: `{"family": [{"label": "...", "family": "..."}], "genus": [{"label": "...", "family": "..."}]}` with empty arrays as appropriate.
- Each group contains at most 20 distinct eligible results, with existing alphabetical ordering and labels preserved.
- Family labels are family names; genus labels retain `Genus - Family (family)`.
- Existing family-list failure remains HTTP 500 with an error object.
- PDF URLs remain `/pages/pdfs/family/<encoded-family>.pdf`.
- Arrow buttons expose `aria-expanded` and `aria-controls`; More affects its own group only.

## Libraries
- React/ReactDOM 16.14 (existing): Component state and rendering; no new dependencies.
- Doctrine ORM 2.7.3 (existing): Parameterized query builder and array hydration.
- LESS 3.13 and Webpack 5 (existing): Existing stylesheet and bundle pipeline.
- Current React and Doctrine documentation retrieved through Context7. Use the installed Doctrine 2.7-compatible query style rather than newer APIs.

## Risks
- Independent limits can starve one group if combined incorrectly: Execute two constrained queries with a shared base and separate 20-result limits.
- Dropdown blur can interrupt button interaction: Preserve pointer behavior and support keyboard focus within the widget.
- Older Doctrine DQL parser: Preserve the existing CASE selection and explicit genus LIKE branches.
- Do not add tests for this project. Verify the endpoint with PHP syntax checks and use browser/API checks for integrated behavior.
- Preserve unrelated work: Leave pre-existing `docs/agent-docs/REVIEW.md` untouched.

## Verification
- Run the relevant syntax/lint checks and production build; record unsupported checks explicitly. Do not add tests.
- Browser worker checks the actual page and API at localhost:8000, uses screenshots for visual verification, and tests controlled larger fixtures if live prefixes do not produce more than five matches.
- All database-backed checks go through the application HTTP API. Do not use database clients, direct database connections, migrations or fixture writes.
