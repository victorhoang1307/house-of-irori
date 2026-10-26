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

## Risks and Mitigations
| Risk | Impact | Mitigation |
|------|--------|------------|
| Composite masked frames in Figma (e.g., embossed bartender frame, kimono frame, watermark) | Med | Export composite node renders directly from Figma `/v1/images` endpoint at 2x scale so embossed borders and masks are 100% preserved |
| Hebrew placeholder text in Figma Google Review cards | High | Replace with authentic English reviews for House of IRORI while preserving exact card layout and stars |
