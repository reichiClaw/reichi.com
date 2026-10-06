/*
 * reichi.it – Ergänzung zu main.js: der Netzplan über dem Festivalgelände im Hero lebt.
 *
 * 1. Pakete laufen vom Uplink über den Core zu den Knoten (und gelegentlich zurück).
 * 2. Alle paar Sekunden fällt eine Leitung aus: Marker auf der Leitung, der betroffene Teil des
 *    Geländes wird kurz dunkel, dann nimmt der Verkehr die gestrichelte Reserveverbindung, bis die
 *    Leitung zurück ist (Störung → Umleitung → wiederhergestellt). Statuszeile und Terminal melden es.
 * 3. Zwischen Geländebild und SVG atmet unter den Access-Point-Knoten ein weiches Versorgungsfeld
 *    (eigene Canvas). Mit Maus wird der Zeiger zum Endgerät, das sich beim nächsten AP anmeldet
 *    (Linie + Pegelanzeige; der Pegel ist aus dem Abstand gerechnet, keine Messung).
 * 4. Der Terminal-Streifen unter dem Plan tippt die Zeilen aus content-it.php in Schleife.
 * 5. (eigener Block am Ende) Strom im Textblock: an der leuchtenden zweiten Überschriftzeile
 *    springen kleine Funken von den Buchstaben, ab und zu kriecht ein Lichtbogen an der Grundlinie
 *    entlang; alle 11–16 s eine Überspannung – Überschrift und Tagline flackern, die Zeile flammt
 *    auf, Schläge laufen über die ganze Zeile, ein Blitz schlägt von der ersten Zeile über.
 *    Die Grafik rechts ist davon nicht betroffen.
 *
 * Koordinaten kommen aus dem SVG (data-x/data-y im viewBox-Raum des Bilds, hero.php). Ohne
 * JavaScript, ohne Maus (nur 1, 2, 3 ohne Endgerät, 4, 5) oder mit prefers-reduced-motion (gar nichts)
 * bleiben Bild und statischer Plan mit den ersten drei Terminal-Zeilen – nichts hier ist nötig.
 */
(function () {
  'use strict';

  var root = document.querySelector('[data-it-net]');
  if (!root) { return; }
  var panel = root.querySelector('.it-net__panel');
  var svg = root.querySelector('.it-net__svg');
  var canvas = root.querySelector('.it-net__canvas');
  var fieldCanvas = root.querySelector('.it-net__field');
  var log = root.querySelector('.it-net__log');
  var statusDot = root.querySelector('.it-net__status');
  var statusText = root.querySelector('.it-net__meta');
  var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
  var fctx = fieldCanvas && fieldCanvas.getContext ? fieldCanvas.getContext('2d') : null;
  if (!panel || !svg || !ctx) { return; }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  var finePointer = window.matchMedia('(hover: hover) and (pointer: fine)');
  if (reduceMotion.matches) { return; }

  // viewBox-Kantenlänge des SVG (= Koordinatenraum des Geländebilds); Längen unten in diesen Einheiten
  var VIEW = (svg.viewBox && svg.viewBox.baseVal && svg.viewBox.baseVal.width) || 1000;
  var U = VIEW / 1000;
  var style = getComputedStyle(document.documentElement);
  function token(name, fallback) { return (style.getPropertyValue(name) || fallback).trim(); }
  var accent = token('--accent', '#00e6c3');
  var accentInk = token('--accent-ink', '#00705f');
  var ink = token('--ink', '#15151a');
  var ink2 = token('--ink-2', '#4a4a52');
  var red = token('--red', '#d8371f');
  var ok = token('--ok', '#1f7a46');
  var panelBg = token('--panel', '#fbfaf7');
  var labelEl = svg.querySelector('.it-net__label');
  var monoFont = labelEl ? getComputedStyle(labelEl).fontFamily : 'monospace';

  function map(list, fn) { return Array.prototype.map.call(list, fn); }

  var nodes = map(svg.querySelectorAll('.it-net__node'), function (el) {
    var label = el.querySelector('.it-net__label');
    return {
      x: parseFloat(el.getAttribute('data-x')),
      y: parseFloat(el.getAttribute('data-y')),
      label: label ? label.textContent.trim() : '',
      ap: el.hasAttribute('data-ap')
    };
  });
  var edges = map(svg.querySelectorAll('.it-net__edge'), function (el) {
    return {
      a: parseInt(el.getAttribute('data-a'), 10),
      b: parseInt(el.getAttribute('data-b'), 10),
      dashed: el.classList.contains('it-net__edge--dashed'),
      el: el
    };
  });
  if (nodes.length < 2 || !edges.length) { return; }
  var aps = [];
  nodes.forEach(function (n, i) { if (n.ap) { aps.push(i); } });
  var reserve = -1;
  edges.forEach(function (e, i) { if (e.dashed && reserve === -1) { reserve = i; } });

  // Routing: Baum vom Uplink (Index 0) über die aktiven Leitungen. Die gestrichelte Reserve
  // zählt nur, wenn useReserve gesetzt ist; failedEdge ist gesperrt.
  var downstream, upstream, reachable;
  function route(failedEdge, useReserve) {
    downstream = nodes.map(function () { return []; });
    upstream = nodes.map(function () { return null; });
    reachable = nodes.map(function () { return false; });
    var queue = [0];
    reachable[0] = true;
    while (queue.length) {
      var n = queue.shift();
      edges.forEach(function (e, i) {
        if (i === failedEdge || (e.dashed && !useReserve)) { return; }
        var other = e.a === n ? e.b : (e.b === n ? e.a : -1);
        if (other === -1 || reachable[other]) { return; }
        reachable[other] = true;
        downstream[n].push({ edge: i, to: other });
        upstream[other] = { edge: i, to: n };
        queue.push(other);
      });
    }
  }
  // Welche Leitungen dürfen ausfallen, ohne dass über die Reserve ein Knoten unerreichbar bleibt?
  var failable = [];
  edges.forEach(function (e, i) {
    if (e.dashed) { return; }
    route(i, true);
    if (reachable.every(Boolean)) { failable.push(i); }
  });
  route(-1, false);

  var width = 0, height = 0, dpr = 1, scale = 1, ox = 0, oy = 0;
  function size() {
    width = panel.clientWidth;
    height = panel.clientHeight;
    dpr = Math.min(2, window.devicePixelRatio || 1);
    [canvas, fieldCanvas].forEach(function (c) {
      if (!c) { return; }
      c.width = Math.round(width * dpr);
      c.height = Math.round(height * dpr);
      c.getContext('2d').setTransform(dpr, 0, 0, dpr, 0, 0);
    });
    // „meet“-Abbildung wie das SVG: Quadrat mittig, Ränder bleiben frei
    scale = Math.min(width, height) / VIEW;
    ox = (width - VIEW * scale) / 2;
    oy = (height - VIEW * scale) / 2;
  }
  function px(v) { return ox + v * scale; }
  function py(v) { return oy + v * scale; }

  // Pakete: laufen entlang einer Leitung von from nach to (t 0 → 1)
  var pulses = [];
  var SPEED = 0.55; // Leitungen pro Sekunde
  function spawn(edgeIndex, from, to, down) {
    pulses.push({ edge: edgeIndex, from: from, to: to, t: 0, down: down });
  }
  function wave() {
    downstream[0].forEach(function (next) { spawn(next.edge, 0, next.to, true); });
  }
  function echo() {
    // Antwort eines zufälligen erreichbaren Blatt-Knotens zurück Richtung Uplink
    var leaves = [];
    nodes.forEach(function (n, i) { if (i !== 0 && reachable[i] && !downstream[i].length && upstream[i]) { leaves.push(i); } });
    if (!leaves.length) { return; }
    var from = leaves[Math.floor(Math.random() * leaves.length)];
    spawn(upstream[from].edge, from, upstream[from].to, false);
  }

  // Terminal-Streifen: Zeilen aus dem Markup, danach übernimmt das Skript die Liste.
  var lines = [], events = {}, ticker = null;
  if (log) {
    lines = map(log.querySelectorAll('li'), function (el) { return el.textContent.trim(); }).filter(Boolean);
    events = {
      down: log.getAttribute('data-event-down') || '',
      reroute: log.getAttribute('data-event-reroute') || '',
      up: log.getAttribute('data-event-up') || ''
    };
    while (log.firstChild) { log.removeChild(log.firstChild); }
    root.classList.add('it-net--live');
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
        li.className = 'it-net__line' + (item.kind ? ' it-net__line--' + item.kind : '');
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

  // Störung: eine Leitung fällt aus, der Verkehr nimmt die Reserve, dann kommt die Leitung zurück.
  var fault = { edge: -1, via: -1, phase: 'ok', t: 0, timer: 6 + Math.random() * 3 };
  function setStatus(warn) {
    if (statusDot) { statusDot.classList.toggle('is-warn', warn); }
    if (statusText) {
      var text = statusText.getAttribute(warn ? 'data-meta-failover' : 'data-meta-ok');
      if (text) { statusText.textContent = text; }
    }
  }
  function eventText(kind, e) {
    return (events[kind] || '')
      .replace('{a}', nodes[e.a].label)
      .replace('{b}', nodes[e.b].label)
      .replace('{via}', fault.via !== -1 ? nodes[fault.via].label : '');
  }
  function faultStart() {
    var edge = failable[Math.floor(Math.random() * failable.length)];
    var e = edges[edge], r = edges[reserve];
    fault.edge = edge;
    fault.phase = 'down';
    fault.t = 0;
    fault.via = r ? (r.a === e.b ? r.b : r.a) : -1;
    // Erst ist der Teil hinter der Leitung weg (Feld wird dunkel), die Umleitung folgt in faultReroute()
    route(edge, false);
    for (var i = pulses.length - 1; i >= 0; i--) {
      if (pulses[i].edge === edge) { pulses.splice(i, 1); }
    }
    e.el.classList.add('is-down');
    setStatus(true);
    say(eventText('down', e), 'down');
  }
  function faultReroute() {
    fault.phase = 'rerouted';
    route(fault.edge, true);
    if (edges[reserve]) { edges[reserve].el.classList.add('is-active'); }
    say(eventText('reroute', edges[fault.edge]), 'ok');
    if (fault.via !== -1) { rings.push({ node: fault.via, t: 0, color: accentInk }); }
  }
  function faultResolve(silent) {
    var e = edges[fault.edge];
    e.el.classList.remove('is-down');
    if (edges[reserve]) { edges[reserve].el.classList.remove('is-active'); }
    route(-1, false);
    setStatus(false);
    if (!silent) {
      rings.push({ node: e.a, t: 0, color: ok });
      rings.push({ node: e.b, t: 0, color: ok });
      say(eventText('up', e), 'up');
    }
    fault.phase = 'ok';
    fault.edge = -1;
    fault.timer = 9 + Math.random() * 5;
  }
  function faultStep(dt) {
    if (!failable.length || reserve === -1) { return; }
    if (fault.phase === 'ok') {
      fault.timer -= dt;
      if (fault.timer <= 0) { faultStart(); }
      return;
    }
    fault.t += dt;
    if (fault.phase === 'down' && fault.t > 0.8) { faultReroute(); }
    if (fault.t > 6) { faultResolve(false); }
  }
  function drawFaultMarker() {
    if (fault.edge === -1) { return; }
    var e = edges[fault.edge];
    var mx = px((nodes[e.a].x + nodes[e.b].x) / 2);
    var my = py((nodes[e.a].y + nodes[e.b].y) / 2);
    var pulse = 0.5 + 0.5 * Math.sin(time * 7);
    ctx.beginPath();
    ctx.arc(mx, my, 15 + pulse * 7, 0, Math.PI * 2);
    ctx.strokeStyle = hexAlpha(red, 0.45 * (1 - pulse));
    ctx.lineWidth = 1.5;
    ctx.stroke();
    ctx.beginPath();
    ctx.arc(mx, my, 8.5 + pulse * 1.5, 0, Math.PI * 2);
    ctx.fillStyle = hexAlpha(red, 0.92);
    ctx.fill();
    ctx.strokeStyle = panelBg;
    ctx.lineWidth = 2;
    ctx.lineCap = 'round';
    ctx.beginPath();
    ctx.moveTo(mx - 3.2, my - 3.2); ctx.lineTo(mx + 3.2, my + 3.2);
    ctx.moveTo(mx + 3.2, my - 3.2); ctx.lineTo(mx - 3.2, my + 3.2);
    ctx.stroke();
  }

  // Versorgungsfeld der Access Points (hinter dem SVG)
  var fieldAlpha = nodes.map(function () { return 1; });
  function drawField() {
    if (!fctx) { return; }
    fctx.clearRect(0, 0, width, height);
    aps.forEach(function (i, k) {
      var n = nodes[i];
      fieldAlpha[i] += ((reachable[i] ? 1 : 0.18) - fieldAlpha[i]) * 0.08;
      var breathe = 0.5 + 0.5 * Math.sin(time * 0.55 + k * 1.9);
      var r = (145 + 36 * breathe) * U * scale;
      var x = px(n.x), y = py(n.y), a = fieldAlpha[i];
      var g = fctx.createRadialGradient(x, y, 0, x, y, r);
      g.addColorStop(0, hexAlpha(accent, 0.26 * a));
      g.addColorStop(0.5, hexAlpha(accent, 0.12 * a));
      g.addColorStop(1, hexAlpha(accent, 0));
      fctx.fillStyle = g;
      fctx.beginPath();
      fctx.arc(x, y, r, 0, Math.PI * 2);
      fctx.fill();
      fctx.strokeStyle = hexAlpha(accentInk, 0.11 * a);
      fctx.lineWidth = 1;
      fctx.stroke();
    });
  }

  // Zeiger als Endgerät
  var pointer = { x: 0, y: 0, tx: 0, ty: 0, active: false, alpha: 0 };
  var assoc = -1, assocDist = 0, dashOffset = 0;
  var rings = []; // kurz aufleuchtende Ringe an Knoten
  function roundRect(x, y, w, h, r) {
    ctx.beginPath();
    ctx.moveTo(x + r, y);
    ctx.arcTo(x + w, y, x + w, y + h, r);
    ctx.arcTo(x + w, y + h, x, y + h, r);
    ctx.arcTo(x, y + h, x, y, r);
    ctx.arcTo(x, y, x + w, y, r);
    ctx.closePath();
  }
  function drawClient(dt) {
    if (!pointer.active && pointer.alpha <= 0.01) { assoc = -1; return; }
    pointer.x += (pointer.tx - pointer.x) * 0.12;
    pointer.y += (pointer.ty - pointer.y) * 0.12;
    pointer.alpha += ((pointer.active ? 1 : 0) - pointer.alpha) * 0.1;
    var a = pointer.alpha;

    // Zeigerlicht
    var r = Math.max(width, height) * 0.28;
    var g = ctx.createRadialGradient(pointer.x, pointer.y, 0, pointer.x, pointer.y, r);
    g.addColorStop(0, hexAlpha(accent, 0.2 * a));
    g.addColorStop(1, hexAlpha(accent, 0));
    ctx.fillStyle = g;
    ctx.fillRect(0, 0, width, height);

    // Anmeldung beim nächsten erreichbaren Access Point
    var near = -1, best = 290 * U * scale;
    aps.forEach(function (i) {
      if (!reachable[i]) { return; }
      var d = Math.hypot(px(nodes[i].x) - pointer.x, py(nodes[i].y) - pointer.y);
      if (d < best) { best = d; near = i; }
    });
    if (pointer.active) {
      if (near !== -1 && near !== assoc) { rings.push({ node: near, t: 0, color: accentInk }); }
      assoc = near;
    }
    if (assoc === -1) { return; }
    if (pointer.active) { assocDist = best; }
    var n = nodes[assoc], ax = px(n.x), ay = py(n.y);

    dashOffset -= dt * 40;
    ctx.save();
    ctx.setLineDash([3, 5]);
    ctx.lineDashOffset = dashOffset;
    ctx.strokeStyle = hexAlpha(accentInk, 0.6 * a);
    ctx.lineWidth = 1.25;
    ctx.beginPath();
    ctx.moveTo(pointer.x, pointer.y);
    ctx.lineTo(ax, ay);
    ctx.stroke();
    ctx.restore();

    ctx.beginPath();
    ctx.arc(pointer.x, pointer.y, 4, 0, Math.PI * 2);
    ctx.fillStyle = hexAlpha(ink, a);
    ctx.fill();
    ctx.beginPath();
    ctx.arc(pointer.x, pointer.y, 9, 0, Math.PI * 2);
    ctx.strokeStyle = hexAlpha(accentInk, 0.75 * a);
    ctx.lineWidth = 1.5;
    ctx.stroke();

    // Pegel aus dem Abstand gerechnet (Schema, keine Messung)
    var rssi = Math.max(-88, Math.round(-38 - (assocDist / (U * scale)) * 0.18));
    var text = n.label.toUpperCase() + '  ' + rssi + ' dBm';
    ctx.font = '500 11px ' + monoFont;
    ctx.textBaseline = 'middle';
    var tw = Math.ceil(ctx.measureText(text).width) + 14;
    var bx = Math.min(width - tw - 4, Math.max(4, pointer.x + 14));
    var by = Math.max(4, pointer.y - 32);
    roundRect(bx, by, tw, 21, 3);
    ctx.fillStyle = hexAlpha(panelBg, 0.94 * a);
    ctx.fill();
    ctx.strokeStyle = hexAlpha(ink, 0.3 * a);
    ctx.lineWidth = 1;
    ctx.stroke();
    ctx.fillStyle = hexAlpha(ink2, a);
    ctx.fillText(text, bx + 7, by + 10.5);
  }

  var running = false, rafId = 0, lastT = 0, time = 0, waveTimer = 0, echoTimer = 0;
  var onScreen = true;

  function step(now) {
    rafId = 0;
    var dt = Math.min(0.05, (now - (lastT || now)) / 1000);
    lastT = now;
    time += dt;

    waveTimer -= dt;
    if (waveTimer <= 0) { wave(); waveTimer = 2.6 + Math.random() * 1.2; }
    echoTimer -= dt;
    if (echoTimer <= 0) { echo(); echoTimer = 3.9 + Math.random() * 2.5; }
    faultStep(dt);

    drawField();
    ctx.clearRect(0, 0, width, height);
    drawClient(dt);

    // Ringe
    for (var k = rings.length - 1; k >= 0; k--) {
      var ring = rings[k];
      ring.t += dt * 1.6;
      if (ring.t >= 1) { rings.splice(k, 1); continue; }
      var rn = nodes[ring.node];
      ctx.beginPath();
      ctx.arc(px(rn.x), py(rn.y), (30 + ring.t * 44) * U * scale, 0, Math.PI * 2);
      ctx.strokeStyle = hexAlpha(ring.color, 0.65 * (1 - ring.t));
      ctx.lineWidth = 1.5;
      ctx.stroke();
    }

    // Pakete
    for (var i = pulses.length - 1; i >= 0; i--) {
      var p = pulses[i];
      p.t += dt * SPEED;
      var from = nodes[p.from];
      var to = nodes[p.to];
      if (p.t >= 1) {
        pulses.splice(i, 1);
        if (p.down) {
          downstream[p.to].forEach(function (next) { spawn(next.edge, p.to, next.to, true); });
        } else if (p.to !== 0 && upstream[p.to]) {
          spawn(upstream[p.to].edge, p.to, upstream[p.to].to, false);
        }
        continue;
      }
      var x = px(from.x + (to.x - from.x) * p.t);
      var y = py(from.y + (to.y - from.y) * p.t);
      var tailT = Math.max(0, p.t - 0.14);
      var tx = px(from.x + (to.x - from.x) * tailT);
      var ty = py(from.y + (to.y - from.y) * tailT);
      // abwärts: elektrischer Schweif mit dunklem Kern (das helle Blaugrün allein wäre auf Papier zu blass),
      // aufwärts (Echo): Tinte
      var color = p.down ? accent : ink;
      var grad = ctx.createLinearGradient(tx, ty, x, y);
      grad.addColorStop(0, hexAlpha(color, 0));
      grad.addColorStop(1, hexAlpha(color, p.down ? 0.95 : 0.7));
      ctx.strokeStyle = grad;
      ctx.lineWidth = p.down ? 3 : 2.5;
      ctx.lineCap = 'round';
      ctx.beginPath();
      ctx.moveTo(tx, ty);
      ctx.lineTo(x, y);
      ctx.stroke();
      if (p.down) {
        ctx.beginPath();
        ctx.arc(x, y, 6, 0, Math.PI * 2);
        ctx.fillStyle = hexAlpha(accent, 0.45);
        ctx.fill();
      }
      ctx.beginPath();
      ctx.arc(x, y, 3, 0, Math.PI * 2);
      ctx.fillStyle = p.down ? accentInk : ink;
      ctx.fill();
    }

    drawFaultMarker();
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

  function hexAlpha(hex, alpha) {
    var h = hex.replace('#', '');
    if (h.length === 3) { h = h[0] + h[0] + h[1] + h[1] + h[2] + h[2]; }
    var n = parseInt(h, 16);
    if (isNaN(n)) { return 'rgba(0,112,95,' + alpha + ')'; }
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
    panel.addEventListener('mouseleave', function () { pointer.active = false; });
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
      if (fault.edge !== -1) { faultResolve(true); }
      ctx.clearRect(0, 0, width, height);
      if (fctx) { fctx.clearRect(0, 0, width, height); }
    } else {
      start();
    }
  });

  size();
  waveTimer = 0.9; // erstes Paket kurz nach dem Einstieg
  echoTimer = 3;
  start();
})();

/*
 * 5. Strom im Textblock. Eigene Canvas (.it-spark) über .hero__copy, 3rem größer als der Block
 * (it.css). Alle Positionen kommen aus den Zeilenboxen des Texts (Range.getClientRects), also
 * stimmen sie bei jedem Umbruch und jeder Breite. Läuft nur sichtbar, ohne Maus-Bedingung, nicht
 * mit prefers-reduced-motion. Die beiden Überschriftzeilen sind die „Leiter“: Zeile A („IT fürs
 * Event.“) schwarz, Zeile B (.it-hero__title-b) leuchtend – an B passiert fast alles.
 */
(function () {
  'use strict';

  var copy = document.querySelector('[data-it-spark]');
  var canvas = copy && copy.querySelector('.it-spark');
  var title = copy && copy.querySelector('.it-hero__title');
  var titleB = title && title.querySelector('.it-hero__title-b');
  var tagline = copy && copy.querySelector('.it-hero__tagline');
  var ctx = canvas && canvas.getContext ? canvas.getContext('2d') : null;
  if (!copy || !ctx || !titleB) { return; }

  var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
  if (reduceMotion.matches) { return; }

  var style = getComputedStyle(document.documentElement);
  function token(name, fallback) { return (style.getPropertyValue(name) || fallback).trim(); }
  var accent = token('--accent', '#00e6c3');
  var accentInk = token('--accent-ink', '#00705f');
  var core = token('--accent-core', '#eafffb');

  // Zeitabstände in Sekunden: [min, Spanne]
  var CRACKLE_EVERY = [1.2, 2];   // kleiner Funke an einer Buchstabenkante
  var CRAWL_EVERY = [4.5, 4];     // Kriechstrom an der Grundlinie
  var SURGE_EVERY = [11, 5];      // Überspannung
  var SURGE_FIRST = 7;            // erste Überspannung nach dem Laden
  var SURGE_LEN = 1.4;

  var width = 0, height = 0, dpr = 1, pad = 0;
  function size() {
    width = canvas.clientWidth;
    height = canvas.clientHeight;
    dpr = Math.min(2, window.devicePixelRatio || 1);
    canvas.width = Math.round(width * dpr);
    canvas.height = Math.round(height * dpr);
    ctx.setTransform(dpr, 0, 0, dpr, 0, 0);
    // Überstand der Canvas über den Textblock (3rem laut CSS) – gemessen
    pad = copy.getBoundingClientRect().left - canvas.getBoundingClientRect().left;
  }

  // Zeilenboxen eines Knotens in Canvas-Koordinaten
  function rectsOf(range) {
    var base = copy.getBoundingClientRect();
    return Array.prototype.filter.call(range.getClientRects(), function (r) { return r.width > 30 && r.height > 10; })
      .map(function (r) { return { x: r.left - base.left + pad, y: r.top - base.top + pad, w: r.width, h: r.height }; });
  }
  function lineA() {
    var range = document.createRange();
    range.setStart(title, 0);
    range.setEndBefore(titleB);
    return rectsOf(range);
  }
  function lineB() {
    var range = document.createRange();
    range.selectNodeContents(titleB);
    return rectsOf(range);
  }
  function pick(list) { return list[Math.floor(Math.random() * list.length)]; }

  // Blitzlinie: Punkte mit Querversatz, an den Enden ohne Versatz
  function bolt(x1, y1, x2, y2, jag, segs) {
    var pts = [[x1, y1]];
    var dx = x2 - x1, dy = y2 - y1, len = Math.hypot(dx, dy) || 1;
    var nx = -dy / len, ny = dx / len;
    for (var i = 1; i < segs; i++) {
      var t = i / segs, env = Math.sin(t * Math.PI);
      var off = (Math.random() * 2 - 1) * jag * env;
      pts.push([x1 + dx * t + nx * off, y1 + dy * t + ny * off]);
    }
    pts.push([x2, y2]);
    return pts;
  }
  function stroke(pts) {
    ctx.beginPath();
    ctx.moveTo(pts[0][0], pts[0][1]);
    for (var i = 1; i < pts.length; i++) { ctx.lineTo(pts[i][0], pts[i][1]); }
    ctx.stroke();
  }
  function drawBolt(pts, alpha, haloW, coreW) {
    ctx.save();
    ctx.lineCap = 'round';
    ctx.lineJoin = 'round';
    ctx.globalAlpha = alpha * 0.4; ctx.strokeStyle = accentInk; ctx.lineWidth = haloW + 2.5; stroke(pts);
    ctx.globalAlpha = alpha * 0.85; ctx.strokeStyle = accent; ctx.lineWidth = haloW; stroke(pts);
    ctx.globalAlpha = alpha; ctx.strokeStyle = core; ctx.lineWidth = coreW; stroke(pts);
    ctx.restore();
  }

  var arcs = []; // { pts, t, life, hw, cw, crawl }

  // kleiner Funke an der Ober- oder Unterkante einer leuchtenden Zeile
  function crackle(strong) {
    var lines = lineB();
    if (!lines.length) { return; }
    var r = pick(lines);
    var top = Math.random() < 0.5;
    var x = r.x + 8 + Math.random() * (r.w - 16);
    var y = top ? r.y + r.h * 0.12 : r.y + r.h * 0.88;
    var a = (top ? -Math.PI / 2 : Math.PI / 2) + (Math.random() - 0.5) * 1.6;
    var l = (strong ? 16 : 9) + Math.random() * (strong ? 26 : 12);
    arcs.push({ pts: bolt(x, y, x + Math.cos(a) * l, y + Math.sin(a) * l, 4, 4), t: 0, life: 0.08 + Math.random() * 0.07, hw: 2.4, cw: 1.1 });
  }
  // Kriechstrom entlang der Grundlinie (oder Oberkante) – ganz oder ein Stück
  function crawl(r, top, strong) {
    var y = top ? r.y + r.h * 0.1 : r.y + r.h * 0.9;
    var x1 = r.x - 4, x2 = r.x + r.w + 4;
    if (!strong) {
      var s = Math.random() * 0.5, e = s + 0.3 + Math.random() * 0.3;
      x2 = x1 + (r.w + 8) * Math.min(1, e);
      x1 = x1 + (r.w + 8) * s;
    }
    arcs.push({
      pts: bolt(x1, y, x2, y, strong ? 5 : 3, Math.max(6, Math.round((x2 - x1) / 12))),
      t: 0, life: (strong ? 0.22 : 0.16) + Math.random() * 0.1, hw: strong ? 3 : 2.2, cw: strong ? 1.4 : 1, crawl: true
    });
  }
  // Überschlag von Zeile A auf Zeile B
  function bridge() {
    var a = lineA()[0], b = lineB()[0];
    if (!a || !b) { return; }
    var x = b.x + 20 + Math.random() * Math.min(a.w, b.w) * 0.8;
    arcs.push({ pts: bolt(x + (Math.random() - 0.5) * 30, a.y + a.h * 0.92, x, b.y + b.h * 0.1, 7, 6), t: 0, life: 0.14 + Math.random() * 0.08, hw: 2.6, cw: 1.2 });
  }
  // kurzer Sprung zwischen zwei Zeilen von B, wenn sie umbricht
  function hop() {
    var lines = lineB();
    if (lines.length < 2) { return; }
    var x = lines[1].x + 10 + Math.random() * (Math.min(lines[0].w, lines[1].w) - 20);
    arcs.push({ pts: bolt(x + (Math.random() - 0.5) * 16, lines[0].y + lines[0].h * 0.9, x, lines[1].y + lines[1].h * 0.1, 4, 4), t: 0, life: 0.12, hw: 2.2, cw: 1 });
  }

  var surge = null;
  var surgeTimer = SURGE_FIRST, crackleTimer = 1, crawlTimer = 2.5;
  function after(base) { return base[0] + Math.random() * base[1]; }
  function startSurge() {
    surge = { t: 0, fired: {} };
    titleB.classList.add('is-surge');
    title.classList.add('is-flicker');
    if (tagline) { tagline.classList.add('is-flicker'); }
    window.setTimeout(function () { titleB.classList.remove('is-surge'); }, 1100);
    window.setTimeout(function () {
      title.classList.remove('is-flicker');
      if (tagline) { tagline.classList.remove('is-flicker'); }
    }, 850);
    surgeTimer = after(SURGE_EVERY);
  }
  function surgeStep(dt) {
    surge.t += dt;
    var lines = lineB();
    // drei Schläge im Abstand von 80 ms über jede leuchtende Zeile, unten und (zweimal) oben
    for (var k = 0; k < 3; k++) {
      var when = 0.02 + k * 0.08;
      if (surge.t >= when && !surge.fired[k]) {
        surge.fired[k] = true;
        lines.forEach(function (r) {
          crawl(r, false, true);
          if (k < 2) { crawl(r, true, true); }
        });
        if (k === 0) { bridge(); hop(); }
        if (k === 2) { bridge(); }
      }
    }
    if (surge.t < 0.6 && Math.random() < 0.55) { crackle(true); }
    if (surge.t > SURGE_LEN) { surge = null; }
  }

  var running = false, rafId = 0, lastT = 0, onScreen = true;
  function step(now) {
    rafId = 0;
    var dt = Math.min(0.05, (now - (lastT || now)) / 1000);
    lastT = now;

    crackleTimer -= dt;
    if (crackleTimer <= 0) { crackle(false); crackleTimer = after(CRACKLE_EVERY); }
    crawlTimer -= dt;
    if (crawlTimer <= 0) {
      var lines = lineB();
      if (lines.length) { crawl(pick(lines), Math.random() < 0.3, false); }
      if (Math.random() < 0.35) { hop(); }
      crawlTimer = after(CRAWL_EVERY);
    }
    surgeTimer -= dt;
    if (surgeTimer <= 0 && !surge) { startSurge(); }
    if (surge) { surgeStep(dt); }

    ctx.clearRect(0, 0, width, height);
    for (var i = arcs.length - 1; i >= 0; i--) {
      var a = arcs[i];
      a.t += dt;
      if (a.t >= a.life) { arcs.splice(i, 1); continue; }
      var k = a.t / a.life;
      var alpha = (k < 0.2 ? k / 0.2 : 1 - (k - 0.2) / 0.8) * (0.7 + 0.3 * Math.random());
      // innere Punkte leicht zittern lassen, damit der Bogen „kriecht“
      if (Math.random() < 0.6) {
        for (var j = 1; j < a.pts.length - 1; j++) {
          a.pts[j][0] += (Math.random() - 0.5) * 1.4;
          a.pts[j][1] += (Math.random() - 0.5) * (a.crawl ? 1.8 : 1.4);
        }
      }
      drawBolt(a.pts, alpha, a.hw, a.cw);
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

  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      onScreen = entries[0].isIntersecting;
      if (onScreen) { start(); }
    }).observe(copy);
  }
  document.addEventListener('visibilitychange', start);
  if ('ResizeObserver' in window) {
    new ResizeObserver(function () { size(); }).observe(copy);
  } else {
    window.addEventListener('resize', size);
  }
  reduceMotion.addEventListener && reduceMotion.addEventListener('change', function () {
    if (reduceMotion.matches) {
      running = false;
      arcs.length = 0;
      surge = null;
      titleB.classList.remove('is-surge');
      title.classList.remove('is-flicker');
      if (tagline) { tagline.classList.remove('is-flicker'); }
      ctx.clearRect(0, 0, width, height);
    } else {
      start();
    }
  });

  size();
  start();
})();
