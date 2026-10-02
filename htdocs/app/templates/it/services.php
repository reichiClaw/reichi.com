<?php
/** reichi.it – Leistungen: drei nummerierte Karten (Aufbauen, Betreiben, Beraten) mit Stichpunkten. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$services = $content['services'];
?>
<section class="section services" id="leistungen" aria-labelledby="services-title">
  <div class="section__inner">
    <header class="section__head section__head--split" data-reveal>
      <div>
        <p class="eyebrow" lang="en"><?= e($services['eyebrow']) ?></p>
        <h2 class="section__title" id="services-title"><?= e($services['title']) ?></h2>
      </div>
      <p class="section__note"><?= e($services['note']) ?></p>
    </header>

    <ol class="services__list">
      <?php foreach ($services['items'] as $i => $item): ?>
        <li class="services__item" id="<?= e($item['id']) ?>" data-reveal>
          <div class="services__head">
            <span class="services__index" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
            <span class="services__tag" lang="en"><?= e($item['tag']) ?></span>
          </div>
          <h3 class="services__name"><?= e($item['name']) ?></h3>
          <p class="services__text"><?= e($item['text']) ?></p>
          <ul class="services__points">
            <?php foreach ($item['points'] as $point): ?>
              <li><?= e($point) ?></li>
            <?php endforeach; ?>
          </ul>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
