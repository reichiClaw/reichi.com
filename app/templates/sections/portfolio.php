<?php
/** Referenzen: drei getrennte Listen + Logo-Zeile „Clients“. */

declare(strict_types=1);

$portfolio = $content['portfolio'];
$clients = $content['clients'];
?>
<section class="section portfolio" id="portfolio" aria-labelledby="portfolio-title">
  <div class="section__inner">
    <header class="section__head">
      <p class="eyebrow">Portfolio</p>
      <h2 class="section__title" id="portfolio-title"><?= e($portfolio['title']) ?></h2>
      <p class="section__note"><?= e($portfolio['claim']) ?></p>
    </header>

    <div class="refs">
      <?php foreach ($portfolio['groups'] as $group): ?>
        <div class="refs__group" id="refs-<?= e($group['id']) ?>" data-reveal>
          <h3 class="refs__title">
            <?= e($group['title']) ?>
            <span class="refs__count" aria-hidden="true"><?= count($group['items']) ?></span>
          </h3>
          <ul class="refs__list">
            <?php foreach ($group['items'] as $name): ?>
              <li><?= e($name) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
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
          <?php $cls = 'clients__logo' . ($logo['light_bg'] ? ' clients__logo--plate' : ''); ?>
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
