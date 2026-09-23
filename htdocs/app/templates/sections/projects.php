<?php
/** Verbundene Projekte: BleedingStar und R-Stream mit externen Links. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$projects = $content['projects'];
?>
<section class="section projects" id="projekte" aria-labelledby="projects-title">
  <div class="section__inner">
    <header class="section__head">
      <p class="eyebrow"><?= e($projects['eyebrow']) ?></p>
      <h2 class="section__title" id="projects-title"><?= e($projects['title']) ?></h2>
    </header>

    <ul class="projects__list">
      <?php foreach ($projects['items'] as $p): ?>
        <li class="projects__item" id="<?= e($p['id']) ?>" data-reveal>
          <a class="projects__link" href="<?= e($p['url']) ?>" rel="noopener noreferrer" target="_blank">
            <span class="projects__logo<?= !empty($p['logo']['invert']) ? ' projects__logo--invert' : '' ?>"><?= logo_img($p['logo']) ?></span>
            <span class="projects__body">
              <span class="projects__name"><?= e($p['name']) ?></span>
              <span class="projects__text"><?= e($p['text']) ?></span>
              <span class="projects__url"><?= e($p['link_label']) ?> <?= icon('external', 'icon icon--small') ?></span>
            </span>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</section>
