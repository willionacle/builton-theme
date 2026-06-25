/**
 * Built-On theme entry. Sass and GSAP.
 */
import './scss/main.scss';
import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { ScrollSmoother } from 'gsap/ScrollSmoother';
import './contact.js';
import './contact-form.js';
import './team.js';

gsap.registerPlugin(ScrollTrigger, ScrollSmoother);

// GSAP available globally for Twig/HTML use, or use modules as needed
window.gsap = gsap;

document.addEventListener('DOMContentLoaded', () => {
  // Hero meta: live clocks per IANA timezone (Customizer / ACF-driven markup)
  const heroClockFormatters = new Map();
  const getHeroClockFormatter = (timeZone) => {
    if (heroClockFormatters.has(timeZone)) {
      return heroClockFormatters.get(timeZone);
    }
    try {
      const formatter = new Intl.DateTimeFormat(undefined, {
        timeZone,
        hour: 'numeric',
        minute: '2-digit',
        hour12: true,
      });
      heroClockFormatters.set(timeZone, formatter);
      return formatter;
    } catch {
      return null;
    }
  };

  document.querySelectorAll('[data-hero-clock][data-timezone]').forEach((el) => {
    const tz = el.getAttribute('data-timezone');
    if (!tz) return;
    const formatter = getHeroClockFormatter(tz);
    if (!formatter) return;
    const tick = () => {
      el.textContent = formatter.format(new Date());
    };
    tick();
    setInterval(tick, 60000);
  });

  // ScrollSmoother: create before any ScrollTrigger-based animations (uses #smooth-wrapper / #smooth-content from base.twig)
  const smoother = ScrollSmoother.create({
    smooth: 1,
    effects: true,
    smoothTouch: 0.1,
  });

  // ScrollSmoother transforms #smooth-content instead of using native scrolling, so it swallows
  // the browser's default hash-anchor jump (e.g. footer "Portfolio" links to
  // /projects/#project-N-title) — drive it through the smoother instead, both for the initial
  // page load and for same-page anchor clicks.
  const scrollSmootherToHash = (hash) => {
    const target = hash && document.querySelector(hash);
    if (!target) return;
    // .site-header is position: fixed, so it overlaps the top of the viewport — pull the
    // scroll position up by its rendered height so the target isn't tucked underneath it.
    const headerOffset = document.querySelector('.site-header')?.offsetHeight || 0;
    smoother.scrollTo(smoother.offset(target, 'top') - headerOffset, true);
  };

  if (window.location.hash) {
    window.addEventListener('load', () => {
      ScrollTrigger.refresh();
      scrollSmootherToHash(window.location.hash);
    });
  }

  document.addEventListener('click', (event) => {
    const link = event.target.closest('a[href*="#"]');
    if (!link) return;
    const url = new URL(link.href, window.location.href);
    if (url.pathname !== window.location.pathname || !url.hash) return;
    event.preventDefault();
    scrollSmootherToHash(url.hash);
  });

  const demo = document.querySelector('[data-gsap-demo]');
  if (demo) {
    gsap.from(demo, { opacity: 0, y: 20, duration: 0.6 });
  }

  // What we do: parallax banner (scrubbed; respects reduced motion)
  const wwdParallaxReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-wwd-parallax]').forEach((wrap) => {
    const inner = wrap.querySelector('[data-wwd-parallax-inner]');
    if (!inner || wwdParallaxReduceMotion) return;
    gsap.fromTo(
      inner,
      { yPercent: 10 },
      {
        yPercent: -10,
        ease: 'none',
        scrollTrigger: {
          trigger: wrap,
          start: 'top bottom',
          end: 'bottom top',
          scrub: true,
        },
      },
    );
  });

  // Header: add subtle blur background when scrolled
  const siteHeader = document.querySelector('.site-header--overlay');
  if (siteHeader) {
    const scrollThreshold = 20;
    const onScroll = () => {
      siteHeader.classList.toggle('site-header--scrolled', window.scrollY > scrollThreshold);
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  // Projects page sub-nav: scroll-spy underline via ScrollTrigger (one trigger per project
  // section); does not touch the delegated anchor-click handler above -- clicking a link still
  // smooth-scrolls via scrollSmootherToHash, this just toggles which link is marked .is-active as
  // sections pass by.
  const projectSubnav = document.querySelector('[data-project-subnav]');
  if (projectSubnav) {
    const subnavLinks = projectSubnav.querySelectorAll('[data-project-subnav-link]');
    const setActiveLink = (link) => {
      subnavLinks.forEach((l) => l.classList.toggle('is-active', l === link));
      link?.scrollIntoView({ block: 'nearest', inline: 'nearest', behavior: 'smooth' });
    };

    subnavLinks.forEach((link) => {
      const hash = new URL(link.href, window.location.href).hash;
      const heading = hash ? document.querySelector(hash) : null;
      const section = heading ? heading.closest('.single-project') : null;
      if (!section) return;

      ScrollTrigger.create({
        trigger: section,
        start: 'top center',
        end: 'bottom center',
        onEnter: () => setActiveLink(link),
        onEnterBack: () => setActiveLink(link),
      });
    });
  }

  // Mobile hamburger: animate to X and open/close full-screen overlay
  const hamburger = document.querySelector('.site-header__hamburger');
  const overlay = document.querySelector('.site-header__overlay');
  if (hamburger && overlay) {
  const line1 = hamburger.querySelector('.site-header__hamburger-line--1');
  const line2 = hamburger.querySelector('.site-header__hamburger-line--2');
  const line3 = hamburger.querySelector('.site-header__hamburger-line--3');
  const overlayMenu = overlay.querySelector('.site-header__overlay-menu');
  const overlayLinks = overlayMenu ? overlayMenu.querySelectorAll('a') : [];
  const overlayClose = overlay.querySelector('.site-header__overlay-close');

  let isOpen = false;

  const toX = () => {
    gsap.timeline()
      .to(line1, { y: 7, rotation: 45, duration: 0.25, ease: 'power2.inOut' }, 0)
      .to(line2, { opacity: 0, duration: 0.2 }, 0)
      .to(line3, { y: -7, rotation: -45, duration: 0.25, ease: 'power2.inOut' }, 0);
  };

  const toHamburger = () => {
    gsap.timeline()
      .to(line1, { y: 0, rotation: 0, duration: 0.25, ease: 'power2.inOut' }, 0)
      .to(line2, { opacity: 1, duration: 0.2 }, 0)
      .to(line3, { y: 0, rotation: 0, duration: 0.25, ease: 'power2.inOut' }, 0);
  };

  const openOverlay = () => {
    overlay.classList.add('is-open');
    gsap.fromTo(overlay, { opacity: 0 }, { opacity: 1, duration: 0.3, ease: 'power2.out' });
    gsap.fromTo(overlayLinks, { opacity: 0, y: 12 }, { opacity: 1, y: 0, duration: 0.35, stagger: 0.05, delay: 0.1, ease: 'power2.out' });
    document.body.style.overflow = 'hidden';
  };

  const closeOverlay = () => {
    gsap.to(overlay, { opacity: 0, duration: 0.25, ease: 'power2.in', onComplete: () => {
      overlay.classList.remove('is-open');
      document.body.style.overflow = '';
    } });
  };

  hamburger.addEventListener('click', () => {
    isOpen = !isOpen;
    hamburger.setAttribute('aria-expanded', isOpen);
    hamburger.setAttribute('aria-label', isOpen ? 'Close menu' : 'Open menu');
    overlay.setAttribute('aria-hidden', !isOpen);

    if (isOpen) {
      toX();
      openOverlay();
    } else {
      toHamburger();
      closeOverlay();
    }
  });

  const closeMenu = () => {
    if (!isOpen) return;
    isOpen = false;
    hamburger.setAttribute('aria-expanded', 'false');
    hamburger.setAttribute('aria-label', 'Open menu');
    overlay.setAttribute('aria-hidden', 'true');
    toHamburger();
    closeOverlay();
  };

  overlayLinks.forEach((link) => link.addEventListener('click', closeMenu));

  if (overlayClose) overlayClose.addEventListener('click', closeMenu);

  overlay.addEventListener('click', (e) => {
    if (e.target === overlay) closeMenu();
  });
  }

  // Hero entrance: reference-style word-by-word fade in + smooth zoom; whole heading drifts up; then italic, then header/bottom/subheading
  const hero = document.querySelector('.hero');
  if (hero) {
    const heroHeadline = hero.querySelector('.hero__headline');
    const headlineLines = hero.querySelectorAll('.hero__headline-line');
    const accentWordEls = hero.querySelectorAll('.hero__headline-slant .hero__headline-word');
    const subheading = hero.querySelector('.hero__subheading');
    const heroBottom = hero.querySelector('.hero__bottom');
    const marqueeImages = hero.querySelectorAll('.hero__marquee-image');
    if (headlineLines.length && accentWordEls.length >= 2 && subheading && heroBottom) {
      const tl = gsap.timeline({ defaults: { ease: 'power2.out' } });
      // Step 1: plain blank subtle yellow (hold)
      tl.to({}, { duration: 0.6 });
      // Step 2: heading drift + per-line; softer eases so same speed feels smoother/slower
      const driftTotal = 0.68;
      tl.fromTo(heroHeadline, { y: '4vh' }, { y: '3.2vh', duration: driftTotal * 0.25, ease: 'power1.in' }, 0.6);
      tl.fromTo(heroHeadline, { y: '3.2vh' }, { y: 0, duration: driftTotal * 0.75, ease: 'power1.inOut' }, 0.6 + driftTotal * 0.25);
      tl.to(headlineLines, { opacity: 1, y: 0, scale: 1, duration: 0.44, stagger: 0.1, ease: 'power1.inOut' }, 0.6);
      // Step 3: italic via skew
      tl.to(accentWordEls[0], { skewX: -14, duration: 0.32, ease: 'power1.inOut' }, '+=0.18');
      tl.to(accentWordEls[1], { skewX: -14, duration: 0.32, ease: 'power1.inOut' }, '+=0.15');
      // Step 4: header slides down; bottom slides up from clip (same pattern as headline-reveal: yPercent only, no opacity); subheading fades
      if (siteHeader) {
        tl.set(siteHeader, { opacity: 1 }, '+=0.12');
        tl.fromTo(siteHeader, { y: '-3rem' }, { y: 0, duration: 1.25, ease: 'power2.out' }, '<');
      }
      tl.to(hero, { backgroundColor: '#fff', duration: 1.7, ease: 'sine.inOut' }, '<');
      // Hero bottom: clip reveal like headline-reveal — only yPercent/y, no opacity
      tl.fromTo(heroBottom, { yPercent: 100, y: 0 }, { yPercent: 0, y: 0, duration: 1, ease: 'power2.out' }, '<');
      // Marquee images: per-image slide-up from clip (same as headline-reveal), in parallel with hero bottom
      if (marqueeImages.length) {
        tl.fromTo(marqueeImages, { yPercent: 100, y: 0 }, { yPercent: 0, y: 0, duration: 1, stagger: 0.12, ease: 'power2.out' }, '<');
      }
      tl.to(subheading, { opacity: 1, duration: 1.25, ease: 'power2.out' }, '<+0.15');
    }
  }

  // Hero marquee: infinite horizontal scroll (two tracks, seamless loop); pause on hover
  const marqueeEl = document.querySelector('.hero__marquee');
  const marqueeInner = document.querySelector('.hero__marquee-inner');
  const marqueeTrack = document.querySelector('.hero__marquee-track');
  if (marqueeEl && marqueeInner && marqueeTrack) {
    const trackWidth = marqueeTrack.offsetWidth;
    const gap = 5; // must match .hero__marquee-inner { gap }
    const scrollDistance = trackWidth + gap;
    if (trackWidth > 0) {
      const marqueeTl = gsap.timeline({ repeat: -1, ease: 'none' });
      marqueeTl.to(marqueeInner, { x: -scrollDistance, duration: 80, ease: 'none' })
        .to(marqueeInner, { x: 0, duration: 0 }, '-=0');
      marqueeEl.addEventListener('mouseenter', () => marqueeTl.pause());
      marqueeEl.addEventListener('mouseleave', () => marqueeTl.play());
    }
  }

  // Hero marquee: cursor-follow title pill per thumbnail (same pattern as .featured-project__cursor)
  const marqueeFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  const marqueeReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  if (marqueeFinePointer) {
    document.querySelectorAll('[data-hero-marquee-item]').forEach((wrap) => {
      const pill = wrap.querySelector('.hero__marquee-title');
      if (!pill) return;

      gsap.set(pill, { xPercent: -50, yPercent: -50 });
      const xTo = gsap.quickTo(pill, 'x', { duration: marqueeReduceMotion ? 0.01 : 0.22, ease: 'power3.out' });
      const yTo = gsap.quickTo(pill, 'y', { duration: marqueeReduceMotion ? 0.01 : 0.22, ease: 'power3.out' });

      const toLocal = (e) => {
        const rect = wrap.getBoundingClientRect();
        return { x: e.clientX - rect.left, y: e.clientY - rect.top };
      };

      wrap.addEventListener('pointerenter', (e) => {
        const { x, y } = toLocal(e);
        gsap.set(pill, { x, y });
        xTo(x);
        yTo(y);
        wrap.classList.add('is-pointer-active');
      });

      wrap.addEventListener('pointermove', (e) => {
        if (!wrap.classList.contains('is-pointer-active')) return;
        const { x, y } = toLocal(e);
        xTo(x);
        yTo(y);
      });

      wrap.addEventListener('pointerleave', () => {
        wrap.classList.remove('is-pointer-active');
      });
    });
  }

  // Headline reveal: text fits 100vw (font-size set on load/resize); per-word slide-up; replay every time section is revealed
  function fitHeadlineToViewport(section) {
    const inner = section.querySelector('.headline-reveal__inner');
    const heading = section.querySelector('.headline-reveal__heading');
    if (!inner || !heading) return;
    const innerWidth = inner.clientWidth;
    if (innerWidth <= 0) return;
    const rawBase = parseFloat(section.dataset.headlineFitBaseVw);
    const baseVw = Number.isFinite(rawBase) && rawBase > 0 ? rawBase : 10;
    heading.style.fontSize = `${baseVw}vw`;

    // When CSS stacks word-wraps into block lines (e.g. mobile footer), each line is its
    // own row, so scale each word-wrap independently to fill the line (no trailing gap).
    const wordWraps = heading.querySelectorAll('.headline-reveal__word-wrap');
    const stacked = wordWraps.length > 0 && getComputedStyle(wordWraps[0]).display === 'block';

    if (stacked) {
      // Measure the word span (still inline-block, so it shrink-wraps to its text) rather
      // than the wrap itself (block, so it always reports the full line width).
      wordWraps.forEach((wrap) => {
        const word = wrap.querySelector('.headline-reveal__word');
        if (!word) return;
        wrap.style.fontSize = '';
        const scrollWidth = word.scrollWidth;
        if (scrollWidth > 0) {
          const scale = (innerWidth * 0.98) / scrollWidth; // 98% so text stays clear of edges
          wrap.style.fontSize = `${baseVw * scale}vw`;
        }
      });
      return;
    }

    const scrollWidth = heading.scrollWidth;
    if (scrollWidth > 0) {
      const scale = (innerWidth * 0.98) / scrollWidth; // 98% so text stays clear of edges
      heading.style.fontSize = `${baseVw * scale}vw`;
    }
  }

  const headlineRevealTriggers = [];
  document.querySelectorAll('.headline-reveal').forEach((section) => {
    const words = section.querySelectorAll('.headline-reveal__word');
    const heading = section.querySelector('.headline-reveal__heading');
    if (!words.length) return;

    fitHeadlineToViewport(section);
    window.addEventListener('resize', () => fitHeadlineToViewport(section));

    // Initial state comes from CSS: .headline-reveal__word { transform: translateY(100%) }
    // We animate from fully clipped at the bottom (100%) to 0 so the words slide up out of the mask.
    const tl = gsap.timeline({ paused: true });
    tl.fromTo(
      words,
      { yPercent: 100, y: 0 },
      {
        yPercent: 0,
        y: 0,
        duration: 0.45,
        ease: 'power2.out',
        stagger: 0.05,
      }
    );

    // Trigger at ~10% from the bottom of the viewport; 0.75s delay before animation starts
    let delayId = null;
    const playAfterDelay = () => {
      clearTimeout(delayId);
      delayId = setTimeout(() => {
        tl.play();
        delayId = null;
      }, 750);
    };
    const st = ScrollTrigger.create({
      trigger: section,
      start: 'top 90%',
      onEnter: playAfterDelay,
      onEnterBack: playAfterDelay,
      onLeaveBack: () => {
        clearTimeout(delayId);
        delayId = null;
        tl.reverse();
      },
    });
    headlineRevealTriggers.push({ st, tl, playAfterDelay });
  });
  // Sections already in view on load don't get onEnter; run timeline so text shows (after 0.75s delay)
  requestAnimationFrame(() => {
    ScrollTrigger.refresh();
    headlineRevealTriggers.forEach(({ st, tl, playAfterDelay }) => {
      if (st.progress > 0) playAfterDelay();
    });
  });

  // Reveal grid: scroll-scrubbed content reveal (cards only, pop in one by one); images grow from bottom (scaleY), no fade
  document.querySelectorAll('[data-reveal-grid]').forEach((section) => {
    const items = section.querySelectorAll('[data-reveal-item]');
    const cardItems = section.querySelectorAll('[data-reveal-item]:not([data-reveal-image])');
    if (!items.length) return;

    gsap.set(cardItems, { opacity: 0, y: 18, scale: 0.92 });
    gsap.set(section.querySelectorAll('[data-reveal-image]'), { opacity: 1 });

    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top 75%',
        end: 'bottom 25%',
        scrub: 1,
      },
    });
    tl.to(cardItems, { opacity: 1, y: 0, scale: 1, duration: 1, stagger: 0.12, ease: 'power2.out' });

    section.querySelectorAll('[data-reveal-image]').forEach((media) => {
      const inner = media.querySelector('.reveal-grid__media-inner');
      if (!inner) return;
      gsap.set(inner, { scale: 0 });
      // Growing container: scale X and Y from 0→1 over first 40% of scroll range
      const setImageScale = (progress) => {
        const s = progress <= 0.4 ? progress / 0.4 : 1;
        gsap.set(inner, { scale: s });
      };
      const st = ScrollTrigger.create({
        trigger: media,
        start: 'top bottom',
        end: 'bottom top',
        onUpdate: (self) => setImageScale(self.progress),
      });
      setImageScale(st.progress);
    });
  });

  // Outcome cards: scroll-scrubbed vertical slide-up, cards align around mid-scroll
  document.querySelectorAll('[data-outcome-cards]').forEach((section) => {
    const cards = section.querySelectorAll('.outcome-card');
    if (!cards.length) return;

    // initial state is already set in CSS (opacity 0, translateY)
    const tl = gsap.timeline({
      scrollTrigger: {
        trigger: section,
        start: 'top 80%',
        end: 'bottom 20%',
        scrub: 1,
      },
    });

    // Card 1 starts first, then 2, then 3
    tl.to(cards[0], { opacity: 1, y: 0, duration: 1, ease: 'power2.out' }, 0);
    if (cards[1]) {
      tl.to(cards[1], { opacity: 1, y: 0, duration: 1, ease: 'power2.out' }, 0.1);
    }
    if (cards[2]) {
      tl.to(cards[2], { opacity: 1, y: 0, duration: 1, ease: 'power2.out' }, 0.2);
    }
  });

  // Featured project card: sync media top; arrow slide loop on hover; cursor-follow (fine pointer)
  document.querySelectorAll('[data-featured-project]').forEach((card) => {
    const header = card.querySelector('.featured-project__header');
    const cursor = card.querySelector('.featured-project__cursor');
    const arrowIcon = card.querySelector('[data-featured-arrow]');
    const finePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    const setMediaTop = () => {
      if (header) {
        card.style.setProperty('--fp-media-top', `${header.offsetHeight}px`);
      }
    };
    setMediaTop();
    window.addEventListener('resize', setMediaTop);
    if (header && typeof ResizeObserver !== 'undefined') {
      new ResizeObserver(setMediaTop).observe(header);
    }

    let arrowTl = null;
    const resetArrow = () => {
      if (arrowTl) {
        arrowTl.kill();
        arrowTl = null;
      }
      if (arrowIcon) {
        gsap.set(arrowIcon, { x: 0, opacity: 1, clearProps: 'transform' });
      }
    };

    if (arrowIcon && finePointer && !reduceMotion) {
      const runArrowLoop = () => {
        resetArrow();
        gsap.set(arrowIcon, { x: 0, opacity: 1 });
        arrowTl = gsap.timeline({ repeat: -1, repeatDelay: 0.65 });
        arrowTl
          .to(arrowIcon, { x: 14, duration: 0.3, ease: 'power2.in' })
          .set(arrowIcon, { x: -14, opacity: 0 })
          .to(arrowIcon, { x: 0, opacity: 1, duration: 0.34, ease: 'power2.out' });
      };
      // Card hover: small icon hit-area missed easily; motion runs while pointer is on card
      card.addEventListener('pointerenter', runArrowLoop);
      card.addEventListener('pointerleave', resetArrow);
    }

    if (!cursor || !finePointer) return;

    gsap.set(cursor, { xPercent: -50, yPercent: -50 });
    const xTo = gsap.quickTo(cursor, 'x', {
      duration: reduceMotion ? 0.01 : 0.22,
      ease: 'power3.out',
    });
    const yTo = gsap.quickTo(cursor, 'y', {
      duration: reduceMotion ? 0.01 : 0.22,
      ease: 'power3.out',
    });

    const toLocal = (e) => {
      const rect = card.getBoundingClientRect();
      return { x: e.clientX - rect.left, y: e.clientY - rect.top };
    };

    card.addEventListener('pointerenter', (e) => {
      const { x, y } = toLocal(e);
      gsap.set(cursor, { x, y });
      xTo(x);
      yTo(y);
      card.classList.add('is-pointer-active');
    });

    card.addEventListener('pointermove', (e) => {
      if (!card.classList.contains('is-pointer-active')) return;
      const { x, y } = toLocal(e);
      xTo(x);
      yTo(y);
    });

    card.addEventListener('pointerleave', () => {
      card.classList.remove('is-pointer-active');
    });
  });

  // Tandem hub: center disk image rotation scrubbed to scroll
  const hubReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-tandem-hub]').forEach((section) => {
    const spin = section.querySelector('[data-tandem-hub-spin]');
    if (!spin || hubReducedMotion) return;
    const maxRot = parseFloat(section.getAttribute('data-tandem-hub-spin-max') || '720', 10);
    gsap.to(spin, {
      rotation: Number.isFinite(maxRot) ? maxRot : 720,
      ease: 'none',
      transformOrigin: '50% 50%',
      scrollTrigger: {
        trigger: section,
        start: 'top bottom',
        end: 'bottom top',
        scrub: true,
      },
    });
  });

  // Timeline accordion: floating card, word stagger, image scale, merging dots
  const DOT_INSET = 8;

  function initTimelineAccordion(section) {
    const mqMobile = window.matchMedia('(max-width: 767px)');
    const mqFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    let abortCtrl = new AbortController();

    const killItemMotion = (item) => {
      const dotL = item.querySelector('[data-timeline-dot="left"]');
      const dotM = item.querySelector('[data-timeline-dot="mid"]');
      const dotR = item.querySelector('[data-timeline-dot="right"]');
      const img = item.querySelector('[data-timeline-img]');
      const words = item.querySelectorAll('.timeline-accordion__word');
      gsap.killTweensOf(
        [dotL, dotM, dotR, img, ...Array.from(words)].filter(Boolean),
      );
    };

    const resetItemVisuals = (item) => {
      const dotL = item.querySelector('[data-timeline-dot="left"]');
      const dotM = item.querySelector('[data-timeline-dot="mid"]');
      const dotR = item.querySelector('[data-timeline-dot="right"]');
      const img = item.querySelector('[data-timeline-img]');
      const words = item.querySelectorAll('.timeline-accordion__word');
      if (dotL && dotM && dotR) {
        gsap.set([dotL, dotM, dotR], { clearProps: 'transform,opacity,scale' });
      }
      if (img) gsap.set(img, { scale: 0 });
      if (words.length) gsap.set(words, { opacity: 0 });
    };

    const hardReset = () => {
      abortCtrl.abort();
      section.querySelectorAll('[data-timeline-item]').forEach((it) => {
        killItemMotion(it);
        it.classList.remove('is-open');
        it.style.removeProperty('--ta-panel-left');
        const panel = it.querySelector('[data-timeline-panel]');
        const trigger = it.querySelector('[data-timeline-trigger]');
        if (panel) panel.setAttribute('hidden', '');
        if (trigger) trigger.setAttribute('aria-expanded', 'false');
        resetItemVisuals(it);
      });
    };

    const bind = () => {
      abortCtrl = new AbortController();
      const { signal } = abortCtrl;
      const items = section.querySelectorAll('[data-timeline-item]');
      const isMobile = mqMobile.matches;
      const useHoverDesktop = !isMobile && mqFinePointer.matches;
      const timelines = new WeakMap();

      const syncA11y = (it, open) => {
        const trigger = it.querySelector('[data-timeline-trigger]');
        const panel = it.querySelector('[data-timeline-panel]');
        if (trigger) trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
        if (panel) {
          if (open) panel.removeAttribute('hidden');
          else panel.setAttribute('hidden', '');
        }
      };

      const closeItem = (it) => {
        const tl = timelines.get(it);
        if (tl) tl.kill();
        timelines.delete(it);
        it.classList.remove('is-open');
        syncA11y(it, false);
        resetItemVisuals(it);
      };

      items.forEach((item) => {
        const trigger = item.querySelector('[data-timeline-trigger]');
        const panel = item.querySelector('[data-timeline-panel]');
        const bodyEl = item.querySelector('[data-timeline-body]');
        const yearEl = item.querySelector('[data-timeline-year]');
        const img = item.querySelector('[data-timeline-img]');
        const dotL = item.querySelector('[data-timeline-dot="left"]');
        const dotM = item.querySelector('[data-timeline-dot="mid"]');
        const dotR = item.querySelector('[data-timeline-dot="right"]');
        let wordsBuilt = false;

        const ensureWords = () => {
          if (wordsBuilt || !bodyEl) return;
          if (bodyEl.querySelector('.timeline-accordion__word')) {
            wordsBuilt = true;
            return;
          }
          const text = bodyEl.textContent.trim();
          if (!text) {
            wordsBuilt = true;
            return;
          }
          bodyEl.textContent = '';
          text.split(/(\s+)/).forEach((part) => {
            if (/^\s+$/.test(part)) {
              bodyEl.appendChild(document.createTextNode(part));
            } else {
              const span = document.createElement('span');
              span.className = 'timeline-accordion__word';
              span.textContent = part;
              bodyEl.appendChild(span);
            }
          });
          wordsBuilt = true;
        };

        const openItem = () => {
          items.forEach((other) => {
            if (other !== item) closeItem(other);
          });
          item.classList.add('is-open');
          syncA11y(item, true);
          if (panel) void panel.offsetWidth;
          ensureWords();
          const words = item.querySelectorAll('.timeline-accordion__word');
          const activeTl = gsap.timeline();
          timelines.set(item, activeTl);

          if (reduceMotion) {
            if (words.length) gsap.set(words, { opacity: 1 });
            if (img) gsap.set(img, { scale: 1 });
            if (dotL && dotM && dotR) {
              gsap.set(dotL, { x: DOT_INSET, opacity: 0 });
              gsap.set(dotR, { x: -DOT_INSET, opacity: 0 });
              gsap.set(dotM, { scale: 1.4 });
            }
            return;
          }

          if (words.length) {
            gsap.set(words, { opacity: 0 });
            activeTl.to(words, {
              opacity: 1,
              duration: 0.09,
              stagger: 0.058,
              ease: 'none',
            }, 0);
          }
          if (img) {
            gsap.set(img, { scale: 0 });
            activeTl.to(img, {
              scale: 1,
              duration: 0.88,
              ease: 'power2.out',
            }, 0.12);
          }
          if (dotL && dotM && dotR) {
            gsap.set([dotL, dotM, dotR], { clearProps: 'all' });
            activeTl.to(dotL, { x: DOT_INSET, duration: 0.48, ease: 'power2.inOut' }, 0);
            activeTl.to(dotR, { x: -DOT_INSET, duration: 0.48, ease: 'power2.inOut' }, 0);
            activeTl.to([dotL, dotR], { opacity: 0, duration: 0.22 }, 0.28);
            activeTl.to(dotM, { scale: 1.5, duration: 0.42, ease: 'power2.out' }, 0.24);
          }
        };

        const onTriggerKeydown = (e) => {
          if (e.key !== 'Enter' && e.key !== ' ') return;
          e.preventDefault();
          if (item.classList.contains('is-open')) closeItem(item);
          else openItem();
        };

        if (isMobile) {
          trigger?.addEventListener('click', (e) => {
            e.stopPropagation();
            if (item.classList.contains('is-open')) closeItem(item);
            else openItem();
          }, { signal });
          trigger?.addEventListener('keydown', onTriggerKeydown, { signal });
        } else {
          const updatePanelLeft = () => {
            if (!trigger || !yearEl) return;
            const gap = parseFloat(getComputedStyle(trigger).columnGap) || 24;
            const w = yearEl.getBoundingClientRect().width;
            item.style.setProperty('--ta-panel-left', `${Math.round(w + gap)}px`);
          };
          updatePanelLeft();
          const ro = new ResizeObserver(updatePanelLeft);
          if (trigger) ro.observe(trigger);
          signal.addEventListener('abort', () => ro.disconnect());

          if (useHoverDesktop) {
            item.addEventListener('mouseenter', () => openItem(), { signal });
            item.addEventListener('mouseleave', (e) => {
              if (!item.contains(e.relatedTarget)) closeItem(item);
            }, { signal });
            item.addEventListener('focusin', () => {
              openItem();
            }, { signal });
            item.addEventListener('focusout', (e) => {
              if (!item.contains(e.relatedTarget)) closeItem(item);
            }, { signal });
          } else {
            trigger?.addEventListener('click', (e) => {
              e.stopPropagation();
              if (item.classList.contains('is-open')) closeItem(item);
              else openItem();
            }, { signal });
            trigger?.addEventListener('keydown', onTriggerKeydown, { signal });
          }
        }
      });

      if (isMobile) {
        document.addEventListener('pointerdown', (e) => {
          if (section.contains(e.target)) return;
          items.forEach((it) => closeItem(it));
        }, { signal });
      }
    };

    bind();
    mqMobile.addEventListener('change', () => {
      hardReset();
      bind();
    });
  }

  document.querySelectorAll('[data-timeline-accordion]').forEach(initTimelineAccordion);

  // Footer grid links: same arrow loop as featured-project (per link hover)
  const footerFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
  const footerReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.querySelectorAll('[data-footer-arrow-link]').forEach((link) => {
    const arrowIcon = link.querySelector('[data-footer-arrow-motion]');
    if (!arrowIcon || !footerFinePointer || footerReduceMotion) return;
    let footerArrowTl = null;
    const resetFooterArrow = () => {
      if (footerArrowTl) {
        footerArrowTl.kill();
        footerArrowTl = null;
      }
      gsap.set(arrowIcon, { x: 0, opacity: 1, clearProps: 'transform' });
    };
    const runFooterArrowLoop = () => {
      resetFooterArrow();
      gsap.set(arrowIcon, { x: 0, opacity: 1 });
      footerArrowTl = gsap.timeline({ repeat: -1, repeatDelay: 0.65 });
      footerArrowTl
        .to(arrowIcon, { x: 14, duration: 0.3, ease: 'power2.in' })
        .set(arrowIcon, { x: -14, opacity: 0 })
        .to(arrowIcon, { x: 0, opacity: 1, duration: 0.34, ease: 'power2.out' });
    };
    link.addEventListener('pointerenter', runFooterArrowLoop);
    link.addEventListener('pointerleave', resetFooterArrow);
  });

  // Explore dual cards: arrow loop (no cursor pill, no glow)
  const exploreReduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  const exploreFinePointer = window.matchMedia('(hover: hover) and (pointer: fine)').matches;

  document.querySelectorAll('[data-explore-dual-card]').forEach((card) => {
    const arrowIcon = card.querySelector('[data-explore-arrow]');
    if (!arrowIcon || !exploreFinePointer || exploreReduceMotion) return;

    let arrowTl = null;
    const resetArrow = () => {
      if (arrowTl) {
        arrowTl.kill();
        arrowTl = null;
      }
      gsap.set(arrowIcon, { x: 0, opacity: 1, clearProps: 'transform' });
    };
    const runArrowLoop = () => {
      resetArrow();
      gsap.set(arrowIcon, { x: 0, opacity: 1 });
      arrowTl = gsap.timeline({ repeat: -1, repeatDelay: 0.65 });
      arrowTl
        .to(arrowIcon, { x: 14, duration: 0.3, ease: 'power2.in' })
        .set(arrowIcon, { x: -14, opacity: 0 })
        .to(arrowIcon, { x: 0, opacity: 1, duration: 0.34, ease: 'power2.out' });
    };
    card.addEventListener('pointerenter', runArrowLoop);
    card.addEventListener('pointerleave', resetArrow);
  });

  // Leadership card: translate faces on hover + class for dim/back reveal (CSS scales imgs)
  document.querySelectorAll('[data-explore-leadership-card]').forEach((card) => {
    const cluster = card.querySelector('[data-leadership-cluster]');
    if (!cluster) return;
    const faces = cluster.querySelectorAll('.explore-dual__face');

    const setActive = (on) => {
      card.classList.toggle('is-leadership-active', on);
    };

    if (exploreReduceMotion) {
      const toggle = (on) => () => setActive(on);
      card.addEventListener('pointerenter', toggle(true));
      card.addEventListener('pointerleave', toggle(false));
      card.addEventListener('focus', toggle(true));
      card.addEventListener('blur', toggle(false));
      return;
    }

    const openLeadership = () => {
      setActive(true);
      faces.forEach((face) => {
        const dx = Number(face.getAttribute('data-dx')) || 0;
        const dy = Number(face.getAttribute('data-dy')) || 0;
        gsap.to(face, {
          x: dx,
          y: dy,
          duration: 0.55,
          ease: 'power2.out',
          overwrite: 'auto',
        });
      });
    };

    const closeLeadership = () => {
      setActive(false);
      gsap.to(faces, {
        x: 0,
        y: 0,
        duration: 0.45,
        ease: 'power2.inOut',
        overwrite: 'auto',
      });
    };

    card.addEventListener('pointerenter', openLeadership);
    card.addEventListener('pointerleave', closeLeadership);
    card.addEventListener('focus', openLeadership);
    card.addEventListener('blur', closeLeadership);
  });

  // Project gallery carousel
  document.querySelectorAll('[data-project-gallery]').forEach((section) => {
    const track = section.querySelector('[data-project-gallery-track]');
    const slides = section.querySelectorAll('[data-project-gallery-slide]');
    const dots = section.querySelectorAll('[data-project-gallery-dot]');
    if (!track || !slides.length) return;

    let index = 0;
    const getStep = () => {
      const slide = slides[0];
      if (!slide) return 0;
      const gap = parseFloat(getComputedStyle(track).gap) || 0;
      return slide.offsetWidth + gap;
    };

    const goTo = (i) => {
      index = Math.max(0, Math.min(i, slides.length - 1));
      const step = getStep();
      gsap.to(track, {
        x: -index * step,
        duration: 0.65,
        ease: 'power2.out',
        overwrite: 'auto',
      });
      dots.forEach((dot, di) => {
        dot.classList.toggle('is-active', di === index);
        dot.setAttribute('aria-current', di === index ? 'true' : 'false');
      });
    };

    dots.forEach((dot) => {
      dot.addEventListener('click', () => {
        const idx = parseInt(dot.getAttribute('data-project-gallery-dot') || '0', 10);
        if (!Number.isNaN(idx)) goTo(idx);
      });
    });

    window.addEventListener('resize', () => goTo(index));
    goTo(0);
  });

  // Project FAQ accordion
  document.querySelectorAll('[data-project-faq]').forEach((section) => {
    section.querySelectorAll('[data-project-faq-item]').forEach((item) => {
      const trigger = item.querySelector('[data-project-faq-trigger]');
      const panel = item.querySelector('[data-project-faq-panel]');
      if (!trigger || !panel) return;

      const closeItem = (target) => {
        target.classList.remove('is-open');
        const t = target.querySelector('[data-project-faq-trigger]');
        const p = target.querySelector('[data-project-faq-panel]');
        if (t) t.setAttribute('aria-expanded', 'false');
        if (p) p.setAttribute('hidden', '');
      };

      const openItem = (target) => {
        target.classList.add('is-open');
        const t = target.querySelector('[data-project-faq-trigger]');
        const p = target.querySelector('[data-project-faq-panel]');
        if (t) t.setAttribute('aria-expanded', 'true');
        if (p) p.removeAttribute('hidden');
      };

      trigger.addEventListener('click', () => {
        const isOpen = item.classList.contains('is-open');
        section.querySelectorAll('[data-project-faq-item].is-open').forEach((other) => {
          if (other !== item) closeItem(other);
        });
        if (isOpen) closeItem(item);
        else openItem(item);
      });
    });
  });

  // Project approach: video play overlay
  document.querySelectorAll('[data-project-video-play]').forEach((btn) => {
    btn.addEventListener('click', () => {
      const wrap = btn.closest('.project-approach__video-wrap');
      const video = wrap ? wrap.querySelector('video') : null;
      if (!video) return;
      video.controls = true;
      const playPromise = video.play();
      if (playPromise && typeof playPromise.catch === 'function') {
        playPromise.catch(() => {});
      }
      btn.hidden = true;
    });
  });
});
