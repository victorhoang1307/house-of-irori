# Todo List — House of IRORI (`Home v3B`)

## Task 1: Export Figma Assets for `Home v3B`
**Description:** Extract and download all raster images, composite framed artwork, watermarks, and SVG logos/icons from Figma (`unKDZtWKWErXbWWjttuqWa`) into `assets/images/` and `assets/icons/`.
**Acceptance criteria:**
- [x] All Hero, Story, Collage, Alley, Bar, Map, Banner, Blog, and Footer images/SVGs exist in `assets/` with size > 0 bytes.
**Verification:**
- [x] `ls -lh assets/images assets/icons` confirms all assets downloaded.
**Dependencies:** None
**Files likely touched:** `assets/images/*`, `assets/icons/*`
**Estimated scope:** Small

## Task 2: Automated Test Suite + Header & Hero Slice
**Description:** Create `tests/verify-home.mjs` (TDD Red -> Green) and implement `index.html` + `css/style.css` foundation with Sticky Header (Default & Scrolled states), Mobile Drawer (`390w`), and Hero Section (`A Quiet Corner of Japan...` + embossed bartender frame).
**Acceptance criteria:**
- [x] Header has top contact bar, logo emblem, 5 nav links (`Welcome`, `Menu`, `Events`, `Blogs`, `Contact`), `Reservations` CTA, and Mobile `Menu` button + drawer.
- [x] Hero renders vintage Tokyo background, H1 title, `Discover the story` scroll link, and overlapping embossed bartender frame over dark wood pattern band.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 1
**Files likely touched:** `tests/verify-home.mjs`, `index.html`, `css/style.css`
**Estimated scope:** Medium

## Task 3: Story, Collage, Level One (`The Alley`), and Level Two (`The Bar`)
**Description:** Implement the Story section (`Hidden in Plain Sight` with subtle background watermarks), Asymmetric Collage (`The Space Between, Stillness and Beauty` + kimono painting + watermark), `Level One: The Alley`, and `Level Two: The Bar` 50/50 split sections with 3-dot photo carousels.
**Acceptance criteria:**
- [x] Story and Collage match Figma desktop & mobile stacking.
- [x] `The Alley` and `The Bar` have 50/50 split on desktop, stacked on mobile, with working 3-dot image carousels.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 2
**Files likely touched:** `index.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Task 4: Google Reviews & Map, Atmospheric Strip, Latest News Slider, and Footer
**Description:** Implement the 50/50 `Google Reviews & Map` section (with English reviews slider replacing Hebrew placeholder), full-width atmospheric bar strip, `Latest News` 3-column slider, and 2-part Footer (`A Glimpse Inside` + `Section 20` dark footer), plus `js/main.js` interactive controls.
**Acceptance criteria:**
- [x] Zero Hebrew placeholder characters in `index.html`.
- [x] Reviews slider, Latest News slider, Alley/Bar carousels, Sticky header scroll state, and Mobile menu drawer work cleanly.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 3
**Files likely touched:** `index.html`, `css/style.css`, `js/main.js`, `tests/verify-home.mjs`
**Estimated scope:** Medium

## Checkpoint: After Tasks 1-4
- [x] All tests in `node tests/verify-home.mjs` pass
- [x] Zero JS syntax errors (`node --check js/main.js`)
- [x] Secret scan passes (`git grep -E "figd_[A-Za-z0-9_-]{20,}"`)

## Task 5: Browser Runtime & Visual QA (Desktop 1920px & Mobile 390px)
**Description:** Start local HTTP server, verify Desktop (`1920px`) and Mobile (`390px`) rendering and interactive behaviors via Chrome DevTools MCP, check console/network logs, and run review subagents.
**Acceptance criteria:**
- [x] Zero console errors or failed network requests in Chrome DevTools MCP.
- [x] Full-page Desktop and Mobile screenshots match Figma `Home v3B`.
- [x] Mobile `Reservations` CTA bar (`617:803`) is fixed to the bottom of the mobile viewport (`position: fixed; bottom: 0; left: 0; right: 0; z-index: 100`).
**Verification:**
- [x] Chrome DevTools MCP inspection + `node tests/verify-home.mjs`
**Dependencies:** Task 4
**Files likely touched:** `css/style.css`, `index.html`, `js/main.js`
**Estimated scope:** Small

## Task 6: Export Subpage Figma Assets & Update `index.html` Navigation
**Description:** Export all new raster/composite images for `Menu` (`617:1167`), `Events` (`622:991`), `Blogs` (`622:1667`), and `Blog Articles` (`622:1944`) from Figma API into `assets/images/` and wire `index.html` nav/CTA links to `menu.html`, `events.html`, `blogs.html`, and `blog-detail.html`.
**Acceptance criteria:**
- [x] Subpage Hero images, Occasional event image, Menu showcase image, Events Upcoming featured image, and Blog article images exist in `assets/images/` with non-zero bytes.
- [x] `index.html` links point to `menu.html`, `events.html`, `blogs.html`, and `blog-detail.html`.
**Verification:**
- [x] `ls -lh assets/images`
**Dependencies:** Task 5
**Files likely touched:** `assets/images/*`, `index.html`
**Estimated scope:** Small

## Task 7: Implement `menu.html` and `events.html` (TDD Red -> Green Slice 1)
**Description:** Update `tests/verify-home.mjs` with multi-page assertions, implement `menu.html` (Hero, `OCCASIONAL` 2-col with "House of IRORI" copy, interactive `MENU` tab viewer) and `events.html` (Hero, `UP COMING` wood banner + featured carousel, `OCCASIONAL` 2x2 grid), plus shared CSS/JS.
**Acceptance criteria:**
- [x] `menu.html` and `events.html` match Figma `617:1167` and `622:991` at `1920px` and `390px`.
- [x] Zero occurrences of `"House of Merlin"` across all HTML files.
- [x] Menu tab toggle (`aria-pressed`) and Events `UP COMING` carousel work cleanly.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 6
**Files likely touched:** `tests/verify-home.mjs`, `menu.html`, `events.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Task 8: Implement `blogs.html` and `blog-detail.html` (TDD Green Slice 2)
**Description:** Implement `blogs.html` (Hero, `#category` interactive filter buttons with `aria-pressed`, 6-card grid linking to `blog-detail.html`) and `blog-detail.html` (solid header, `960px` article container with `ELLE Decoration PopUp Office` sample copy, `#category` tags, and bottom `Latest News` 3-card slider).
**Acceptance criteria:**
- [x] `blogs.html` and `blog-detail.html` match Figma `622:1667` and `622:1944` at `1920px` and `390px`.
- [x] Category filtering on `blogs.html` and `Latest News` slider on `blog-detail.html` work cleanly.
- [x] All checks in `node tests/verify-home.mjs` pass.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 7
**Files likely touched:** `blogs.html`, `blog-detail.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Checkpoint: After Tasks 6-8
- [x] All 5 pages pass `node tests/verify-home.mjs`
- [x] CSS <= 50KB and JS <= 15KB budgets respected

## Task 9: Browser Verification (Chrome DevTools MCP) & QA Subagents
**Description:** Verify `menu.html`, `events.html`, `blogs.html`, and `blog-detail.html` in Chrome DevTools MCP at Desktop (`1920x1080`) and Mobile (`390x844`), verify interactive controls and `0` console/network errors, and run QA subagents.
**Acceptance criteria:**
- [x] Zero console errors or failed network requests across all 5 pages.
- [x] Mobile fixed bottom `RESERVATIONS` bar verified on all subpages.
**Verification:**
- [x] Chrome DevTools MCP + QA subagents
**Dependencies:** Task 8
**Files likely touched:** `css/style.css`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`
**Estimated scope:** Small
