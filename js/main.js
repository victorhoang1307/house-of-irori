/**
 * House of IRORI — Home v3B Interactive Behaviour
 * - Sticky header state transition on scroll
 * - Accessible Mobile Menu Drawer toggle
 * - Image carousels for Level One (The Alley) & Level Two (The Bar)
 * - Horizontal slider controls for Google Reviews & Latest News
 */

document.addEventListener('DOMContentLoaded', () => {
  // 1. Sticky Header Scroll State
  const header = document.getElementById('site-header');
  const syncHeaderScroll = () => {
    if (!header) return;
    if (window.scrollY > 60) {
      header.classList.add('is-scrolled');
    } else {
      header.classList.remove('is-scrolled');
    }
  };
  window.addEventListener('scroll', syncHeaderScroll, { passive: true });
  syncHeaderScroll();

  // 2. Mobile Menu Drawer Toggle
  const mobileTrigger = document.getElementById('mobile-menu-trigger');
  const mobileDrawer = document.getElementById('mobile-drawer');

  if (mobileTrigger && mobileDrawer) {
    mobileTrigger.addEventListener('click', () => {
      const isExpanded = mobileTrigger.getAttribute('aria-expanded') === 'true';
      mobileTrigger.setAttribute('aria-expanded', String(!isExpanded));
      mobileDrawer.hidden = isExpanded;
    });

    mobileDrawer.querySelectorAll('a').forEach((link) => {
      link.addEventListener('click', () => {
        mobileTrigger.setAttribute('aria-expanded', 'false');
        mobileDrawer.hidden = true;
      });
    });
  }

  // 3. Multi-Carousel Controller (Level One, Level Two, and Events UP COMING)
  document.querySelectorAll('[data-carousel]').forEach((carouselEl) => {
    const slides = Array.from(carouselEl.querySelectorAll('.carousel__slide'));
    const dots = Array.from(carouselEl.querySelectorAll('.carousel__dot'));
    const prevBtn = carouselEl.querySelector('[data-carousel-prev]');
    const nextBtn = carouselEl.querySelector('[data-carousel-next]');
    let currentIdx = 0;

    const activateSlide = (targetIndex) => {
      currentIdx = (targetIndex + slides.length) % slides.length;
      slides.forEach((slide, idx) => {
        const active = idx === currentIdx;
        slide.classList.toggle('is-active', active);
        slide.hidden = !active;
      });
      dots.forEach((dot, idx) => {
        const active = idx === currentIdx;
        dot.classList.toggle('is-active', active);
        dot.setAttribute('aria-pressed', String(active));
      });
    };

    dots.forEach((dot, idx) => {
      dot.addEventListener('click', () => activateSlide(idx));
    });

    if (prevBtn) {
      prevBtn.addEventListener('click', () => activateSlide(currentIdx - 1));
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', () => activateSlide(currentIdx + 1));
    }
  });

  // 4. Menu Page Category Tabs (Cocktail vs Food)
  const menuTabs = Array.from(document.querySelectorAll('[data-menu-tab]'));
  const menuPanels = Array.from(document.querySelectorAll('[data-menu-panel]'));

  if (menuTabs.length > 0 && menuPanels.length > 0) {
    menuTabs.forEach((tab) => {
      tab.addEventListener('click', () => {
        const targetKey = tab.getAttribute('data-menu-tab');
        menuTabs.forEach((t) => {
          const active = t === tab;
          t.classList.toggle('is-active', active);
          t.setAttribute('aria-pressed', String(active));
        });
        menuPanels.forEach((panel) => {
          const active = panel.getAttribute('data-menu-panel') === targetKey;
          panel.classList.toggle('is-active', active);
          panel.hidden = !active;
        });
      });
    });
  }

  // 5. Blogs Page #category Tag Filter
  const filterButtons = Array.from(document.querySelectorAll('[data-filter-category]'));
  const blogGrid = document.getElementById('blogs-grid');

  if (filterButtons.length > 0 && blogGrid) {
    const blogCards = Array.from(blogGrid.querySelectorAll('.news-card'));
    let activeCategory = null;

    filterButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const clickedCat = btn.getAttribute('data-filter-category');
        // Clicking the already active category resets to show all articles
        activeCategory = activeCategory === clickedCat ? null : clickedCat;

        filterButtons.forEach((b) => {
          const isPressed = b.getAttribute('data-filter-category') === activeCategory;
          b.classList.toggle('is-active', isPressed);
          b.setAttribute('aria-pressed', String(isPressed));
        });

        blogCards.forEach((card) => {
          const cardCat = card.getAttribute('data-category');
          card.hidden = Boolean(activeCategory && cardCat !== activeCategory);
        });
      });
    });
  }

  // 4. Google Reviews Slider Controls
  const reviewsTrack = document.getElementById('reviews-track');
  const reviewsPrev = document.getElementById('reviews-prev');
  const reviewsNext = document.getElementById('reviews-next');

  if (reviewsTrack && reviewsPrev && reviewsNext) {
    const scrollAmount = 263; // card width (245) + gap (18)
    reviewsPrev.addEventListener('click', () => {
      reviewsTrack.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
    });
    reviewsNext.addEventListener('click', () => {
      reviewsTrack.scrollBy({ left: scrollAmount, behavior: 'smooth' });
    });
  }

  // 5. Latest News Slider Controls (Mobile single-card cycling + Desktop highlight)
  const newsTrack = document.getElementById('news-track');
  const newsPrev = document.getElementById('news-prev');
  const newsNext = document.getElementById('news-next');

  if (newsTrack && newsPrev && newsNext) {
    const cards = Array.from(newsTrack.querySelectorAll('.news-card'));
    let activeNewsIdx = 0;

    const syncNewsCards = () => {
      cards.forEach((card, idx) => {
        card.classList.toggle('is-mobile-visible', idx === activeNewsIdx);
      });
    };
    syncNewsCards();

    newsPrev.addEventListener('click', () => {
      activeNewsIdx = (activeNewsIdx - 1 + cards.length) % cards.length;
      syncNewsCards();
    });

    newsNext.addEventListener('click', () => {
      activeNewsIdx = (activeNewsIdx + 1) % cards.length;
      syncNewsCards();
    });
  }
});
