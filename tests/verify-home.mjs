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
];

for (const asset of requiredAssets) {
  checkFileExists(asset, 500);
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
console.log(`✓ css/style.css passes token and budget checks (${(cssStat.size / 1024).toFixed(1)} KB).`);

// 4. Verify JS interactive module & size budget
const jsPath = checkFileExists('js/main.js', 500);
const jsStat = fs.statSync(jsPath);
assert.ok(jsStat.size <= 25 * 1024, `js/main.js exceeds 25KB budget (${jsStat.size}B)`);
console.log(`✓ js/main.js passes budget check (${(jsStat.size / 1024).toFixed(1)} KB).`);

console.log('ALL VERIFICATION CHECKS PASSED!');
