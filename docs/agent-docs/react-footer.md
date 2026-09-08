---
name: react-footer
description: "js/react/src/footer — sitewide footer, stateless presentation component with dynamic copyright year"
---

## Files
- `main.jsx` — single file, entire footer app (`FooterApp` class, but holds no state)

## Behavior
- No API calls, no state, no event handlers
- Copyright year auto-calculated with `new Date().getFullYear()`
- Login link hidden on mobile via `footer.less`
- Reads `clientRoot` from a `data-client-root` attribute on the `#footer-app` mount element
- Mounts via the deprecated `ReactDOM.render()`

## Layout
1. Navbar: Contact, Disclaimer (external), Site Map, Login links + copyright
2. Footer content: 3-column grid — Donation, OSU org info, Symbiota framework info

## Gotchas
- A "Site Feedback" mailto link is commented out rather than removed (preserved per project convention of keeping intent/history in comments)

## Related
[[react-frontend]], [[react-less]]
