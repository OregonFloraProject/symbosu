# Playwright Testing Plan: Unified Taxa Profile Page

Status: ready to execute
Spec: spec.md
Satisfies: AC-7 (covers AC-1, AC-2, AC-3, AC-4, AC-5, AC-6)
Owner: user, to run later against a running site

This plan verifies that the unified taxa page (`taxa-unified.js`) reproduces the old
per-route pages (`taxa.js`, `taxa-garden.js`, `taxa-rare.js`) with no visible or
functional change, and that the `$TAXA_UNIFIED_FLAG` toggle is reversible and safe.
It is written as a manual runbook that doubles as automated Playwright code. Run the
automated sketches where noted, and record every result in the table in section 14.

No file in the application is created or changed by running this plan. The Playwright
harness lives outside the application repository.

---

## 0. Prerequisites

### 0.1 Two server states

Every parity check compares the unified build against the old build. The cleanest way
is two live document roots served under different origins, so both can be queried in
the same Playwright run.

| Env var | Meaning | `$TAXA_UNIFIED_FLAG` | Bundle loaded |
| --- | --- | --- | --- |
| `BASE_UNIFIED` | Unified state | `1` | `taxa-unified.js` |
| `BASE_BASELINE` | Old state | `0` (default) | `taxa.js` / `taxa-garden.js` / `taxa-rare.js` |

Acceptable alternatives when a second host is not available:

1. Flip `$TAXA_UNIFIED_FLAG` in `config/symbini.php`, reload, and capture baseline
   artifacts first, then unified artifacts, then diff offline.
2. Use a saved recording of the flag-off pages captured before the feature landed.
   Section 7 requires this recording for the byte-identical check.

After editing the constant, clear PHP opcache or restart PHP-FPM so the new value is
read. The default in `config/symbini.php` and `config/symbini_template.php` is `0`.

### 0.2 Build

The unified bundle must exist when the flag is on:

```bash
cd js/react
npm install          # required once; runs patch-package
npm run build        # produces dist/taxa-unified.js alongside the old bundles
```

If `dist/taxa-unified.js` is missing while the flag is on, every page fails at
`filemtime()` and the mount div stays empty. Confirm the file exists before starting.

### 0.3 Rare route gate

`taxa/rare.php` line 3 redirects to the login page when `!$RPG_FLAG && !$SYMB_UID`.
Rare tests require `$RPG_FLAG = 1` (the shipped default in this environment) or a
logged-in session. This gate is unchanged by the feature and applies in BOTH flag
states. Verify it once with `$RPG_FLAG = 0` and a logged-out context, and once with a
logged-in context.

### 0.4 Test data

Substitute real ids for the placeholders. The core examples `2454` and `6617`
(ambiguous synonym) appear in in-repo comments; garden and rare need a taxon that has
that profile.

| Placeholder | Needed route | Notes |
| --- | --- | --- |
| `<TID_CORE>` | core species | `rankId > 180`, has descriptions and human-observation images. Example `2454`. |
| `<TID_GENUS>` | core chooser | `rankId <= 180` (`RANK_GENUS = 180`). |
| `<TID_FAMILY>` | core chooser | `rankId <= 140` (`RANK_FAMILY = 140`). |
| `<TID_AMBIG>` | core ambiguous | Has `acceptedSynonyms`. Example `6617`. |
| `<TID_GARDEN>` | garden | Has a garden profile and a `gardenId`. |
| `<TID_RARE>` | rare | Has a rare profile. |
| `<TID_RESTRICTED>` | rare | `accessRestricted = true`. |

### 0.5 Playwright harness

Install outside the application repo. Chromium only.

```bash
mkdir -p /tmp/opencode/uxt-playwright && cd /tmp/opencode/uxt-playwright
npm init -y
npm i -D @playwright/test
npx playwright install chromium
```

`playwright.config.js`:

```js
const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
  testDir: './tests',
  timeout: 30000,
  retries: 0,
  reporter: [['list'], ['json', { outputFile: 'results.json' }]],
  use: {
    headless: true,
    actionTimeout: 15000,
    trace: 'retain-on-failure',
  },
});
```

Shared helpers (`tests/helpers.js`):

```js
const BASE = {
  unified: process.env.BASE_UNIFIED || 'http://localhost:8081',
  baseline: process.env.BASE_BASELINE || 'http://localhost:8082',
};

function url(state, path) {
  return BASE[state] + path;
}

async function waitForApp(page) {
  // Loading overlay keeps the class "loading" until data arrives, then drops it.
  await page.waitForSelector('.loading-overlay:not(.loading)', { timeout: 20000 });
}

// Normalize known per-render randomness so old and unified DOM can be compared.
function normalize(html) {
  return html
    .replace(/carousel-[a-z0-9]+/g, 'carousel-ID') // Math.random() id in common/imageGallery.jsx
    .replace(/\?[0-9]{9,12}/g, '?MTIME');           // filemtime cache-bust query strings
}

async function domSnapshot(page, mountSelector) {
  await waitForApp(page);
  const html = await page.locator(mountSelector).evaluate((el) => el.outerHTML);
  return normalize(html);
}

module.exports = { BASE, url, waitForApp, normalize, domSnapshot };
```

Run with:

```bash
BASE_UNIFIED=http://localhost:8081 BASE_BASELINE=http://localhost:8082 npx playwright test
```

---

## 1. Smoke per route

Purpose: with the flag on, each route loads `taxa-unified.js`, keeps its mount div, and
renders the layout that belongs to that route. With the flag off, each route loads its
old bundle.

Mount divs never change with the flag:

| Route | URL | Mount div | Shell marker | Flag off bundle |
| --- | --- | --- | --- | --- |
| core | `/taxa/index.php?taxon=<TID_CORE>` | `#react-taxa-app` | `.container.taxa-detail` | `taxa.js` |
| garden | `/taxa/garden.php?taxon=<TID_GARDEN>` | `#react-taxa-garden-app` | `.container.pl-4.pr-4.pt-5` | `taxa-garden.js` |
| rare | `/taxa/rare.php?taxon=<TID_RARE>` | `#react-taxa-rare-app` | `.container.py-5` plus `.profile-type` text `Rare Plant Profile` | `taxa-rare.js` |

Automated sketch, parameterized:

```js
const ROUTES = {
  core: {
    path: '/taxa/index.php?taxon=' + process.env.TID_CORE,
    mount: '#react-taxa-app',
    unifiedScript: /taxa-unified\.js/,
    offlineScript: /\/taxa\.js/,
    markers: ['.container.taxa-detail'],
  },
  garden: {
    path: '/taxa/garden.php?taxon=' + process.env.TID_GARDEN,
    mount: '#react-taxa-garden-app',
    unifiedScript: /taxa-unified\.js/,
    offlineScript: /taxa-garden\.js/,
    markers: ['.container.pl-4.pr-4.pt-5'],
  },
  rare: {
    path: '/taxa/rare.php?taxon=' + process.env.TID_RARE,
    mount: '#react-taxa-rare-app',
    unifiedScript: /taxa-unified\.js/,
    offlineScript: /taxa-rare\.js/,
    markers: ['.container.py-5', '.profile-type'],
  },
};

for (const [name, r] of Object.entries(ROUTES)) {
  test(`smoke ${name}: unified bundle and layout`, async ({ page }) => {
    await page.goto(url('unified', r.path));
    await waitForApp(page);
    await expect(page.locator('script[src*="taxa-unified.js"]')).toHaveCount(1);
    await expect(page.locator(r.mount)).toBeVisible();
    for (const marker of r.markers) {
      await expect(page.locator(marker).first()).toBeVisible();
    }
    await expect(page.locator('.print-header')).toHaveCount(1);
    await expect(page.locator('.row.print-start .print-trigger')).toHaveText('Print page');
  });

  test(`smoke ${name}: flag off loads old bundle`, async ({ page }) => {
    await page.goto(url('baseline', r.path));
    await waitForApp(page);
    const scripts = await page.locator('script[src]').evaluateAll((els) =>
      els.map((e) => e.getAttribute('src')),
    );
    expect(scripts.some((s) => r.offlineScript.test(s))).toBe(true);
    expect(scripts.some((s) => /taxa-unified\.js/.test(s))).toBe(false);
  });
}
```

Expected: all six smoke checks pass. A missing unified script means the PHP flag gate
is not reading `1`; an empty mount div means the bundle is missing or threw during
bootstrap (check the browser console for a React error).

AC covered: AC-1, AC-2, AC-3, AC-6.

---

## 2. Parity checks per route

The strongest parity check is a normalized DOM comparison of the mount div between
`BASE_BASELINE` (flag off) and `BASE_UNIFIED` (flag on), with the image modal closed.
Run it first; the targeted checks below explain and localize any mismatch.

```js
for (const [name, r] of Object.entries(ROUTES)) {
  test(`parity ${name}: normalized DOM matches`, async ({ browser }) => {
    const a = await (await browser.newContext()).newPage();
    const b = await (await browser.newContext()).newPage();
    await a.goto(url('baseline', r.path));
    await b.goto(url('unified', r.path));
    const [baseHtml, unifiedHtml] = await Promise.all([
      domSnapshot(a, r.mount),
      domSnapshot(b, r.mount),
    ]);
    expect(unifiedHtml).toBe(baseHtml);
  });
}
```

If the diff is only the image modal subtree, that is expected only when a modal is
open. This snapshot runs with modals closed. The modal is encapsulated in
`TaxaImageGallery` on the unified page, so its parent chain differs from the old pages
when open; section 2.6 tests the modal behaviorally instead of structurally.

### 2.1 `document.title`

The title side-effect runs after data loads. Both states must produce the same string.

| Route | Expected `document.title` | Selector check |
| --- | --- | --- |
| core species | `${DEFAULT_TITLE} ${sciName}` | `await expect(page).toHaveTitle(...)` |
| core chooser | `${DEFAULT_TITLE} ${sciName || family}` | genus/family fallback |
| garden | `${DEFAULT_TITLE} ${sciName}` | |
| rare | `${DEFAULT_TITLE} - ${sciName} - Rare Plant Profile` | |

`DEFAULT_TITLE` is read from the header `data-props` (`react-header`). For the
automated check, read it from the page instead of hardcoding:

```js
const defaultTitle = await page.evaluate(() =>
  JSON.parse(document.getElementById('react-header').getAttribute('data-props'))['defaultTitle'],
);
await waitForApp(page);
await expect(page).toHaveTitle(new RegExp('^' + defaultTitle.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')));
```

Then compare the full title string between baseline and unified per route and assert
equality. Title strings differ by route, so compare like for like, never core against
garden.

### 2.2 `h1` / `h2` order and classes

| Route | Expected heading structure |
| --- | --- |
| core species | `h1` = `<span.font-italic>{sciName}</span> {author}`; `h2` = `{vernacularNames[0]}`. When ambiguous: `h2.ambiguous` starts with `In Oregon, this name is a synonym for the following accepted taxon/taxa: ` and contains `.font-italic`. Optional `.synonym` span when `synonym` is present. |
| core chooser | `h1` = `{sciName} {author}`; no `h2`. |
| garden | `h1` = `{vernacularNames[0]}` with no extra class; `h2.font-italic` = `{sciName}`. |
| rare | `h1.font-italic` = `{sciName}`; `h2` = `{vernacularNames[0]}`. |

Automated sketch, per route:

```js
test('core species heading order', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_CORE));
  await waitForApp(page);
  const h1 = page.locator('.row.print-start h1');
  await expect(h1.locator('span.font-italic')).toHaveCount(1);
  await expect(page.locator('.row.print-start h1 + h2')).toHaveCount(1);
});
```

Also run the ambiguous case (`<TID_AMBIG>`): expect `.row.print-start h2.ambiguous`, an
`#subspecies` block titled `Accepted taxon` or `Accepted taxa`, and no `#img-main` and
no `.distribution` MapItem (both are suppressed for ambiguous taxa).

### 2.3 Hero image `#img-main`

All routes that have an image render exactly one hero figure:

```html
<figure>
  <div class="img-main-wrapper"><img id="img-main" ...></div>
  <figcaption>{photographer}</figcaption>
</figure>
```

Checks:

- `page.locator('#img-main')` has count 1 and is visible.
- Its `src` equals the baseline `src`.
- `.img-main-wrapper + figcaption` is not empty and equals the baseline text.
- When the taxon has no images: core/garden render no `#img-main`; rare falls back to
  the first herbarium image (`images[0] ?? herbariumImages[0]`), so `#img-main` is
  present whenever either basis has an image.

### 2.4 Description rendering

| Route | Expected |
| --- | --- |
| core | `.description-tabs` present when `descriptions.length > 0`; tabs render one `Tab` per description caption; first `.tabTitle` equals the first caption. |
| rare | Same, wrapped in `.taxa-prose`. Tab order is `Summary` (profile 8) first, then `Taxon description` (first non-8, non-9 profile), filtered to non-empty `desc`. |
| garden | No tabs. A single `p.mt-4` holds `gardenDescription` with glossary tooltips. |

Automated rare summary check (AC-4 profile-8 filtering):

```js
test('rare uses profile 8 as Summary', async ({ page }) => {
  await page.goto(url('unified', '/taxa/rare.php?taxon=' + process.env.TID_RARE));
  await waitForApp(page);
  const tabs = page.locator('.taxa-prose .description-tabs .tabTitle');
  await expect(tabs.first()).toHaveText('Summary');
  if ((await tabs.count()) > 1) {
    await expect(tabs.nth(1)).toHaveText('Taxon description');
  }
});
```

Compare the full `.description-tabs` outerHTML (normalized) between states for core and
rare, and the `p.mt-4` outerHTML for garden.

### 2.5 Carousels and bases

The unified `TaxaImageGallery` wraps `common/imageGallery.jsx` plus `common/modal.jsx`.
Each gallery renders as `<div class="mt-4 dashed-border taxa-slideshows">` containing
`h3.text-light-green` plus thumbnail cards (`img.d-block`).

- core: dual basis. `Photo images` gallery for `imagesBasis.HumanObservation` and
  `Herbarium specimens` gallery for `imagesBasis.PreservedSpecimen`, each only when
  that array has length > 0. Ambiguous taxa render neither.
- rare: dual basis, same two headings (`{sciName} images` rendered as an italic span for
  the photo gallery, `Herbarium specimens`).
- garden: single basis. One gallery titled `{vernacularNames[0]} images`, images are
  `imagesBasis.HumanObservation`.

Automated checks:

```js
test('core has two galleries with the expected headings', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_CORE));
  await waitForApp(page);
  const headings = page.locator('.taxa-slideshows h3');
  await expect(headings.nth(0)).toHaveText('Photo images');
  await expect(headings.nth(1)).toHaveText('Herbarium specimens');
});

test('garden has one gallery', async ({ page }) => {
  await page.goto(url('unified', '/taxa/garden.php?taxon=' + process.env.TID_GARDEN));
  await waitForApp(page);
  await expect(page.locator('.taxa-slideshows')).toHaveCount(1);
});
```

Compare collapsed thumbnail counts (`.taxa-slideshows img.d-block`) per gallery between
baseline and unified.

### 2.6 Image modal open, close, and basis toggle

Modal DOM: `.modal-backdrop > .modal-content`, caption
`h3 > span{modalTitle} images`, carousel `.lightbox-wrapper` with `#main-lightbox`, and
the close control `.close-modal` (a FontAwesome SVG).

Open:

```js
test('core modal opens on thumbnail click and closes', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_CORE));
  await waitForApp(page);
  const photoGallery = page.locator('.taxa-slideshows').nth(0);
  await photoGallery.locator('img.d-block').first().click();
  const modal = page.locator('.modal-backdrop');
  await expect(modal).toBeVisible();
  await expect(modal.locator('.modal-content h3')).toContainText('images');
  await page.locator('.close-modal').first().click();
  await expect(page.locator('.modal-backdrop')).toHaveCount(0);
});
```

Basis toggle (core and rare). The preserved-specimen view omits the
`{fulldate} (c) {photographer}, Courtesy of OregonFlora` line that the
human-observation view contains (`imageModalCarousel.jsx` renders that block only when
`basisofrecord != 'PreservedSpecimen'`).

```js
test('rare basis toggle switches modal images', async ({ page }) => {
  await page.goto(url('unified', '/taxa/rare.php?taxon=' + process.env.TID_RARE));
  await waitForApp(page);
  // Human observation basis: location credit line is present.
  await page.locator('.taxa-slideshows').nth(0).locator('img.d-block').first().click();
  await expect(page.locator('.modal-backdrop .image-details')).toContainText('Courtesy of OregonFlora');
  await page.locator('.close-modal').first().click();

  // Preserved specimen basis: credit line is absent.
  const herbCount = await page.locator('.taxa-slideshows').nth(1).locator('img.d-block').count();
  test.skip(herbCount === 0, 'no herbarium images for this tid');
  await page.locator('.taxa-slideshows').nth(1).locator('img.d-block').first().click();
  await expect(page.locator('.modal-backdrop .image-details')).not.toContainText('Courtesy of OregonFlora');
});
```

Garden has no basis toggle: one gallery, one basis, same open/close behavior.

### 2.7 Sidebar sections

The sidebar column is `.row.mt-2.main-wrapper > .col-md-4.sidebar-section`. Every
section inside carries `.sidebar-section` as well, with an
`h3.text-light-green.font-weight-bold` title. Assert the ordered list of titles per
route rather than counting `.sidebar-section`, because the column shares the class.

Expected order by route (conditional sections noted):

| Route | Sidebar headings in order |
| --- | --- |
| core species | Context, Distribution, Web links |
| core chooser | Context, Web links (no Distribution) |
| core ambiguous | Web links only (Context and Distribution suppressed) |
| garden | Highlights, Native plant groups (only when matched), Plant Facts, Growth and Maintenance, Commercial Availability |
| rare | Context, Distribution, Survey & Manage, Look-Alikes, Associated species |

```js
const EXPECTED = {
  core: ['Context', 'Distribution', 'Web links'],
  garden: ['Highlights', 'Plant Facts', 'Growth and Maintenance', 'Commercial Availability'],
  rare: ['Context', 'Distribution', 'Survey & Manage', 'Look-Alikes', 'Associated species'],
};
```

Vendor, look-alikes, and associated species come from the existing read-only components
(`SideBarSectionVendor`, `SideBarSectionLookalikesTable`, `SideBarSectionSpeciesList`)
and expose their own inner selectors:

- vendor rows: `.sidebar-section .row.dashed-border .char-label` and vendor links
  `a[href*="/checklists/checklist.php?cl="]` with `&pid=4`.
- look-alikes: `.row.dashed-border.is-header` plus `.lookalike-sciname` links to
  `taxa/index.php?taxon=`.
- associated species: `.associated-sciname` links.

Content checks:

- Every non-empty section has a trailing `<span class="row dashed-border">`.
- Empty sections keep `d-none` and are not visible.
- Context on core species includes Family, Common Names, Synonyms, Origin, and
  optional Status / More info rows. Context on rare uses rare-flavor rows with
  glossary tooltips.
- Web links on core always includes the replaced `IPNI` and `USDA PLANTS Database`
  entries; any data-driven `ipni`/`usda` link is filtered out.

### 2.8 MapItem and restricted overlay

MapItem: `.sidebar-section.mb-5.distribution` with heading `Distribution`, image
`img[src*="/images/maps/"]`, and a `.map-overlay-box` that carries `hidden` unless
toggled.

Unrestricted taxon (`needsPermission = false`): clicking `.map-link` calls
`window.open` with the leaflet URL.

```js
test('unrestricted map opens leaflet map', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_CORE));
  await waitForApp(page);
  const [popup] = await Promise.all([
    page.waitForEvent('popup'),
    page.locator('.distribution .map-link').first().click(),
  ]);
  expect(popup.url()).toContain('leafletmap.php');
});
```

Restricted taxon (`needsPermission = true`): clicking toggles the overlay in place and
no popup opens.

```js
test('restricted map toggles overlay', async ({ page }) => {
  await page.goto(url('unified', '/taxa/rare.php?taxon=' + process.env.TID_RESTRICTED));
  await waitForApp(page);
  const box = page.locator('.distribution .map-overlay-box');
  await expect(box).toHaveClass(/hidden/);
  await page.locator('.distribution .map-link').first().click();
  await expect(box).not.toHaveClass(/hidden/);
  await expect(box).toContainText('Access to detailed locality data limited');
  await expect(box.locator('a.inner-link')).toHaveAttribute('href', /rare\/policy\.php\?refurl=/);
});
```

Compare `needsPermission` behavior between baseline and unified for the same tid.
Ambiguous core taxa have no MapItem and core chooser has no MapItem.

### 2.9 Cross-profile buttons and legacy fact sheet

Core `More info` rows (Context sidebar) render as buttons built from `moreInfo`:

- `Rare Plant Profile` when `specialChecklists` includes `CLID_RARE_ALL = 14948`,
  otherwise `Rare Plant Fact Sheet` when `rarePlantFactSheet` is non-empty.
- `Garden Fact Sheet` when `gardenId > 0`.
- A PDF URL renders the button with the `images/pdf24.png` icon.

Garden and rare render a `.taxa-link` block:

- Garden: one link to `{clientRoot}/taxa/index.php?taxon=<tid>` with button text
  `Core profile page`.
- Rare: `Core profile page` (with `style="margin-right: 1rem"`) plus, when
  `legacyFactSheetUrl` is set, an `a[target="_blank"][rel="noreferrer"]` to
  `{clientRoot}{legacyFactSheetUrl}` with button text `Legacy fact sheet` and the PDF
  icon. The legacy button is absent otherwise.

```js
test('rare cross links', async ({ page }) => {
  await page.goto(url('unified', '/taxa/rare.php?taxon=' + process.env.TID_RARE));
  await waitForApp(page);
  await expect(page.locator('.taxa-link a[href*="/taxa/index.php?taxon="] button')).toHaveText('Core profile page');
});
```

Compare presence, href, target, rel, and button text between baseline and unified.

### 2.10 Data fetching contract

Intercept and assert the requests the unified page makes per route. The endpoints and
query shapes must be identical to the old pages.

| Route | Requests |
| --- | --- |
| core | `./rpc/api.php?taxon=<tid>`, `../glossary/rpc/getterms.php` |
| garden | `./rpc/api.php?taxon=<tid>&type=garden`, `../glossary/rpc/getterms.php`, `${clientRoot}/garden/rpc/api.php?canned=true`, `${clientRoot}/checklists/rpc/api-vendor.php?action=taxa_garden&tid=<tid>` |
| rare | `./rpc/api.php?taxon=<tid>&type=rare`, `../glossary/rpc/getterms.php` |

```js
test('garden extra calls preserved', async ({ page }) => {
  const seen = [];
  page.on('request', (r) => seen.push(r.url()));
  await page.goto(url('unified', '/taxa/garden.php?taxon=' + process.env.TID_GARDEN));
  await waitForApp(page);
  expect(seen.some((u) => u.includes('/rpc/api.php?taxon=') && u.includes('type=garden'))).toBe(true);
  expect(seen.some((u) => u.includes('/garden/rpc/api.php?canned=true'))).toBe(true);
  expect(seen.some((u) => u.includes('api-vendor.php?action=taxa_garden'))).toBe(true);
});
```

Garden native groups appear only when a canned search's `clid` is in the taxon's
`specialChecklists`; assert the Native plant groups block and its
`.canned-search-result` count match baseline. Opening a `.canned-title` must open
`ExplorePreviewModal`.

---

## 3. Redirect cases

All redirects are client-side after the bundle loads, so wait for the final URL.

| Case | Start URL | Expected final URL | Flag states |
| --- | --- | --- | --- |
| no query params | `/taxa/index.php` | `{BASE}/` | both |
| `search` param | `/taxa/index.php?search=Rosa` | `/taxa/search.php?search=Rosa` (URL-encoded) | both |
| `tid` alias core | `/taxa/index.php?tid=<TID_CORE>` | stays, renders core species | both |
| `tid` alias rare | `/taxa/rare.php?tid=<TID_RARE>` | stays, renders rare profile | both |
| `tid` alias garden | `/taxa/garden.php?tid=<TID_GARDEN>` | `{BASE}/` (no normalization) | both |
| `taxon` garden | `/taxa/garden.php?taxon=<TID_GARDEN>` | stays, renders garden | both |
| invalid `taxon=-1` (all routes); invalid `tid=-1` (core and rare only, garden does not normalize `tid`) | `/taxa/index.php?taxon=-1`, `/taxa/index.php?tid=-1`, `/taxa/rare.php?taxon=-1`, `/taxa/rare.php?tid=-1`, `/taxa/garden.php?taxon=-1` | `{BASE}/` | both |

The garden exception is deliberate: `main-unified.jsx` normalizes `tid` to `taxon` only
when the variant is not garden, matching the old garden page.

```js
test('no tid redirects home', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php'));
  await page.waitForURL(new RegExp('^' + process.env.BASE_UNIFIED + '/?$'));
});

test('garden tid alias does not normalize', async ({ page }) => {
  await page.goto(url('unified', '/taxa/garden.php?tid=' + process.env.TID_GARDEN));
  await page.waitForURL(new RegExp('^' + process.env.BASE_UNIFIED + '/?$'));
});
```

Run every row in both flag states and confirm identical outcomes.

With `$RPG_FLAG = 0` and a logged-out context, `/taxa/rare.php?...` must redirect to
`../profile/index.php?refurl=...` in both flag states. This is the AC-6 gate check.

---

## 4. Chooser layout on core

When `rankId <= RANK_GENUS` (180), the core variant renders the chooser: `h1` with
`{sciName} {author}`, no `h2`, an optional `#subspecies` grid titled
`Species, subspecies and varieties`, and a sidebar of Context plus Web links with no
MapItem. Family taxa (`rankId <= 140`) behave the same and fall back to `family` in the
title when `sciName` is empty.

```js
test('genus chooser layout', async ({ page }) => {
  await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_GENUS));
  await waitForApp(page);
  await expect(page.locator('.container.taxa-detail')).toBeVisible();
  await expect(page.locator('.row.print-start h2')).toHaveCount(0);
  await expect(page.locator('.distribution')).toHaveCount(0);
  const titles = page.locator('.col-md-4.sidebar-section h3.text-light-green');
  await expect(titles).toHaveText(['Context', 'Web links']);
});
```

Compare the chooser DOM snapshot and title with baseline. Note the unified chooser
passes `wrapperClassName="row-cols-sm-2"`, giving
`.row.mt-2.row-cols-sm-2.main-wrapper`, matching `TaxaChooser`.

---

## 5. Resize behavior at 1200 / 992

There are two independent width mechanisms, and only one reaches the DOM.

- `useSlideshowCount` computes 5 at width >= 1200, 4 below 1200, and 3 below 992. This
  value is passed to `common/imageGallery.jsx` as the `slideshowCount` prop.
- `common/imageGallery.jsx` does not read `slideshowCount`. Its collapsed preview row
  derives from a separate 768px breakpoint: 5 cards at width >= 768 and 3 cards below
  768 (`PC_ROW_LIMIT` / `PHONE_ROW_LIMIT`).

Consequence for testing: the 1200 and 992 breakpoints do not change the rendered card
count. Do not assert an absolute count change at those widths; a test that does will
fail for both the old and unified pages because they share `imageGallery.jsx`. Instead
assert parity with baseline at each width.

```js
test('slideshow card count matches baseline at each width', async ({ browser }) => {
  for (const width of [1440, 1100, 900, 700]) {
    const page = await browser.newPage({ viewport: { width, height: 900 } });
    await page.goto(url('unified', '/taxa/index.php?taxon=' + process.env.TID_CORE));
    await waitForApp(page);
    const unifiedCount = await page.locator('.taxa-slideshows').nth(0).locator('img.d-block').count();
    // Repeat against baseline and compare.
  }
});
```

Observable expectations to record:

- width >= 768: 5 collapsed preview cards per gallery.
- width < 768: 3 collapsed preview cards per gallery.
- Expanding a gallery (`.slick-down` chevron) shows the full page slice, unaffected by
  the collapse preview limits.
- Baseline and unified must match at every width, and a manual browser resize must
  produce the same transition timing.

This is a pre-existing behavior in the shared gallery, not a regression introduced by
the unified page. Confirm no LESS/CSS changed (spec Non-Goals).

---

## 6. Print button

Every route renders `.print-trigger` inside `.row.print-start`. Clicking it must call
`window.print()` exactly once. Stub `window.print` before navigation and count calls.

```js
for (const [name, r] of Object.entries(ROUTES)) {
  test(`print button calls window.print: ${name}`, async ({ page }) => {
    await page.addInitScript(() => {
      window.__printCalls = 0;
      window.print = () => { window.__printCalls += 1; };
    });
    await page.goto(url('unified', r.path));
    await waitForApp(page);
    await page.locator('.print-trigger').click();
    const calls = await page.evaluate(() => window.__printCalls);
    expect(calls).toBe(1);
  });
}
```

Also assert the print-only header content: `.print-header` equals `{pageTitle}` plus a
`<br>` plus `window.location.href`, where `pageTitle` matches the title table in 2.1.
Compare baseline and unified.

Run all three flag-on and once per route flag-off to confirm the old button is
untouched.

---

## 7. Flag off: old pages byte-identical to baseline

This check proves the feature is revertable with zero rendered change. Choose one
baseline source:

1. A recording of the three pages captured before the feature landed (preferred for a
   true byte comparison).
2. A second document root checked out at the parent commit of the flag wiring.
3. `git show <pre-feature-commit>:taxa/index.php` compared against the current file's
   emitted HTML for the `#innertext` region.

Steps per route:

1. Load the page with `$TAXA_UNIFIED_FLAG = 0` and `waitForApp`.
2. Capture `document.querySelector('#innertext').outerHTML`.
3. Remove `<script>` elements (they carry `filemtime` query strings) and normalize
   Carousel ids with `normalize()` from section 0.5.
4. Compare to the baseline recording. They must be identical.

Additionally assert at the HTML source level:

```js
test('flag off emits only the old bundle', async ({ page }) => {
  const res = await page.goto(url('baseline', '/taxa/index.php?taxon=' + process.env.TID_CORE));
  const html = await res.text();
  expect(html).toMatch(/\/js\/react\/dist\/taxa\.js\?/);
  expect(html).not.toContain('taxa-unified.js');
  expect(html).toContain('id="react-taxa-app"');
});

test('flag off garden and rare emit old bundles', async ({ page }) => {
  const garden = await (await page.goto(url('baseline', '/taxa/garden.php?taxon=' + process.env.TID_GARDEN))).text();
  expect(garden).toMatch(/taxa-garden\.js\?/);
  expect(garden).not.toContain('taxa-unified.js');

  const rare = await (await page.goto(url('baseline', '/taxa/rare.php?taxon=' + process.env.TID_RARE))).text();
  expect(rare).toMatch(/taxa-rare\.js\?/);
  expect(rare).not.toContain('taxa-unified.js');
});
```

`rare.php` only emits any bundle when `$RPG_FLAG === 1`. With `$RPG_FLAG = 0` and no
session, the page redirects and emits none. Confirm the redirect is unchanged.

Mount divs stay `react-taxa-app`, `react-taxa-garden-app`, and `react-taxa-rare-app` in
both flag states.

---

## 8. Removed `imageCount` prop

The old garden page passes `imageCount={this.state.length}` (undefined) to
`ImageGallery`. The unified page omits the prop because `common/imageGallery.jsx`
derives counts internally from `images.length` and never reads `imageCount`.
`imageCount` is not rendered to the DOM, so its removal is not directly observable in
markup. Prove visual identity instead.

Checks:

1. Normalized DOM parity of the garden `.main-section` between flag-off and flag-on
   (section 2 base test, garden row).
2. `.taxa-slideshows` count is 1 on both states.
3. Count of collapsed thumbnail cards (`.taxa-slideshows img.d-block`) is equal.
4. Expand a gallery and compare the rendered card count and pagination controls
   (`.pagination` / `select#pageSize`) between states.
5. Screenshot the `.main-section` at a fixed viewport (for example 1440x1200) in both
   states and diff pixel-for-pixel. Any difference is a failure unless it is the
   known random carousel id, which does not render.

```js
test('garden gallery visual parity', async ({ browser }) => {
  const u = await (await browser.newContext({ viewport: { width: 1440, height: 1200 } })).newPage();
  const b = await (await browser.newContext({ viewport: { width: 1440, height: 1200 } })).newPage();
  await u.goto(url('unified', '/taxa/garden.php?taxon=' + process.env.TID_GARDEN));
  await b.goto(url('baseline', '/taxa/garden.php?taxon=' + process.env.TID_GARDEN));
  await Promise.all([waitForApp(u), waitForApp(b)]);
  const uCount = await u.locator('.taxa-slideshows img.d-block').count();
  const bCount = await b.locator('.taxa-slideshows img.d-block').count();
  expect(uCount).toBe(bCount);
  // Optional pixel diff of '.main-section'.
});
```

---

## 9. Empty and fallback image cases

- Core and garden with no human-observation images: no `#img-main`, no visible gallery
  content. Core still renders a `Herbarium specimens` gallery if that basis is
  non-empty.
- Rare with no human-observation images: hero falls back to the first herbarium image;
  modal basis remains correct.
- Core ambiguous taxa: no hero, no galleries, `#subspecies` accepted-taxa grid only.

Pick tids that exercise each case or stub the RPC response with Playwright
`page.route` using a captured API payload.

---

## 10. Error and loading states

- The loading overlay `.loading-overlay` starts with class `loading` and drops it after
  the RPC resolves, in all three variants.
- Rare renders `.alert.alert-danger` with text `An error occurred. Please try again
  later.` when its RPC throws. Force this with `page.route('**/rpc/api.php**', route =>
  route.abort())` and confirm the alert appears and the title remains the default.
- Core and garden do not render the error alert (unchanged from old pages).

---

## 11. Glossary tooltips

`useGlossary` fetches `../glossary/rpc/getterms.php`. On success, glossary-tooltipped
terms are wrapped by `addGlossaryTooltips`. Confirm the same terms are wrapped as in
baseline. On fetch failure, the page still renders and the error is logged only.

---

## 12. Execution order

1. Confirm the build and both server states are up (section 0).
2. Smoke (section 1).
3. Normalized DOM parity (section 2 intro) to catch drift early.
4. Targeted parity checks (sections 2.1 through 2.10).
5. Redirects (section 3), chooser (section 4), resize (section 5), print (section 6).
6. Flag off identity (section 7), imageCount (section 8).
7. Edge sections 9, 10, 11 as time allows.

Any failure in sections 3 through 8 is a release blocker because the spec requires
functional parity. Failures in sections 9 through 11 should be triaged against baseline
first; if baseline shows the same behavior, it is pre-existing and out of scope per the
spec Non-Goals.

---

## 13. Playwright snippets for dialog and viewport handling

Print uses a stub, not a dialog:

```js
await page.addInitScript(() => { window.print = () => { window.__printCalls++; }; });
```

Popup (unrestricted map):

```js
const [popup] = await Promise.all([page.waitForEvent('popup'), trigger.click()]);
```

Viewport change:

```js
await page.setViewportSize({ width: 1100, height: 900 });
await page.waitForTimeout(300); // allow resize listeners to re-render
```

Confirm no native `beforeunload` dialog blocks the `search` or `no tid` redirects.

---

## 14. Results table template

Record one row per check. Use `P` for pass, `F` for fail, `N/A` for not applicable, and
a reason for any `F`. Attach `results.json` and traces for failures.

| Check ID | Route | AC | Action / selector | Expected (flag on) | Baseline (flag off) | Result | Notes |
| --- | --- | --- | --- | --- | --- | --- | --- |
| S-CORE | core | AC-1, AC-3, AC-6 | `script[src*="taxa-unified.js"]`, `#react-taxa-app`, `.container.taxa-detail` | 1 script, visible shell | `taxa.js`, no unified | | |
| S-GARDEN | garden | AC-1, AC-3, AC-6 | `#react-taxa-garden-app`, `.container.pl-4.pr-4.pt-5` | correct shell | `taxa-garden.js` | | |
| S-RARE | rare | AC-1, AC-3, AC-6 | `#react-taxa-rare-app`, `.profile-type` | `Rare Plant Profile` | `taxa-rare.js` | | |
| P-DOM-CORE | core | AC-4 | `domSnapshot('#react-taxa-app')` | equals baseline | reference | | |
| P-DOM-GARDEN | garden | AC-4 | `domSnapshot('#react-taxa-garden-app')` | equals baseline | reference | | |
| P-DOM-RARE | rare | AC-4 | `domSnapshot('#react-taxa-rare-app')` | equals baseline | reference | | |
| P-TITLE-CORE | core | AC-4 | `toHaveTitle` | `${defaultTitle} ${sciName}` | same | | |
| P-TITLE-GARDEN | garden | AC-4 | `toHaveTitle` | `${defaultTitle} ${sciName}` | same | | |
| P-TITLE-RARE | rare | AC-4 | `toHaveTitle` | `${defaultTitle} - ${sciName} - Rare Plant Profile` | same | | |
| P-H1H2-CORE | core | AC-4 | `.row.print-start h1`, `h2` | italic sciname + author; vernacular h2 | same | | |
| P-H1H2-GARDEN | garden | AC-4 | `.row.print-start h1`, `h2.font-italic` | vernacular h1; italic sciname h2 | same | | |
| P-H1H2-RARE | rare | AC-4 | `.row.print-start h1.font-italic`, `h2` | italic sciname; vernacular h2 | same | | |
| P-HERO | all | AC-4 | `#img-main`, `.img-main-wrapper + figcaption` | src and caption match baseline | reference | | |
| P-DESC-TABS | core, rare | AC-4 | `.description-tabs .tabTitle` | captions and order match | reference | | |
| P-DESC-RARE-SUMMARY | rare | AC-4 | first `.tabTitle` | `Summary` (profile 8) | same | | |
| P-DESC-GARDEN | garden | AC-4 | `.main-section p.mt-4` | garden paragraph, no tabs | same | | |
| P-CAROUSEL-CORE | core | AC-4 | `.taxa-slideshows` | 2 headings: Photo images, Herbarium specimens | same | | |
| P-CAROUSEL-RARE | rare | AC-4 | `.taxa-slideshows` | dual basis as present | same | | |
| P-CAROUSEL-GARDEN | garden | AC-4 | `.taxa-slideshows` | 1 gallery | same | | |
| P-MODAL-OPEN | all | AC-4 | click `.taxa-slideshows img.d-block` | `.modal-backdrop` visible | same | | |
| P-MODAL-CLOSE | all | AC-4 | click `.close-modal` | `.modal-backdrop` count 0 | same | | |
| P-MODAL-BASIS | core, rare | AC-4 | click herbarium thumbnail | credit line absent, preserved images | same | | |
| P-SIDEBAR-CORE | core | AC-4 | `.col-md-4.sidebar-section h3.text-light-green` | Context, Distribution, Web links | same | | |
| P-SIDEBAR-CHOOSER | core genus | AC-4 | sidebar headings | Context, Web links | same | | |
| P-SIDEBAR-AMBIG | core ambiguous | AC-4 | sidebar headings | Web links only | same | | |
| P-SIDEBAR-GARDEN | garden | AC-4 | sidebar headings | Highlights, [Native plant groups], Plant Facts, Growth and Maintenance, Commercial Availability | same | | |
| P-SIDEBAR-RARE | rare | AC-4 | sidebar headings | Context, Distribution, Survey & Manage, Look-Alikes, Associated species | same | | |
| P-VENDOR | garden | AC-4 | `.sidebar-section a[href*="checklist.php?cl="]` | links with `&pid=4` | same | | |
| P-LOOKALIKE | rare | AC-4 | `.lookalike-sciname a` | taxa links | same | | |
| P-ASSOC | rare | AC-4 | `.associated-sciname a` | taxa links | same | | |
| P-MAP | core, garden, rare | AC-4 | `.distribution`, `.map-link` | map image and link match baseline | reference | | |
| P-MAP-RESTRICTED | rare | AC-4 | `.map-overlay-box` toggle | overlay text on click, no popup | same | | |
| P-MAP-OPEN | core | AC-4 | click `.map-link` | popup `leafletmap.php` | same | | |
| P-XREF-CORE | core | AC-4 | Context More info buttons | Rare/Garden buttons as data dictates | same | | |
| P-XREF-GARDEN | garden | AC-4 | `.taxa-link button` | `Core profile page` | same | | |
| P-LEGACY | rare | AC-4 | `.taxa-link a[target="_blank"] button` | `Legacy fact sheet` when set, else absent | same | | |
| P-RPC-CORE | core | AC-4 | `page.on('request')` | `rpc/api.php?taxon`, no `type` | same | | |
| P-RPC-GARDEN | garden | AC-4 | request | `type=garden`, canned, vendor | same | | |
| P-RPC-RARE | rare | AC-4 | request | `type=rare` | same | | |
| R-NOTID | all | AC-4 | `/taxa/<page>.php` | redirect `/` | same | | |
| R-SEARCH | all | AC-4 | `?search=Rosa` | redirect `/taxa/search.php?search=Rosa` | same | | |
| R-TID-CORE | core | AC-4 | `?tid=<TID_CORE>` | renders species | same | | |
| R-TID-RARE | rare | AC-4 | `?tid=<TID_RARE>` | renders rare | same | | |
| R-TID-GARDEN | garden | AC-4 | `?tid=<TID_GARDEN>` | redirect `/` | same | | |
| R-TID-INVALID | all | AC-4 | `?taxon=-1`, `?tid=-1` | redirect `/` (spec edge case: invalid `tid === -1`) | same | | garden `?tid=-1` out of scope: no `tid` normalization |
| R-RPG-GATE | rare | AC-6 | `$RPG_FLAG=0`, logged out | redirect `profile/index.php?refurl=` | same | | |
| C-GENUS | core | AC-3, AC-4 | `<TID_GENUS>` | chooser layout, no h2/MapItem | same | | |
| C-FAMILY | core | AC-3, AC-4 | `<TID_FAMILY>` | chooser, family title fallback | same | | |
| C-AMBIG | core | AC-4 | `<TID_AMBIG>` | `h2.ambiguous`, accepted taxa grid | same | | |
| W-1440 | all | AC-4 | viewport 1440 | collapsed cards match baseline | reference | | |
| W-1100 | all | AC-4 | viewport 1100 | match baseline (no absolute change) | reference | | |
| W-900 | all | AC-4 | viewport 900 | match baseline | reference | | |
| W-700 | all | AC-4 | viewport 700 | 3 collapsed cards, match baseline | reference | | |
| PR-CORE | core | AC-4 | click `.print-trigger` | `window.print` called once | same | | |
| PR-GARDEN | garden | AC-4 | click `.print-trigger` | once | same | | |
| PR-RARE | rare | AC-4 | click `.print-trigger` | once | same | | |
| P-PRINT-HEADER | all | AC-4 | `.print-header` | `pageTitle` + url | same | | |
| F-OFF-IDENTICAL | all | AC-2 | `#innertext` normalized HTML | equals pre-feature recording | reference | | |
| F-OFF-BUNDLE | all | AC-2, AC-6 | source HTML | old bundle only, no unified | reference | | |
| G-IMAGECOUNT | garden | AC-4 | gallery cards/expand/pixel diff | identical to baseline | reference | | |
| E-EMPTY-CORE | core | AC-4 | no images tid | no hero, no gallery | same | | |
| E-EMPTY-RARE | rare | AC-4 | no human images | hero from herbarium | same | | |
| E-LOADING | all | AC-4 | `.loading-overlay` | `loading` class drops | same | | |
| E-API-ERROR | rare | AC-4 | abort RPC | `.alert.alert-danger` shown | same | | |
| E-GLOSSARY | all | AC-4 | tooltip spans | same terms wrapped | same | | |

---

## 15. Selector reference

| Selector | Meaning |
| --- | --- |
| `script[src*="taxa-unified.js"]` | unified bundle loaded (flag on) |
| `script[src*="/taxa.js"]`, `taxa-garden.js`, `taxa-rare.js` | old bundles (flag off) |
| `#react-taxa-app` / `#react-taxa-garden-app` / `#react-taxa-rare-app` | mount divs, unchanged per route |
| `#react-header` | header bundle; `data-props` holds `defaultTitle` and `clientRoot` |
| `.container.taxa-detail` | core shell |
| `.container.pl-4.pr-4.pt-5` | garden shell |
| `.container.py-5` plus `.profile-type` | rare shell |
| `.loading-overlay` / `.loading-overlay.loading` | loading indicator |
| `.print-header` | print-only title and URL |
| `.row.print-start` | title block plus print button |
| `h1`, `h2`, `h2.ambiguous`, `span.font-italic`, `span.synonym` | headings |
| `.print-trigger` | Print page button |
| `.row.mt-2.main-wrapper` | 8/4 content grid |
| `.main-section` | left column |
| `.col-md-4.sidebar-section` | sidebar column |
| `.sidebar-section` | any sidebar section wrapper |
| `h3.text-light-green.font-weight-bold` | section heading |
| `.distribution`, `.map-link`, `.map-overlay-box`, `.map-overlay` | MapItem |
| `.img-main-wrapper`, `#img-main`, `figcaption` | hero image |
| `.description-tabs`, `.tabTitle`, `.reference`, `.description` | description tabs |
| `.taxa-prose` | rare description wrapper |
| `.taxa-slideshows`, `.slick-down`, `select#pageSize`, `.pagination` | image galleries |
| `.modal-backdrop`, `.modal-content`, `.close-modal`, `#main-lightbox`, `.slide-inner-container`, `.image-details` | image modal |
| `.taxa-link`, `button.btn-primary` | cross-profile and print actions |
| `.sidebar-canned`, `.canned-title`, `.canned-results` | garden native groups |
| `.lookalike-sciname`, `.associated-sciname` | rare look-alikes and associated species |
| `.alert.alert-danger` | rare API error |

---

## 16. Definition of done for this plan

The plan is complete and passing when:

1. All smoke checks pass in both flag states.
2. Normalized DOM parity passes for core, garden, and rare.
3. Every AC-4 item in section 2 is verified with no unexplained difference.
4. All redirect and chooser edge cases in sections 3 and 4 pass in both flag states.
5. Flag-off pages are byte-identical to the baseline recording.
6. The garden `imageCount` removal shows no visual difference.
7. The results table in section 14 is filled in and every release-blocking row is `P`.
