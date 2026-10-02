<?php
/** reichi.it – Ablauf: vier Schritte als „Patchkabel“-Strang. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$process = $content['process'];
?>
<section class="section process" id="ablauf" aria-labelledby="process-title">
  <div class="section__inner">
    <header class="section__head section__head--split" data-reveal>
      <div>
        <p class="eyebrow" lang="en"><?= e($process['eyebrow']) ?></p>
        <h2 class="section__title" id="process-title"><?= e($process['title']) ?></h2>
      </div>
      <p class="section__note"><?= e($process['note']) ?></p>
    </header>

    <ol class="process__list">
      <?php foreach ($process['steps'] as $i => $step): ?>
        <li class="process__step" data-reveal>
          <span class="process__marker" aria-hidden="true"><span class="process__num"><?= $i + 1 ?></span></span>
          <h3 class="process__name"><?= e($step['name']) ?></h3>
          <p class="process__text"><?= e($step['text']) ?></p>
        </li>
      <?php endforeach; ?>
    </ol>
  </div>
</section>
