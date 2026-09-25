<?php
/** Hero: Wortmarke, Rollen, Einleitung, zwei Aktionen, Porträt, Bühnenlicht (Verfolger per JS). */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$hero = $content['hero'];
$photo = $content['photos'][$hero['photo']];
?>
<section class="hero" aria-labelledby="hero-title">
  <div class="hero__spot" aria-hidden="true"></div>
  <div class="hero__inner">
    <div class="hero__copy">
      <p class="eyebrow hero__eyebrow"><?= e($hero['eyebrow']) ?></p>
      <h1 class="hero__title" id="hero-title"><?= e($hero['headline']) ?><span class="hero__dot" aria-hidden="true">.</span></h1>
      <ul class="hero__roles" aria-label="Tätigkeiten">
        <?php foreach ($hero['roles'] as $role): ?>
          <li><?= e($role) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="hero__intro"><?= e($hero['intro']) ?></p>
      <div class="hero__actions">
        <a class="button button--primary" href="<?= e($hero['primary']['href']) ?>"><?= e($hero['primary']['label']) ?> <?= icon('arrow-down', 'icon button__icon') ?></a>
        <a class="button button--ghost" href="<?= e($hero['secondary']['href']) ?>"><?= e($hero['secondary']['label']) ?> <?= icon('arrow-right', 'icon button__icon') ?></a>
      </div>
    </div>

    <figure class="hero__figure">
      <?= picture($photo, ['sizes' => '(max-width: 47.99em) 100vw, 42vw', 'loading' => 'eager', 'fetchpriority' => 'high', 'class' => 'hero__img']) ?>
      <figcaption class="hero__caption">
        <span class="hero__caption-label"><?= e($photo['caption']) ?></span>
        <span class="hero__caption-meta"><?= icon('pin', 'icon icon--small') ?> <?= e($hero['location']) ?></span>
      </figcaption>
      <?php // Oszilloskop-Linie: ersetzt die CSS-Linie nur mit JS und Maus (main.js), sonst unsichtbar. ?>
      <canvas class="hero__scope" aria-hidden="true"></canvas>
    </figure>
  </div>
  <p class="hero__credit">Foto: <?= e($photo['credit']) ?></p>
</section>
