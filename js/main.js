/**
 * House of IRORI — Home v3B Interactive Behaviour
 * - Sticky header state transition on scroll
 * - Accessible Mobile Menu Drawer toggle
 * - Image carousels for Level One (The Alley) & Level Two (The Bar)
 * - Horizontal slider controls for Google Reviews & Latest News
 */

document.addEventListener('DOMContentLoaded', () => {
  const $ = (id) => document.getElementById(id);
  const $$ = (sel, root = document) => [...root.querySelectorAll(sel)];
  const on = (el, ev, fn, opts) => el?.addEventListener(ev, fn, opts);
  const mq = (q) => window.matchMedia(q);
  const hasIO = 'IntersectionObserver' in window;
  const reducedMotion = mq('(prefers-reduced-motion: reduce)').matches;
  const setAttr = (el, k, v) => el.setAttribute(k, String(v));
  const setPressed = (el, onState) => {
    el.classList.toggle('is-active', onState);
    setAttr(el, 'aria-pressed', onState);
  };
  const setHidden = (el, hide) => {
    setAttr(el, 'aria-hidden', hide);
    el.inert = hide;
  };

  /**
   * Reusable autoplay controller that pauses on hover, keyboard focus, and hidden tab.
   * Tones down cadence gently when prefers-reduced-motion is enabled instead of breaking autoplay.
   */
  const createAutoplay = (box, stepFn, ms = 4500) => {
    if (!box) return { restart: () => {} };
    const delay = reducedMotion ? Math.round(ms * 1.3) : ms;
    let timer = null, hovered = false, focused = false, inView = !hasIO, warmed = false;
    const stop = () => { clearInterval(timer); timer = null; };
    const start = () => {
      stop();
      if (inView && !hovered && !focused && !document.hidden) timer = setInterval(stepFn, delay);
    };

    if (hasIO) {
      new IntersectionObserver((entries) => {
        inView = Boolean(entries[0]?.isIntersecting);
        if (inView && !warmed) {
          warmed = true;
          $$('img[loading="lazy"]', box).forEach((img) => img.removeAttribute('loading'));
        }
        inView ? start() : stop();
      }, { threshold: 0.05 }).observe(box);
    }

    on(box, 'mouseenter', () => ((hovered = true), stop()));
    on(box, 'mouseleave', () => ((hovered = false), start()));
    on(box, 'focusin', () => ((focused = true), stop()));
    on(box, 'focusout', (e) => { if (!box.contains(e.relatedTarget)) { focused = false; start(); } });
    on(document, 'visibilitychange', () => (document.hidden ? stop() : start()));
    start();
    return { restart: start };
  };

  // 1. Sticky Header Scroll State
  const header = $('site-header');
  const syncHeader = () => {
    if (header && !header.classList.contains('is-menu-open')) header.classList.toggle('is-scrolled', scrollY > 40);
  };
  on(window, 'scroll', syncHeader, { passive: true });
  syncHeader();

  // 2. Mobile Menu Drawer Toggle
  const menuBtn = $('mobile-menu-trigger');
  const drawer = $('mobile-drawer');
  if (menuBtn && drawer) {
    const bgRegions = $$('main, footer, #mobile-reservations-bar');
    const setMenu = (open, focusBtn = false) => {
      setAttr(menuBtn, 'aria-expanded', open);
      setHidden(drawer, !open);
      header?.classList.toggle('is-menu-open', open);
      document.body.classList.toggle('is-menu-open', open);
      bgRegions.forEach((el) => (el.inert = open));
      if (!open) {
        syncHeader();
        if (focusBtn) menuBtn.focus();
      }
    };
    drawer.hidden = false;
    setMenu(false);
    on(menuBtn, 'click', () => setMenu(menuBtn.getAttribute('aria-expanded') !== 'true'));
    $$('.mobile-drawer a, .header-logo', header || drawer).forEach((a) => on(a, 'click', () => setMenu(false)));
    on(document, 'keydown', (e) => {
      if (e.key === 'Escape' && menuBtn.getAttribute('aria-expanded') === 'true') setMenu(false, true);
    });
    on(mq('(min-width: 769px)'), 'change', (e) => e.matches && setMenu(false));
  }

  // 3. Multi-Carousel Horizontal Track Slider (Level One, Level Two, Events UP COMING)
  $$('[data-carousel]').forEach((el) => {
    const track = el.querySelector('.carousel__track');
    const slides = $$('.carousel__slide', el);
    const dots = $$('.carousel__dot', el);
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
    on(el.querySelector('[data-carousel-prev]'), 'click', () => (go(cur - 1), auto.restart()));
    on(el.querySelector('[data-carousel-next]'), 'click', () => (go(cur + 1), auto.restart()));
  });

  // 4. Menu Page Category Tabs (Cocktail vs Food)
  const menuTabs = $$('[data-menu-tab]');
  const menuPanels = $$('[data-menu-panel]');
  if (menuTabs.length && menuPanels.length) {
    menuTabs.forEach((tab) => on(tab, 'click', () => {
      const key = tab.dataset.menuTab;
      menuTabs.forEach((t) => setPressed(t, t === tab));
      menuPanels.forEach((p) => { const ok = p.dataset.menuPanel === key; p.classList.toggle('is-active', ok); p.hidden = !ok; });
    }));
  }

  // 5. Load More Pagination (Occasionals & Events)
  $$('[data-load-more]').forEach((btn) => {
    if (btn.dataset.loadMore === 'blogs') return;
    on(btn, 'click', () => {
      $$('[data-extra-item]', btn.closest('section') || document).forEach((el) => (el.hidden = false));
      btn.hidden = true;
    });
  });

  // 6. Blogs Page #category Tag Filter + ?category= URL Query + Load More
  const filterBtns = $$('[data-filter-category]');
  const blogGrid = $('blogs-grid');
  const blogMore = document.querySelector('[data-load-more="blogs"]');
  if (filterBtns.length && blogGrid) {
    const cards = $$('.news-card', blogGrid);
    const valid = new Set(filterBtns.map((b) => b.dataset.filterCategory));
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
    const nextRev = () => {
      const end = revTrack.scrollLeft + revTrack.clientWidth >= revTrack.scrollWidth - 16;
      revTrack.scrollTo({ left: end ? 0 : revTrack.scrollLeft + stepPx, behavior });
    };
    const revAuto = createAutoplay($('reviews-slider') || revTrack, nextRev);
    on(revPrev, 'click', () => {
      revTrack.scrollTo({ left: revTrack.scrollLeft <= 8 ? revTrack.scrollWidth : revTrack.scrollLeft - stepPx, behavior });
      revAuto.restart();
    });
    on(revNext, 'click', () => (nextRev(), revAuto.restart()));
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
    const nextNews = () => { idx = idx >= maxIdx() ? 0 : idx + 1; syncNews(); };
    const newsAuto = createAutoplay(newsTrack.closest('.latest-news__slider-wrap') || newsTrack, nextNews);
    on(newsPrev, 'click', () => { idx = idx <= 0 ? maxIdx() : idx - 1; syncNews(); newsAuto.restart(); });
    on(newsNext, 'click', () => (nextNews(), newsAuto.restart()));
  }

  // 8. Parallax Fallback for Browsers without Native Scroll-Driven Animations
  if (!window.CSS?.supports?.('(animation-timeline: view()) and (animation-range: entry)') && hasIO) {
    const stripSpan = reducedMotion ? 44 : 50; // -22%..+22% or -25%..+25%
    const glimpseSpan = reducedMotion ? 36 : 40; // -18%..+18% or -20%..+20%
    const pxItems = [
      ...$$('.atmosphere-strip').map((wrap) => ({ wrap, layer: wrap.querySelector('img'), span: stripSpan })),
      ...$$('.footer-glimpse').map((wrap) => ({ wrap, layer: wrap.querySelector('.footer-glimpse__bg'), span: glimpseSpan })),
    ].filter((x) => x.layer);

    if (pxItems.length) {
      const pairMap = new Map(pxItems.map((p) => [p.wrap, p]));
      const visible = new Set();
      let raf = false;
      const syncPx = () => {
        raf = false;
        const vh = innerHeight || 1;
        const writes = [];
        visible.forEach((it) => {
          const r = it.wrap.getBoundingClientRect();
          const t = Math.max(0, Math.min(1, (vh - r.top) / (vh + r.height)));
          writes.push([it.layer, ((t - 0.5) * it.span).toFixed(2)]);
        });
        writes.forEach(([l, s]) => (l.style.transform = `translate3d(0, ${s}%, 0)`));
      };
      const onScroll = () => { if (!raf && visible.size) { raf = true; requestAnimationFrame(syncPx); } };
      const io = new IntersectionObserver((entries) => {
        entries.forEach((e) => {
          const m = pairMap.get(e.target);
          if (m) e.isIntersecting ? visible.add(m) : visible.delete(m);
        });
        visible.size ? (on(window, 'scroll', onScroll, { passive: true }), onScroll()) : removeEventListener('scroll', onScroll);
      }, { threshold: 0 });
      pxItems.forEach((it) => io.observe(it.wrap));
    }
  }

  // 9. Interactive Grayscale Homepage Map (Leaflet 1.9.4 + OpenStreetMap)
  const mapEl = $('irori-map'), zoomIn = $('map-zoom-in'), zoomOut = $('map-zoom-out');
  if (mapEl) {
    const clampNum = (v, min, max, fb) => {
      const s = String(v ?? '').trim();
      const n = s ? Number(s) : NaN;
      return Number.isFinite(n) && n >= min && n <= max ? n : fb;
    };
    const lat = clampNum(mapEl.dataset.mapLat, -90, 90, 10.78492);
    const lng = clampNum(mapEl.dataset.mapLng, -180, 180, 106.7006);
    const zoom = clampNum(mapEl.dataset.mapZoom, 13, 19, 16);
    const cdn = 'https://unpkg.com/leaflet@1.9.4/dist/';
    let mapP = null;

    const loadTag = (tag, attrs, pre = false) => new Promise((res, rej) => {
      const el = Object.assign(document.createElement(tag), attrs, { crossOrigin: 'anonymous', onload: res, onerror: rej });
      pre ? document.head.prepend(el) : document.head.append(el);
    });

    const initMap = () => mapP || (mapP = Promise.all([
      loadTag('link', { rel: 'stylesheet', href: `${cdn}leaflet.css`, integrity: 'sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=' }, true),
      window.L ? 0 : loadTag('script', { src: `${cdn}leaflet.js`, integrity: 'sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=' }),
    ]).then(() => {
      const L = window.L;
      const map = L.map(mapEl, {
        center: [lat, lng], zoom, minZoom: 13, maxZoom: 19, zoomControl: false,
        scrollWheelZoom: false, touchZoom: true, dragging: true,
      });
      const tiles = L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright" target="_blank" rel="noopener noreferrer">OpenStreetMap</a> contributors',
      });
      let tileOk = false;
      tiles.once('tileload', () => (tileOk = true));
      tiles.on('load', () => tileOk && mapEl.classList.add('is-ready'));
      tiles.addTo(map);
      L.marker([lat, lng], {
        interactive: false, keyboard: false,
        icon: L.divIcon({
          className: 'irori-pin-icon', iconSize: [28, 38], iconAnchor: [14, 38],
          html: '<div class="irori-pin"><span class="irori-pin__label">House of IRORI — Cocktail Bar &amp; Eatery</span><svg width="28" height="38" viewBox="0 0 28 38" fill="none" aria-hidden="true"><path d="M14 0a14 14 0 0 0-14 14c0 10 14 24 14 24s14-14 14-24A14 14 0 0 0 14 0Z" fill="#4A3B32"/><circle cx="14" cy="14" r="5" fill="#C6A15B"/></svg></div>',
        }),
      }).addTo(map);
      const syncBtns = () => {
        if (zoomIn) zoomIn.disabled = map.getZoom() >= map.getMaxZoom();
        if (zoomOut) zoomOut.disabled = map.getZoom() <= map.getMinZoom();
      };
      map.on('zoomend', syncBtns);
      syncBtns();
      if ('ResizeObserver' in window) new ResizeObserver(() => map.invalidateSize(false)).observe(mapEl);
      mapEl._map = map;
      return map;
    }).catch(() => {
      if (zoomIn) zoomIn.disabled = true;
      if (zoomOut) zoomOut.disabled = true;
      return null;
    }));

    on(zoomIn, 'click', () => initMap().then((m) => m?.zoomIn()));
    on(zoomOut, 'click', () => initMap().then((m) => m?.zoomOut()));
    if (hasIO) {
      const io = new IntersectionObserver((e) => { if (e[0]?.isIntersecting) { io.disconnect(); initMap(); } }, { rootMargin: '800px 0px' });
      io.observe(mapEl);
    } else initMap();
  }
});
