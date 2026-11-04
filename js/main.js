/**
 * House of IRORI — Home v3B Interactive Behaviour
 * - Sticky header state transition on scroll
 * - Accessible Mobile Menu Drawer toggle
 * - Image carousels for Level One (The Alley) & Level Two (The Bar)
 * - Horizontal slider controls for Google Reviews & Latest News
 */

document.addEventListener('DOMContentLoaded', () => {
  const $ = (id) => document.getElementById(id), qs = (s, r = document) => r.querySelector(s), $$ = (s, r = document) => [...r.querySelectorAll(s)];
  const on = (el, ev, fn, opts) => el?.addEventListener(ev, fn, opts), mq = (q) => matchMedia(q);
  const hasIO = 'IntersectionObserver' in window, reducedMotion = mq('(prefers-reduced-motion: reduce)').matches;
  const setAttr = (el, k, v) => el.setAttribute(k, String(v));
  const setPressed = (el, s) => { el.classList.toggle('is-active', s); setAttr(el, 'aria-pressed', s); };
  const setHidden = (el, h) => { setAttr(el, 'aria-hidden', h); el.inert = h; };

  /**
   * Reusable autoplay controller that pauses on hover, keyboard focus, and hidden tab.
   * Tones down cadence gently when prefers-reduced-motion is enabled instead of breaking autoplay.
   */
  const createAutoplay = (box, stepFn, ms = 4500) => {
    if (!box) return { restart: () => {} };
    const delay = reducedMotion ? Math.round(ms * 1.3) : ms;
    let timer = null, hovered = false, focused = false, inView = !hasIO, warmed = false;
    const stop = () => { clearInterval(timer); timer = null; };
    const start = () => { stop(); if (inView && !hovered && !focused && !document.hidden) timer = setInterval(stepFn, delay); };
    if (hasIO) {
      new IntersectionObserver((e) => {
        inView = Boolean(e[0]?.isIntersecting);
        if (inView && !warmed) { warmed = true; $$('img[loading="lazy"]', box).forEach((img) => img.removeAttribute('loading')); }
        inView ? start() : stop();
      }, { threshold: 0.05 }).observe(box);
    }
    on(box, 'mouseenter', () => ((hovered = true), stop()));
    on(box, 'mouseleave', () => ((hovered = false), start()));
    on(box, 'focusin', () => ((focused = true), stop()));
    on(box, 'focusout', (e) => !box.contains(e.relatedTarget) && ((focused = false), start()));
    on(document, 'visibilitychange', () => (document.hidden ? stop() : start()));
    start();
    return { restart: start };
  };

  const bindScroll = (el, step, auto, touch = 1) => {
    let t = 0, sx = 0, sy = 0;
    on(el, 'wheel', (e) => {
      const dx = e.deltaX, dy = e.deltaY, d = Math.abs(dx) > Math.abs(dy) ? (e.preventDefault(), dx) : dy, now = Date.now();
      if (Math.abs(d) > 15 && now - t > 420) { t = now; step(d > 0 ? 1 : -1); auto.restart(); }
    }, { passive: false });
    on(el, 'touchstart', (e) => { sx = e.touches[0].clientX; sy = e.touches[0].clientY; }, { passive: true });
    on(el, 'touchend', (e) => {
      const dx = sx - e.changedTouches[0].clientX, dy = sy - e.changedTouches[0].clientY;
      if (Math.abs(dx) > 36 && Math.abs(dx) > Math.abs(dy)) { if (touch) step(dx > 0 ? 1 : -1); auto.restart(); }
    }, { passive: true });
  };

  // 1. Sticky Header Scroll State
  const header = $('site-header');
  const syncHeader = () => header && !header.classList.contains('is-menu-open') && header.classList.toggle('is-scrolled', scrollY > 40);
  on(window, 'scroll', syncHeader, { passive: true });
  syncHeader();

  // 2. Mobile Menu Drawer Toggle
  const menuBtn = $('mobile-menu-trigger'), drawer = $('mobile-drawer');
  if (menuBtn && drawer) {
    const bgRegions = $$('main, footer, #mobile-reservations-bar');
    const setMenu = (open, focusBtn = false) => {
      setAttr(menuBtn, 'aria-expanded', open);
      setHidden(drawer, !open);
      header?.classList.toggle('is-menu-open', open);
      document.body.classList.toggle('is-menu-open', open);
      bgRegions.forEach((el) => (el.inert = open));
      if (!open) { syncHeader(); if (focusBtn) menuBtn.focus(); }
    };
    drawer.hidden = false;
    setMenu(false);
    const isOpen = () => menuBtn.getAttribute('aria-expanded') === 'true';
    on(menuBtn, 'click', () => setMenu(!isOpen()));
    $$('.mobile-drawer a, .header-logo', header || drawer).forEach((a) => on(a, 'click', () => setMenu(false)));
    on(document, 'keydown', (e) => e.key === 'Escape' && isOpen() && setMenu(false, true));
    on(mq('(min-width: 769px)'), 'change', (e) => e.matches && setMenu(false));
  }

  // 3. Multi-Carousel Horizontal Track Slider (Level One, Level Two, Events UP COMING)
  $$('[data-carousel]').forEach((el) => {
    const track = qs('.carousel__track', el), slides = $$('.carousel__slide', el), dots = $$('.carousel__dot', el);
    let cur = 0;
    const go = (n) => {
      if (!slides.length) return;
      cur = (n + slides.length) % slides.length;
      if (track) track.style.transform = `translate3d(-${cur * 100}%, 0, 0)`;
      slides.forEach((s, i) => { s.classList.toggle('is-active', i === cur); setHidden(s, i !== cur); });
      dots.forEach((d, i) => setPressed(d, i === cur));
    };
    const auto = createAutoplay(el, () => go(cur + 1));
    dots.forEach((d, i) => on(d, 'click', () => (go(i), auto.restart())));
    on(qs('[data-carousel-prev]', el), 'click', () => (go(cur - 1), auto.restart()));
    on(qs('[data-carousel-next]', el), 'click', () => (go(cur + 1), auto.restart()));
    bindScroll(el, (dir) => go(cur + dir), auto);
  });

  // 4. Menu Page Category Tabs (Cocktail vs Food)
  const menuTabs = $$('[data-menu-tab]'), menuPanels = $$('[data-menu-panel]');
  if (menuTabs.length && menuPanels.length) {
    menuTabs.forEach((tab) => on(tab, 'click', () => {
      const key = tab.dataset.menuTab;
      menuTabs.forEach((t) => setPressed(t, t === tab));
      menuPanels.forEach((p) => { const ok = p.dataset.menuPanel === key; p.classList.toggle('is-active', ok); p.hidden = !ok; });
    }));
  }

  // 5. Load More Pagination (Occasionals & Events)
  $$('[data-load-more]').forEach((btn) => btn.dataset.loadMore !== 'blogs' && on(btn, 'click', () => {
    $$('[data-extra-item]', btn.closest('section') || document).forEach((el) => (el.hidden = false));
    btn.hidden = true;
  }));

  // 6. Blogs Page #category Tag Filter + ?category= URL Query + Load More
  const filterBtns = $$('[data-filter-category]'), blogGrid = $('blogs-grid'), blogMore = qs('[data-load-more="blogs"]');
  if (filterBtns.length && blogGrid) {
    const cards = $$('.news-card', blogGrid), valid = new Set(filterBtns.map((b) => b.dataset.filterCategory));
    let cat = null, expanded = false;
    const syncBlogs = (pushUrl) => {
      filterBtns.forEach((b) => setPressed(b, b.dataset.filterCategory === cat));
      cards.forEach((c) => (c.hidden = cat ? c.dataset.category !== cat : !expanded && c.hasAttribute('data-extra-item')));
      if (blogMore) blogMore.hidden = Boolean(cat) || expanded;
      if (pushUrl && history.replaceState) {
        const u = new URL(location.href);
        cat ? u.searchParams.set('category', cat) : u.searchParams.delete('category');
        history.replaceState(null, '', u.pathname + u.search + u.hash);
      }
    };
    const initCat = (new URLSearchParams(location.search).get('category') || '').toLowerCase();
    if (valid.has(initCat)) cat = initCat;
    syncBlogs(false);
    filterBtns.forEach((b) => on(b, 'click', () => { cat = cat === b.dataset.filterCategory ? null : b.dataset.filterCategory; syncBlogs(true); }));
    on(blogGrid, 'click', (e) => {
      const linkCat = e.target.closest('[data-category-link]')?.dataset.categoryLink?.toLowerCase();
      if (valid.has(linkCat)) { e.preventDefault(); cat = linkCat; syncBlogs(true); }
    });
    on(blogMore, 'click', () => { expanded = true; syncBlogs(false); });
  }

  // 6. Google Reviews Slider Controls + Autoplay
  const revTrack = $('reviews-track'), revPrev = $('reviews-prev'), revNext = $('reviews-next');
  if (revTrack && revPrev && revNext) {
    const stepPx = 263; // card width (245) + gap (18)
    const behavior = reducedMotion ? 'auto' : 'smooth';
    const stepRev = (dir = 1) => {
      const l = revTrack.scrollLeft;
      const left = dir > 0 ? (l + revTrack.clientWidth >= revTrack.scrollWidth - 16 ? 0 : l + stepPx) : (l <= 8 ? revTrack.scrollWidth : l - stepPx);
      revTrack.scrollTo({ left, behavior });
    };
    const revWrap = $('reviews-slider') || revTrack, revAuto = createAutoplay(revWrap, () => stepRev(1));
    on(revPrev, 'click', () => (stepRev(-1), revAuto.restart()));
    on(revNext, 'click', () => (stepRev(1), revAuto.restart()));
    bindScroll(revWrap, stepRev, revAuto, 0);
  }

  // 7. Latest News Horizontal Track Slider + Autoplay
  const newsTrack = $('news-track'), newsPrev = $('news-prev'), newsNext = $('news-next');
  if (newsTrack && newsPrev && newsNext) {
    const cards = $$('.news-card', newsTrack);
    let idx = 0;
    const perView = () => (mq('(max-width: 768px)').matches ? 1 : mq('(max-width: 1024px)').matches ? 2 : 3);
    const maxIdx = () => Math.max(0, cards.length - perView());
    const syncNews = () => {
      const pv = perView();
      idx = Math.max(0, Math.min(maxIdx(), idx));
      newsTrack.style.setProperty('--news-idx', String(idx));
      cards.forEach((c, i) => setHidden(c, i < idx || i >= idx + pv));
    };
    syncNews();
    on(window, 'resize', syncNews, { passive: true });
    const stepNews = (dir = 1) => { idx = dir > 0 ? (idx >= maxIdx() ? 0 : idx + 1) : (idx <= 0 ? maxIdx() : idx - 1); syncNews(); };
    const newsWrap = newsTrack.closest('.latest-news__slider-wrap') || newsTrack, newsAuto = createAutoplay(newsWrap, () => stepNews(1));
    on(newsPrev, 'click', () => (stepNews(-1), newsAuto.restart()));
    on(newsNext, 'click', () => (stepNews(1), newsAuto.restart()));
    bindScroll(newsWrap, stepNews, newsAuto);
  }

  // 8. Parallax Fallback for Browsers without Native Scroll-Driven Animations
  if (!CSS?.supports?.('(animation-timeline: view()) and (animation-range: entry)') && hasIO) {
    const stripSpan = reducedMotion ? 44 : 50; // -22%..+22% or -25%..+25%
    const glimpseSpan = reducedMotion ? 36 : 40; // -18%..+18% or -20%..+20%
    const pxItems = [
      ...$$('.atmosphere-strip').map((wrap) => ({ wrap, layer: qs('img', wrap), span: stripSpan })),
      ...$$('.footer-glimpse').map((wrap) => ({ wrap, layer: qs('.footer-glimpse__bg', wrap), span: glimpseSpan })),
    ].filter((x) => x.layer);
    if (pxItems.length) {
      const pairMap = new Map(pxItems.map((p) => [p.wrap, p])), visible = new Set();
      let raf = false;
      const syncPx = () => {
        raf = false;
        const vh = innerHeight || 1, writes = [];
        visible.forEach((it) => { const r = it.wrap.getBoundingClientRect(); writes.push([it.layer, ((Math.max(0, Math.min(1, (vh - r.top) / (vh + r.height))) - 0.5) * it.span).toFixed(2)]); });
        writes.forEach(([l, s]) => (l.style.transform = `translate3d(0, ${s}%, 0)`));
      };
      const onScroll = () => !raf && visible.size && ((raf = true), requestAnimationFrame(syncPx));
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => { const m = pairMap.get(e.target); if (m) e.isIntersecting ? visible.add(m) : visible.delete(m); });
        visible.size ? (on(window, 'scroll', onScroll, { passive: true }), onScroll()) : removeEventListener('scroll', onScroll);
      }, { threshold: 0 });
      pxItems.forEach((it) => io.observe(it.wrap));
    }
  }

  // 9. Interactive Grayscale Homepage Map (Leaflet 1.9.4 + Multi-CDN Failover)
  const mapEl = $('irori-map'), zoomIn = $('map-zoom-in'), zoomOut = $('map-zoom-out');
  if (mapEl) {
    const clamp = (v, min, max, fb) => { const s = String(v ?? '').trim(), n = s ? Number(s) : NaN; return Number.isFinite(n) && n >= min && n <= max ? n : fb; };
    const lat = clamp(mapEl.dataset.mapLat, -90, 90, 10.78492), lng = clamp(mapEl.dataset.mapLng, -180, 180, 106.7006), zoom = clamp(mapEl.dataset.mapZoom, 13, 19, 16);
    const cdns = ['https://cdn.jsdelivr.net/npm/leaflet@1.9.4/dist/', 'https://unpkg.com/leaflet@1.9.4/dist/'];
    let mapP = null;
    const loadOnce = (tag, attrs, pre) => new Promise((res, rej) => {
      const el = Object.assign(document.createElement(tag), attrs, { crossOrigin: 'anonymous', onload: res, onerror: () => (el.remove(), rej()) });
      pre ? document.head.prepend(el) : document.head.append(el);
    });
    const loadAsset = (tag, k, f, integrity, pre) => {
      const base = { ...(tag === 'link' ? { rel: 'stylesheet' } : {}), integrity };
      return loadOnce(tag, { ...base, [k]: cdns[0] + f }, pre).catch(() => loadOnce(tag, { ...base, [k]: cdns[1] + f }, pre));
    };
    const setZoomDis = (inD, outD) => { if (zoomIn) zoomIn.disabled = inD; if (zoomOut) zoomOut.disabled = outD; };
    const initMap = () => mapP || (mapP = Promise.all([
      loadAsset('link', 'href', 'leaflet.css', 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=', true),
      window.L ? 0 : loadAsset('script', 'src', 'leaflet.js', 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo='),
    ]).then(() => {
      const L = window.L, map = L.map(mapEl, { center: [lat, lng], zoom, minZoom: 13, maxZoom: 19, zoomControl: false, scrollWheelZoom: false, touchZoom: true, dragging: true });
      let ok = 0, fb = false;
      const attachTiles = (url, opts) => {
        const layer = L.tileLayer(url, { maxZoom: 19, ...opts });
        layer.on('tileload', () => ++ok >= 4 && mapEl.classList.add('is-ready'));
        layer.on('load', () => ok && mapEl.classList.add('is-ready'));
        layer.on('tileerror', () => !ok && !fb && ((fb = true), map.removeLayer(layer), attachTiles('https://{s}.tile.openstreetmap.fr/hot/{z}/{x}/{y}.png', { subdomains: 'abc', attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a>' })));
        return layer.addTo(map);
      };
      attachTiles('https://server.arcgisonline.com/ArcGIS/rest/services/World_Street_Map/MapServer/tile/{z}/{y}/{x}', { attribution: 'Tiles &copy; Esri &mdash; Esri, HERE, OpenStreetMap' });
      L.marker([lat, lng], {
        interactive: false, keyboard: false,
        icon: L.divIcon({
          className: 'irori-pin-icon', iconSize: [28, 38], iconAnchor: [14, 38],
          html: '<div class="irori-pin"><span class="irori-pin__label">House of IRORI — Cocktail Bar &amp; Eatery</span><svg width="28" height="38" viewBox="0 0 28 38" fill="none" aria-hidden="true"><path d="M14 0a14 14 0 0 0-14 14c0 10 14 24 14 24s14-14 14-24A14 14 0 0 0 14 0Z" fill="#4A3B32"/><circle cx="14" cy="14" r="5" fill="#C6A15B"/></svg></div>',
        }),
      }).addTo(map);
      const syncBtns = () => setZoomDis(map.getZoom() >= map.getMaxZoom(), map.getZoom() <= map.getMinZoom());
      map.on('zoomend', syncBtns);
      syncBtns();
      if ('ResizeObserver' in window) new ResizeObserver(() => map.invalidateSize(false)).observe(mapEl);
      mapEl._map = map;
      return map;
    }).catch(() => (setZoomDis(true, true), null)));
    on(zoomIn, 'click', () => initMap().then((m) => m?.zoomIn()));
    on(zoomOut, 'click', () => initMap().then((m) => m?.zoomOut()));
    if (hasIO) {
      const io = new IntersectionObserver((e) => e[0]?.isIntersecting && (io.disconnect(), initMap()), { rootMargin: '800px 0px' });
      io.observe(mapEl);
    } else initMap();
  }
});
