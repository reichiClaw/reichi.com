/*
 * rstream.at – Ergänzung zu main.js: der Multiviewer im Hero lebt.
 *
 * 1. Der Timecode in der Kopfzeile läuft mit der Uhrzeit (25 Bilder pro Sekunde).
 * 2. Alle paar Sekunden schneidet die Regie: die Kamera aus der Vorschau (grünes Tally) geht ins
 *    Programm (rotes Tally), meist als harter Schnitt, ab und zu als Blende; dann wird eine neue
 *    Vorschau gewählt. Mit Maus wird die Kachel unter dem Zeiger zur Vorschau – der nächste Schnitt
 *    nimmt sie.
 * 3. Die beiden Tonpegel neben dem Programmbild atmen wie Musik (Zufallsbewegung mit Spitzenwert-
 *    Haltelinie; keine Messung).
 * 4. Der Terminal-Streifen unter dem Panel tippt die Zeilen aus content-rstream.php in Schleife
 *    und meldet jeden Schnitt.
 *
 * Alle Werte stehen im Markup (hero.php): Szenen und Beschriftungen an den Kacheln (data-scene,
 * data-name, data-shot), Texte der Meldungen am Terminal. Ohne JavaScript oder mit
 * prefers-reduced-motion steht ein fester Zustand (Cam 1 im Programm, Cam 2 in der Vorschau, die
 * ersten drei Terminal-Zeilen) – nichts hier ist nötig.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-rs-mv]');
  if (!root) { return; }
  var panel = root.querySelector('.rs-mv__panel');
  var tc = root.querySelector('.rs-mv__tc');
  var pgmTile = root.querySelector('.rs-mv__pgm');
  var pgmScene = pgmTile && pgmTile.querySelector('.rs-mv__scene:not(.rs-mv__scene--next)');
  var pgmNext = pgmTile && pgmTile.querySelector('.rs-mv__scene--next');
  var pgmShot = pgmTile && pgmTile.querySelector('.rs-mv__shot');
  var camEls = root.querySelectorAll('.rs-mv__cam');
  var meters = root.querySelectorAll('.rs-mv__meter-fill');
  var log = root.querySelector('.rs-mv__log');
  if (!panel || !pgmTile || !pgmScene || camEls.length < 2) { return; }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (reduceMotion.matches) { return; }

  function map(list, fn) { return Array.prototype.map.call(list, fn); }
  function rand(a, b) { return a + Math.random() * (b - a); }

  var cams = map(camEls, function (el, i) {
    return {
      el: el,
      index: i,
      scene: el.getAttribute('data-scene') || '',
      name: el.getAttribute('data-name') || '',
      shot: el.getAttribute('data-shot') || ''
    };
  });

  // Regiezustand: Programm und Vorschau wie im Markup (is-pgm / is-pvw), sonst 0 und 1
  var pgm = 0, pvw = 1;
  cams.forEach(function (c) {
    if (c.el.classList.contains('is-pgm')) { pgm = c.index; }
    if (c.el.classList.contains('is-pvw')) { pvw = c.index; }
  });
  if (pvw === pgm) { pvw = (pgm + 1) % cams.length; }

  var FPS = 25;
  var lastFrame = -1;
  function pad(n) { return (n < 10 ? '0' : '') + n; }
  function timecode(now) {
    if (!tc) { return; }
    var d = new Date(now);
    var frame = Math.floor(d.getMilliseconds() / 1000 * FPS);
    if (frame === lastFrame) { return; }
    lastFrame = frame;
    tc.textContent = pad(d.getHours()) + ':' + pad(d.getMinutes()) + ':' + pad(d.getSeconds()) + ':' + pad(frame);
  }

  // Terminal-Streifen: Zeilen aus dem Markup, danach übernimmt das Skript die Liste.
  var lines = [], events = {}, ticker = null;
  if (log) {
    lines = map(log.querySelectorAll('li'), function (el) { return el.textContent.trim(); }).filter(Boolean);
    events = {
      cut: log.getAttribute('data-event-cut') || '',
      cutKind: log.getAttribute('data-kind-cut') || 'cut',
      mixKind: log.getAttribute('data-kind-mix') || 'mix'
    };
    while (log.firstChild) { log.removeChild(log.firstChild); }
    root.classList.add('rs-mv--live');
    ticker = { queue: [], el: null, text: '', pos: 0, charT: 0, idle: 1.2, idx: 0 };
  }
  function say(text, kind) {
    if (ticker && text) { ticker.queue.push({ text: text, kind: kind }); }
  }
  function tick(dt) {
    if (!ticker) { return; }
    if (!ticker.el) {
      if (!ticker.queue.length && lines.length) {
        ticker.idle -= dt;
        if (ticker.idle <= 0) { say(lines[ticker.idx++ % lines.length], ''); }
      }
      if (ticker.queue.length) {
        var item = ticker.queue.shift();
        var li = document.createElement('li');
        li.className = 'rs-mv__line' + (item.kind ? ' rs-mv__line--' + item.kind : '');
        log.appendChild(li);
        while (log.children.length > 3) { log.removeChild(log.firstChild); }
        ticker.el = li;
        ticker.text = item.text;
        ticker.pos = 0;
        ticker.charT = 0;
      }
      return;
    }
    ticker.charT += dt;
    var perChar = 1 / 34;
    while (ticker.charT >= perChar && ticker.pos < ticker.text.length) {
      ticker.charT -= perChar;
      ticker.pos++;
    }
    ticker.el.textContent = ticker.text.slice(0, ticker.pos);
    if (ticker.pos >= ticker.text.length) {
      ticker.el = null;
      ticker.idle = 3.4 + Math.random() * 1.8;
    }
  }

  // Tally setzen: genau eine Kamera im Programm, eine in der Vorschau
  function tally() {
    cams.forEach(function (c) {
      c.el.classList.toggle('is-pgm', c.index === pgm);
      c.el.classList.toggle('is-pvw', c.index === pvw);
    });
  }
  function setScene(el, scene) {
    el.className = el.className.replace(/\brs-scene--[\w-]+/g, '').replace(/\s{2,}/g, ' ').trim();
    if (scene) { el.classList.add('rs-scene--' + scene); }
  }
  function showProgram(c) {
    setScene(pgmScene, c.scene);
    if (pgmShot) { pgmShot.textContent = c.name + ' · ' + c.shot; }
  }
  function pickPreview(exclude) {
    var pool = cams.filter(function (c) { return c.index !== pgm && c.index !== exclude; });
    if (!pool.length) { pool = cams.filter(function (c) { return c.index !== pgm; }); }
    return pool[Math.floor(Math.random() * pool.length)].index;
  }

  // Schnitt: Vorschau → Programm; 'mix' blendet über die obere Szenen-Ebene, 'cut' schaltet hart.
  var take = { timer: rand(3.5, 6), mixing: 0, mixTo: -1 };
  function cut() {
    var next = cams[pvw], prev = pgm;
    var mix = Math.random() < 0.28;
    if (mix) {
      setScene(pgmNext, next.scene);
      pgmTile.classList.add('is-mixing');
      take.mixing = 0.65;
      take.mixTo = next.index;
    } else {
      showProgram(next);
    }
    pgm = next.index;
    pvw = pickPreview(prev);
    tally();
    say((events.cut || '').replace('{kind}', mix ? events.mixKind : events.cutKind).replace('{cam}', next.name), 'cut');
    take.timer = rand(4, 8);
  }
  function mixStep(dt) {
    if (take.mixing <= 0) { return; }
    take.mixing -= dt;
    if (take.mixing <= 0) {
      showProgram(cams[take.mixTo]);
      pgmTile.classList.remove('is-mixing');
      setScene(pgmNext, '');
      take.mixTo = -1;
    }
  }

  // Tonpegel: zwei Kanäle, die um ein gemeinsames Niveau herum atmen, mit leichtem Unterschied L/R
  var audio = map(meters, function (el, i) {
    return { el: el, level: 0.6, peak: 0.7, peakHold: 0, phase: i * 1.7 };
  });
  var music = { t: 0, beat: 0 };
  function audioStep(dt) {
    music.t += dt;
    // Grundpegel: langsamer Verlauf plus Schlag im 4/4
    var base = 0.56 + Math.sin(music.t * 0.5) * 0.08 + Math.sin(music.t * 0.17) * 0.05;
    music.beat -= dt;
    var kick = 0;
    if (music.beat <= 0) { music.beat = 0.5; kick = 0.22; }
    audio.forEach(function (ch, i) {
      var target = base + kick + Math.sin(music.t * 3.1 + ch.phase) * 0.05 + (Math.random() - 0.5) * 0.08 + (i ? -0.015 : 0.015);
      target = Math.max(0.05, Math.min(1, target));
      // schnell hinauf, langsam hinunter – wie eine Pegelanzeige
      ch.level += (target - ch.level) * (target > ch.level ? 0.6 : 0.12);
      if (ch.level > ch.peak) { ch.peak = ch.level; ch.peakHold = 1.2; }
      else {
        ch.peakHold -= dt;
        if (ch.peakHold <= 0) { ch.peak = Math.max(ch.level, ch.peak - dt * 0.35); }
      }
      ch.el.style.setProperty('--level', ch.level.toFixed(3));
      ch.el.style.setProperty('--peak', ch.peak.toFixed(3));
    });
  }

  var rafId = 0, lastT = 0, onScreen = true, running = false;
  function step(now) {
    rafId = 0;
    var dt = lastT ? Math.min(0.1, (now - lastT) / 1000) : 0;
    lastT = now;

    timecode(Date.now());
    take.timer -= dt;
    if (take.timer <= 0 && take.mixing <= 0) { cut(); }
    mixStep(dt);
    audioStep(dt);
    tick(dt);

    if (running && onScreen && !document.hidden) {
      rafId = window.requestAnimationFrame(step);
    } else {
      lastT = 0;
    }
  }
  function start() {
    if (!running) { running = true; }
    if (!rafId && onScreen && !document.hidden) { rafId = window.requestAnimationFrame(step); }
  }

  // Mit Maus: die Kachel unter dem Zeiger wird zur Vorschau (nicht das laufende Programm)
  if (finePointer.matches) {
    cams.forEach(function (c) {
      c.el.addEventListener('mouseenter', function () {
        if (c.index === pgm || c.index === pvw) { return; }
        pvw = c.index;
        tally();
        start();
      });
    });
  }

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      onScreen = entries[0].isIntersecting;
      if (onScreen) { start(); }
    }).observe(panel);
  }
  document.addEventListener('visibilitychange', start);
  reduceMotion.addEventListener && reduceMotion.addEventListener('change', function () {
    if (reduceMotion.matches) {
      running = false;
      if (rafId) { window.cancelAnimationFrame(rafId); rafId = 0; }
    } else {
      start();
    }
  });

  tally();
  start();
})();
