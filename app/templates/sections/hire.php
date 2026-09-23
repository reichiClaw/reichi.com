<?php
/**
 * Anfrage & Kontakt: Text, direkte Kontaktdaten, Formular, Porträt.
 * Erwartet $formStatus, $formErrors, $formValues, $formMessage aus index.php.
 */

declare(strict_types=1);

$hire = $content['hire'];
$labels = $hire['form'];
$contact = $content['contact'];
$photo = $content['photos'][$hire['photo']];

$statusText = [
    'sent' => [
        'type' => 'success',
        'title' => 'Danke für deine Anfrage!',
        'text' => 'Die Nachricht wurde an den Mailserver übergeben. Ich melde mich so bald wie möglich. Solltest du in den nächsten Tagen nichts von mir hören, schreib bitte direkt an ' . $contact['email'] . ' oder ruf an.',
    ],
    'invalid' => [
        'type' => 'error',
        'title' => 'Bitte prüfe die markierten Felder.',
        'text' => 'Deine Eingaben sind noch da – nur die markierten Angaben fehlen oder passen nicht.',
    ],
    'csrf' => [
        'type' => 'error',
        'title' => 'Das Formular war zu lange geöffnet.',
        'text' => 'Die Sitzung ist abgelaufen. Bitte sende die Anfrage noch einmal ab – deine Eingaben sind erhalten geblieben.',
    ],
    'limited' => [
        'type' => 'error',
        'title' => 'Kurz warten, bitte.',
        'text' => $formMessage ?? 'Bitte versuch es in ein paar Minuten noch einmal.',
    ],
    'failed' => [
        'type' => 'error',
        'title' => 'Die Nachricht konnte nicht versendet werden.',
        'text' => 'Der Mailversand auf dem Server ist gerade nicht möglich. Bitte schreib direkt an ' . $contact['email'] . ' oder ruf an unter ' . $contact['phone_display'] . '. Deine Eingaben stehen unten weiterhin im Formular.',
    ],
];
$status = $formStatus !== null && isset($statusText[$formStatus]) ? $statusText[$formStatus] : null;

$field = static function (string $name) use ($formValues): string {
    return e($formValues[$name] ?? '');
};
$err = static fn(string $name): ?string => $formErrors[$name] ?? null;
$describedBy = static function (string $name, bool $hasHint) use ($err): string {
    $ids = [];
    if ($hasHint) {
        $ids[] = "f-{$name}-hint";
    }
    if ($err($name) !== null) {
        $ids[] = "f-{$name}-error";
    }
    return $ids ? ' aria-describedby="' . implode(' ', $ids) . '"' : '';
};
?>
<section class="section hire" id="hire" aria-labelledby="hire-title">
  <div class="section__inner hire__grid">
    <div class="hire__intro">
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
      <?php if ($status !== null): ?>
        <div class="form-status form-status--<?= e($status['type']) ?>" role="<?= $status['type'] === 'error' ? 'alert' : 'status' ?>" tabindex="-1" id="form-status">
          <p class="form-status__title"><?= e($status['title']) ?></p>
          <p><?= e($status['text']) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($formStatus !== 'sent'): ?>
      <form class="form" action="/contact.php" method="post" novalidate>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">

        <div class="form__trap" aria-hidden="true">
          <label for="f-website">Website</label>
          <input type="text" id="f-website" name="website" tabindex="-1" autocomplete="off" value="">
        </div>

        <div class="form__row form__row--2">
          <div class="form__field<?= $err('name') ? ' form__field--error' : '' ?>">
            <label class="form__label" for="f-name"><?= e($labels['name']) ?> <span class="form__req" aria-hidden="true">*</span></label>
            <input class="form__input" type="text" id="f-name" name="name" value="<?= $field('name') ?>" required maxlength="<?= CONTACT_LIMITS['name'] ?>" autocomplete="name"<?= $err('name') ? ' aria-invalid="true"' : '' ?><?= $describedBy('name', false) ?>>
            <?php if ($err('name')): ?><p class="form__error" id="f-name-error"><?= e($err('name')) ?></p><?php endif; ?>
          </div>

          <div class="form__field<?= $err('email') ? ' form__field--error' : '' ?>">
            <label class="form__label" for="f-email"><?= e($labels['email']) ?> <span class="form__req" aria-hidden="true">*</span></label>
            <input class="form__input" type="email" id="f-email" name="email" value="<?= $field('email') ?>" required maxlength="<?= CONTACT_LIMITS['email'] ?>" autocomplete="email" inputmode="email"<?= $err('email') ? ' aria-invalid="true"' : '' ?><?= $describedBy('email', false) ?>>
            <?php if ($err('email')): ?><p class="form__error" id="f-email-error"><?= e($err('email')) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="form__row form__row--2">
          <div class="form__field<?= $err('phone') ? ' form__field--error' : '' ?>">
            <label class="form__label" for="f-phone"><?= e($labels['phone']) ?> <span class="form__hint-inline">(<?= e($labels['phone_hint']) ?>)</span></label>
            <input class="form__input" type="tel" id="f-phone" name="phone" value="<?= $field('phone') ?>" maxlength="<?= CONTACT_LIMITS['phone'] ?>" autocomplete="tel" inputmode="tel"<?= $err('phone') ? ' aria-invalid="true"' : '' ?><?= $describedBy('phone', false) ?>>
            <?php if ($err('phone')): ?><p class="form__error" id="f-phone-error"><?= e($err('phone')) ?></p><?php endif; ?>
          </div>

          <div class="form__field<?= $err('subject') ? ' form__field--error' : '' ?>">
            <label class="form__label" for="f-subject"><?= e($labels['subject']) ?></label>
            <input class="form__input" type="text" id="f-subject" name="subject" value="<?= $field('subject') ?>" maxlength="<?= CONTACT_LIMITS['subject'] ?>" autocomplete="off"<?= $err('subject') ? ' aria-invalid="true"' : '' ?><?= $describedBy('subject', false) ?>>
            <?php if ($err('subject')): ?><p class="form__error" id="f-subject-error"><?= e($err('subject')) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="form__field<?= $err('message') ? ' form__field--error' : '' ?>">
          <label class="form__label" for="f-message"><?= e($labels['message']) ?> <span class="form__req" aria-hidden="true">*</span></label>
          <textarea class="form__input form__textarea" id="f-message" name="message" rows="7" required maxlength="<?= CONTACT_LIMITS['message'] ?>"<?= $err('message') ? ' aria-invalid="true"' : '' ?><?= $describedBy('message', true) ?>><?= $field('message') ?></textarea>
          <p class="form__hint" id="f-message-hint"><?= e($labels['message_hint']) ?></p>
          <?php if ($err('message')): ?><p class="form__error" id="f-message-error"><?= e($err('message')) ?></p><?php endif; ?>
        </div>

        <div class="form__footer">
          <p class="form__privacy"><?= e($labels['privacy_note']) ?> <a href="/datenschutz/">Datenschutzerklärung</a></p>
          <button class="button button--primary" type="submit"><?= e($labels['submit']) ?> <?= icon('arrow-right', 'icon button__icon') ?></button>
        </div>
        <p class="form__required-note"><span aria-hidden="true">*</span> Pflichtfeld</p>
      </form>
      <?php else: ?>
        <p class="hire__again"><a class="button button--ghost" href="/#hire">Weitere Anfrage senden</a></p>
      <?php endif; ?>
    </div>
  </div>
</section>
