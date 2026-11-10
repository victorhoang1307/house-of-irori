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

## Task 10: Figma Visual Fidelity Fixes (Hero Showcase, Watermark, Scrolled Header, Events Banner & Showcase)
**Description:** Remove border/shadow & baked-in `#F4F1EA` border on `hero-showcase`, lock `hero-showcase__pattern` flush to `.hero` with `0px` gap, remove `"HOUSE OF IRORI"` text from `watermark-line.png`, style `.site-header.is-scrolled` (`#4A3B32` bg, `#F4F1EA` text, `rgba(244, 241, 234, 0.5)` border), fix `upcoming-banner__bg` (`cover`, `top center` without white cutout), and restore `100%` opaque tiled dark wood pattern on `upcoming-showcase`.
**Acceptance criteria:**
- [x] `.hero-showcase__frame` has zero border, zero padding, zero box-shadow, and `hero-bartender-framed.jpg` has zero baked-in cream border; `0px` gap between `.hero` and `.hero-showcase__pattern` at `1920px` and `390px`.
- [x] `watermark-line.png` contains only the circular brushstroke emblem (no `"HOUSE OF IRORI"` text).
- [x] `.site-header.is-scrolled` has `background-color: #4A3B32`, `color: #F4F1EA`, and `rgba(244, 241, 234, 0.5)` borders with `.header-topbar` visible on desktop.
- [x] `upcoming-banner__bg` displays embossed pattern (`cover`, `top center`, zero white hole) and `upcoming-showcase` displays opaque `#261A12` + tiled `hero-wood-pattern.png`.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 9
**Files likely touched:** `assets/images/hero-bartender-framed.jpg`, `assets/images/watermark-line.png`, `assets/images/events-upcoming-banner.jpg`, `index.html`, `events.html`, `css/style.css`, `tests/verify-home.mjs`
**Estimated scope:** Medium

## Task 11: Motion & Autoplay (Carousel Autoplay + Pause on Hover, Parallax Sections)
**Description:** Enable `4500ms` autoplay with pause-on-hover/focus across all carousels and sliders (`[data-carousel]`, `#reviews-slider`, `#latest-news`), and add subtle compositor-friendly scroll parallax on `.atmosphere-strip` and `.footer-glimpse` across all 5 pages.
**Acceptance criteria:**
- [x] All carousels/sliders auto-advance when unhovered and pause on `mouseenter` / `focusin` (and disable under `prefers-reduced-motion: reduce`).
- [x] `.atmosphere-strip` and `.footer-glimpse` use CSS Scroll-Driven Animations (`@supports ((animation-timeline: view()) and (animation-range: entry))`) with `IntersectionObserver` + `rAF` fallback.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 10
**Files likely touched:** `index.html`, `blog-detail.html`, `css/style.css`, `js/main.js`, `tests/verify-home.mjs`
**Estimated scope:** Medium

## Task 12: Menu Tab Content, Load More Pagination, & Clickable Larger Blog Category Tags
**Description:** Differentiate `Food (Pizza & Side dishes)` tab content in `menu-viewer-section`, add `Load More` buttons + extra items on `menu.html` (`OCCASIONAL`), `events.html` (`OCCASIONAL`), and `blogs.html` (`Blogs`), and enlarge clickable `#category` tags (`?category=<slug>`) across `blogs.html`, `blog-detail.html`, and `.news-card` items.
**Acceptance criteria:**
- [x] Switching between `Our top cocktail` and `Food (Pizza & Side dishes)` displays distinct menu content.
- [x] Clicking `Load More` on `menu.html`, `events.html`, and `blogs.html` reveals hidden items and hides the button when exhausted.
- [x] Category pills are larger (`>= 13px` font, `>= 32px` height) and clicking any category tag navigates/filters articles by that tag (`?category=<slug>`).
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 11
**Files likely touched:** `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`, `index.html`, `css/style.css`, `js/main.js`, `tests/verify-home.mjs`
**Estimated scope:** Medium

## Checkpoint: After Tasks 10-12
- [x] `node --check js/main.js && node tests/verify-home.mjs` passes 100%
- [x] CSS <= 50KB and JS <= 15KB budgets respected

## Task 13: Browser Runtime Verification, QA Subagents, & GitHub Pages Redeployment
**Description:** Verify all 11 client feedback items in Chrome DevTools MCP (`1920x1080` & `390x844`), confirm `0` console/network errors, run parallel QA subagents (`code-reviewer`, `web-performance-auditor`, `security-auditor`, `test-engineer`), update `graphify`, and push clean `gh-pages` build to live demo URL.
**Acceptance criteria:**
- [x] Zero console errors/warnings or failed requests in Chrome DevTools MCP across all 5 pages.
- [x] Live GitHub Pages demo (`https://victorhoang1307.github.io/house-of-irori/`) returns `HTTP 200` with updated build.
**Verification:**
- [x] Chrome DevTools MCP + QA subagents + live HTTP 200 check
**Dependencies:** Task 12
**Files likely touched:** `graphify-out/graph.json`, `tasks/plan.md`, `tasks/todo.md`
**Estimated scope:** Small

## Task 14: 4-Point Polish (Compact Scrolled Header, Unclipped Watermark, Horizontal Sliders, Visible Parallax)
**Description:** Collapse `.header-topbar` and shrink `.header-banner` / `.header-logo` on `.site-header.is-scrolled` (Logo + Menu only, `~110px` desktop), fix proportional layout & `overflow: visible` on `.philosophy__lower` / `.philosophy__watermark`, convert `[data-carousel]` and `#latest-news` to horizontal `translate3d` track sliders, and boost `.atmosphere-strip` / `.footer-glimpse` parallax travel (`--parallax-shift: 25%` / `20%`).
**Acceptance criteria:**
- [x] Scrolled header compacts to `~110px` desktop (`72px` banner + `38px` nav, `.header-topbar` collapsed) and `~86px` mobile.
- [x] `.philosophy__watermark` displays its full `[ X ]` center mark and unclipped bottom brushstroke arc at both `1440px` and `1920px`.
- [x] `[data-carousel]` (`alley`, `bar`, `upcoming`) and `#latest-news` (`index.html`, `blog-detail.html`) slide horizontally via `translate3d` tracks with autoplay + pause-on-hover.
- [x] `.atmosphere-strip` and `.footer-glimpse` exhibit clearly visible parallax travel (`>= 250px` across viewport scroll) with zero edge gap.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 13
**Files likely touched:** `tests/verify-home.mjs`, `index.html`, `events.html`, `blog-detail.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Task 15: Browser Runtime Verification (`1440px`, `1920px`, `390px`), QA Delta Review, & `gh-pages` Redeployment
**Description:** Verify all 4 refinements in Chrome DevTools MCP (`1440x900`, `1920x1080`, `390x844`), run delta review with QA subagents, update `graphify`, and redeploy `gh-pages` to `https://victorhoang1307.github.io/house-of-irori/`.
**Acceptance criteria:**
- [x] Zero console errors/warnings or failed requests in Chrome DevTools MCP.
- [x] Live GitHub Pages demo returns `HTTP 200` with updated assets.
**Verification:**
- [x] Chrome DevTools MCP + QA subagents + live HTTP 200 check
**Dependencies:** Task 14
**Files likely touched:** `graphify-out/graph.json`, `tasks/plan.md`, `tasks/todo.md`
**Estimated scope:** Small

## Task 16: Mobile Fullscreen Navigation Menu (The Grand Hanoi Layout & Motion)
**Description:** Add TDD assertions in `tests/verify-home.mjs` (Red -> Green) and implement the fullscreen `#4A3B32` mobile menu curtain, unified header color takeover (`.site-header.is-menu-open`), `30x30` hamburger-to-`✕` icon morph, `1s cubic-bezier(0, 0.15, 0, 1)` staggered upward item cascade (`WELCOME`..`CONTACT` + bottom contact block), scroll lock, `inert` background locking, and `Escape`/resize dismissal in `css/style.css` and `js/main.js`.
**Acceptance criteria:**
- [x] Clicking `#mobile-menu-trigger` on `<= 768px` opens `.mobile-drawer` fullscreen (`top: var(--mobile-header-h)` to `bottom: 0`) with unified `#4A3B32` header + panel, `✕` icon morph, and staggered upward item glide (`cubic-bezier(0, 0.15, 0, 1)`).
- [x] `<main>`, `<footer>`, and `#mobile-reservations-bar` become `inert` when open; `Escape`, link click, or resizing `> 768px` closes cleanly and restores focus.
- [x] `css/style.css` <= `50 KB` and `js/main.js` <= `15 KB` (`15,360 B`).
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 15
**Files likely touched:** `tests/verify-home.mjs`, `css/style.css`, `js/main.js`
**Estimated scope:** Small

## Task 17: Mobile Fullscreen Menu Browser Verification, QA Subagents, & `gh-pages` Redeployment
**Description:** Verify fullscreen mobile menu geometry, colors, `✕` morph, staggered motion, scrolled-header offset (`86px` vs `121px`), `Escape` close, and desktop `display: none` in Chrome DevTools MCP (`390x844` & `1440x900`), run delta review across all 4 QA subagents, update `graphify`, and redeploy `gh-pages`.
**Acceptance criteria:**
- [x] Zero console errors/warnings or failed requests in Chrome DevTools MCP.
- [x] Live GitHub Pages demo (`https://victorhoang1307.github.io/house-of-irori/`) returns `HTTP 200` with updated CSS/JS.
**Verification:**
- [x] Chrome DevTools MCP + QA subagents + live HTTP 200 check
**Dependencies:** Task 16
**Files likely touched:** `graphify-out/graph.json`, `tasks/plan.md`, `tasks/todo.md`
**Estimated scope:** Small

## Task 18: Interactive Grayscale Homepage Map (Zoom Controls + Mobile Pan & Pinch-Zoom)
**Description:** Extend `tests/verify-home.mjs` (TDD Red -> Green), replace the static `<a class="reviews-map__map-link">` in `index.html` with `#irori-map` (`data-map-lat="10.78492"`, `data-map-lng="106.7006"`, `data-map-zoom="16"`, progressive-enhancement `saigon-map.jpg` fallback, `#map-zoom-in` / `#map-zoom-out` `44x44` buttons, and `.map-external-link`), style isolated map stacking (`isolation: isolate`), monochrome tiles, custom `House of IRORI — Cocktail Bar & Eatery` pin, and controls in `css/style.css` (`<= 50 KB`), and lazy-load `Leaflet 1.9.4` with SHA-256 SRI in `js/main.js` (`<= 15 KB`) enabling mobile touch pan + pinch-to-zoom (`dragging: true`, `touchZoom: true`) and desktop pan (`scrollWheelZoom: false`).
**Acceptance criteria:**
- [x] `#irori-map` renders crisp monochrome CloudFront Esri `World_Street_Map` tiles (`server.arcgisonline.com`, with `tileerror` failover to `tile.openstreetmap.fr/hot`) centered on `17/3 Nguyen Thi Minh Khai` (`10.78492, 106.7006`) with a custom `House of IRORI — Cocktail Bar & Eatery` pin and zero `"House Of Merlin"` text.
- [x] `#map-zoom-in` (`+`) and `#map-zoom-out` (`−`) buttons (`>= 44x44px`) zoom smoothly across desktop & mobile and disable at `minZoom`/`maxZoom`; mobile supports 2D touch pan + 2-finger pinch-zoom while desktop keeps `scrollWheelZoom: false`.
- [x] `css/style.css` `<= 50 KB` (`51,200 B`) and `js/main.js` `<= 15 KB` (`15,360 B`) with `node tests/verify-home.mjs` passing.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 17
**Files likely touched:** `tests/verify-home.mjs`, `index.html`, `css/style.css`, `js/main.js`
**Estimated scope:** Medium

## Task 19: Interactive Map Browser Verification, QA Subagents, & `gh-pages` Redeployment
**Description:** Verify desktop (`1440x900`) and mobile (`390x844`) map rendering, `+`/`−` zoom controls, drag-pan, multi-touch pinch-zoom, stacking below sticky header/mobile menu, and `0` console/network errors in Chrome DevTools MCP (port `8091`), run all 4 QA subagents (`code-reviewer`, `web-performance-auditor`, `security-auditor`, `test-engineer`), update `graphify`, and redeploy `gh-pages`.
**Acceptance criteria:**
- [x] Zero console errors/warnings or failed requests in Chrome DevTools MCP across desktop and mobile viewports.
- [x] All 4 QA subagents approve with zero Critical/High/Medium findings, and live GitHub Pages (`https://victorhoang1307.github.io/house-of-irori/`) returns `HTTP 200` with `#irori-map` deployed.
**Verification:**
- [x] Chrome DevTools MCP + 4 QA subagents + live HTTP 200 check
**Dependencies:** Task 18
**Files likely touched:** `graphify-out/graph.json`, `tasks/plan.md`, `tasks/todo.md`
**Estimated scope:** Small

## Task 20: 4-Point UI & Interaction Update (Hero Frame Border, Flush-Left Philosophy, Carousel Wheel/Swipe, Ultrawide Reviews Width)
**Description:** Update `tests/verify-home.mjs` (TDD Red -> Green), restore the Figma `10px solid var(--bg-cream)` border on `.hero-showcase__frame` (`3px` on `<= 768px`), remove `max-width: 1920px` from `.philosophy__grid` so `.philosophy__interior` sits flush left at `x = 0` on all desktop screens, expand `.reviews-map__inner` (`max-width: 960px`) and center `.reviews-map__reviews-col` with balanced `clamp(24px, 6vw, 120px)` padding, and bind `wheel` + `touchstart`/`touchend` swipe detection across `[data-carousel]`, `#reviews-slider`, and `.latest-news__slider-wrap` while keeping `css/style.css <= 50 KB` and `js/main.js <= 15 KB`.
**Acceptance criteria:**
- [x] `.hero-showcase__frame` renders a `10px solid #F4F1EA` (`var(--bg-cream)`) border on desktop (`3px` on `<= 768px`).
- [x] `.philosophy__grid` has no `max-width: 1920px` cap; `.philosophy__interior` sits at `left: 0px` even at `2560x1440`.
- [x] `.reviews-map__inner` (`max-width: 960px`) fills the left column cleanly on `1920x1080` and `2560x1440` without excessive left whitespace.
- [x] Scrolling (`wheel`) or swiping (`touchstart`/`touchend`) on `[data-carousel]`, `#reviews-slider`, and `.latest-news__slider-wrap` advances/rewinds slides and resets autoplay.
- [x] `css/style.css <= 51,200 B` and `js/main.js <= 15,360 B`.
**Verification:**
- [x] `node --check js/main.js && node tests/verify-home.mjs`
**Dependencies:** Task 19
**Files likely touched:** `tests/verify-home.mjs`, `css/style.css`, `js/main.js`, `index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`
**Estimated scope:** Small

## Task 21: Browser Runtime Verification (`1440px`, `1920px`, `2560px`, `390px`), QA Delta Review, & `gh-pages` Redeployment
**Description:** Verify all 4 items in Chrome DevTools MCP (`1440x900`, `1920x1080`, `2560x1440`, `390x844`), run QA subagents, update `graphify`, and redeploy `gh-pages` (`?v=10.3`).
**Acceptance criteria:**
- [x] Zero console errors/warnings or failed requests in Chrome DevTools MCP.
- [x] Live GitHub Pages (`https://victorhoang1307.github.io/house-of-irori/?v=10.3`) verified end-to-end.
**Verification:**
- [x] Chrome DevTools MCP + QA subagents + live check
**Dependencies:** Task 20
**Files likely touched:** `graphify-out/graph.json`, `tasks/todo.md`
**Estimated scope:** Small

## Task 22: Headless WordPress Must-Use Plugin (`wordpress/mu-plugins/irori-headless-cms.php`)
**Description:** Author `wordpress/mu-plugins/irori-headless-cms.php` registering CPTs (`irori_occasional_menu`, `irori_event`, `irori_review`), blog categories (`interior`, `concept`, `club`), `IRORI Menu Book` & `IRORI Settings` admin screens, initial Figma content seeder (`irori_seeded_v1`), allowlisted CORS, and transient-cached REST endpoints (`/wp-json/irori/v1/bootstrap` and `/wp-json/irori/v1/article/<slug>`).
**Acceptance criteria:**
- [x] Zero PHP syntax warnings/errors (`php -l` / server execution) and all inputs/outputs sanitized with `sanitize_text_field`, `esc_url_raw`, `wp_kses_post`, and capability/nonce checks (`manage_options`, `edit_post`, `wp_verify_nonce`).
- [x] First-boot seeder populates default Articles, Occasional Menus, Menu Book (Cocktail image + Pizza & Izakaya lists), Upcoming & Occasional Events, Reviews, and Site Settings once without duplicates.
**Verification:**
- [x] REST API `/cms/wp-json/irori/v1/bootstrap` returns HTTP `200` JSON containing all seeded collections.
**Dependencies:** Task 21
**Files likely touched:** `wordpress/mu-plugins/irori-headless-cms.php`
**Estimated scope:** Medium

## Task 23: Frontend Headless CMS Hydration (`js/cms.js` + `js/main.js` + 5 HTML Pages + `tests/verify-home.mjs`)
**Description:** Add TDD assertions in `tests/verify-home.mjs` (Red -> Green), build `js/cms.js` (`<= 10 KB`, total JS `<= 25 KB`) using safe DOM creation (`document.createElement` + `textContent`) and `Element.prototype.setHTML` / allowlist DOM tree sanitization for rich blog prose, wire `irori:cms` dynamic refresh in `js/main.js` (`<= 15 KB`), and link `js/cms.js?v=11.0` + `js/main.js?v=11.0` across all 5 HTML pages.
**Acceptance criteria:**
- [x] `js/main.js <= 15,360 B`, `js/cms.js <= 10,240 B`, total JS `<= 25,600 B`, `css/style.css <= 51,200 B`.
- [x] Zero `innerHTML` sinks in `js/cms.js`; progressive fallback preserves static Figma content when CMS API is unreachable.
- [x] `node --check js/main.js && node --check js/cms.js && node tests/verify-home.mjs` passes.
**Verification:**
- [x] `node --check js/main.js && node --check js/cms.js && node tests/verify-home.mjs`
**Dependencies:** Task 22
**Files likely touched:** `tests/verify-home.mjs`, `js/cms.js`, `js/main.js`, `index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`
**Estimated scope:** Medium

## Task 24: cPanel Provisioning (`sites-test.houseofirori.com.vn`), WordPress Setup, Browser Verification & QA Gate
**Description:** Create Addon Domain `sites-test.houseofirori.com.vn` on cPanel (`zhorktpv`), create isolated MariaDB database & user, install WordPress 6.x in `/cms/` (`alt-php83`) with admin credentials, deploy `irori-headless-cms.php` + 5-page static frontend + `.htaccess`, verify live REST API & frontend hydration on cPanel (`45.252.249.25` `Host: sites-test.houseofirori.com.vn`) and Chrome DevTools MCP (`127.0.0.1:8091` mocked/proxied to live API), run Final QA Gate (`code-reviewer`, `security-auditor`, `web-performance-auditor`), commit on `master`, and update `gh-pages`.
**Acceptance criteria:**
- [x] `/cms/wp-login.php` and `/cms/wp-json/irori/v1/bootstrap` on `sites-test.houseofirori.com.vn` (`45.252.249.25`) return HTTP `200` with valid seeded JSON.
- [x] All 5 pages render and hydrate cleanly in Chrome DevTools MCP (`1920x1080` & `390x844`) with `0` console errors/warnings, including dynamic `blog-detail.html?slug=...` navigation.
- [x] All 3 QA subagents approve with `0` Critical/High/Medium issues.
**Verification:**
- [x] cPanel HTTP/API checks + Chrome DevTools MCP + QA Gate + `node tests/verify-home.mjs`
**Dependencies:** Task 23
**Files likely touched:** `.htaccess`, `tasks/plan.md`, `tasks/todo.md`, `graphify-out/graph.json`
**Estimated scope:** Medium

## Task 25: TDD Red — `houseofmerlin` CMS Parity & PDF Menu FlipBook Assertions
**Description:** Update `tests/verify-home.mjs` to assert `assets/menus/irori-cocktail-menu.pdf` & `assets/menus/irori-food-menu.pdf`, `_df_book df-lite menu-pdf-book` in `menu.html`, `mainMenus` + `dFlipLocation` in `js/cms.js`, and `google_reviews` CPT, `irori_config-page`, `main_menus`, `occasional_menus`, `occasional_events`, `other_events`, and `irori_import_local_asset` file existence in `wordpress/mu-plugins/irori-headless-cms.php`.
**Acceptance criteria:**
- [x] `tests/verify-home.mjs` covers all Phase 13 PDF FlipBook and `houseofmerlin` CMS structure requirements.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 24
**Files likely touched:** `tests/verify-home.mjs`
**Estimated scope:** Small

## Task 26: PDF Menu Assets, DFlip Viewer & `houseofmerlin`-Aligned CMS Implementation (TDD Green)
**Description:** Generate 4-page PDF menus (`irori-cocktail-menu.pdf`, `irori-food-menu.pdf`), update `menu.html`, `css/style.css`, `js/main.js`, `js/cms.js`, and `wordpress/mu-plugins/irori-headless-cms.php` (`?v=11.1`), keeping all CSS/JS within strict byte budgets.
**Acceptance criteria:**
- [x] `css/style.css <= 51,200 B`, `js/main.js <= 15,360 B`, `js/cms.js <= 10,240 B`, combined JS `<= 25,600 B`.
- [x] `node --check js/main.js && node --check js/cms.js && node tests/verify-home.mjs` passes 100%.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 25
**Files likely touched:** `assets/menus/*.pdf`, `menu.html`, `index.html`, `events.html`, `blogs.html`, `blog-detail.html`, `css/style.css`, `js/main.js`, `js/cms.js`, `wordpress/mu-plugins/irori-headless-cms.php`
**Estimated scope:** Medium

## Task 27: cPanel Deployment (`acf`, `3d-flipbook-dflip-lite`, `admin-menu-editor`), Browser Verification & QA Gate
**Description:** Provision `acf`, `3d-flipbook-dflip-lite`, and `admin-menu-editor` in `/cms/wp-content/plugins/`, seed `Welcome`/`Menu`/`Events`/`Blogs`/`Contact` Pages, `Irori Settings`, `google_reviews`, and PDF menu attachments, synchronize `ws_menu_editor` page submenu links, verify DFlip PDF rendering & WP Admin in Chrome DevTools MCP (`1920x1080` & `390x844`), remediate all QA subagent findings, and push to `origin/master` & `gh-pages`.
**Acceptance criteria:**
- [x] `menu.html` renders interactive 3D FlipBook (`_df_book df-lite`) PDF menus for `Our top cocktail` and `Food (Pizza & Side dishes)` with `0` console errors.
- [x] `/cms/wp-admin/` matches `houseofmerlin.com.vn` (`Pages` -> `Welcome`/`Menu`/`Events`/`Blogs`/`Contact`, `Blogs`, `Google Reviews`, `Irori Settings`, `Media`).
- [x] All 3 QA subagents approve with `0` unresolved findings.
**Verification:**
- [x] Chrome DevTools MCP + cPanel REST API check + QA subagents + `node tests/verify-home.mjs`
**Dependencies:** Task 26
**Files likely touched:** `tasks/plan.md`, `tasks/todo.md`, `graphify-out/graph.json`
**Estimated scope:** Medium

## Task 28: TDD Red — 100% 1:1 CMS & Website Content Parity Assertions (`?v=11.2`)
**Description:** Update `tests/verify-home.mjs` to assert `wordpress/mu-plugins/irori-headless-cms.php` seeds all 6 `Welcome Page` sections (`A QUIET CORNER OF JAPAN,`, `DISCOVER THE STORY`, `HIDDEN IN PLAIN SIGHT`, `THE SPACE BETWEEN,`, `TWO FLOORS. ONE PHILOSOPHY.`, `THE ALLEY`, `THE BAR`, `This is where you stay.`), all 9 matching Blog titles (`Architecture of Shadow: Designing Level One & Level Two`, `The Ritual of Hand-Carved Ice at the Counter`, `Late-Night Listening: Vinyl & Spirits in Saigon`), zero `houseofmerlin` leftover copy (`!includes('WHERE CONVERSATION COMES FIRST')`, `!includes('In a city of noise and spectacle')`), and `d.welcome`/`d.banners` hydration in `js/cms.js`.
**Acceptance criteria:**
- [x] `tests/verify-home.mjs` covers all Phase 14 (`v11.2`) CMS-to-website parity requirements.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 27
**Files likely touched:** `tests/verify-home.mjs`
**Estimated scope:** Small

## Task 29: 1:1 CMS Field Groups, Seeder `v3` & Frontend Hydration (`?v=11.2` TDD Green)
**Description:** Expand `wordpress/mu-plugins/irori-headless-cms.php` (`group_irori_welcome_page` 6 groups, `group_irori_event_page`, `group_irori_blogs_page`, `group_irori_contact_page`, `group_irori_config`, `irori_seeded_v3`, `irori_build_bootstrap_payload` `version: 3`), update `js/cms.js` (`<= 10 KB`) and `js/main.js` (`<= 15 KB`) to hydrate `d.welcome`, `d.banners`, and all 3 carousels (`alley`, `bar`, `upcoming`), and bump `?v=11.2` across all 5 HTML pages.
**Acceptance criteria:**
- [x] `js/cms.js <= 10,240 B`, `js/main.js <= 15,360 B`, combined JS `<= 25,600 B`, `css/style.css <= 51,200 B`.
- [x] `node --check js/main.js && node --check js/cms.js && php -l wordpress/mu-plugins/irori-headless-cms.php && node tests/verify-home.mjs` passes 100%.
**Verification:**
- [x] `node tests/verify-home.mjs`
**Dependencies:** Task 28
**Files likely touched:** `wordpress/mu-plugins/irori-headless-cms.php`, `js/cms.js`, `js/main.js`, `index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`
**Estimated scope:** Medium

## Task 30: cPanel Deployment (`v11.2`), `wp-admin` + Frontend Verification & Final QA Gate
**Description:** Upload `v11.2` files to `sites-test.houseofirori.com.vn`, trigger `irori_seeded_v3`, fix `wp-config.php` salt quotes, verify all CMS pages (`Welcome`, `Menu`, `Events`, `Blogs`, `Contact`, `Irori Settings`, `Blogs`, `Google Reviews`) and all 5 frontend pages in Chrome DevTools MCP (`0` console errors), run Final QA Gate (`code-reviewer`, `security-auditor`, `web-performance-auditor`), update `graphify`, and push to `origin/master` & `gh-pages`.
**Acceptance criteria:**
- [x] All 30 ACF fields in `Welcome Page` (`post=44`), `Menu Page` (`post=45`), `Events Page` (`post=46`), `Blogs Page` (`post=47`), `Contact Page` (`post=48`), `Irori Settings`, 9 `Blogs` posts, and 5 `Google Reviews` match the website 100%.
- [x] All 5 frontend pages hydrate from `/cms/wp-json/irori/v1/bootstrap` (`version: 3`) with `0` console errors/warnings.
- [x] All 3 QA subagents approve with `0` Critical/High/Medium findings.
**Verification:**
- [x] Chrome DevTools MCP + cPanel REST API check + QA subagents + `node tests/verify-home.mjs`
**Dependencies:** Task 29
**Files likely touched:** `tasks/plan.md`, `tasks/todo.md`, `graphify-out/graph.json`
**Estimated scope:** Medium
