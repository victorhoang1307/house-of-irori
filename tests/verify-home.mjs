import fs from 'node:fs';
import path from 'node:path';
import assert from 'node:assert/strict';

const ROOT = '/Users/Victor/.gemini/antigravity/scratch/house-of-irori';

function checkFileExists(relPath, minBytes = 100) {
  const full = path.join(ROOT, relPath);
  assert.ok(fs.existsSync(full), `Missing file: ${relPath}`);
  const stat = fs.statSync(full);
  assert.ok(stat.size >= minBytes, `File too small (${stat.size}B < ${minBytes}B): ${relPath}`);
  return full;
}

console.log('Running House of IRORI — Home v3B verification suite...');

// 1. Verify exported Figma assets
const requiredAssets = [
  'assets/images/hero-bg.jpg',
  'assets/images/logo-white.png',
  'assets/images/logo-gold.png',
  'assets/images/hero-wood-pattern.png',
  'assets/images/hero-bartender-framed.jpg',
  'assets/images/story-bg.jpg',
  'assets/images/collage-interior.jpg',
  'assets/images/philosophy-card-bg.png',
  'assets/images/collage-kimono-framed.jpg',
  'assets/images/watermark-line.png',
  'assets/images/level1-alley.jpg',
  'assets/images/level2-bar.jpg',
  'assets/images/saigon-map.jpg',
  'assets/images/floating-banner.jpg',
  'assets/images/blog-thumb-1.jpg',
  'assets/images/footer-glimpse-bg.jpg',
  'assets/images/hero-menu.jpg',
  'assets/images/hero-events.jpg',
  'assets/images/hero-blogs.jpg',
  'assets/images/event-wild-turkey.jpg',
  'assets/images/menu-showcase-1.jpg',
  'assets/images/event-upcoming-featured.jpg',
  'assets/images/events-upcoming-banner.jpg',
  'assets/images/events-upcoming-bg.jpg',
  'assets/icons/favicon.png',
  'assets/icons/arrow-ornament-left.svg',
  'assets/icons/arrow-ornament-right.svg',
];

for (const asset of requiredAssets) {
  checkFileExists(asset, 200);
}
console.log(`✓ All ${requiredAssets.length} Figma assets present and non-empty.`);

// 2. Verify HTML structure & content
const htmlPath = checkFileExists('index.html', 2000);
const html = fs.readFileSync(htmlPath, 'utf8');

const requiredHeadingsAndCopy = [
  'A Quiet Corner of Japan',
  'Tucked Into the Heart of Saigon',
  'Discover the story',
  'Hidden in Plain Sight',
  'The Space Between',
  'Stillness and Beauty',
  'Two floors. One philosophy.',
  'LEVEL ONE:',
  'The Alley',
  'LEVEL TWO:',
  'The Bar',
  'from our clients',
  '5.0 / 5.0',
  'Latest News',
  'Why We Work with Spirit, Not Against It',
  'A Glimpse Inside',
  '17/3 Nguyen Thi Minh Khai',
  '+84 97 141 90 66',
  'houseofirori@gmail.com',
  '© 2026 House of IRORI',
];

for (const phrase of requiredHeadingsAndCopy) {
  assert.ok(
    html.toLowerCase().includes(phrase.toLowerCase()),
    `index.html missing required copy: "${phrase}"`
  );
}

// Ensure zero Hebrew placeholder characters remain
assert.ok(
  !/[\u0590-\u05FF]/.test(html),
  'index.html still contains Hebrew placeholder characters in Google Reviews'
);

// Ensure Strapi CMS data attributes & interactive hooks exist
const requiredHooks = [
  'data-cms-collection="reviews"',
  'data-cms-item="review"',
  'data-cms-collection="articles"',
  'data-cms-item="article"',
  'class="hero-showcase"',
  'id="atmosphere-strip"',
  'id="mobile-menu-trigger"',
  'id="mobile-drawer"',
  'id="mobile-reservations-bar"',
  'data-carousel="alley"',
  'data-carousel="bar"',
  'id="reviews-prev"',
  'id="reviews-next"',
  'id="news-prev"',
  'id="news-next"',
  'id="irori-map"',
  'data-map-lat="10.78492"',
  'data-map-lng="106.7006"',
  'data-map-zoom="16"',
  'id="map-zoom-in"',
  'id="map-zoom-out"',
  'class="map-controls"',
  'class="map-external-link"',
];

for (const hook of requiredHooks) {
  assert.ok(html.includes(hook), `index.html missing required selector/attribute: ${hook}`);
}

assert.ok(
  !html.includes('class="reviews-map__map-link"') && !html.toLowerCase().includes('house of merlin'),
  'index.html must replace full-surface .reviews-map__map-link anchor with interactive #irori-map and contain zero "House of Merlin" text'
);

// Ensure modern-web-guidance LCP image fetchpriority="high" is applied
assert.ok(html.includes('fetchpriority="high"'), 'Missing fetchpriority="high" on LCP image');

console.log('✓ index.html passes all section, copy, Strapi attribute, and LCP checks.');

// 3. Verify CSS design tokens & size budget
const cssPath = checkFileExists('css/style.css', 1000);
const cssStat = fs.statSync(cssPath);
assert.ok(cssStat.size <= 50 * 1024, `css/style.css exceeds 50KB budget (${cssStat.size}B)`);
const css = fs.readFileSync(cssPath, 'utf8');

const requiredColors = ['#F4F1EA', '#5A2929', '#4A3B32', '#C6A15B', '#C9A24A', '#0A0A0A', '#141414'];
for (const hex of requiredColors) {
  assert.ok(
    css.toUpperCase().includes(hex),
    `css/style.css missing Figma design token color: ${hex}`
  );
}
for (const font of ['Playfair Display', 'Lora', 'Inter']) {
  assert.ok(css.includes(font), `css/style.css missing font family token: ${font}`);
}
assert.ok(css.includes('@media'), 'css/style.css missing responsive @media queries');
assert.ok(
  /\.hero__mobile-reservations\s*\{[^}]*position:\s*fixed/s.test(css) &&
    /\.hero__mobile-reservations\s*\{[^}]*(bottom:\s*0|inset-block-end:\s*0)/s.test(css),
  'css/style.css must position .hero__mobile-reservations as fixed at bottom: 0 on mobile'
);

// Phase 2B Slice 1: Visual Fidelity Assertions
const wmBuf = fs.readFileSync(path.join(ROOT, 'assets/images/watermark-line.png'));
const wmWidth = wmBuf.readUInt32BE(16);
const wmHeight = wmBuf.readUInt32BE(20);
assert.ok(
  wmWidth === 800 && wmHeight === 800,
  `watermark-line.png must be cropped to 800x800 emblem-only without text (got ${wmWidth}x${wmHeight})`
);
assert.ok(
  /\.philosophy__lower\s*\{[^}]*overflow:\s*visible/s.test(css) &&
    !/\.philosophy__lower\s*\{[^}]*min-height:\s*320px/s.test(css),
  '.philosophy__lower must keep overflow: visible and proportional height so .philosophy__watermark is never clipped or obscured'
);

const heroShowcaseFrameMatch = css.match(/\.hero-showcase__frame\s*\{([^}]*)\}/s);
assert.ok(heroShowcaseFrameMatch, 'Missing .hero-showcase__frame rule in css/style.css');
assert.ok(
  !/box-shadow:\s*0\s+20px/i.test(heroShowcaseFrameMatch[1]) &&
    !/padding:\s*6px/i.test(heroShowcaseFrameMatch[1]) &&
    !/background-color:\s*var\(--color-white\)/i.test(heroShowcaseFrameMatch[1]),
  '.hero-showcase__frame must not have border/padding/white-bg or drop shadow'
);
assert.ok(
  /\.hero-showcase__pattern\s*\{[^}]*top:\s*calc\(var\(--hero-overlap\)\s*-\s*1px\)/s.test(css),
  '.hero-showcase__pattern must lock its top edge to --hero-overlap so there is 0 gap with .hero'
);
assert.ok(
  /\.site-header\.is-scrolled[^{]*\{[^}]*background-color:\s*(#4A3B32|var\(--color-espresso\))/is.test(css) &&
    css.includes('rgba(244, 241, 234, 0.5)') &&
    /\.site-header\.is-scrolled\s+\.header-topbar\s*\{[^}]*max-height:\s*0/s.test(css) &&
    /\.site-header\.is-scrolled\s+\.header-banner\s*\{[^}]*height:\s*72px/s.test(css),
  '.site-header.is-scrolled must use #4A3B32 bg, collapse .header-topbar, and shrink .header-banner to 72px (Logo + Menu only)'
);
assert.ok(
  /\.upcoming-banner__bg\s+img\s*\{[^}]*object-fit:\s*cover[^}]*object-position:\s*top\s+center/s.test(css),
  '.upcoming-banner__bg img must use object-fit: cover and object-position: top center'
);
assert.ok(
  /\.upcoming-showcase__bg\s*\{[^}]*hero-wood-pattern\.png/s.test(css) &&
    !/\.upcoming-showcase__bg\s*\{[^}]*opacity:\s*0\.35/s.test(css),
  '.upcoming-showcase__bg must use opaque tiled hero-wood-pattern.png per Figma 622:1324'
);

// Phase 2B Slice 2 & Phase 8: High-Visibility Parallax & Horizontal Track Slider Assertions
assert.ok(
  css.includes('@supports ((animation-timeline: view()) and (animation-range: entry))') &&
    css.includes('view-timeline:') &&
    css.includes('prefers-reduced-motion: reduce') &&
    /--parallax-shift:\s*2[0-5]%/.test(css) &&
    css.includes('.carousel__track') &&
    css.includes('.latest-news__viewport') &&
    css.includes('.split-level__media-col .carousel__slide img'),
  'css/style.css must implement high-visibility scroll-driven parallax (--parallax-shift >= 20%) and horizontal .carousel__track / .latest-news__viewport tracks'
);

// Phase 2B Slice 3: Larger .category-pill & .btn-load-more + .menu-book-spread styles
const categoryPillMatch = css.match(/\.category-pill\s*\{([^}]*)\}/s);
assert.ok(categoryPillMatch, 'Missing .category-pill rule in css/style.css');
const pillMinBlock = categoryPillMatch[1].match(/min-block-size:\s*(\d+(?:\.\d+)?)px/);
const pillFontSize = categoryPillMatch[1].match(/font-size:\s*(\d+(?:\.\d+)?)px/);
assert.ok(
  pillMinBlock && Number(pillMinBlock[1]) >= 34 && pillFontSize && Number(pillFontSize[1]) >= 13.5,
  '.category-pill must have larger sizing (min-block-size >= 34px, font-size >= 13.5px)'
);
assert.ok(
  css.includes('.btn-load-more') && css.includes('.menu-book-spread'),
  'css/style.css must style .btn-load-more and .menu-book-spread'
);

// Phase 9: Fullscreen Mobile Menu (The Grand Hanoi style & staggered motion)
assert.ok(
  css.includes('.site-header.is-menu-open') &&
    css.includes('body.is-menu-open') &&
    css.includes('--mobile-header-h') &&
    css.includes('clip-path: inset(0 0 70% 0)') &&
    css.includes('rotate(-45deg)') &&
    css.includes('rotate(45deg)') &&
    css.includes('cubic-bezier(0, 0.15, 0, 1)') &&
    css.includes('transition-delay:'),
  'css/style.css must implement fullscreen mobile menu (.is-menu-open), hamburger-to-X rotation, and cubic-bezier(0, 0.15, 0, 1) staggered upward item cascade'
);

// Phase 10: Interactive Grayscale Homepage Map CSS & Specificity Guard Assertions
assert.ok(
  !/\:where\([^)]*(?:\bimg\b|page-hero__bg-img)/.test(css) &&
    css.includes(':is(.site-header.is-scrolled, .site-header.is-menu-open, .site-header--solid)'),
  'css/style.css must preserve natural specificity on cover img selectors and :is(...) specificity on .site-header--solid'
);
assert.ok(
  /\.reviews-map__map-col\s*\{[^}]*isolation:\s*isolate/s.test(css) &&
    /\.reviews-map__fallback\s*\{[^}]*position:\s*absolute/s.test(css) &&
    css.includes('.reviews-map__canvas.is-ready .reviews-map__fallback') &&
    /\.reviews-map__canvas\s*\{[^}]*overscroll-behavior:\s*contain/s.test(css) &&
    css.includes('-webkit-user-select: none') &&
    css.includes('.map-ctrl-btn') &&
    /\.leaflet-tile-pane\s*\{[^}]*filter:\s*[^;}]*grayscale\(1\)/s.test(css) &&
    css.includes('.irori-pin'),
  'css/style.css must isolate .reviews-map__map-col stacking, position .reviews-map__fallback absolutely, contain overscroll on .reviews-map__canvas, style .map-ctrl-btn & .irori-pin, and filter .leaflet-tile-pane grayscale(1)'
);

console.log(`✓ css/style.css passes token and budget checks (${(cssStat.size / 1024).toFixed(1)} KB).`);

// 4. Verify JS interactive module & size budget
const jsPath = checkFileExists('js/main.js', 500);
const jsStat = fs.statSync(jsPath);
assert.ok(jsStat.size <= 15 * 1024, `js/main.js exceeds 15KB budget (${jsStat.size}B)`);
const jsSource = fs.readFileSync(jsPath, 'utf8');
const autoplayCallCount = (jsSource.match(/createAutoplay\(/g) || []).length;
assert.ok(
  jsSource.includes('mouseenter') &&
    jsSource.includes('mouseleave') &&
    jsSource.includes('setInterval') &&
    jsSource.includes('IntersectionObserver') &&
    autoplayCallCount >= 3,
  'js/main.js must wire createAutoplay to all 3 carousel/slider controllers and include IntersectionObserver parallax fallback'
);
assert.ok(
  jsSource.includes('data-load-more') &&
    jsSource.includes('data-extra-item') &&
    jsSource.includes('URLSearchParams') &&
    jsSource.includes('.carousel__track') &&
    jsSource.includes('translate3d(-') &&
    jsSource.includes('--news-idx') &&
    jsSource.includes('is-menu-open') &&
    jsSource.includes('bgRegions') &&
    jsSource.includes('(min-width: 769px)') &&
    jsSource.includes('Escape'),
  'js/main.js must handle Load More buttons, ?category= URL filtering, horizontal translate3d track sliders, and fullscreen mobile menu (is-menu-open + Escape)'
);
assert.ok(
  jsSource.includes('leaflet@1.9.4') &&
    jsSource.includes("crossOrigin: 'anonymous'") &&
    jsSource.includes('sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=') &&
    jsSource.includes('sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=') &&
    jsSource.includes('scrollWheelZoom: false') &&
    jsSource.includes('touchZoom: true') &&
    jsSource.includes('dragging: true') &&
    jsSource.includes('tile.openstreetmap.org/{z}/{x}/{y}.png') &&
    jsSource.includes('is-ready') &&
    jsSource.includes('House of IRORI') &&
    jsSource.includes('map-zoom-in') &&
    jsSource.includes('map-zoom-out'),
  'js/main.js must lazy-load Leaflet 1.9.4 with SHA-256 SRI, OpenStreetMap tiles, House of IRORI marker, zoom buttons, and mobile pan/pinch-zoom'
);
assert.ok(
  html.includes('class="carousel__track"') &&
    html.includes('class="latest-news__viewport"') &&
    !/class="carousel__slide"[^>]*\s+hidden(?:\s|>|=)/.test(html),
  'index.html must wrap carousel slides in .carousel__track (without hidden) and #news-track in .latest-news__viewport'
);
console.log(`✓ js/main.js passes budget check (${(jsStat.size / 1024).toFixed(1)} KB).`);

// 5. Verify all 4 subpages (menu.html, events.html, blogs.html, blog-detail.html)
const subpageSpecs = [
  {
    file: 'menu.html',
    phrases: [
      'Cocktail & Eatery Menu',
      'OCCASIONAL',
      'Guestshift - Wild Turkey 8ys Takeover',
      'House of IRORI',
      'Our top cocktail',
      'Food (Pizza & Side dishes)',
    ],
    hooks: [
      'data-cms-collection="occasional-menus"',
      'data-cms-item="occasional-menu"',
      'data-menu-tab="cocktail"',
      'data-menu-tab="food"',
      'class="menu-book-spread"',
      'data-load-more="occasional-menu"',
      'data-extra-item',
      'id="mobile-reservations-bar"',
      'fetchpriority="high"',
    ],
  },
  {
    file: 'events.html',
    phrases: [
      'Events',
      'up coming',
      'OCCASIONAL',
      'Guestshift - Wild Turkey 8ys Takeover',
      'House of IRORI',
    ],
    hooks: [
      'data-cms-collection="events"',
      'data-cms-item="event"',
      'data-carousel="upcoming"',
      'class="carousel__track"',
      'data-load-more="occasional-events"',
      'data-extra-item',
      'id="mobile-reservations-bar"',
      'fetchpriority="high"',
    ],
  },
  {
    file: 'blogs.html',
    phrases: [
      'BLOGS',
      'Latest News',
      '#category',
      'interior',
      'Concept',
      'Club',
      'Why We Work with Spirit, Not Against It',
    ],
    hooks: [
      'data-cms-collection="articles"',
      'data-cms-item="article"',
      'data-filter-category=',
      'data-load-more="blogs"',
      'data-extra-item',
      'blogs.html?category=interior',
      'href="blog-detail.html"',
      'id="mobile-reservations-bar"',
      'fetchpriority="high"',
    ],
  },
  {
    file: 'blog-detail.html',
    phrases: [
      'ELLE Decoration PopUp Office',
      'April 14, 2026',
      'Why We Work with Spirit, Not Against It',
      '#category',
      'Latest News',
    ],
    hooks: [
      'data-cms-collection="articles"',
      'class="latest-news__viewport"',
      'data-cms-item="article"',
      'href="blogs.html?category=interior"',
      'href="blogs.html?category=concept"',
      'href="blogs.html?category=club"',
      'id="news-prev"',
      'id="news-next"',
      'id="mobile-reservations-bar"',
      'fetchpriority="high"',
    ],
  },
];

for (const spec of subpageSpecs) {
  const p = checkFileExists(spec.file, 1500);
  const content = fs.readFileSync(p, 'utf8');
  const decodedContent = content.replaceAll('&amp;', '&');
  assert.ok(
    !decodedContent.toLowerCase().includes('house of merlin'),
    `${spec.file} still contains template name "House of Merlin"`
  );
  for (const phrase of spec.phrases) {
    assert.ok(
      decodedContent.toLowerCase().includes(phrase.toLowerCase()),
      `${spec.file} missing required copy: "${phrase}"`
    );
  }
  for (const hook of spec.hooks) {
    assert.ok(
      content.includes(hook),
      `${spec.file} missing required selector/attribute: ${hook}`
    );
  }
  for (const navHref of ['href="index.html"', 'href="menu.html"', 'href="events.html"', 'href="blogs.html"', 'href="#footer"']) {
    assert.ok(
      content.includes(navHref),
      `${spec.file} missing shared navigation link: ${navHref}`
    );
  }
  console.log(`✓ ${spec.file} passes all copy, Strapi attribute, and mobile fixed CTA checks.`);
}

// Verify menu.html food panel does not duplicate cocktail panel image
const menuHtml = fs.readFileSync(path.join(ROOT, 'menu.html'), 'utf8');
const foodPanelMatch = menuHtml.match(/data-menu-panel="food"[^>]*>([\s\S]*?)<\/section>/);
assert.ok(
  foodPanelMatch && !foodPanelMatch[1].includes('menu-showcase-1.jpg'),
  'menu.html data-menu-panel="food" must render distinct food content instead of duplicating menu-showcase-1.jpg'
);
assert.ok(
  html.includes('blogs.html?category='),
  'index.html Latest News cards must include clickable category tag links (blogs.html?category=...)'
);

// Verify all local asset src/href references across all 5 HTML files resolve on disk
for (const htmlFile of ['index.html', 'menu.html', 'events.html', 'blogs.html', 'blog-detail.html']) {
  const srcText = fs.readFileSync(path.join(ROOT, htmlFile), 'utf8');
  for (const m of srcText.matchAll(/\b(?:src|href)="(assets\/[^"#?]+)"/g)) {
    const assetDiskPath = path.join(ROOT, m[1]);
    assert.ok(fs.existsSync(assetDiskPath), `${htmlFile} references missing local asset: ${m[1]}`);
  }
  for (const extraTag of srcText.matchAll(/<article[^>]*\bdata-extra-item\b[^>]*>/g)) {
    assert.ok(
      /\bhidden\b/.test(extraTag[0]),
      `${htmlFile} has [data-extra-item] without initial hidden attribute: ${extraTag[0]}`
    );
  }
}

const eventsHtml = fs.readFileSync(path.join(ROOT, 'events.html'), 'utf8');
const showcaseBgMatch = eventsHtml.match(/<div class="upcoming-showcase__bg"[^>]*>([\s\S]*?)<\/div>/);
assert.ok(
  showcaseBgMatch && !showcaseBgMatch[1].includes('<img'),
  'events.html .upcoming-showcase__bg must use tiled CSS background rather than a stretched <img>'
);

console.log('ALL VERIFICATION CHECKS PASSED!');
