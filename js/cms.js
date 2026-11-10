/**
 * House of IRORI — Headless WordPress CMS Client (js/cms.js)
 * Hydrates all 5 Figma static pages from /cms/wp-json/irori/v1/* by cloning existing semantic
 * DOM templates (zero innerHTML sinks, Element.prototype.setHTML + allowlist DOMParser fallback).
 */
(() => {
const q = (s, r = document) => r.querySelector(s), qq = (s, r = document) => [...r.querySelectorAll(s)];
const meta = q('meta[name="irori-cms-api"]')?.content?.trim();
if (!meta && location.hostname.endsWith('github.io')) return;
const api = (meta || 'cms/wp-json').replace(/\/+$/, ''), H = { Accept: 'application/json' };
const safeUrl = (u, h) => {
  const s = String(u ?? '').trim();
  if (!s) return '';
  if ((h && s[0] === '#') || /^(assets\/|(blogs?(-detail)?|index|menu|events)\.html)/i.test(s)) return s;
  try { const p = new URL(s, location.href); return /^(https?|mailto|tel):$/.test(p.protocol) ? p.href : ''; } catch { return ''; }
};
const txt = (s, v, r = document) => { const e = q(s, r); if (e && v != null) e.textContent = v; };
const mk = (t, c, tx) => { const e = document.createElement(t); if (c) e.className = c; if (tx) e.textContent = tx; return e; };
const setImg = (e, u, f, a) => e && ((e.src = safeUrl(u) || `assets/images/${f}`), (e.alt = a || ''));
const setTm = (s, l, i, r) => { const t = q(s, r); if (t) { t.textContent = l || ''; t.setAttribute('datetime', i || ''); } };
const getJ = (p, hi) => fetch(`${api}${p}`, hi ? { headers: H } : { priority: 'low', headers: H }).then((r) => (r.ok ? r.json() : 0));

// Safe Rich HTML renderer (Sanitizer API + allowlist DOMParser fallback, zero innerHTML)
const TAGS = new Set('p,h2,h3,h4,div,span,strong,em,b,i,ul,ol,li,blockquote,figure,figcaption,img,a,br'.split(',')), ATTRS = 'href,src,alt,class,loading,width,height'.split(',');
const scrub = (r) => {
  qq('a[href]', r).forEach((a) => { const u = safeUrl(a.getAttribute('href'), 1); u ? (a.href = u) : a.removeAttribute('href'); if (/^https?:/i.test(u)) a.rel = 'noopener noreferrer'; });
  qq('img[src]', r).forEach((i) => { const u = safeUrl(i.getAttribute('src')); if (u) { i.src = u; i.loading ||= 'lazy'; } else i.remove(); });
};
const renderRich = (el, raw) => {
  const s = String(raw ?? '');
  if ('setHTML' in Element.prototype) { try { el.setHTML(s, { sanitizer: { elements: [...TAGS], attributes: ATTRS } }); return scrub(el); } catch {} }
  const cln = (n) => {
    if (n.nodeType === 3) return document.createTextNode(n.textContent || '');
    const t = n.nodeType === 1 && n.tagName.toLowerCase();
    if (!TAGS.has(t)) return null;
    const o = mk(t);
    for (const k of ATTRS) if (n.hasAttribute(k)) o.setAttribute(k, n.getAttribute(k) || '');
    for (const c of n.childNodes) { const x = cln(c); if (x) o.appendChild(x); }
    return o;
  };
  const f = document.createDocumentFragment();
  for (const c of new DOMParser().parseFromString(s, 'text/html').body.childNodes) { const x = cln(c); if (x) f.appendChild(x); }
  scrub(f); el.replaceChildren(f);
};

const fillTpl = (bS, iS, arr, fn, more, lim = 999) => {
  const b = q(bS), t = b && q(iS, b);
  if (!b || !t || !arr?.length) return;
  const bs = t.cloneNode(true); bs.removeAttribute('data-extra-item'); bs.hidden = false;
  b.replaceChildren(...arr.map((x, i) => { const n = bs.cloneNode(true); if (i >= lim) { n.setAttribute('data-extra-item', ''); n.hidden = true; } fn(n, x, i); return n; }));
  const btn = more && q(`[data-load-more="${more}"]`); if (btn) btn.hidden = arr.length <= lim;
};
const OC = '.occasional-card__', NC = '.news-card__', MB = '.menu-book-', RC = '.review-card__', BA = '.blog-article__';
const bindOcc = (c, x) => { setImg(q('img', c), x.image, 'event-wild-turkey.jpg', x.alt || x.title); txt(`${OC}title`, x.title || '', c); setTm(`${OC}date`, x.dateLabel, x.dateIso, c); txt(`${OC}desc`, x.desc || '', c); };
const bindArt = (c, a, flt) => {
  const u = `blog-detail.html?slug=${encodeURIComponent(a.slug || '')}`, cat = String(a.category || 'interior').toLowerCase(), p = q('.category-pill--card', c);
  if (flt) c.dataset.category = cat;
  setImg(q('img', c), a.image, 'blog-thumb-1.jpg', a.alt || a.title);
  qq(`${NC}media, ${NC}title a, ${NC}readmore`, c).forEach((l) => (l.href = u));
  txt(`${NC}title a`, a.title || '', c); txt(`${NC}excerpt`, a.excerpt || '', c); setTm(`${NC}date`, a.dateLabel, a.dateIso, c);
  if (p) { p.href = `blogs.html?category=${encodeURIComponent(cat)}`; p.textContent = a.categoryLabel || cat; if (flt) p.dataset.categoryLink = cat; }
};

const slug = new URLSearchParams(location.search).get('slug') || '', dtE = q('[data-cms-item="article-detail"]');
getJ('/irori/v1/bootstrap').then((d) => {
  if (!d) return;
  const s = d.settings, mb = d.menuBook, cur = slug || (dtE ? 'elle-decoration-popup-office' : ''), UP = '[data-carousel="upcoming"] ';
  if (s) {
    const ct = (k, u, t1, t2) => {
      qq(`.header-topbar__contact a[href^="${k}:"]`).forEach((a) => ((a.href = u), (a.textContent = t1)));
      qq(`.mobile-drawer__contact a[href^="${k}:"], .footer-col a[href^="${k}:"]`).forEach((a) => ((a.href = u), (a.textContent = t2)));
    };
    if (s.email) ct('mailto', `mailto:${s.email}`, s.email.toUpperCase(), s.email);
    if (s.phone_display && s.phone_tel) ct('tel', `tel:${s.phone_tel.replace(/\s+/g, '')}`, `TEL: ${s.phone_display}`, s.phone_display);
    const ru = safeUrl(s.reservations_url, 1), gU = safeUrl(s.google_maps_url), mE = q('#irori-map'), mL = q('.map-external-link');
    if (ru) qq('.btn-reservations, .hero__mobile-reservations-btn').forEach((a) => (a.href = ru));
    if (s.address_line1) qq('address.footer-col__text').forEach((ad) => ad.replaceChildren(s.address_line1, mk('br'), s.address_line2 || ''));
    txt('.reviews-header__score', s.rating_score);
    for (const [k, u] of Object.entries({ Facebook: s.facebook_url, Instagram: s.instagram_url, TikTok: s.tiktok_url, Threads: s.threads_url })) { const cu = safeUrl(u); if (cu) qq(`.footer-socials__link[aria-label="${k}"]`).forEach((a) => (a.href = cu)); }
    if (mL && gU) mL.href = gU;
    if (mE && s.map_lat && s.map_lng) {
      Object.assign(mE.dataset, { mapLat: s.map_lat, mapLng: s.map_lng, ...(s.map_zoom ? { mapZoom: s.map_zoom } : {}) });
      const pt = [+s.map_lat, +s.map_lng];
      if (mE._map && Number.isFinite(pt[0]) && Number.isFinite(pt[1])) { mE._map.setView(pt, +(s.map_zoom || 16)); mE._pin?.setLatLng(pt); }
    }
  }
  if (mb) {
    setImg(q('[data-menu-panel="cocktail"] img'), mb.cocktail_image, 'menu-showcase-1.jpg', mb.cocktail_alt || 'Cocktail Menu');
    const pgs = qq(`[data-menu-panel="food"] ${MB}page`);
    const fP = (pg, eb, hd, sb, xs, nt) => {
      const l = pg && q(`${MB}list`, pg), tp = l && q(`${MB}item`, l);
      if (!tp || !xs?.length) return;
      txt(`${MB}page__eyebrow`, eb, pg); txt(`${MB}page__heading`, hd, pg); txt(`${MB}page__sub`, sb, pg); if (nt) txt(`${MB}page__note`, nt, pg);
      l.replaceChildren(...xs.map((x) => { const li = tp.cloneNode(true); txt(`${MB}item__name`, x.name || '', li); txt(`${MB}item__price`, x.price || '', li); txt(`${MB}item__desc`, x.desc || '', li); return li; }));
    };
    fP(pgs[0], mb.left_eyebrow, mb.left_heading, mb.left_sub, mb.left_items);
    fP(pgs[1], mb.right_eyebrow, mb.right_heading, mb.right_sub, mb.right_items, mb.right_note);
  }
  fillTpl('#reviews-track', '.review-card', d.reviews, (c, r) => {
    txt(`${RC}text`, r.text || '', c); txt(`${RC}avatar`, r.initial || 'G', c); txt(`${RC}name`, r.author || '', c);
    txt(`${RC}stars`, '★'.repeat(Math.max(1, Math.min(5, +r.rating || 5))), c); setTm(`${RC}time`, r.timeLabel, r.dateIso, c);
  });
  fillTpl('[data-cms-collection="occasional-menus"]', '.occasional-card', d.occasionalMenus, bindOcc, 'occasional-menu', 2);
  fillTpl('[data-cms-collection="events"]', '.occasional-card', d.events, bindOcc, 'occasional-events', 4);
  if (d.upcomingSlides?.length) {
    fillTpl(`${UP}.carousel__track`, '.carousel__slide', d.upcomingSlides, (dv, sl, i) => {
      const im = q('img', dv); dv.className = `carousel__slide${i ? '' : ' is-active'}`; dv.dataset.slideIndex = i; dv.setAttribute('aria-hidden', !!i); dv.inert = !!i;
      if (im && i) { im.loading = 'lazy'; im.fetchPriority = 'low'; } setImg(im, sl.image, 'event-upcoming-featured.jpg', sl.alt || sl.title);
    });
    fillTpl(`${UP}.upcoming-carousel__dots`, '.carousel__dot', d.upcomingSlides, (b, _, i) => {
      b.className = `carousel__dot${i ? '' : ' is-active'}`; b.setAttribute('aria-pressed', !i); b.setAttribute('aria-label', `Upcoming slide ${i + 1}`); b.dataset.slideTo = i;
    });
  }
  if (d.articles?.length) {
    const lst = cur && d.articles.length > 3 ? d.articles.filter((a) => a.slug !== cur) : d.articles;
    fillTpl('#news-track', '.news-card', lst.slice(0, 9), (c, a) => bindArt(c, a, 0));
    fillTpl('#blogs-grid', '.news-card', d.articles, (c, a) => bindArt(c, a, 1), 'blogs', 6);
  }
  document.dispatchEvent(new CustomEvent('irori:cms'));
}).catch(() => {});

if (dtE && slug) {
  getJ(`/irori/v1/article/${encodeURIComponent(slug)}`, 1).then((a) => {
    if (!a) return;
    const bd = q(`${BA}body`, dtE), tg = q(`${BA}tags`, dtE), fg = q(`${BA}figure`, bd), pr = mk('div', 'blog-article__prose');
    renderRich(pr, a.contentHtml || '');
    if (a.title) document.title = `${a.title} — House of IRORI`;
    txt(`${BA}title`, a.title, dtE); setTm(`${BA}date`, a.dateDetailLabel || a.dateLabel, a.dateIso, dtE);
    if (bd && fg) {
      const im = q('img', fg), nu = safeUrl(a.image) || 'assets/images/blog-thumb-1.jpg';
      if (im) { if (new URL(nu, location.href).href !== im.src) im.src = nu; im.alt = a.alt || a.title || ''; }
      if (tg && a.categories?.length) q('.category-filter__pills', tg)?.replaceChildren(...a.categories.map((c) => Object.assign(mk('a', 'category-pill', c.name), { href: `blogs.html?category=${encodeURIComponent(c.slug)}` })));
      bd.replaceChildren(...[fg, a.subheading ? mk('h2', 'blog-article__subheading', a.subheading) : null, pr, tg].filter(Boolean));
    }
  }).catch(() => {});
}
})();
