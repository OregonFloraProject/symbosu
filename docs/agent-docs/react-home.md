---
name: react-home
description: "js/react/src/home — home page carousel/search, news/events page, newsletters archive"
---

## Files
| File | Purpose |
|------|---------|
| `main.jsx` | Home page: react-slick carousel, `SearchWidget`, YouTube modal, news/events preview |
| `whatsnew.jsx` | Full news and events page |
| `newsletters.jsx` | Newsletter archive with collapsible issues/articles |

All three accept only a `clientRoot` prop.

## API Calls
| Component | Endpoint | Notes |
|-----------|----------|-------|
| Home | `./home/rpc/api.php` | Fetches a preview: 3 news items + 2 events |
| WhatsNew | `../home/rpc/api.php` | Fetches full lists |
| Newsletters | `./rpc/api.php` | Returns an object (not array) of issues keyed by volume, to preserve sort order |
| Home `onSearch` | `{clientRoot}/taxa/rpc/api.php?synonym={taxonId}` | Synonym uniqueness check |
| `SearchWidget` | `{suggestionUrl}?q={query}` | Autocomplete, throttled 500ms |

## Gotchas
- `onSearch()` synonym logic is copied verbatim from `header/main.jsx` — see [[react-header]]; both files carry a warning comment about the duplication
- `newsletters.jsx` converts the API's object response to an array manually in `componentDidMount`
- `toggleItemDisplay()` in Newsletters mutates state via a reference copy (`let newArr = this.state.issues`) rather than a real copy
- Home uses `.splice(0, 3)` / `.splice(0, 2)` to trim news/events to preview counts; WhatsNew fetches the full lists instead
- `dangerouslySetInnerHTML` used for all news/event content (server HTML assumed pre-sanitized)
- Direct DOM title manipulation via `document.getElementsByTagName('title')[0].innerHTML = ...`
- `SlickButtonFix` wrapper component works around a react-slick arrow-button rendering issue

## Related
[[react-frontend]], [[react-header]], [[react-common]]
