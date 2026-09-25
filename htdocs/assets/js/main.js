/*
 * reichi.com – minimales Vanilla-JavaScript.
 * Alles hier ist optional: Navigation, Inhalte und Formular funktionieren ohne JS.
 *  1. Mobile Navigation (Toggle, Escape, Fokus)
 *  2. Header-Zustand beim Scrollen
 *  3. Sanftes Einblenden von Abschnitten (respektiert prefers-reduced-motion)
 *  4. Lightbox für die Galerie auf Basis von <dialog>
 *  5. Fokus auf Formular-Statusmeldung nach dem Absenden
 *  6. Formular: Interaktions-Token und optional Cloudflare Turnstile (erst bei Nutzung geladen)
 *  7. Hero-Pegelanzeige (Canvas), reagiert auf die Maus; statisch bei prefers-reduced-motion
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
    // Erst nach dem Sprung des Browsers zum Fragment ausführen, sonst wird der Fokus wieder entfernt.
    var focusStatus = function () {
      setTimeout(function () {
        status.focus({ preventScroll: true });
        status.scrollIntoView({ block: 'center', behavior: reduceMotion.matches ? 'auto' : 'smooth' });
      }, 50);
    };
    if (doc.readyState === 'complete') { focusStatus(); } else { window.addEventListener('load', focusStatus); }
  }

  /* 6. Formular: Interaktions-Token, optional Turnstile ------------------- */
  var form = doc.querySelector('form.form');
  if (form) {
    // Beleg, dass ein Browser mit JavaScript das Formular gesendet hat (Server prüft strrev(csrf)).
    var csrf = form.querySelector('[name="csrf_token"]');
    var jsToken = form.querySelector('[data-js-token]');
    if (csrf && jsToken) { jsToken.value = csrf.value.split('').reverse().join(''); }

    var siteKey = form.getAttribute('data-turnstile-sitekey');
    var widgetHost = form.querySelector('[data-turnstile-widget]');
    if (siteKey && widgetHost) {
      var submit = form.querySelector('button[type="submit"]');
      var submitLabel = submit ? submit.innerHTML : '';
      var tokenReady = false;
      var pendingSubmit = false;
      var loading = false;

      function setWaiting(waiting) {
        if (!submit) { return; }
        submit.disabled = waiting;
        submit.innerHTML = waiting ? 'Sicherheitsprüfung läuft …' : submitLabel;
      }

      window.reichiTurnstileReady = function () {
        window.turnstile.render(widgetHost, {
          sitekey: siteKey,
          theme: 'dark',
          language: 'de',
          action: 'contact',
          appearance: form.getAttribute('data-turnstile-appearance') || 'always',
          callback: function () {
            tokenReady = true;
            setWaiting(false);
            if (pendingSubmit) { pendingSubmit = false; form.submit(); }
          },
          'expired-callback': function () { tokenReady = false; },
          'error-callback': function () { tokenReady = false; setWaiting(false); }
        });
      };

      // Das Cloudflare-Script wird erst geladen, wenn der Besucher das Formular tatsächlich benutzt.
      function loadTurnstile() {
        if (loading) { return; }
        loading = true;
        var script = doc.createElement('script');
        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?onload=reichiTurnstileReady&render=explicit';
        script.async = true;
        script.defer = true;
        doc.head.appendChild(script);
      }
      form.addEventListener('focusin', loadTurnstile, { once: true });
      form.addEventListener('pointerdown', loadTurnstile, { once: true });

      form.addEventListener('submit', function (event) {
        if (tokenReady) { return; }
        // Token noch nicht da: Absenden zurückhalten, bis die Prüfung fertig ist.
        event.preventDefault();
        pendingSubmit = true;
        setWaiting(true);
        loadTurnstile();
      });
    }
  }

  /* 7. Hero-Pegelanzeige ------------------------------------------------- */
  // Eine Reihe dünner Balken wie die LED-Meter einer Meterbridge: ruhiges Grundrauschen,
  // unter dem Mauszeiger steigt der Pegel (schneller Attack, langsames Release, Peak-Hold),
  // bei schneller Bewegung clippt der obere Bereich in der Akzentfarbe.
  var meter = doc.querySelector('[data-hero-meter]');
  var heroEl = meter ? meter.closest('.hero') : null;
  if (meter && heroEl && meter.getContext) {
    var ctx = meter.getContext('2d');
    var heroInner = heroEl.querySelector('.hero__inner');
    var led = heroEl.querySelector('.hero__dot');
    var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
    var styles = getComputedStyle(html);
    var monoFont = (styles.getPropertyValue('--font-mono') || 'monospace').trim();
    var accent = parseHex(styles.getPropertyValue('--accent')) || [255, 77, 58];
    var accentRgb = accent.join(',');
    var dpr = Math.min(2, window.devicePixelRatio || 1);
    var W = 0, H = 0, N = 0, slot = 0, scaleH = 0, baseline = 0, labelX = 0;
    var levels = [], peaks = [];
    var pointer = { x: -1, speed: 0, lastX: null, lastT: 0 };
    var clock = 0;
    var running = false, onScreen = true, rafId = 0, lastClip = 0;

    function parseHex(value) {
      var m = /^#?([0-9a-f]{6})$/i.exec((value || '').trim());
      if (!m) { return null; }
      var n = parseInt(m[1], 16);
      return [(n >> 16) & 255, (n >> 8) & 255, n & 255];
    }

    function resize() {
      W = meter.clientWidth;
      H = meter.clientHeight;
      if (!W || !H) { return; }
      meter.width = Math.round(W * dpr);
      meter.height = Math.round(H * dpr);
      ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
      N = Math.max(36, Math.round(W / 14));
      slot = W / N;
      baseline = H - 14;
      scaleH = H - 26;
      // dB-Skala rechts, an der Innenkante des Inhalts ausgerichtet
      var innerRight = heroInner ? heroInner.getBoundingClientRect().right - meter.getBoundingClientRect().left : W - 16;
      labelX = Math.min(W - 12, innerRight);
      if (levels.length !== N) {
        levels = []; peaks = [];
        for (var i = 0; i < N; i++) { levels.push(0.08); peaks.push(0.08); }
      }
    }

    function noiseFloor(i) {
      return 0.06
        + 0.035 * (0.5 + 0.5 * Math.sin(clock * 1.3 + i * 0.7))
        + 0.02 * Math.sin(clock * 3.1 + i * 2.3);
    }

    function draw(animate) {
      ctx.clearRect(0, 0, W, H);
      var clipped = false;
      for (var i = 0; i < N; i++) {
        var target = animate ? noiseFloor(i) : 0.08;
        if (animate && pointer.x >= 0) {
          var d = ((i + 0.5) * slot - pointer.x) / (slot * 7);
          target += Math.exp(-d * d) * (0.5 + 0.55 * pointer.speed);
        }
        target = Math.min(1, target);
        if (animate) {
          // Meter-Ballistik: schneller Attack, langsames Release
          levels[i] += (target - levels[i]) * (target > levels[i] ? 0.45 : 0.06);
          if (levels[i] > peaks[i]) { peaks[i] = levels[i]; } else { peaks[i] -= 0.006; }
        } else {
          levels[i] = target; peaks[i] = target;
        }
        if (peaks[i] > 0.86) { clipped = true; }

        var x = Math.round(i * slot + slot / 2) - 1;
        // an den Rändern ausblenden, damit kein harter Kasten entsteht
        var edge = Math.min(1, Math.min(i, N - 1 - i) / (N * 0.18));
        var h = levels[i] * scaleH;
        var grad = ctx.createLinearGradient(0, baseline, 0, baseline - scaleH);
        grad.addColorStop(0, 'rgba(255,255,255,' + (0.10 * edge) + ')');
        grad.addColorStop(0.75, 'rgba(255,255,255,' + (0.30 * edge) + ')');
        grad.addColorStop(0.86, 'rgba(255,196,120,' + (0.55 * edge) + ')');
        grad.addColorStop(1, 'rgba(' + accentRgb + ',' + (0.9 * edge) + ')');
        ctx.fillStyle = grad;
        ctx.fillRect(x, baseline - h, 2, h);

        var py = baseline - peaks[i] * scaleH;
        ctx.fillStyle = peaks[i] > 0.86
          ? 'rgba(' + accentRgb + ',' + (0.9 * edge) + ')'
          : 'rgba(255,255,255,' + (0.45 * edge) + ')';
        ctx.fillRect(x, py - 1, 2, 2);
      }

      // dB-Skala nur ab Tablet-Breite; auf dem Handy bleiben nur die Balken
      var labels = W >= 768 ? [['0', 1], ['-6', 0.86], ['-12', 0.7], ['-20', 0.5], ['-40', 0.18]] : [];
      ctx.font = '600 10px ' + monoFont;
      ctx.textAlign = 'right';
      for (var k = 0; k < labels.length; k++) {
        var y = baseline - labels[k][1] * scaleH;
        ctx.fillStyle = labels[k][1] === 1 ? 'rgba(' + accentRgb + ',0.6)' : 'rgba(255,255,255,0.28)';
        ctx.fillText(labels[k][0], labelX, y + 3);
        ctx.fillRect(labelX - 34, y, 8, 1);
      }

      if (animate && clipped && led && performance.now() - lastClip > 700) {
        lastClip = performance.now();
        led.classList.remove('is-clip');
        void led.offsetWidth; // Animation neu starten
        led.classList.add('is-clip');
      }
    }

    function frame() {
      if (!running) { rafId = 0; return; }
      clock += 0.016;
      pointer.speed *= 0.92;
      draw(true);
      rafId = window.requestAnimationFrame(frame);
    }

    function updateRunning() {
      var shouldRun = onScreen && !doc.hidden && !reduceMotion.matches;
      if (shouldRun && !running) { running = true; rafId = window.requestAnimationFrame(frame); }
      if (!shouldRun) { running = false; }
    }

    function onPointerMove(event) {
      var rect = meter.getBoundingClientRect();
      var x = event.clientX - rect.left;
      var now = performance.now();
      if (pointer.lastX !== null) {
        var dt = Math.max(8, now - pointer.lastT);
        pointer.speed = Math.min(1, pointer.speed * 0.6 + Math.abs(x - pointer.lastX) / dt * 0.25);
      }
      pointer.lastX = x; pointer.lastT = now; pointer.x = x;
    }

    function onPointerLeave() { pointer.x = -1; pointer.lastX = null; }

    function bindPointer() {
      if (finePointer.matches) {
        heroEl.addEventListener('mousemove', onPointerMove);
        heroEl.addEventListener('mouseleave', onPointerLeave);
      } else {
        heroEl.removeEventListener('mousemove', onPointerMove);
        heroEl.removeEventListener('mouseleave', onPointerLeave);
        onPointerLeave();
      }
    }

    resize();
    if (reduceMotion.matches) {
      draw(false);
    } else {
      bindPointer();
      updateRunning();
    }

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        onScreen = entries[0].isIntersecting;
        updateRunning();
      }).observe(heroEl);
    }
    doc.addEventListener('visibilitychange', updateRunning);
    window.addEventListener('resize', function () {
      resize();
      if (!running) { draw(false); }
    });
    if (led) { led.addEventListener('animationend', function () { led.classList.remove('is-clip'); }); }
    var onMotionChange = function () {
      if (reduceMotion.matches) { running = false; draw(false); } else { bindPointer(); updateRunning(); }
    };
    if (reduceMotion.addEventListener) {
      reduceMotion.addEventListener('change', onMotionChange);
      finePointer.addEventListener('change', bindPointer);
    }
  }
})();
