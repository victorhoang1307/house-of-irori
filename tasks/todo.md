# Todo List — House of IRORI (`Home v3B`)

## Task 1: Export Figma Assets for `Home v3B`
**Description:** Extract and download all raster images, composite framed artwork, watermarks, and SVG logos/icons from Figma (`unKDZtWKWErXbWWjttuqWa`) into `assets/images/` and `assets/icons/`.
**Acceptance criteria:**
- [ ] All Hero, Story, Collage, Alley, Bar, Map, Banner, Blog, and Footer images/SVGs exist in `assets/` with size > 0 bytes.
**Verification:**
- [ ] `ls -lh assets/images assets/icons` confirms all assets downloaded.
**Dependencies:** None
**Files likely touched:** `assets/images/*`, `assets/icons/*`
**Estimated scope:** Small

## Task 2: Automated Test Suite + Header & Hero Slice
**Description:** Create `tests/verify-home.mjs` (TDD Red -> Green) and implement `index.html` + `css/style.css` foundation with Sticky Header (Default & Scrolled states), Mobile Drawer (`390w`), and Hero Section (`A Quiet Corner of Japan...` + embossed bartender frame).
**Acceptance criteria:**
- [ ] Header has top contact bar, logo emblem, 5 nav links (`Welcome`, `Menu`, `Events`, `Blogs`, `Contact`), `Reservations` CTA, and Mobile `Menu` button + drawer.
- [ ] Hero renders vintage Tokyo background, H1 title, `Discover the story` scroll link, and overlapping embossed bartender frame over dark wood pattern band.
**Verification:**
- [ ] `node tests/verify-home.mjs`
**Dependencies:** Task 1
**Files likely touched:** `tests/verify-home.mjs`, `index.html`, `css/style.css`
**Estimated scope:** Medium

## Task 3: Story, Collage, Level One (`The Alley`), and Level Two (`The Bar`)
**Description:** Implement the Story section (`Hidden in Plain Sight` with subtle background watermarks), Asymmetric Collage (`The Space Between, Stillness and Beauty` + kimono painting + watermark), `Level One: The Alley`, and `Level Two: The Bar` 50/50 split sections with 3-dot photo carousels.
**Acceptance criteria:**
- [ ] Story and Collage match Figma desktop & mobile stacking.
- [ ] `The Alley` and `The Bar` have 50/50 split on desktop, stacked on mobile, with working 3-dot image carousels.
**Verification:**
- [ ] `node tests/verify-home.mjs`
**Dependencies:** Task 2
**Files likely touched:** `index.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Task 4: Google Reviews & Map, Atmospheric Strip, Latest News Slider, and Footer
**Description:** Implement the 50/50 `Google Reviews & Map` section (with English reviews slider replacing Hebrew placeholder), full-width atmospheric bar strip, `Latest News` 3-column slider, and 2-part Footer (`A Glimpse Inside` + `Section 20` dark footer), plus `js/main.js` interactive controls.
**Acceptance criteria:**
- [ ] Zero Hebrew placeholder characters in `index.html`.
- [ ] Reviews slider, Latest News slider, Alley/Bar carousels, Sticky header scroll state, and Mobile menu drawer work cleanly.
**Verification:**
- [ ] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 3
**Files likely touched:** `index.html`, `css/style.css`, `js/main.js`, `tests/verify-home.mjs`
**Estimated scope:** Medium

## Checkpoint: After Tasks 1-4
- [ ] All tests in `node tests/verify-home.mjs` pass
- [ ] Zero JS syntax errors (`node --check js/main.js`)
- [ ] Secret scan passes (`git grep -i "figd_"`)

## Task 5: Browser Runtime & Visual QA (Desktop 1920px & Mobile 390px)
**Description:** Start local HTTP server, verify Desktop (`1920px`) and Mobile (`390px`) rendering and interactive behaviors via Chrome DevTools MCP, check console/network logs, and run review subagents.
**Acceptance criteria:**
- [ ] Zero console errors or failed network requests in Chrome DevTools MCP.
- [ ] Full-page Desktop and Mobile screenshots match Figma `Home v3B`.
**Verification:**
- [ ] Chrome DevTools MCP inspection + `node tests/verify-home.mjs`
**Dependencies:** Task 4
**Files likely touched:** `css/style.css`, `index.html`, `js/main.js`
**Estimated scope:** Small
