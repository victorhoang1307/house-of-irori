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
  'data-carousel="alley"',
  'data-carousel="bar"',
  'id="reviews-prev"',
  'id="reviews-next"',
  'id="news-prev"',
  'id="news-next"',
];

for (const hook of requiredHooks) {
  assert.ok(html.includes(hook), `index.html missing required selector/attribute: ${hook}`);
}

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
    !/\.site-header\.is-scrolled\s+\.header-topbar\s*\{\s*display:\s*none/s.test(css),
  '.site-header.is-scrolled must use #4A3B32 bg, rgba(244, 241, 234, 0.5) border, and keep .header-topbar visible on desktop'
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

// Phase 2B Slice 2: Parallax & Motion Assertions
assert.ok(
  css.includes('@supports ((animation-timeline: view()) and (animation-range: entry))') &&
    css.includes('view-timeline:') &&
    css.includes('prefers-reduced-motion: reduce'),
  'css/style.css must implement native scroll-driven parallax with feature detection and prefers-reduced-motion'
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

console.log(`✓ css/style.css passes token and budget checks (${(cssStat.size / 1024).toFixed(1)} KB).`);

// 4. Verify JS interactive module & size budget
const jsPath = checkFileExists('js/main.js', 500);
const jsStat = fs.statSync(jsPath);
assert.ok(jsStat.size <= 15 * 1024, `js/main.js exceeds 15KB budget (${jsStat.size}B)`);
const jsSource = fs.readFileSync(jsPath, 'utf8');
assert.ok(
  jsSource.includes('mouseenter') &&
    jsSource.includes('mouseleave') &&
    jsSource.includes('setInterval') &&
    jsSource.includes('IntersectionObserver'),
  'js/main.js must implement carousel autoplay with hover pause and IntersectionObserver parallax fallback'
);
assert.ok(
  jsSource.includes('data-load-more') &&
    jsSource.includes('data-extra-item') &&
    jsSource.includes('URLSearchParams'),
  'js/main.js must handle Load More buttons and ?category= URL query filtering'
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

console.log('ALL VERIFICATION CHECKS PASSED!');
