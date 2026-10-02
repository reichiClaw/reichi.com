<?php
/** reichi.it – Referenz: bestätigte Einsätze als Karten, Verweis auf die Bühnen-Referenzen von reichi.com. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$ref = $content['reference'];
?>
<section class="section reference" id="referenz" aria-labelledby="reference-title">
  <div class="section__inner">
    <header class="section__head" data-reveal>
      <p class="eyebrow" lang="en"><?= e($ref['eyebrow']) ?></p>
      <h2 class="section__title" id="reference-title"><?= e($ref['title']) ?></h2>
    </header>

    <ul class="reference__list">
      <?php foreach ($ref['items'] as $item): ?>
        <li class="reference__item" data-reveal>
          <p class="reference__kind"><?= e($item['kind']) ?></p>
          <h3 class="reference__name"><?= e($item['name']) ?></h3>
          <p class="reference__text"><?= e($item['text']) ?></p>
          <?php if (!empty($item['url'])): ?>
            <a class="reference__url" href="<?= e($item['url']) ?>" rel="noopener noreferrer" target="_blank"><?= e($item['link_label']) ?> <?= icon('external', 'icon icon--small') ?></a>
          <?php endif; ?>
        </li>
      <?php endforeach; ?>
      <li class="reference__item reference__item--more" data-reveal>
        <p class="reference__kind">reichi.com</p>
        <p class="reference__text"><?= e($ref['more_label']) ?></p>
        <a class="button button--ghost button--small" href="<?= e($ref['more_url']) ?>" rel="noopener noreferrer" target="_blank">Zu den Referenzen <?= icon('external', 'icon button__icon') ?></a>
      </li>
    </ul>
  </div>
</section>
