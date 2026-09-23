<?php
/** Fotos: Galerie mit Bildunterschriften; Links auf die große Datei (Lightbox per JS optional). */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$gallery = $content['gallery'];
?>
<section class="section gallery" id="fotos" aria-labelledby="gallery-title">
  <div class="section__inner">
    <header class="section__head section__head--split">
      <div>
        <p class="eyebrow"><?= e($gallery['eyebrow']) ?></p>
        <h2 class="section__title" id="gallery-title"><?= e($gallery['title']) ?></h2>
      </div>
      <p class="section__note"><?= e($gallery['text']) ?></p>
    </header>

    <ul class="gallery__grid" data-gallery>
      <?php foreach ($gallery['items'] as $item): ?>
        <?php $photo = $content['photos'][$item['photo']]; ?>
        <li class="gallery__item gallery__item--<?= e($item['size']) ?>" data-reveal>
          <figure class="gallery__figure">
            <a class="gallery__link" href="<?= e(photo_full_url($photo)) ?>"
               data-lightbox
               data-caption="<?= e($photo['caption']) ?>"
               data-credit="<?= e($photo['credit'] ?? '') ?>"
               data-alt="<?= e($photo['alt']) ?>"
               data-width="<?= (int) $photo['width'] ?>" data-height="<?= (int) $photo['height'] ?>">
              <?= picture($photo, [
                  'sizes' => $item['size'] === 'wide' ? '(max-width: 47.99em) 100vw, 66vw' : '(max-width: 47.99em) 100vw, 33vw',
                  'class' => 'gallery__img',
              ]) ?>
              <span class="gallery__zoom" aria-hidden="true"><?= icon('expand', 'icon icon--small') ?></span>
            </a>
            <figcaption class="gallery__caption">
              <span><?= e($photo['caption']) ?></span>
              <?php if (!empty($photo['credit'])): ?>
                <span class="gallery__credit">Foto: <?= e($photo['credit']) ?></span>
              <?php endif; ?>
            </figcaption>
          </figure>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
