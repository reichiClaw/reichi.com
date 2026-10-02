<?php
/**
 * reichi.it – Hero: Überschrift, englische Zeile, Leistungs-Chips, Einleitung, zwei Aktionen
 * und rechts ein schematischer Netzplan (Inline-SVG). it.js legt eine Canvas darüber, auf der
 * Datenpakete entlang der Leitungen laufen und der Mauszeiger die Knoten „anleuchtet“.
 * Die Klassen hero/hero__copy/hero__figure stammen aus style.css (Layout + Parallax aus main.js).
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$hero = $content['hero'];
$labels = $hero['diagram_nodes'];

// Knoten des Netzplans im 600×600-Koordinatenraum: [x, y, Beschriftung]. Reihenfolge wie $labels.
$nodes = [
    [300, 72, $labels[0]],   // Uplink
    [300, 232, $labels[1]],  // Produktion (Core)
    [140, 350, $labels[2]],  // FOH
    [460, 350, $labels[3]],  // Stage
    [478, 500, $labels[4]],  // Backstage
    [118, 500, $labels[5]],  // Kassa
    [300, 528, $labels[6]],  // Guest Wi-Fi
];
// Leitungen als Indexpaare; 'dashed' = redundante Querverbindung.
$edges = [
    [0, 1], [1, 2], [1, 3], [2, 5], [3, 4], [1, 6], [2, 3, 'dashed'],
];
$tagW = static fn(string $label): int => max(64, (int) (mb_strlen($label) * 9.2) + 26);
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
        <svg class="it-net__svg" viewBox="0 0 600 600" role="img" aria-labelledby="it-net-title" focusable="false">
          <title id="it-net-title"><?= e($hero['diagram_label']) ?></title>
          <g class="it-net__edges" fill="none" stroke-linecap="round">
            <?php foreach ($edges as $edge): ?>
              <?php [$a, $b] = $edge; ?>
              <line class="it-net__edge<?= isset($edge[2]) ? ' it-net__edge--' . e($edge[2]) : '' ?>" x1="<?= $nodes[$a][0] ?>" y1="<?= $nodes[$a][1] ?>" x2="<?= $nodes[$b][0] ?>" y2="<?= $nodes[$b][1] ?>" data-a="<?= $a ?>" data-b="<?= $b ?>"/>
            <?php endforeach; ?>
          </g>
          <g class="it-net__nodes">
            <?php foreach ($nodes as $i => [$x, $y, $label]): ?>
              <?php $w = $tagW($label); $core = $i === 1; $h = $core ? 40 : 34; ?>
              <g class="it-net__node<?= $core ? ' it-net__node--core' : '' ?><?= $i === 0 ? ' it-net__node--uplink' : '' ?>" data-x="<?= $x ?>" data-y="<?= $y ?>" transform="translate(<?= $x ?> <?= $y ?>)">
                <rect class="it-net__tag" x="<?= -$w / 2 ?>" y="<?= -$h / 2 ?>" width="<?= $w ?>" height="<?= $h ?>" rx="4"/>
                <circle class="it-net__led" cx="<?= -$w / 2 + 13 ?>" cy="0" r="3.5"/>
                <text class="it-net__label" x="<?= -$w / 2 + 24 ?>" y="0" dominant-baseline="central"><?= e($label) ?></text>
              </g>
            <?php endforeach; ?>
          </g>
        </svg>
        <canvas class="it-net__canvas" aria-hidden="true"></canvas>
      </div>
      <figcaption class="hero__caption">
        <span class="hero__caption-label"><?= e($hero['diagram_caption']) ?></span>
        <span class="hero__caption-meta"><span class="it-net__status" aria-hidden="true"></span><?= e($hero['diagram_meta']) ?></span>
      </figcaption>
    </figure>
  </div>
</section>
