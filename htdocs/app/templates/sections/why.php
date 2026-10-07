<?php
/** Warum ich: Einleitung, Punkte, Fakten, ein Satz zum Mitnehmen – gemeinsam für reichi.it und rstream.at ($content['why']). */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$why = $content['why'];
?>
<section class="section why" id="warum" aria-labelledby="why-title">
  <div class="section__inner why__body">
    <header class="section__head why__head" data-reveal>
      <p class="eyebrow" lang="en"><?= e($why['eyebrow']) ?></p>
      <h2 class="section__title" id="why-title"><?= e($why['title']) ?></h2>
      <p class="section__note"><?= e($why['lead']) ?></p>
    </header>

    <ul class="why__points" data-reveal>
      <?php foreach ($why['points'] as $point): ?>
        <li class="why__point">
          <h3 class="why__name"><?= e($point['name']) ?></h3>
          <p class="why__text"><?= e($point['text']) ?></p>
        </li>
      <?php endforeach; ?>
    </ul>

    <dl class="about__facts why__facts" data-reveal>
      <?php foreach ($why['facts'] as $fact): ?>
        <div class="about__fact">
          <dt><?= e($fact['label']) ?></dt>
          <dd><?= e($fact['value']) ?></dd>
        </div>
      <?php endforeach; ?>
    </dl>

    <p class="about__quote why__quote" lang="en" data-reveal><?= e($why['quote']) ?></p>
  </div>
</section>
