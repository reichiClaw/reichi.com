<?php
/** Über mich: Werdegang als Erzählung mit großem Bühnenfoto. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$about = $content['about'];
$photo = $content['photos'][$about['photo']];
?>
<section class="section about" id="about" aria-labelledby="about-title">
  <figure class="about__figure">
    <?= picture($photo, [
        'sizes' => '100vw',
        'portrait_media' => '(max-width: 47.99em)',
        'class' => 'about__img',
    ]) ?>
    <?php if (!empty($photo['credit'])): ?>
      <figcaption class="photo-credit">Foto: <?= e($photo['credit']) ?></figcaption>
    <?php endif; ?>
  </figure>

  <div class="section__inner about__body">
    <header class="about__head">
      <p class="eyebrow"><?= e($about['eyebrow']) ?></p>
      <h2 class="section__title" id="about-title"><?= e($about['title']) ?></h2>
    </header>

    <div class="about__text" data-reveal>
      <p class="about__lead"><?= e($about['lead']) ?></p>
      <?php foreach ($about['paragraphs'] as $p): ?>
        <p><?= e($p) ?></p>
      <?php endforeach; ?>
      <a class="button button--ghost" href="<?= e($about['cta']['href']) ?>"><?= e($about['cta']['label']) ?> <?= icon('arrow-right', 'icon button__icon') ?></a>
    </div>

    <dl class="about__facts" data-reveal>
      <?php foreach ($about['facts'] as $fact): ?>
        <div class="about__fact">
          <dt><?= e($fact['label']) ?></dt>
          <dd><?= e($fact['value']) ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>

    <p class="about__quote"><?= e($about['quote']) ?></p>
  </div>
</section>
