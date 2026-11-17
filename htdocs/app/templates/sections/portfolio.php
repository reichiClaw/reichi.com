<?php
/**
 * Referenzen als Setlisten: drei Blätter Papier, mit Gaffa an die Wand geklebt (CSS, .sheet;
 * die beiden leeren Spans sind die Klebestreifen, das Blatt selbst zeichnet Knick und Eselsohr),
 * dazu die Logo-Zeile „Clients“. Die Namen bleiben echter Text in nummerierten Listen,
 * damit Suchmaschinen und Screenreader sie wie bisher lesen.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$portfolio = $content['portfolio'];
$clients = $content['clients'];
$sheetCount = count($portfolio['groups']);
?>
<section class="section portfolio" id="portfolio" aria-labelledby="portfolio-title">
  <div class="section__inner">
    <header class="section__head" data-reveal>
      <p class="eyebrow">Portfolio</p>
      <h2 class="section__title" id="portfolio-title"><?= e($portfolio['title']) ?></h2>
      <p class="section__note"><?= e($portfolio['claim']) ?></p>
    </header>

    <div class="setlist">
      <?php foreach ($portfolio['groups'] as $i => $group): ?>
        <article class="sheet sheet--<?= e($group['id']) ?>" id="refs-<?= e($group['id']) ?>" data-reveal>
          <span class="sheet__tape sheet__tape--a" aria-hidden="true"></span>
          <span class="sheet__tape sheet__tape--b" aria-hidden="true"></span>
          <header class="sheet__head">
            <p class="sheet__meta">
              <span><?= e($group['set_label'] ?? sprintf('Set %d/%d', $i + 1, $sheetCount)) ?></span>
              <span><?= count($group['items']) ?> <?= e($group['count_label'] ?? 'Titel') ?></span>
            </p>
            <h3 class="sheet__title"><?= e($group['title']) ?></h3>
          </header>
          <ol class="sheet__list">
            <?php foreach ($group['items'] as $name): ?>
              <li><?= e($name) ?></li>
            <?php endforeach; ?>
          </ol>
          <?php if (!empty($group['footer'])): ?>
            <p class="sheet__foot"><?= e($group['footer']) ?></p>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    </div>
  </div>

  <div class="clients" id="clients" aria-labelledby="clients-title">
    <div class="section__inner">
      <header class="clients__head">
        <h3 class="clients__title" id="clients-title"><?= e($clients['title']) ?></h3>
        <p class="clients__note"><?= e($clients['claim']) ?></p>
      </header>
      <ul class="clients__list">
        <?php foreach ($clients['items'] as $logo): ?>
          <?php $cls = 'clients__logo' . ($logo['light_bg'] ? ' clients__logo--plate' : '') . (!empty($logo['invert']) ? ' clients__logo--invert' : ''); ?>
          <li class="clients__item">
            <?php if (!empty($logo['url'])): ?>
              <a class="<?= $cls ?>" href="<?= e($logo['url']) ?>" rel="noopener noreferrer" target="_blank" title="<?= e($logo['name']) ?> (externer Link)">
                <?= logo_img($logo) ?>
              </a>
            <?php else: ?>
              <span class="<?= $cls ?>" title="<?= e($logo['name']) ?>"><?= logo_img($logo) ?></span>
            <?php endif; ?>
          </li>
        <?php endforeach; ?>
      </ul>
    </div>
  </div>
</section>
