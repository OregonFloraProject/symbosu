# Plan: Convert the Taxonomic Checklist page to React

Status: approved
Request: Convert the Taxonomic Checklist page to a React page and leave its PHP file as a shell like `taxa/index.php`.

## Approach
Move the existing Taxonomic Checklist page content and family/genus search interaction into a new React bundle. Keep `pages/taxonomic-checklist.php` as the shared-header/footer shell and mount point. Preserve the existing search JSON contract by moving its PHP implementation to a dedicated RPC filename and updating the React endpoint URL. Preserve the page's existing copy, downloads, links, and family PDF selection behavior.

## Affected Code
- `pages/taxonomic-checklist.php`: Replace page markup and inline JavaScript with the application shell and React bundle include.
- `pages/rpc/taxonomic-checklist.php`: Move the current JSON search implementation to a distinct endpoint so the page route can use the current path.
- `pages/rpc/taxonomic-checklist-search.php`: Serve the existing family/genus autocomplete JSON contract.
- `js/react/src/taxonomic-checklist/main.jsx`: Render the page content and implement debounced family/genus search, suggestion selection, and PDF link confirmation.
- `js/react/webpack.config.js`: Add the taxonomic-checklist bundle entry.
- `docs/agent-docs/arch-page-entry-points.md`: Update the page-to-bundle/RPC reference.

## Data Model and Contracts
- Preserve the RPC response shape: an array of `{label, family, name}` matches; retain existing `400` invalid-mode behavior, empty-array handling for blank/oversized queries, and the current server-error response.
- Keep the page's existing family PDF URL pattern: `pages/pdfs/family/<encoded-family>.pdf`.

## Libraries
- React (existing project dependency): render the page and manage interaction state.

## Risks
- The current PHP page contains substantial editorial content and several downloads: preserve all visible content and links during migration.
- The RPC filename changes: update the React caller and page-entry documentation together; retain the response contract.
- Autocomplete requests can resolve out of order: cancel or ignore stale requests when the query or mode changes.
