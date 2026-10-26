/**
 * House of IRORI — Home v3B Interactive Behaviour
 * - Sticky header state transition on scroll
 * - Accessible Mobile Menu Drawer toggle
 * - Image carousels for Level One (The Alley) & Level Two (The Bar)
 * - Horizontal slider controls for Google Reviews & Latest News
 */

document.addEventListener('DOMContentLoaded', () => {
  const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

  /**
   * Reusable autoplay controller that pauses on hover, keyboard focus, and hidden tab.
   * Tones down cadence gently when prefers-reduced-motion is enabled instead of breaking autoplay.
   */
  const createAutoplay = (containerEl, stepFn, intervalMs = 4500) => {
    if (!containerEl) {
      return { restart: () => {} };
    }
    const effectiveInterval = prefersReducedMotion ? Math.round(intervalMs * 1.3) : intervalMs;
    let timerId = null;
    let isHovered = false;
    let isFocused = false;

    const stop = () => {
      if (timerId !== null) {
        clearInterval(timerId);
        timerId = null;
      }
    };

    const start = () => {
      stop();
      if (!isHovered && !isFocused && !document.hidden) {
        timerId = setInterval(stepFn, effectiveInterval);
      }
    };

    containerEl.addEventListener('mouseenter', () => {
      isHovered = true;
      stop();
    });
    containerEl.addEventListener('mouseleave', () => {
      isHovered = false;
      start();
    });
    containerEl.addEventListener('focusin', () => {
      isFocused = true;
      stop();
    });
    containerEl.addEventListener('focusout', (e) => {
      if (!containerEl.contains(e.relatedTarget)) {
        isFocused = false;
        start();
      }
    });
    document.addEventListener('visibilitychange', () => {
      if (document.hidden) {
        stop();
      } else {
        start();
      }
    });

    start();
    return { restart: start };
  };

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

    const autoplay = createAutoplay(carouselEl, () => activateSlide(currentIdx + 1), 4500);

    dots.forEach((dot, idx) => {
      dot.addEventListener('click', () => {
        activateSlide(idx);
        autoplay.restart();
      });
    });

    if (prevBtn) {
      prevBtn.addEventListener('click', () => {
        activateSlide(currentIdx - 1);
        autoplay.restart();
      });
    }
    if (nextBtn) {
      nextBtn.addEventListener('click', () => {
        activateSlide(currentIdx + 1);
        autoplay.restart();
      });
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

  // 5. Load More Pagination (Occasionals & Events)
  document.querySelectorAll('[data-load-more]').forEach((btn) => {
    if (btn.getAttribute('data-load-more') === 'blogs') return;
    btn.addEventListener('click', () => {
      const section = btn.closest('section') || document;
      section.querySelectorAll('[data-extra-item]').forEach((item) => {
        item.hidden = false;
      });
      btn.hidden = true;
    });
  });

  // 6. Blogs Page #category Tag Filter + ?category= URL Query + Load More
  const filterButtons = Array.from(document.querySelectorAll('[data-filter-category]'));
  const blogGrid = document.getElementById('blogs-grid');
  const blogsLoadMore = document.querySelector('[data-load-more="blogs"]');

  if (filterButtons.length > 0 && blogGrid) {
    const blogCards = Array.from(blogGrid.querySelectorAll('.news-card'));
    const validCategories = new Set(
      filterButtons.map((b) => b.getAttribute('data-filter-category'))
    );
    let activeCategory = null;
    let blogsExpanded = false;

    const applyBlogState = (updateUrl) => {
      filterButtons.forEach((b) => {
        const isPressed = b.getAttribute('data-filter-category') === activeCategory;
        b.classList.toggle('is-active', isPressed);
        b.setAttribute('aria-pressed', String(isPressed));
      });

      blogCards.forEach((card) => {
        const cardCat = card.getAttribute('data-category');
        const isExtra = card.hasAttribute('data-extra-item');
        card.hidden = activeCategory
          ? cardCat !== activeCategory
          : !blogsExpanded && isExtra;
      });

      if (blogsLoadMore) {
        blogsLoadMore.hidden = Boolean(activeCategory) || blogsExpanded;
      }

      if (updateUrl && window.history && window.history.replaceState) {
        const url = new URL(window.location.href);
        if (activeCategory) {
          url.searchParams.set('category', activeCategory);
        } else {
          url.searchParams.delete('category');
        }
        window.history.replaceState(null, '', url.pathname + url.search + url.hash);
      }
    };

    const initialCat = (new URLSearchParams(window.location.search).get('category') || '').toLowerCase();
    if (validCategories.has(initialCat)) {
      activeCategory = initialCat;
    }
    applyBlogState(false);

    filterButtons.forEach((btn) => {
      btn.addEventListener('click', () => {
        const clickedCat = btn.getAttribute('data-filter-category');
        activeCategory = activeCategory === clickedCat ? null : clickedCat;
        applyBlogState(true);
      });
    });

    blogGrid.addEventListener('click', (e) => {
      const catLink = e.target.closest('[data-category-link]');
      if (!catLink) return;
      const targetCat = (catLink.getAttribute('data-category-link') || '').toLowerCase();
      if (validCategories.has(targetCat)) {
        e.preventDefault();
        activeCategory = targetCat;
        applyBlogState(true);
      }
    });

    if (blogsLoadMore) {
      blogsLoadMore.addEventListener('click', () => {
        blogsExpanded = true;
        applyBlogState(false);
      });
    }
  }

  // 6. Google Reviews Slider Controls + Autoplay
  const reviewsSlider = document.getElementById('reviews-slider');
  const reviewsTrack = document.getElementById('reviews-track');
  const reviewsPrev = document.getElementById('reviews-prev');
  const reviewsNext = document.getElementById('reviews-next');

  if (reviewsTrack && reviewsPrev && reviewsNext) {
    const scrollAmount = 263; // card width (245) + gap (18)
    const scrollBehavior = prefersReducedMotion ? 'auto' : 'smooth';
    const stepReviewsNext = () => {
      const atEnd = reviewsTrack.scrollLeft + reviewsTrack.clientWidth >= reviewsTrack.scrollWidth - 16;
      if (atEnd) {
        reviewsTrack.scrollTo({ left: 0, behavior: scrollBehavior });
      } else {
        reviewsTrack.scrollBy({ left: scrollAmount, behavior: scrollBehavior });
      }
    };
    const reviewsAutoplay = createAutoplay(reviewsSlider || reviewsTrack, stepReviewsNext, 4500);

    reviewsPrev.addEventListener('click', () => {
      if (reviewsTrack.scrollLeft <= 8) {
        reviewsTrack.scrollTo({ left: reviewsTrack.scrollWidth, behavior: scrollBehavior });
      } else {
        reviewsTrack.scrollBy({ left: -scrollAmount, behavior: scrollBehavior });
      }
      reviewsAutoplay.restart();
    });
    reviewsNext.addEventListener('click', () => {
      stepReviewsNext();
      reviewsAutoplay.restart();
    });
  }

  // 7. Latest News Slider Controls + Autoplay (Desktop 3-card window + Mobile single-card)
  const newsTrack = document.getElementById('news-track');
  const newsPrev = document.getElementById('news-prev');
  const newsNext = document.getElementById('news-next');

  if (newsTrack && newsPrev && newsNext) {
    const cards = Array.from(newsTrack.querySelectorAll('.news-card'));
    const sliderWrap = newsTrack.closest('.latest-news__slider-wrap') || newsTrack;
    let activeNewsIdx = 0;
    newsTrack.classList.add('is-carousel-ready');

    const syncNewsCards = () => {
      const total = cards.length;
      if (total === 0) return;
      const visibleDesktopMap = new Map();
      const desktopCount = Math.min(3, total);
      for (let slot = 0; slot < desktopCount; slot += 1) {
        visibleDesktopMap.set((activeNewsIdx + slot) % total, slot);
      }

      cards.forEach((card, idx) => {
        card.classList.toggle('is-mobile-visible', idx === activeNewsIdx);
        const slotOrder = visibleDesktopMap.get(idx);
        const isDesktopVisible = slotOrder !== undefined;
        card.classList.toggle('is-desktop-visible', isDesktopVisible);
        if (isDesktopVisible) {
          card.style.setProperty('--slot-order', String(slotOrder));
        }
      });
    };
    syncNewsCards();

    const stepNewsNext = () => {
      activeNewsIdx = (activeNewsIdx + 1) % cards.length;
      syncNewsCards();
    };
    const newsAutoplay = createAutoplay(sliderWrap, stepNewsNext, 4500);

    newsPrev.addEventListener('click', () => {
      activeNewsIdx = (activeNewsIdx - 1 + cards.length) % cards.length;
      syncNewsCards();
      newsAutoplay.restart();
    });

    newsNext.addEventListener('click', () => {
      stepNewsNext();
      newsAutoplay.restart();
    });
  }

  // 8. Parallax Fallback for Browsers without Native Scroll-Driven Animations
  const supportsScrollDriven =
    typeof window.CSS !== 'undefined' &&
    typeof window.CSS.supports === 'function' &&
    window.CSS.supports('(animation-timeline: view()) and (animation-range: entry)');

  if (!supportsScrollDriven && 'IntersectionObserver' in window) {
    const parallaxSpan = prefersReducedMotion ? 9 : 20; // -4.5%..+4.5% or -10%..+10%
    const parallaxPairs = [
      ...Array.from(document.querySelectorAll('.atmosphere-strip')).map((wrap) => ({
        wrap,
        layer: wrap.querySelector('img'),
      })),
      ...Array.from(document.querySelectorAll('.footer-glimpse')).map((wrap) => ({
        wrap,
        layer: wrap.querySelector('.footer-glimpse__bg'),
      })),
    ].filter((item) => item.layer);

    if (parallaxPairs.length > 0) {
      const visibleSet = new Set();
      let rafPending = false;

      const updateParallax = () => {
        rafPending = false;
        const vh = window.innerHeight || 1;
        const pendingWrites = [];
        visibleSet.forEach((item) => {
          const rect = item.wrap.getBoundingClientRect();
          const progress = (vh - rect.top) / (vh + rect.height);
          const clamped = Math.max(0, Math.min(1, progress));
          const shiftPercent = (clamped - 0.5) * parallaxSpan;
          pendingWrites.push([item.layer, shiftPercent.toFixed(2)]);
        });
        pendingWrites.forEach(([layer, shift]) => {
          layer.style.transform = `translate3d(0, ${shift}%, 0)`;
        });
      };

      const onScroll = () => {
        if (!rafPending && visibleSet.size > 0) {
          rafPending = true;
          window.requestAnimationFrame(updateParallax);
        }
      };

      const observer = new IntersectionObserver(
        (entries) => {
          entries.forEach((entry) => {
            const match = parallaxPairs.find((p) => p.wrap === entry.target);
            if (!match) return;
            if (entry.isIntersecting) {
              visibleSet.add(match);
            } else {
              visibleSet.delete(match);
            }
          });
          if (visibleSet.size > 0) {
            window.addEventListener('scroll', onScroll, { passive: true });
            onScroll();
          } else {
            window.removeEventListener('scroll', onScroll);
          }
        },
        { threshold: 0 }
      );

      parallaxPairs.forEach((item) => observer.observe(item.wrap));
    }
  }
});
