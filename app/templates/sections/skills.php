<?php
/** Skills: nummerierte Liste der Tätigkeitsfelder. */

declare(strict_types=1);

$skills = $content['skills'];
?>
<section class="section skills" id="skills" aria-labelledby="skills-title">
  <div class="section__inner">
    <header class="section__head section__head--split">
      <div>
        <p class="eyebrow"><?= e($skills['title']) ?></p>
        <h2 class="section__title" id="skills-title"><?= e($skills['claim']) ?></h2>
      </div>
      <p class="section__note"><?= e($skills['focus']) ?></p>
    </header>

    <ol class="skills__list">
      <?php foreach ($skills['items'] as $i => $item): ?>
        <li class="skills__item" data-reveal>
          <span class="skills__index" aria-hidden="true"><?= str_pad((string) ($i + 1), 2, '0', STR_PAD_LEFT) ?></span>
          <h3 class="skills__name"><?= e($item['name']) ?></h3>
          <p class="skills__text"><?= e($item['text']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
