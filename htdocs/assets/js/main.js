/*
 * reichi.com – minimales Vanilla-JavaScript.
 * Alles hier ist optional: Navigation, Inhalte und Formular funktionieren ohne JS.
 *  1. Mobile Navigation (Toggle, Escape, Fokus)
 *  2. Header-Zustand beim Scrollen
 *  3. Sanftes Einblenden von Abschnitten (respektiert prefers-reduced-motion)
 *  4. Lightbox für die Galerie auf Basis von <dialog>
 *  5. Fokus auf Formular-Statusmeldung nach dem Absenden
 *  6. Formular: Interaktions-Token und optional Cloudflare Turnstile (erst bei Nutzung geladen)
 *  7. Hero: Bühnenlicht folgt der Maus (Verfolger), Bühnenkante als Oszilloskop-Linie
 *  8. Hero: Kopf folgt dem Mauszeiger (optional, config 'hero_visual' => 'animation')
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

  /* 7. Hero: Verfolger-Licht und Oszilloskop-Linie ------------------------ */
  // Nur mit echter Maus (hover + feiner Zeiger) und ohne prefers-reduced-motion.
  // Das Bühnenlicht schwenkt mit deutlicher Verzögerung Richtung Zeiger, Porträt und Text
  // verschieben sich minimal gegeneinander. Die Bühnenkanten-Linie neben dem Porträt wird
  // zur Oszilloskop-Spur: gerade, solange nichts passiert; in Zeigernähe wellt sie sich
  // abhängig von der Mausgeschwindigkeit und klingt wieder ab.
  var heroEl = doc.querySelector('.hero');
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (heroEl && finePointer.matches && !reduceMotion.matches) {
    var spot = heroEl.querySelector('.hero__spot');
    var figure = heroEl.querySelector('.hero__figure');
    var copy = heroEl.querySelector('.hero__copy');
    var scope = heroEl.querySelector('.hero__scope');
    var scopeCtx = scope && scope.getContext ? scope.getContext('2d') : null;
    var accentColor = (getComputedStyle(html).getPropertyValue('--accent') || '#ff4d3a').trim();

    var target = { x: 0, y: 0 };   // Zeiger relativ zur Hero-Mitte, -1 … 1
    var current = { x: 0, y: 0 };
    var onScreen = true;
    var rafId = 0;

    // Oszilloskop-Zustand
    var scopeW = 0, scopeH = 0, scopeDpr = 1, scopeReady = false;
    var wave = { amp: 0, y: 0, phase: 0, speed: 0, lastX: null, lastY: null, lastT: 0 };

    function sizeScope() {
      if (!scopeCtx) { return; }
      scopeW = scope.clientWidth;
      scopeH = scope.clientHeight;
      scopeDpr = Math.min(2, window.devicePixelRatio || 1);
      scope.width = Math.round(scopeW * scopeDpr);
      scope.height = Math.round(scopeH * scopeDpr);
      scopeCtx.setTransform(scopeDpr, 0, 0, scopeDpr, 0, 0);
    }

    function drawScope() {
      if (!scopeCtx || !scopeW) { return; }
      scopeCtx.clearRect(0, 0, scopeW, scopeH);
      var grad = scopeCtx.createLinearGradient(0, 0, 0, scopeH);
      grad.addColorStop(0, 'rgba(255,77,58,0)');
      grad.addColorStop(0.5, accentColor);
      grad.addColorStop(1, 'rgba(255,77,58,0)');
      scopeCtx.strokeStyle = grad;
      scopeCtx.lineWidth = 1;
      scopeCtx.beginPath();
      var cx = Math.floor(scopeW / 2) + 0.5;
      if (wave.amp < 0.003) {
        scopeCtx.moveTo(cx, 0);
        scopeCtx.lineTo(cx, scopeH);
      } else {
        for (var y = 0; y <= scopeH; y += 1) {
          var d = (y - wave.y) / 70;
          var env = Math.exp(-d * d) * wave.amp;
          var x = cx + Math.sin(y * 0.11 + wave.phase) * env * 16 + Math.sin(y * 0.29 + wave.phase * 1.7) * env * 6;
          if (y === 0) { scopeCtx.moveTo(x, y); } else { scopeCtx.lineTo(x, y); }
        }
      }
      scopeCtx.stroke();
    }

    function settled() {
      return Math.abs(target.x - current.x) < 0.002 && Math.abs(target.y - current.y) < 0.002 && wave.amp < 0.003;
    }

    function frame() {
      rafId = 0;
      // Verfolger: träge nachführen
      current.x += (target.x - current.x) * 0.035;
      current.y += (target.y - current.y) * 0.035;
      var hw = heroEl.clientWidth, hh = heroEl.clientHeight;
      if (spot) {
        spot.style.transform = 'translate3d(' + (current.x * hw * 0.32).toFixed(1) + 'px,' + (current.y * hh * 0.22).toFixed(1) + 'px,0)';
      }
      if (figure) { figure.style.transform = 'translate3d(' + (current.x * -6).toFixed(2) + 'px,' + (current.y * -4).toFixed(2) + 'px,0)'; }
      if (copy) { copy.style.transform = 'translate3d(' + (current.x * 3).toFixed(2) + 'px,' + (current.y * 2).toFixed(2) + 'px,0)'; }

      // Oszilloskop: Welle läuft und klingt ab
      if (scopeReady) {
        wave.phase += 0.25;
        wave.amp *= 0.94;
        drawScope();
      }

      if (onScreen && !doc.hidden && !settled()) {
        rafId = window.requestAnimationFrame(frame);
      }
    }

    function kick() {
      if (!rafId && onScreen && !doc.hidden) { rafId = window.requestAnimationFrame(frame); }
    }

    heroEl.addEventListener('mousemove', function (event) {
      if (reduceMotion.matches) { return; }
      var rect = heroEl.getBoundingClientRect();
      target.x = Math.max(-1, Math.min(1, ((event.clientX - rect.left) / rect.width - 0.5) * 2));
      target.y = Math.max(-1, Math.min(1, ((event.clientY - rect.top) / rect.height - 0.5) * 2));

      if (scopeReady) {
        var sr = scope.getBoundingClientRect();
        var sx = event.clientX - (sr.left + sr.width / 2);
        var sy = event.clientY - sr.top;
        var now = performance.now();
        if (wave.lastX !== null) {
          var dt = Math.max(8, now - wave.lastT);
          var v = Math.hypot(event.clientX - wave.lastX, event.clientY - wave.lastY) / dt; // px/ms
          // Nähe zur Linie (waagrecht) bestimmt, wie stark die Bewegung durchschlägt
          var near = Math.exp(-(sx * sx) / (2 * 110 * 110));
          wave.amp = Math.min(1, Math.max(wave.amp, near * Math.min(1, v * 0.9)));
          if (near > 0.05) { wave.y = Math.max(0, Math.min(scopeH, sy)); }
        }
        wave.lastX = event.clientX; wave.lastY = event.clientY; wave.lastT = now;
      }
      kick();
    });
    heroEl.addEventListener('mouseleave', function () {
      target.x = 0; target.y = 0;
      wave.lastX = null;
      kick();
    });

    if ('IntersectionObserver' in window) {
      new IntersectionObserver(function (entries) {
        onScreen = entries[0].isIntersecting;
        if (onScreen) { kick(); }
      }).observe(heroEl);
    }
    doc.addEventListener('visibilitychange', kick);

    // Die Canvas-Linie übernimmt erst, wenn die CSS-Linie fertig „aufgezogen“ ist,
    // damit der Einstieg unverändert bleibt.
    if (scopeCtx && figure) {
      var takeOver = function () {
        if (scopeReady) { return; }
        scopeReady = true;
        figure.classList.add('has-scope');
        sizeScope();
        drawScope();
      };
      figure.addEventListener('animationend', function (event) {
        if (event.target === figure && event.pseudoElement === '::after') { takeOver(); }
      });
      window.setTimeout(takeOver, 1800);
      window.addEventListener('resize', function () {
        if (scopeReady) { sizeScope(); drawScope(); }
      });
    }

    reduceMotion.addEventListener && reduceMotion.addEventListener('change', function () {
      if (reduceMotion.matches) {
        target.x = 0; target.y = 0; wave.amp = 0;
        if (spot) { spot.style.transform = ''; }
        if (figure) { figure.style.transform = ''; }
        if (copy) { copy.style.transform = ''; }
        drawScope();
      }
    });
  }

  /* 8. Hero: Kopf folgt dem Mauszeiger ----------------------------------- */
  // 64 vorberechnete Einzelbilder (5,625° Schritte, 0 = oben, im Uhrzeigersinn) plus
  // center.webp. Pro Frame wird genau EIN Bild deckend gezeichnet – kein Überblenden, kein
  // Video-Seeking. Der Winkel Zeiger→Gesicht wird mit kürzestem Weg um den Kreis geglättet
  // (Faktor 0.26 ≈ 35 ms Reaktion); in der Nähe des Gesichts schaut sie direkt in die Kamera.
  var stage = doc.querySelector('[data-hero-character]');
  if (stage && finePointer.matches && !reduceMotion.matches) {
    var characterCanvas = stage.querySelector('.hero__character');
    var characterCtx = characterCanvas && characterCanvas.getContext ? characterCanvas.getContext('2d', { alpha: false }) : null;
    if (characterCtx) {
      var frameCount = parseInt(stage.getAttribute('data-frames'), 10) || 64;
      var frameDir = stage.getAttribute('data-dir');
      var faceFx = parseFloat(stage.getAttribute('data-face-x')) || 0.5;
      var faceFy = parseFloat(stage.getAttribute('data-face-y')) || 0.45;
      var background = stage.getAttribute('data-background') || '#000000';
      var LERP = 0.26;
      var DEADZONE = 0.12;              // Anteil der kürzeren Viewport-Seite
      var STEP = (Math.PI * 2) / frameCount;

      var images = [];
      var centerImage = null;
      var ready = false;
      var cssW = 0, cssH = 0, dpr = 1;
      var drawRect = null;              // Lage des Bildes auf der Canvas (cover)
      var smoothed = -Math.PI / 2;      // Startwinkel: oben
      var targetAngle = -Math.PI / 2;
      var wantCenter = true;            // ohne Zeiger: Blick in die Kamera
      var shownIndex = -2;              // -1 = center, 0..n-1 = Frame
      var pointerX = -1, pointerY = -1;
      var charRaf = 0, charOnScreen = true;

      function lerpAngle(a, b, t) {
        var d = b - a;
        while (d > Math.PI) { d -= Math.PI * 2; }
        while (d < -Math.PI) { d += Math.PI * 2; }
        return a + d * t;
      }

      function angleToIndex(angle) {
        // 0 = oben (-90°), dann im Uhrzeigersinn (Bildschirmkoordinaten, y nach unten)
        var turns = (angle + Math.PI / 2) / (Math.PI * 2);
        turns -= Math.floor(turns);
        return Math.round(turns * frameCount) % frameCount;
      }

      function sizeCanvas() {
        var rect = stage.getBoundingClientRect();
        cssW = Math.max(1, Math.round(rect.width));
        cssH = Math.max(1, Math.round(rect.height));
        dpr = Math.min(2, window.devicePixelRatio || 1);
        characterCanvas.width = Math.round(cssW * dpr);
        characterCanvas.height = Math.round(cssH * dpr);
        characterCtx.setTransform(dpr, 0, 0, dpr, 0, 0);
        characterCtx.imageSmoothingEnabled = true;
        characterCtx.imageSmoothingQuality = 'high';
        var img = centerImage;
        var scale = Math.max(cssW / img.naturalWidth, cssH / img.naturalHeight);   // wie object-fit: cover
        var w = img.naturalWidth * scale, h = img.naturalHeight * scale;
        drawRect = { x: (cssW - w) / 2, y: (cssH - h) / 2, w: w, h: h };
        shownIndex = -2;
      }

      function paint(index) {
        if (index === shownIndex || !drawRect) { return; }
        shownIndex = index;
        var img = index < 0 ? centerImage : images[index];
        characterCtx.fillStyle = background;
        characterCtx.fillRect(0, 0, cssW, cssH);
        characterCtx.globalAlpha = 1;
        characterCtx.drawImage(img, drawRect.x, drawRect.y, drawRect.w, drawRect.h);
      }

      function facePoint() {
        var rect = stage.getBoundingClientRect();
        return {
          x: rect.left + drawRect.x + drawRect.w * faceFx,
          y: rect.top + drawRect.y + drawRect.h * faceFy
        };
      }

      function updateTarget() {
        if (pointerX < 0) { wantCenter = true; return; }
        var face = facePoint();
        var dx = pointerX - face.x, dy = pointerY - face.y;
        var deadzone = DEADZONE * Math.min(window.innerWidth, window.innerHeight);
        wantCenter = Math.hypot(dx, dy) < deadzone;
        if (!wantCenter) { targetAngle = Math.atan2(dy, dx); }
      }

      function characterFrame() {
        charRaf = 0;
        if (!ready) { return; }
        if (reduceMotion.matches) { paint(-1); return; }
        updateTarget();
        if (wantCenter) {
          paint(-1);
          smoothed = targetAngle;   // beim Verlassen der Zone ohne Umweg weiterdrehen
          return;
        }
        smoothed = lerpAngle(smoothed, targetAngle, LERP);
        paint(angleToIndex(smoothed));
        var remaining = Math.abs(lerpAngle(smoothed, targetAngle, 1) - smoothed);
        if (remaining > STEP / 8 && charOnScreen && !doc.hidden) {
          charRaf = window.requestAnimationFrame(characterFrame);
        }
      }

      function characterKick() {
        if (ready && !charRaf && charOnScreen && !doc.hidden) {
          charRaf = window.requestAnimationFrame(characterFrame);
        }
      }

      function load(src) {
        return new Promise(function (resolve, reject) {
          var img = new Image();
          img.decoding = 'async';
          img.onload = function () {
            // Vollständig dekodieren, damit das erste Zeichnen jedes Bildes nicht ruckelt
            if (img.decode) { img.decode().then(function () { resolve(img); }, function () { resolve(img); }); } else { resolve(img); }
          };
          img.onerror = reject;
          img.src = src;
        });
      }

      function startCharacter() {
        var jobs = [load(frameDir + '/center.webp')];
        for (var i = 0; i < frameCount; i++) {
          jobs.push(load(frameDir + '/frame-' + (i < 10 ? '0' + i : i) + '.webp'));
        }
        Promise.all(jobs).then(function (loaded) {
          centerImage = loaded[0];
          images = loaded.slice(1);
          sizeCanvas();
          paint(-1);
          characterCanvas.hidden = false;
          ready = true;
          characterKick();
        }, function () {
          // Ein Bild fehlt: Standbild bleibt, keine Animation
        });
      }

      // Erst nach dem Laden der Seite starten, damit die Einzelbilder nicht mit dem
      // Hero-Bild und dem CSS konkurrieren.
      if (doc.readyState === 'complete') { startCharacter(); } else { window.addEventListener('load', startCharacter); }

      doc.addEventListener('mousemove', function (event) {
        pointerX = event.clientX; pointerY = event.clientY;
        characterKick();
      }, { passive: true });
      doc.addEventListener('mouseleave', function () { pointerX = -1; pointerY = -1; characterKick(); });
      window.addEventListener('scroll', characterKick, { passive: true });
      window.addEventListener('resize', function () {
        if (ready) { sizeCanvas(); paint(wantCenter ? -1 : angleToIndex(smoothed)); characterKick(); }
      });
      if ('IntersectionObserver' in window) {
        new IntersectionObserver(function (entries) {
          charOnScreen = entries[0].isIntersecting;
          characterKick();
        }).observe(stage);
      }
      doc.addEventListener('visibilitychange', characterKick);
      reduceMotion.addEventListener && reduceMotion.addEventListener('change', function () {
        if (reduceMotion.matches && ready) { pointerX = -1; paint(-1); }
      });
    }
  }
})();
