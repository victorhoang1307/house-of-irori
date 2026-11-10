# Implementation Plan: House of IRORI — Home v3B (`index.html`)

## Overview
Build the complete, responsive Static HTML5/CSS3/JS implementation of `Home v3B` (`494:776` Desktop 1920px & `494:1209` Mobile 390px) for House of IRORI using real assets exported from the Figma REST API (`unKDZtWKWErXbWWjttuqWa`).

## Architecture Decisions
- **Direct Figma Asset Export Pipeline:** Export every raster image fill and vector logo/ornament directly from Figma API into `assets/images/` and `assets/icons/` so there is zero loss of visual fidelity.
- **CSS Custom Properties & Responsive Grid:** Use exact Figma colors (`#F4F1EA`, `#FFFFFF`, `#5A2929`, `#4A3B32`, `#C6A15B`, `#0A0A0A`) and typography (`Playfair Display`, `Lora`, `Inter`) with desktop-to-mobile breakpoints matching `1920w default` and `390w default`.
- **Strapi-Ready Data Attributes:** Mark dynamic collection containers (`data-cms-collection="reviews"`, `data-cms-collection="articles"`) and item templates (`data-cms-item`) for seamless Strapi integration in Phase 2.

## Task List

### Phase 1: Foundation & Asset Pipeline
- [x] Task 1: Export all Figma raster images, framed artwork, watermark graphics, and SVG logos/icons for `Home v3B` into `assets/images/` and `assets/icons/`.
- [x] Task 2: Write automated verification test suite (`tests/verify-home.mjs`) and base HTML5/CSS3 skeleton (`index.html`, `css/style.css`) with Header (`Stick header` Default + Scrolled + Mobile Drawer) and Hero section.

### Checkpoint: Foundation
- [x] Asset files verified on disk, Header & Hero render cleanly.

### Phase 2: Core Content Sections
- [x] Task 3: Implement Story (`Hidden in Plain Sight`), Asymmetric Collage (`The Space Between, Stillness and Beauty`), `Level One: The Alley` (50/50 split + carousel), and `Level Two: The Bar` (50/50 split + carousel).
- [x] Task 4: Implement `Google Reviews & Map` (50/50 split with English cocktail bar reviews slider + interactive/styled map), `Atmospheric Banner Strip`, `Latest News` (3-card slider), and `Footer` (`A Glimpse Inside` + `Section 20`).

### Checkpoint: Core Features
- [x] All 10 sections and interactive JS sliders/carousels (`js/main.js`) pass `tests/verify-home.mjs`.

### Phase 3: Browser Verification & Visual QA
- [x] Task 5: Serve locally, inspect Desktop (`1920x1080`) and Mobile (`390x844`) in Chrome DevTools MCP, verify zero console/network errors, run QA/Review subagents.

### Checkpoint: Complete (Phase 1)
- [x] All acceptance criteria met and verified against Figma renders.

### Phase 4: Subpages Asset Export & Navigation Wiring
- [x] Task 6: Export all remaining Figma assets for `Menu` (`617:1167`), `Events` (`622:991`), `Blogs` (`622:1667`), and `Blog Articles` (`622:1944`) into `assets/images/`, and update `index.html` navigation links (`menu.html`, `events.html`, `blogs.html`, `blog-detail.html`).

### Phase 5: Implement `menu.html`, `events.html`, `blogs.html`, and `blog-detail.html`
- [x] Task 7: Extend `tests/verify-home.mjs` for all 5 pages (TDD Red), then build `menu.html` and `events.html` + shared subpage styles/scripts in `css/style.css` and `js/main.js`.
- [x] Task 8: Build `blogs.html` (with `#category` filter pills) and `blog-detail.html` (editorial article + bottom `Latest News` slider) and pass all tests in `tests/verify-home.mjs` (TDD Green).

### Phase 6: Browser Runtime Verification & Multi-Agent QA
- [x] Task 9: Verify all 4 new pages at Desktop (`1920x1080`) and Mobile (`390x844`) via Chrome DevTools MCP (`0` console/network errors) and run QA review subagents.

### Phase 7: Client Feedback Refinements (11-Point Revision)
- [x] Task 10: Figma Visual Fidelity Fixes — borderless/shadowless `hero-showcase`, zero-gap `hero-showcase__pattern`, text-free `philosophy__watermark`, `#4A3B32` / `#F4F1EA` scrolled header, `cover top center` `upcoming-banner__bg`, and opaque tiled dark wood `upcoming-showcase` background.
- [x] Task 11: Motion & Autoplay — autoplay + pause-on-hover across all carousels/sliders (`[data-carousel]`, `#reviews-slider`, `#latest-news`) and subtle scroll parallax on `.atmosphere-strip` & `.footer-glimpse`.
- [x] Task 12: Interactive Content & Taxonomy — distinct `Food (Pizza & Side dishes)` tab spread in `menu.html`, `Load More` pagination on `menu.html` / `events.html` / `blogs.html`, and larger clickable blog `#category` tags (`?category=<slug>`) across all blog views.
- [x] Task 13: Browser Runtime Verification, Multi-Agent QA, & GitHub Pages Demo Redeployment.

### Phase 8: 4-Point Polish (Compact Sticky Header, Unclipped Watermark, Horizontal Sliders, Visible Parallax)
- [x] Task 14: Implement compact Logo + Menu scrolled header (`~110px`), scale-invariant unclipped `.philosophy__watermark`, GPU-composited horizontal `.carousel__track` & `.latest-news__viewport` sliders, and high-visibility scroll parallax (`~0.27x` depth).
- [x] Task 15: Verify across `1440x900`, `1920x1080`, and `390x844` in Chrome DevTools MCP, run delta QA subagent checks, and redeploy `gh-pages`.

### Phase 9: Mobile Fullscreen Navigation Menu (The Grand Hanoi Style & Motion)
- [x] Task 16: Implement TDD assertions (`tests/verify-home.mjs`), fullscreen mobile curtain menu + staggered upward item cascade + hamburger-to-`✕` morph (`css/style.css`), and accessible `is-menu-open` / `inert` / `Escape` state controller within the `15 KB` JS budget (`js/main.js`).
- [x] Task 17: Verify mobile (`390x844` unscrolled & scrolled) and desktop (`1440x900`) runtime behavior in Chrome DevTools MCP, run delta QA subagent checks, and redeploy `gh-pages`.

### Phase 10: Interactive Grayscale Homepage Map (Zoom Controls + Mobile Pan & Pinch-Zoom)
- [x] Task 18: Add TDD assertions (`tests/verify-home.mjs`) and implement `#irori-map` markup (`index.html`), isolated stacking & luxury `+`/`−` zoom controls (`css/style.css`), and lazy-loaded `Leaflet 1.9.4` grayscale tile + custom `House of IRORI` pin + mobile pan/pinch-zoom controller (`js/main.js`) within the `50 KB` CSS and `15 KB` JS budgets.
- [x] Task 19: Verify desktop (`1440x900`) and mobile (`390x844`, touch pan & pinch-zoom + `+`/`−` buttons) runtime behavior in Chrome DevTools MCP, run all 4 QA subagents, update `graphify`, and redeploy `gh-pages`.

### Phase 11: 4-Point Ultrawide & Carousel Scroll Refinements
- [x] Task 20: Restore `.hero-showcase__frame` border (`10px` desktop / `3px` mobile), flush-left `.philosophy__grid`, wheel/touch swipe carousel scrolling, and `960px` `.reviews-map__inner`.
- [x] Task 21: Browser runtime verification (`1440px`, `1920px`, `2560px`, `390px`), QA gate, and `gh-pages` redeployment (`?v=10.3`).

### Phase 12: Headless WordPress CMS (`/cms/`) & cPanel Deployment (`sites-test.houseofirori.com.vn`)
- [x] Task 22: Build `wordpress/mu-plugins/irori-headless-cms.php` (CPTs, Menu Book & Site Settings admin UI, transient-cached REST API `/irori/v1/bootstrap` & `/irori/v1/article/<slug>`, and automatic initial Figma content seeder).
- [x] Task 23: Extend `tests/verify-home.mjs` (TDD Red -> Green) and implement `js/cms.js` (`<= 10 KB`) + dynamic `irori:cms` refresh hooks in `js/main.js` (`<= 15 KB`) + script wiring across all 5 HTML pages (`?v=11.0`).
- [x] Task 24: Provision `sites-test.houseofirori.com.vn` on cPanel (`zhorktpv`), create isolated DB `zhorktpv_irori`, install WordPress 6.x in `/cms/` with `irori-headless-cms.php`, deploy the 5-page frontend + `.htaccess` to webroot, and verify end-to-end (cPanel HTTP/REST API + Chrome DevTools MCP + QA subagents).

### Phase 13: `houseofmerlin` CMS Structure Parity & PDF Menu FlipBook Viewer (`?v=11.1`)
- [x] Task 25: Update `tests/verify-home.mjs` (TDD Red) to assert PDF menu files (`assets/menus/irori-cocktail-menu.pdf`, `assets/menus/irori-food-menu.pdf`), PDF viewer markup in `menu.html` (`_df_book` + `data-pdf-src`), `mainMenus` PDF tab hydration in `js/cms.js`, delegated tab switching + resize trigger in `js/main.js`, and `houseofmerlin`-aligned ACF field groups (`Welcome Page`, `Menu Page` with `main_menus` PDF repeater & `occasional_menus`, `Event Page` with `occasional_events` & `other_events`, `Blogs Page`, `Contact Page`, `Irori Config`) + `google_reviews` CPT in `wordpress/mu-plugins/irori-headless-cms.php`.
- [x] Task 26: Generate multi-page PDF menus (`assets/menus/irori-cocktail-menu.pdf` & `assets/menus/irori-food-menu.pdf`), update `menu.html`, `css/style.css`, `js/main.js`, `js/cms.js`, and `wordpress/mu-plugins/irori-headless-cms.php` (TDD Green), keeping `css/style.css <= 50 KB`, `js/main.js <= 15 KB`, `js/cms.js <= 10 KB`, and combined JS `<= 25 KB`.
- [x] Task 27: Provision `acf`, `3d-flipbook-dflip-lite`, and `admin-menu-editor` in `/home/zhorktpv/sites-test.houseofirori.com.vn/cms/wp-content/plugins/`, seed ACF Pages (`Welcome`, `Menu`, `Events`, `Blogs`, `Contact`), `Irori Settings` (`irori_config-page`), `google_reviews`, and Media attachments on cPanel, verify live PDF flipbook tabs & CMS admin structure in Chrome DevTools MCP, run QA gate (`code-reviewer`, `security-auditor`, `web-performance-auditor`), and push to `origin/master`.

### Phase 14: 100% 1:1 Content & Field Parity Between CMS and Website (`?v=11.2`)
- [x] Task 28: Update `tests/verify-home.mjs` (TDD Red) to assert that `wordpress/mu-plugins/irori-headless-cms.php` seeds the exact `index.html`, `menu.html`, `events.html`, `blogs.html`, and `blog-detail.html` copy/assets (zero `houseofmerlin` leftover copy like `WHERE CONVERSATION COMES FIRST`, full `Welcome` sections: `HIDDEN IN PLAIN SIGHT`, `THE SPACE BETWEEN`, `THE ALLEY`, `THE BAR`, and all 9 matching Blog titles/dates/images) and that `js/cms.js` hydrates `d.welcome` and `d.banners`.
- [x] Task 29: Update `wordpress/mu-plugins/irori-headless-cms.php`, `js/cms.js`, `js/main.js`, and the 5 HTML pages (`?v=11.2`) (TDD Green) so every section on `Welcome`, `Menu`, `Events`, `Blogs`, `Contact`, `Google Reviews`, and `Irori Settings` matches the website 100% and hydrates dynamically within the `10 KB` `cms.js` / `15 KB` `main.js` budgets.
- [x] Task 30: Deploy to cPanel (`sites-test.houseofirori.com.vn`), re-seed all Pages/Blogs/Settings, verify every CMS page in `wp-admin` and every frontend page in Chrome DevTools MCP (`1920x1080` & `390x844`), run Final QA Gate (`code-reviewer`, `security-auditor`, `web-performance-auditor`), and push to `origin/master` & `gh-pages`.

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Composite masked frames in Figma (e.g., embossed bartender frame, kimono frame, watermark) | Med | Export composite node renders directly from Figma `/v1/images` endpoint at 2x scale so embossed borders and masks are 100% preserved |
| Hebrew placeholder text in Figma Google Review cards | High | Replace with authentic English reviews for House of IRORI while preserving exact card layout and stars |
| Ultrawide (`>1920px`) layout alignment & wheel/swipe carousel ergonomics | Medium | Restore Figma `10px` cream frame border on `.hero-showcase__frame`, remove `max-width: 1920px` on `.philosophy__grid` for flush-left alignment, widen `.reviews-map__inner` (`max-width: 960px`) with balanced padding, and bind `wheel` + touch swipe across all carousels within the `15 KB` JS / `50 KB` CSS budgets |
