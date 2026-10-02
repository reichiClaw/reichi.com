/*
 * reichi.it – Ergänzung zu main.js: der Netzplan im Hero lebt.
 * Auf einer Canvas über dem Inline-SVG laufen „Pakete“ vom Uplink über den Core zu den
 * Knoten (und gelegentlich zurück); mit Maus leuchtet der Zeiger die Umgebung weich an
 * und Knoten in Zeigernähe bekommen einen Ring. Ohne JavaScript, ohne Maus oder mit
 * prefers-reduced-motion bleibt der statische Plan – nichts hier ist nötig.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-it-net]');
  if (!root) { return; }
  var panel = root.querySelector('.it-net__panel');
  var svg = root.querySelector('.it-net__svg');
  var canvas = root.querySelector('.it-net__canvas');
  var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
  if (!panel || !svg || !ctx) { return; }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (reduceMotion.matches) { return; }

  var VIEW = 600; // viewBox-Kantenlänge des SVG
  var style = getComputedStyle(document.documentElement);
  var accent = (style.getPropertyValue('--accent') || '#0b6fb3').trim();
  var ink = (style.getPropertyValue('--ink') || '#15151a').trim();

  var nodes = Array.prototype.map.call(svg.querySelectorAll('.it-net__node'), function (el) {
    return { x: parseFloat(el.getAttribute('data-x')), y: parseFloat(el.getAttribute('data-y')) };
  });
  var edges = Array.prototype.map.call(svg.querySelectorAll('.it-net__edge'), function (el) {
    return {
      a: parseInt(el.getAttribute('data-a'), 10),
      b: parseInt(el.getAttribute('data-b'), 10),
      dashed: el.classList.contains('it-net__edge--dashed')
    };
  });
  if (nodes.length < 2 || !edges.length) { return; }

  // Baum: welche Leitungen führen von einem Knoten weiter weg vom Uplink (Index 0)?
  var downstream = nodes.map(function () { return []; });
  var upstream = nodes.map(function () { return null; });
  edges.forEach(function (edge, i) {
    if (edge.dashed) { return; }
    downstream[edge.a].push({ edge: i, to: edge.b });
    upstream[edge.b] = { edge: i, to: edge.a };
  });

  var width = 0, height = 0, dpr = 1, scale = 1, ox = 0, oy = 0;
  function size() {
    width = panel.clientWidth;
    height = panel.clientHeight;
    dpr = Math.min(2, window.devicePixelRatio || 1);
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    // „meet“-Abbildung wie das SVG: Quadrat mittig, Ränder bleiben frei
    scale = Math.min(width, height) / VIEW;
    ox = (width - VIEW * scale) / 2;
    oy = (height - VIEW * scale) / 2;
  }
  function px(v) { return ox + v * scale; }
  function py(v) { return oy + v * scale; }

  // Pakete: laufen entlang einer Leitung von a nach b (t 0 → 1)
  var pulses = [];
  var SPEED = 0.55; // Leitungen pro Sekunde
  function spawn(edgeIndex, forward, down) {
    pulses.push({ edge: edgeIndex, t: 0, forward: forward, down: down });
  }
  function wave() {
    if (downstream[0].length) { spawn(downstream[0][0].edge, true, true); }
  }
  function echo() {
    // Antwort eines zufälligen Blatt-Knotens zurück Richtung Uplink
    var leaves = [];
    nodes.forEach(function (n, i) { if (i !== 0 && !downstream[i].length && upstream[i]) { leaves.push(i); } });
    if (!leaves.length) { return; }
    var from = leaves[Math.floor(Math.random() * leaves.length)];
    spawn(upstream[from].edge, false, false);
  }

  var pointer = { x: 0, y: 0, tx: 0, ty: 0, active: false, alpha: 0 };
  var rings = []; // kurz aufleuchtende Ringe an Knoten in Zeigernähe
  var lastNear = -1;

  var running = false, rafId = 0, lastT = 0, waveTimer = 0, echoTimer = 0;
  var onScreen = true;

  function step(now) {
    rafId = 0;
    var dt = Math.min(0.05, (now - (lastT || now)) / 1000);
    lastT = now;

    waveTimer -= dt;
    if (waveTimer <= 0) { wave(); waveTimer = 2.6 + Math.random() * 1.2; }
    echoTimer -= dt;
    if (echoTimer <= 0) { echo(); echoTimer = 3.9 + Math.random() * 2.5; }

    ctx.clearRect(0, 0, width, height);

    // Zeigerlicht
    if (pointer.active || pointer.alpha > 0.01) {
      pointer.x += (pointer.tx - pointer.x) * 0.12;
      pointer.y += (pointer.ty - pointer.y) * 0.12;
      pointer.alpha += ((pointer.active ? 1 : 0) - pointer.alpha) * 0.1;
      var r = Math.max(width, height) * 0.28;
      var g = ctx.createRadialGradient(pointer.x, pointer.y, 0, pointer.x, pointer.y, r);
      g.addColorStop(0, hexAlpha(accent, 0.13 * pointer.alpha));
      g.addColorStop(1, hexAlpha(accent, 0));
      ctx.fillStyle = g;
      ctx.fillRect(0, 0, width, height);

      // nächster Knoten in Zeigernähe bekommt einen Ring
      var near = -1, best = 70 * scale;
      nodes.forEach(function (n, i) {
        var d = Math.hypot(px(n.x) - pointer.tx, py(n.y) - pointer.ty);
        if (d < best) { best = d; near = i; }
      });
      if (pointer.active && near !== -1 && near !== lastNear) {
        rings.push({ node: near, t: 0 });
        lastNear = near;
      }
      if (near === -1) { lastNear = -1; }
    }

    // Ringe
    for (var k = rings.length - 1; k >= 0; k--) {
      var ring = rings[k];
      ring.t += dt * 1.6;
      if (ring.t >= 1) { rings.splice(k, 1); continue; }
      var n = nodes[ring.node];
      ctx.beginPath();
      ctx.arc(px(n.x), py(n.y), (18 + ring.t * 26) * scale, 0, Math.PI * 2);
      ctx.strokeStyle = hexAlpha(accent, 0.55 * (1 - ring.t));
      ctx.lineWidth = 1.5;
      ctx.stroke();
    }

    // Pakete
    for (var i = pulses.length - 1; i >= 0; i--) {
      var p = pulses[i];
      p.t += dt * SPEED;
      var e = edges[p.edge];
      var from = nodes[p.forward ? e.a : e.b];
      var to = nodes[p.forward ? e.b : e.a];
      if (p.t >= 1) {
        pulses.splice(i, 1);
        var arrived = p.forward ? e.b : e.a;
        if (p.down) {
          downstream[arrived].forEach(function (next) { spawn(next.edge, true, true); });
        } else if (arrived !== 0 && upstream[arrived]) {
          spawn(upstream[arrived].edge, false, false);
        }
        continue;
      }
      var x = px(from.x + (to.x - from.x) * p.t);
      var y = py(from.y + (to.y - from.y) * p.t);
      var tailT = Math.max(0, p.t - 0.14);
      var tx = px(from.x + (to.x - from.x) * tailT);
      var ty = py(from.y + (to.y - from.y) * tailT);
      var color = p.down ? accent : ink;
      var grad = ctx.createLinearGradient(tx, ty, x, y);
      grad.addColorStop(0, hexAlpha(color, 0));
      grad.addColorStop(1, hexAlpha(color, 0.7));
      ctx.strokeStyle = grad;
      ctx.lineWidth = 2.5;
      ctx.lineCap = 'round';
      ctx.beginPath();
      ctx.moveTo(tx, ty);
      ctx.lineTo(x, y);
      ctx.stroke();
      ctx.beginPath();
      ctx.arc(x, y, 3.2, 0, Math.PI * 2);
      ctx.fillStyle = color;
      ctx.fill();
    }

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

  function hexAlpha(hex, alpha) {
    var h = hex.replace('#', '');
    if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
    var n = parseInt(h, 16);
    if (isNaN(n)) { return 'rgba(11,111,179,' + alpha + ')'; }
    return 'rgba(' + ((n >> 16) & 255) + ',' + ((n >> 8) & 255) + ',' + (n & 255) + ',' + alpha.toFixed(3) + ')';
  }

  if (finePointer.matches) {
    panel.addEventListener('mousemove', function (event) {
      var rect = panel.getBoundingClientRect();
      pointer.tx = event.clientX - rect.left;
      pointer.ty = event.clientY - rect.top;
      if (!pointer.active) { pointer.x = pointer.tx; pointer.y = pointer.ty; }
      pointer.active = true;
      start();
    });
    panel.addEventListener('mouseleave', function () { pointer.active = false; lastNear = -1; });
  }

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      onScreen = entries[0].isIntersecting;
      if (onScreen) { start(); }
    }).observe(panel);
  }
  document.addEventListener('visibilitychange', start);
  if ('ResizeObserver' in window) {
    new ResizeObserver(function () { size(); }).observe(panel);
  } else {
    window.addEventListener('resize', size);
  }
  reduceMotion.addEventListener && reduceMotion.addEventListener('change', function () {
    if (reduceMotion.matches) {
      running = false;
      pulses.length = 0;
      rings.length = 0;
      ctx.clearRect(0, 0, width, height);
    } else {
      start();
    }
  });

  size();
  waveTimer = 0.9; // erstes Paket kurz nach dem Einstieg
  echoTimer = 3;
  start();
})();
