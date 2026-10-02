<?php
/**
 * Anfrage & Kontakt: Text, direkte Kontaktdaten, Formular, Porträt.
 * Erwartet $formStatus, $formErrors, $formValues, $formMessage aus index.php
 * (werden an partials/contact-form.php durchgereicht).
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$hire = $content['hire'];
$labels = $hire['form'];
$contact = $content['contact'];
$photo = $content['photos'][$hire['photo']];
?>
<section class="section hire" id="hire" aria-labelledby="hire-title">
  <div class="section__inner hire__grid">
    <div class="hire__intro" data-reveal>
      <p class="eyebrow"><?= e($hire['eyebrow']) ?></p>
      <h2 class="section__title" id="hire-title"><?= e($hire['title']) ?></h2>
      <p class="hire__question"><?= e($hire['question']) ?></p>
      <?php foreach ($hire['paragraphs'] as $p): ?>
        <p><?= e($p) ?></p>
      <?php endforeach; ?>

      <div class="contact" id="contact">
        <h3 class="contact__title"><?= e($content['contact_section']['title']) ?></h3>
        <ul class="contact__list">
          <li>
            <a class="contact__link" href="mailto:<?= e($contact['email']) ?>"><?= icon('mail') ?><span><?= e($contact['email']) ?></span></a>
          </li>
          <li>
            <a class="contact__link" href="<?= e(tel_href($contact['phone_href'])) ?>"><?= icon('phone') ?><span><?= e($contact['phone_display']) ?></span></a>
          </li>
          <li class="contact__address">
            <?= icon('pin') ?>
            <address>
              <?= e($contact['name']) ?><br>
              <?= e($contact['street']) ?><br>
              <?= e($contact['zip']) ?> <?= e($contact['city']) ?><br>
              <?= e($contact['district']) ?>, <?= e($contact['country']) ?>
            </address>
          </li>
          <?php foreach ($content['social'] as $s): ?>
            <li>
              <a class="contact__link" href="<?= e($s['url']) ?>" rel="noopener noreferrer me" target="_blank"><?= icon($s['icon']) ?><span><?= e($s['label']) ?> <?= e($s['handle']) ?></span></a>
            </li>
          <?php endforeach; ?>
        </ul>
        <p class="contact__region"><strong>Region:</strong> <?= e($contact['region']) ?><br><?= e($contact['availability']) ?></p>
      </div>

      <figure class="hire__figure">
        <?= picture($photo, [
            'sizes' => '(max-width: 47.99em) 100vw, (max-width: 63.99em) 50vw, 36vw',
            'portrait_media' => '(min-width: 48em)',
            'class' => 'hire__img',
        ]) ?>
        <figcaption class="photo-credit">Foto: <?= e($photo['credit']) ?></figcaption>
      </figure>
    </div>

    <div class="hire__form-wrap">
      <?php render('partials/contact-form', [
          'labels' => $labels,
          'formStatus' => $formStatus,
          'formErrors' => $formErrors,
          'formValues' => $formValues,
          'formMessage' => $formMessage,
      ]); ?>
    </div>
  </div>
</section>
