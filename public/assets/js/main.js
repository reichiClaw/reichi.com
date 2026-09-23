/*
 * reichi.com – minimales Vanilla-JavaScript.
 * Alles hier ist optional: Navigation, Inhalte und Formular funktionieren ohne JS.
 *  1. Mobile Navigation (Toggle, Escape, Fokus)
 *  2. Header-Zustand beim Scrollen
 *  3. Sanftes Einblenden von Abschnitten (respektiert prefers-reduced-motion)
 *  4. Lightbox für die Galerie auf Basis von <dialog>
 *  5. Fokus auf Formular-Statusmeldung nach dem Absenden
 */
(function () {
  'use strict';

  var doc = document;
  var html = doc.documentElement;
  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');

  // Kennzeichnet, dass JS läuft; CSS nutzt das nur für optionale Verbesserungen.
  html.classList.add('js');

  /* 1. Mobile Navigation ------------------------------------------------- */
  var header = doc.querySelector('.site-header');
  var toggle = doc.querySelector('.nav-toggle');
  var nav = doc.getElementById('site-nav');

  function isMobileNav() {
    return window.matchMedia('(max-width: 63.99em)').matches;
  }

  function setNav(open) {
    if (!toggle || !nav) { return; }
    nav.classList.toggle('is-open', open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    toggle.querySelector('.nav-toggle__label').textContent = open ? 'Schließen' : 'Menü';
    if (open) {
      var first = nav.querySelector('a');
      if (first) { first.focus(); }
    }
  }

  if (toggle && nav) {
    toggle.addEventListener('click', function () {
      setNav(!nav.classList.contains('is-open'));
    });

    nav.addEventListener('click', function (event) {
      if (event.target.closest('a') && isMobileNav()) {
        setNav(false);
      }
    });

    doc.addEventListener('keydown', function (event) {
      if (event.key === 'Escape' && nav.classList.contains('is-open')) {
        setNav(false);
        toggle.focus();
      }
    });

    doc.addEventListener('click', function (event) {
      if (nav.classList.contains('is-open') && header && !header.contains(event.target)) {
        setNav(false);
      }
    });

    window.matchMedia('(min-width: 64em)').addEventListener('change', function (event) {
      if (event.matches) { setNav(false); }
    });
  }

  /* 2. Header-Zustand ---------------------------------------------------- */
  if (header) {
    var lastState = null;
    var onScroll = function () {
      var scrolled = window.scrollY > 8;
      if (scrolled !== lastState) {
        header.classList.toggle('is-scrolled', scrolled);
        lastState = scrolled;
      }
    };
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
  }

  /* 3. Einblenden -------------------------------------------------------- */
  var revealTargets = doc.querySelectorAll('[data-reveal]');
  if (revealTargets.length && 'IntersectionObserver' in window && !reduceMotion.matches) {
    var io = new IntersectionObserver(function (entries) {
      entries.forEach(function (entry) {
        if (entry.isIntersecting) {
          entry.target.classList.add('is-visible');
          io.unobserve(entry.target);
        }
      });
    }, { rootMargin: '0px 0px -8% 0px', threshold: 0.05 });
    revealTargets.forEach(function (el) { io.observe(el); });
  } else {
    revealTargets.forEach(function (el) { el.classList.add('is-visible'); });
  }

  /* 4. Lightbox ---------------------------------------------------------- */
  var lightbox = doc.getElementById('lightbox');
  var links = Array.prototype.slice.call(doc.querySelectorAll('[data-lightbox]'));

  if (lightbox && links.length && typeof lightbox.showModal === 'function') {
    var img = lightbox.querySelector('.lightbox__img');
    var caption = lightbox.querySelector('.lightbox__caption');
    var current = -1;
    var opener = null;

    function show(index) {
      current = (index + links.length) % links.length;
      var link = links[current];
      img.src = link.getAttribute('href');
      img.alt = link.getAttribute('data-alt') || '';
      img.width = parseInt(link.getAttribute('data-width'), 10) || 1200;
      img.height = parseInt(link.getAttribute('data-height'), 10) || 800;
      caption.textContent = '';
      var text = doc.createTextNode(link.getAttribute('data-caption') || '');
      caption.appendChild(text);
      var credit = link.getAttribute('data-credit');
      if (credit) {
        var small = doc.createElement('small');
        small.textContent = 'Foto: ' + credit;
        caption.appendChild(small);
      }
    }

    function open(index, source) {
      opener = source || null;
      show(index);
      lightbox.showModal();
      lightbox.querySelector('[data-lightbox-close]').focus();
    }

    links.forEach(function (link, index) {
      link.addEventListener('click', function (event) {
        // Mit gedrückter Zusatztaste soll der Link normal funktionieren (neuer Tab etc.).
        if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) { return; }
        event.preventDefault();
        open(index, link);
      });
    });

    lightbox.querySelector('[data-lightbox-close]').addEventListener('click', function () { lightbox.close(); });
    lightbox.querySelector('[data-lightbox-prev]').addEventListener('click', function () { show(current - 1); });
    lightbox.querySelector('[data-lightbox-next]').addEventListener('click', function () { show(current + 1); });

    lightbox.addEventListener('click', function (event) {
      if (event.target === lightbox) { lightbox.close(); }
    });

    lightbox.addEventListener('keydown', function (event) {
      if (event.key === 'ArrowLeft') { show(current - 1); }
      if (event.key === 'ArrowRight') { show(current + 1); }
    });

    lightbox.addEventListener('close', function () {
      img.removeAttribute('src');
      if (opener) { opener.focus(); }
    });
  }

  /* 5. Formularstatus ---------------------------------------------------- */
  var status = doc.getElementById('form-status');
  if (status && window.location.hash === '#hire') {
    status.focus({ preventScroll: true });
    status.scrollIntoView({ block: 'center', behavior: reduceMotion.matches ? 'auto' : 'smooth' });
  }
})();
