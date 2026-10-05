<?php
/**
 * reichi.it – Hero: Überschrift, englische Zeile, Leistungs-Chips, Einleitung, zwei Aktionen
 * und rechts das Festivalgelände: ein isometrisches Modell (Bild, tools/build-it-ground.py) mit
 * dem Netzplan darüber (Inline-SVG: Leitungen und Knoten-Schilder). it.js legt eine Canvas
 * zwischen Bild und SVG (WLAN-Versorgungsfeld) und eine darüber (Datenpakete, simulierter
 * Leitungsausfall mit Umleitung, Mauszeiger als Endgerät) und tippt im Terminal-Streifen
 * darunter Statuszeilen.
 * Die Klassen hero/hero__copy/hero__figure stammen aus style.css (Layout + Parallax aus main.js).
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$hero = $content['hero'];
$labels = $hero['diagram_nodes'];
$ground = $hero['ground'];

// Knoten im 1000×1000-Koordinatenraum des Geländebilds: [x, y, Beschriftung, Lage des Schilds].
// Reihenfolge wie $labels. Der Punkt sitzt auf dem Objekt (Mast, Container, Turm …), das Schild
// hängt darüber – oder mit 'below' darunter, wo es sonst etwas verdecken würde (das Logo auf dem
// Containerdach) oder mit einem Nachbarn zusammenstieße.
$nodes = [
    [690, 215, $labels[0]],           // Uplink – Funkmast
    [741, 454, $labels[1], 'below'],  // Produktion (Core) – Bürocontainer, vordere linke Ecke am Boden
    [480, 430, $labels[2]],           // FOH – Mischturm
    [300, 200, $labels[3]],           // Stage – Bühnendach
    [590, 290, $labels[4]],           // Backstage – Nightliner und Pagoden
    [650, 560, $labels[5], 'below'],  // Kassa – Ticketbox am Eingang
    [240, 610, $labels[6]],           // Guest Wi-Fi – Camping
];
// Leitungen als Indexpaare (Versorger zuerst); 'dashed' = Reserve (im simulierten Störfall aktiv).
$edges = [
    [0, 1], [1, 4], [4, 3], [1, 2], [1, 5], [2, 6], [2, 3, 'dashed'],
];
// Knoten mit Access Point (Versorgungsfeld + Anmeldung des Zeiger-Endgeräts): alle außer Uplink und Core.
$accessPoints = [2, 3, 4, 5, 6];
$tagW = static fn(string $label): int => max(110, (int) (mb_strlen($label) * 15.5) + 44);
$groundSrc = asset('images/' . $ground['file'] . '-' . $ground['widths'][0] . '.webp');
$groundSet = implode(', ', array_map(
    static fn(int $w): string => asset('images/' . $ground['file'] . '-' . $w . '.webp') . ' ' . $w . 'w',
    $ground['widths']
));
?>
<section class="hero it-hero" aria-labelledby="hero-title">
  <div class="hero__inner">
    <div class="hero__copy">
      <p class="eyebrow hero__eyebrow"><?= e($hero['eyebrow']) ?></p>
      <h1 class="hero__title it-hero__title" id="hero-title"><?= e($hero['headline_a']) ?> <span class="it-hero__title-b"><?= e($hero['headline_b']) ?></span></h1>
      <p class="it-hero__tagline" lang="en"><?= e($hero['tagline']) ?></p>
      <ul class="hero__roles" aria-label="Leistungen">
        <?php foreach ($hero['chips'] as $chip): ?>
          <li><?= e($chip) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="hero__intro"><?= e($hero['intro']) ?></p>
      <div class="hero__actions">
        <a class="button button--primary" href="<?= e($hero['primary']['href']) ?>"><?= e($hero['primary']['label']) ?> <?= icon('arrow-right', 'icon button__icon') ?></a>
        <a class="button button--ghost" href="<?= e($hero['secondary']['href']) ?>"><?= e($hero['secondary']['label']) ?> <?= icon('arrow-down', 'icon button__icon') ?></a>
      </div>
    </div>

    <figure class="hero__figure it-net" data-it-net>
      <div class="it-net__panel">
        <img class="it-net__ground" src="<?= e($groundSrc) ?>" srcset="<?= e($groundSet) ?>" sizes="(max-width: 47.99em) 100vw, (max-width: 63.99em) 50vw, 32rem" width="<?= (int) $ground['widths'][0] ?>" height="<?= (int) $ground['widths'][0] ?>" alt="" decoding="async" fetchpriority="high">
        <canvas class="it-net__field" aria-hidden="true"></canvas>
        <svg class="it-net__svg" viewBox="0 0 1000 1000" role="img" aria-labelledby="it-net-title" focusable="false">
          <title id="it-net-title"><?= e($hero['diagram_label']) ?></title>
          <g class="it-net__edges" fill="none" stroke-linecap="round">
            <?php foreach ($edges as $edge): ?>
              <?php [$a, $b] = $edge; ?>
              <line class="it-net__edge<?= isset($edge[2]) ? ' it-net__edge--' . e($edge[2]) : '' ?>" x1="<?= $nodes[$a][0] ?>" y1="<?= $nodes[$a][1] ?>" x2="<?= $nodes[$b][0] ?>" y2="<?= $nodes[$b][1] ?>" data-a="<?= $a ?>" data-b="<?= $b ?>"/>
            <?php endforeach; ?>
          </g>
          <g class="it-net__nodes">
            <?php foreach ($nodes as $i => $node): ?>
              <?php
                [$x, $y, $label] = $node;
                $below = ($node[3] ?? '') === 'below';
                $w = $tagW($label);
                $core = $i === 1;
                $h = $core ? 50 : 44;
                $top = $below ? 22 : -22 - $h;
                $dir = $below ? 1 : -1;
              ?>
              <g class="it-net__node<?= $core ? ' it-net__node--core' : '' ?><?= $i === 0 ? ' it-net__node--uplink' : '' ?>" data-x="<?= $x ?>" data-y="<?= $y ?>"<?= in_array($i, $accessPoints, true) ? ' data-ap' : '' ?> transform="translate(<?= $x ?> <?= $y ?>)">
                <line class="it-net__stem" x1="0" y1="<?= 8 * $dir ?>" x2="0" y2="<?= 22 * $dir ?>"/>
                <rect class="it-net__tag" x="<?= -$w / 2 ?>" y="<?= $top ?>" width="<?= $w ?>" height="<?= $h ?>" rx="6"/>
                <text class="it-net__label" x="0" y="<?= $top + $h / 2 ?>" dominant-baseline="central" text-anchor="middle"><?= e($label) ?></text>
                <circle class="it-net__led" cx="0" cy="0" r="7"/>
              </g>
            <?php endforeach; ?>
          </g>
        </svg>
        <canvas class="it-net__canvas" aria-hidden="true"></canvas>
      </div>
      <ul class="it-net__log" aria-hidden="true"
          data-event-down="<?= e($hero['log_events']['down']) ?>"
          data-event-reroute="<?= e($hero['log_events']['reroute']) ?>"
          data-event-up="<?= e($hero['log_events']['up']) ?>">
        <?php foreach ($hero['log_lines'] as $line): ?>
          <li><?= e($line) ?></li>
        <?php endforeach; ?>
      </ul>
      <figcaption class="hero__caption">
        <span class="hero__caption-label"><?= e($hero['diagram_caption']) ?></span>
        <span class="hero__caption-meta"><span class="it-net__status" aria-hidden="true"></span><span class="it-net__meta" data-meta-ok="<?= e($hero['diagram_meta']) ?>" data-meta-failover="<?= e($hero['diagram_meta_failover']) ?>"><?= e($hero['diagram_meta']) ?></span></span>
      </figcaption>
    </figure>
  </div>
</section>
