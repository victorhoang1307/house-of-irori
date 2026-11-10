# Spec: House of IRORI Website — Phase 1 (`Home v3B`) & Phase 2 (`Menu`, `Events`, `Blogs`, `Blog Articles`)

## Objective
Convert the client's complete Figma design (**House of IRORI - Website**, `file_key: unKDZtWKWErXbWWjttuqWa`, canvas `Home Final` `494:4`) into a fast, semantic, pixel-perfect **Static HTML5 / CSS3 / Vanilla JS** multi-page website for both **Desktop (`1920px`)** and **Mobile (`390px`)** viewports:
1. **`index.html` (`Home v3B` `494:1203`)** — Completed in Phase 1.
2. **`menu.html` (`Menu` `617:1167`)** — Hero (`Cocktail & Eatery Menu`), `OCCASIONAL` 2-column guestshift showcase, and interactive `MENU` viewer (`Our top cocktail` / `Food (Pizza & Side dishes)` tabs + slide viewer).
3. **`events.html` (`Events` `622:991`)** — Hero (`Events`), `UP COMING` wood-textured banner + overlapping `970x683` featured event carousel, and `OCCASIONAL` 2x2 event grid.
4. **`blogs.html` (`Blogs` `622:1667`)** — Hero (`BLOGS`), `Latest News` section with `#category` interactive filter pills (`interior`, `Concept`, `Club`), and 3x2 article card grid linking to `blog-detail.html`.
5. **`blog-detail.html` (`Blog Articles` `622:1944`)** — Editorial article layout (`960px` container, gold H1 title, date, `900x630` imagery, body copy, `#category` tags) + bottom `Latest News` 3-card slider.

All repeatable sections use clean Strapi-ready DOM attributes (`data-cms-collection="reviews|articles|events|occasional-menus|menu-pages"`, `data-cms-item="..."`) so the entire 5-page static build can be wired directly to **Strapi Headless CMS** in Phase 3 without refactoring HTML/CSS.

## Tech Stack
- **Markup:** Semantic HTML5 (`index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`)
- **Styling:** Shared Modern Vanilla CSS3 (`css/style.css`) using CSS Custom Properties (Design Tokens), CSS Grid, Flexbox, and fluid `clamp()` typography. Zero CSS framework bloat.
- **Interactivity:** Shared Vanilla ES6+ JavaScript (`js/main.js`), zero runtime dependencies.
- **Typography (Google Fonts):**
  - `Playfair Display` (Regular 400, Medium 500) — Headings, section titles, navigation links
  - `Lora` (Regular 400, Medium 500, SemiBold 600, Bold 700) — Body copy, metadata, category pills, footer
  - `Inter` (Regular 400, Medium 500) — Google Review cards UI
- **Design Tokens (Extracted from Figma `node_494_4.json`):**
  - `--bg-cream`: `#F4F1EA` (Story, Menu viewer, Upcoming Events, & Latest News background)
  - `--bg-white`: `#FFFFFF` (Level One, Menu Occasional, & Blog Article body background)
  - `--bg-burgundy`: `#5A2929` (Level Two: The Bar background & Latest News heading)
  - `--bg-dark`: `#0A0A0A` (Reviews/Map & Footer bottom background)
  - `--bg-dark-elevated`: `#141414` (Footer top banner overlay)
  - `--color-espresso`: `#4A3B32` (Primary dark brown text, category pills, & mobile fixed bottom bar)
  - `--color-gold`: `#C6A15B` / `#C9A24A` (Gold headings, accents, and `RESERVATIONS` CTA)

## Commands
- **Dev Server:** `python3 -m http.server 8080 --directory /Users/Victor/.gemini/antigravity/scratch/house-of-irori`
- **JS Syntax Check:** `node --check /Users/Victor/.gemini/antigravity/scratch/house-of-irori/js/main.js`
- **Automated Verification Suite:** `node /Users/Victor/.gemini/antigravity/scratch/house-of-irori/tests/verify-home.mjs`
- **Secret Scan:** `git -C /Users/Victor/.gemini/antigravity/scratch/house-of-irori grep -E "figd_[A-Za-z0-9_-]{20,}" && exit 1 || exit 0`

## Project Structure
```text
/Users/Victor/.gemini/antigravity/scratch/house-of-irori/
├── index.html              → Home v3B page (Desktop 1920px & Mobile 390px responsive)
├── menu.html               → Menu page (Figma 617:1167)
├── events.html             → Events page (Figma 622:991)
├── blogs.html              → Blogs listing page (Figma 622:1667)
├── blog-detail.html        → Blog article detail page (Figma 622:1944)
├── css/
│   └── style.css           → Shared design tokens, components, and responsive rules (≤ 50KB)
├── js/
│   └── main.js             → Sticky header, mobile drawer, carousels, menu tabs, blog filters (≤ 15KB)
├── assets/
│   ├── images/             → High-res images exported directly from Figma API
│   └── icons/              → Favicon and ornamental SVG/PNG icons from Figma API
├── tests/
│   └── verify-home.mjs     → Automated multi-page DOM, asset, style, and budget test suite
├── CONSTRAINTS.md          → Quality gates & budgets
└── SPEC.md                 → This specification
```

## Code Style
```html
<!-- Clean BEM-style semantic markup with Strapi-ready data attributes -->
<article class="news-card" data-cms-item="article" data-category="interior">
  <div class="news-card__media">
    <img src="assets/images/blog-thumb-1.jpg" alt="Why We Work with Spirit, Not Against It" loading="lazy" width="370" height="300">
  </div>
  <div class="news-card__body">
    <time class="news-card__date" datetime="2026-04-14">APRIL 14, 2026</time>
    <h3 class="news-card__title">Why We Work with Spirit, Not Against It</h3>
    <p class="news-card__excerpt">A conversation about respect, slow curation, and why every ingredient has a story to tell...</p>
    <a href="blog-detail.html" class="news-card__link">READ MORE <span aria-hidden="true">↗</span></a>
  </div>
</article>
```

## Testing Strategy
- **Static & DOM Tests (`tests/verify-home.mjs`):** Node.js test suite verifying all 5 HTML pages (`index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`) exist, contain their required Figma sections and Strapi `data-cms-*` attributes, link to each other via navigation (`index.html`, `menu.html`, `events.html`, `blogs.html`, `#footer`), resolve all exported Figma assets on disk, and stay within the `50KB` CSS and `15KB` JS budgets.
- **Browser Runtime & Visual Verification (Chrome DevTools MCP):**
  - Render all 5 pages at `1920x1080` (Desktop) and `390x844` (Mobile) against local HTTP server.
  - Verify Sticky Header, Mobile fixed bottom `RESERVATIONS` bar, Menu tab switching (`Our top cocktail` / `Food`), Events `UP COMING` carousel, Blogs `#category` filter pills, and Blog Detail `Latest News` slider.
  - Verify zero console/network errors (`list_console_messages`, `list_network_requests`).

## Boundaries
- **Always:** Export real assets from Figma API (`unKDZtWKWErXbWWjttuqWa`), preserve exact typography and color hex codes from Figma, keep `.figma-cache/` out of git commits, include mobile fixed bottom `RESERVATIONS` bar across all 5 pages.
- **Ask first:** Adding any third-party npm library, changing layout structure away from Figma `494:4`, or initializing the Strapi backend project.
- **Never:** Commit Figma Personal Access Tokens to git, leave Hebrew placeholder text, or exceed CSS/JS file size budgets in `CONSTRAINTS.md`.

## Success Criteria
1. All 5 pages (`index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html`) are implemented and match their Figma frames (`494:1203`, `617:1167`, `622:991`, `622:1667`, `622:1944`) at both Desktop (`1920px`) and Mobile (`390px`).
2. Cross-page navigation works seamlessly across all 5 pages (`WELCOME` → `index.html`, `MENU` → `menu.html`, `EVENTS` → `events.html`, `BLOGS` → `blogs.html`, `READ MORE` → `blog-detail.html`, `CONTACT` / `RESERVATIONS` → `#footer`).
3. All new Figma images for `Menu`, `Events`, `Blogs`, and `Blog Articles` are exported directly from Figma API into `assets/images/` and load with HTTP `200`.
4. Interactive controls on subpages (Menu category tabs + page viewer, Events `UP COMING` carousel, Blogs `#category` tag filter, Blog Detail `Latest News` slider, Mobile drawer, Mobile fixed bottom `RESERVATIONS` bar) work with `0` console errors.
5. **Phase 2B Client Polish & Refinements (11-point feedback):**
   - `hero-showcase`: Borderless, shadowless image (`padding: 0; border: none; box-shadow: none;`, baked-in `#F4F1EA` border cropped from `hero-bartender-framed.jpg`), with `hero-showcase__pattern` locked flush to `.hero` bottom (`0px` gap across all viewports).
   - `philosophy__watermark`: Emblem-only watermark (cropped `"HOUSE OF IRORI"` text out of `watermark-line.png`), rendered proportionally inside `.philosophy__lower` (`overflow: visible` on `.philosophy__lower` and `overflow-y: visible` on `.philosophy`) so the `[ X ]` center symbol and bottom circular brushstroke arc are 100% unclipped and unobscured at both laptop (`1440px`) and desktop (`1920px`) viewports.
   - Compact Scrolled & Solid Header (`.site-header.is-scrolled`, `.site-header--solid`): `background-color: #4A3B32`, font color `#F4F1EA`, divider borders `rgba(244, 241, 234, 0.5)` (`#F4F1EA` 50%). On scroll (`.site-header.is-scrolled`), collapse `.header-topbar` (`max-height: 0; opacity: 0; visibility: hidden;`) and compact `.header-banner` (`72px` desktop / `52px` mobile) + `.header-logo img` (`50x55` desktop / `34x38` mobile) so only Logo + Menu remain visible (`~110px` total desktop height, modeled on The Grand Hanoi).
   - Carousels & Sliders (`[data-carousel]`, `#reviews-slider`, `#latest-news`): Smooth horizontal sliding track transitions (`translate3d` flex tracks via `.carousel__track` and `.latest-news__viewport` + `#news-track`) with `4500ms` autoplay, pausing on hover/focus.
   - Parallax: Clearly visible scroll-driven parallax (`~0.27x` counter-scroll travel, `--parallax-shift: 25%` on `.atmosphere-strip img` with `height: 212%` and `--parallax-shift: 20%` on `.footer-glimpse__bg` with `height: 176%`, gently toned down rather than flattened under `prefers-reduced-motion: reduce`), backed by `IntersectionObserver` + `rAF` fallback.
   - `menu-viewer-section`: Distinct `Food (Pizza & Side dishes)` two-page editorial spread when switching tabs.
   - `events.html` `UP COMING`: `upcoming-banner__bg` uses cropped top ornamental strip with `object-fit: cover; object-position: top center;`; `upcoming-showcase` uses `100%` opaque dark carved wood tiled pattern (`#261A12` + `hero-wood-pattern.png`).
   - `Load More`: Interactive `[data-load-more]` buttons revealing additional items on `menu.html` (`OCCASIONAL`), `events.html` (`OCCASIONAL`), and `blogs.html` (`Blogs`).
   - Blog Category Tags: Larger `.category-pill` sizing (`13.5px`, `34px` min-height) and clickable tag navigation (`blogs.html?category=<slug>`) across `blogs.html`, `blog-detail.html`, and `.news-card` items.
   - Mobile Fullscreen Menu (`<= 768px`, modeled on The Grand Hanoi): Clicking `#mobile-menu-trigger` transitions `.site-header` and `.mobile-bar` to unified `#4A3B32` with `#F4F1EA` text and `rgba(244, 241, 234, 0.5)` borders, morphs the 3-bar hamburger into `✕` (`rotate(-45deg)` / `rotate(45deg)`), drops `.mobile-drawer` fullscreen from below `.mobile-bar` (`top: var(--mobile-header-h)`) to `bottom: 0` (`clip-path: inset(0 0 70% 0)` → `inset(0 0 0% 0)` + `opacity: 0` → `1`), and glides all 5 uppercase navigation items + bottom contact block upward with staggered delays (`1s cubic-bezier(0, 0.15, 0, 1)`), while locking body scroll, marking background landmarks `inert`, and supporting `Escape` dismissal.
   - Interactive Grayscale Homepage Map (`index.html` Section 7 `#reviews-map`): Replace static `<a><img>` wrapper with `#irori-map` (`data-map-lat="10.78492"`, `data-map-lng="106.7006"`, `data-map-zoom="16"`), lazy-loading `Leaflet 1.9.4` from CDN (`cdn.jsdelivr.net` + `unpkg.com` failover) with verified SHA-256 SRI hashes (`0` npm runtime dependencies, keeping `assets/images/saigon-map.jpg` as progressive-enhancement fallback) and CloudFront-backed Esri `World_Street_Map` tiles (`server.arcgisonline.com`, with `tileerror` failover to `tile.openstreetmap.fr/hot`) rendered monochrome (`filter: grayscale(1) contrast(1.08)`). Includes custom `House of IRORI — Cocktail Bar & Eatery` marker at `17/3 Nguyen Thi Minh Khai`, accessible `44x44` `+` (`#map-zoom-in`) / `−` (`#map-zoom-out`) buttons with min/max zoom `disabled` states, `Open in Google Maps ↗` link, mobile 2D drag-pan + 2-finger pinch-to-zoom (`dragging: true`, `touchZoom: true`), and desktop drag-pan with `scrollWheelZoom: false` so vertical page scrolling is never trapped.

## Open Questions
- None (Confirmed by Victor: replace `House of Merlin` with `House of IRORI` in `Menu`/`Events` descriptions; keep the `ELLE Decoration PopUp Office` sample article copy in `Blog Articles`).
- Phase 11 Refinements:
  1. `.hero-showcase__frame` border: `10px solid var(--bg-cream)` on desktop (Figma `494:1043`) and `3px solid var(--bg-cream)` on mobile (`<= 768px`, Figma `494:1370`).
  2. `.philosophy__grid`: remove `max-width: 1920px; margin-inline: auto` so `.philosophy__interior` and `.philosophy__lower` sit flush against the left viewport edge (`x = 0`) on all desktop viewports (`>= 1025px`).
  3. Carousel Scroll & Swipe Detection: support `wheel` (mouse wheel / trackpad) and touch swipe (`touchstart`/`touchend`) directly over `[data-carousel]` (`alley`, `bar`, `upcoming`), Google Reviews (`#reviews-slider`), and Latest News (`#latest-news` / `.latest-news__slider-wrap`) to advance/rewind slides with a `450ms` wheel cooldown and autoplay timer reset.
  4. `.reviews-map__inner` & `.reviews-map__reviews-col`: expand `.reviews-map__inner` (`max-width: 960px`) with centered alignment and balanced responsive inline padding (`padding: 72px clamp(24px, 6vw, 120px)`) so ultrawide/QHD screens (`1920px–2560px+`) have no excessive left empty space.
- Phase 12 Headless WordPress CMS Integration & cPanel Deployment (`sites-test.houseofirori.com.vn`):
  1. **Backend (`wordpress/mu-plugins/irori-headless-cms.php` -> `/cms/wp-content/mu-plugins/`):** Zero-bloat WordPress Must-Use plugin registering Custom Post Types (`google_reviews`), Blog categories (`interior`, `concept`, `club`), ACF Page & Options Field Groups matching `houseofmerlin.com.vn`, first-boot Figma data & media attachment seeder, transient-cached REST endpoints (`GET /cms/wp-json/irori/v1/bootstrap` & `GET /cms/wp-json/irori/v1/article/<slug>`), and strict allowlisted CORS.
  2. **Frontend Hydration (`js/cms.js` + `js/main.js`):** Progressive-enhancement client module (`js/cms.js`, `<= 10 KB`, keeping `js/main.js <= 15 KB` and total JS `<= 25 KB`) hydrating all 5 pages (`index.html`, `menu.html`, `events.html`, `blogs.html`, `blog-detail.html?slug=...`) using `document.createElement` + `Element.prototype.setHTML` / allowlist DOM tree sanitization (`0` `innerHTML` sinks) and `priority: 'low'` background fetch on pre-rendered pages, then dispatching `irori:cms` so carousels, sliders, and blog filters stay synchronized.
  3. **cPanel Hosting (`https://ghv55-24914.azdigihost.com:2083`, `zhorktpv`):** Provision Addon Domain `sites-test.houseofirori.com.vn`, isolated MariaDB database & user (`zhorktpv_irori`), WordPress 6.x in `/cms/` (`alt-php83`), static 5-page frontend at webroot `/`, and `.htaccess` security/cache headers.
- Phase 13 `houseofmerlin` CMS Parity & PDF Menu FlipBook Viewer:
  1. **1:1 Admin Parity with `houseofmerlin.com.vn` (`/home/zhorktpv/public_html`):** Install & activate `Advanced Custom Fields PRO` (`acf/acf.php`), `3D FlipBook - DFlip Lite` (`3d-flipbook-dflip-lite/3d-flipbook-dflip-lite.php`), and `Admin Menu Editor` (`admin-menu-editor/menu-editor.php`). Configure the exact same admin menu order (`Pages` -> `Blogs` (`edit.php`) -> `Google Reviews` (`edit.php?post_type=google_reviews`) -> `Media` -> `Irori Settings` (`irori_config-page`)), disable Gutenberg (`use_block_editor_for_post => false`), hide the raw page editor on the static pages (`Welcome`, `Menu`, `Events`, `Blogs`, `Contact`), and register the ACF Field Groups (`Welcome Page`, `Menu Page`, `Event Page`, `Blogs Page`, `Contact Page`, `Irori Config`).
  2. **PDF Menu Tabs (`main_menus` Repeater + Frontend 3D FlipBook PDF Viewer):** Replace manual dish-row entry with `Main Menus` (`main_menus` repeater on `Pages -> Menu`) where each row has `Menu file` (`pdf` file upload) and `Menu name` (`menu_name` text). Seed multi-page PDF menus (`assets/menus/irori-cocktail-menu.pdf` & `assets/menus/irori-food-menu.pdf`, plus WP Media Library attachments) and render every tab in `menu.html` (`#menu-book`) as an interactive PDF FlipBook (`_df_book df-lite` via DFlip Lite with clean static fallback) that dynamically supports adding/editing/removing PDF menu tabs in WordPress Admin.
- Phase 14 Full 1:1 Content & Field Parity Between CMS and Website (`?v=11.2`):
  1. **`Welcome Page` (`index.html`) 1:1 ACF Fields & Seeded Content:** Replace all `houseofmerlin` leftover copy with the exact 8 sections of `index.html`: `Welcome Banner` (`hero-bg.jpg`, `A QUIET CORNER OF JAPAN, TUCKED INTO THE HEART OF SAIGON`, `DISCOVER THE STORY`, `hero-bartender-framed.jpg`), `Story Section` (`story-bg.jpg`, `HIDDEN IN PLAIN SIGHT`, two-paragraph `omotenashi` copy), `Philosophy Section` (`THE SPACE BETWEEN, STILLNESS AND BEAUTY`, `TWO FLOORS. ONE PHILOSOPHY.`, `collage-interior.jpg`, `collage-kimono-framed.jpg`), `Level One: The Alley` (`LEVEL ONE:`, `THE ALLEY`, description, 3-slide gallery `[level1-alley.jpg, collage-interior.jpg, floating-banner.jpg]`), `Level Two: The Bar` (`LEVEL TWO:`, `THE BAR`, description, `This is where you stay.`, 3-slide gallery `[level2-bar.jpg, footer-glimpse-bg.jpg, collage-interior.jpg]`), and `Bottom Section` (`saigon-map.jpg`, `floating-banner.jpg`).
  2. **`Menu Page`, `Events Page`, `Blogs Page`, `Blogs` Posts, `Google Reviews`, and `Irori Settings` 1:1 Hydration:** Ensure all 9 seeded `Blogs` posts match the exact titles, dates, categories, excerpts, and images in `blogs.html` and `index.html` (and the two-figure/two-subheading editorial layout in `blog-detail.html`), order `Events Page` fields top-to-bottom (`Event Banner` -> `Up Coming Events` -> `Occasional Events`), sync `Contact Page` with `Irori Settings` (`A GLIMPSE INSIDE`, `Brand Tagline`, `VISIT`, `CONTACT`, `FOLLOW`, `Disclaimer`, `Copyright`), and hydrate every field onto the live DOM via `/cms/wp-json/irori/v1/bootstrap` and `js/cms.js` (`<= 10 KB`).
