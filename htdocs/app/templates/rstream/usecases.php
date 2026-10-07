<?php
/**
 * rstream.at – Einsatzbereiche als Karten (Klassen aus style.css 9b.4, wie die Referenz-Karten
 * auf reichi.it), zum Schluss der Verweis auf die Bühnen-Referenzen von reichi.com.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$uc = $content['usecases'];
?>
<section class="section reference usecases" id="einsatz" aria-labelledby="usecases-title">
  <div class="section__inner">
    <header class="section__head" data-reveal>
      <p class="eyebrow" lang="en"><?= e($uc['eyebrow']) ?></p>
      <h2 class="section__title" id="usecases-title"><?= e($uc['title']) ?></h2>
      <?php if (!empty($uc['note'])): ?>
        <p class="section__note"><?= e($uc['note']) ?></p>
      <?php endif; ?>
    </header>

    <ul class="reference__list usecases__list">
      <?php foreach ($uc['items'] as $item): ?>
        <li class="reference__item" data-reveal>
          <p class="reference__kind"><?= e($item['kind']) ?></p>
          <h3 class="reference__name"><?= e($item['name']) ?></h3>
          <p class="reference__text"><?= e($item['text']) ?></p>
        </li>
      <?php endforeach; ?>
      <li class="reference__item reference__item--more" data-reveal>
        <p class="reference__kind">reichi.com</p>
        <p class="reference__text"><?= e($uc['more_label']) ?></p>
        <a class="button button--ghost button--small" href="<?= e($uc['more_url']) ?>" rel="noopener noreferrer" target="_blank">Zu den Referenzen <?= icon('external', 'icon button__icon') ?></a>
      </li>
    </ul>
  </div>
</section>
