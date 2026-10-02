<?php
/**
 * Kontaktformular mit Statusmeldung – gemeinsam für reichi.com und reichi.it.
 * Erwartet $formStatus, $formErrors, $formValues, $formMessage (aus index.php) sowie
 * $labels (Feldbezeichnungen aus content.php, 'hire' → 'form'). Optional in $labels:
 * 'subject_options' (Liste → Betreff als Auswahlfeld statt Textfeld).
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$contact = $content['contact'];
$anchor = url('/#' . ($content['hire']['id'] ?? 'hire'));

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
    'spam' => [
        'type' => 'error',
        'title' => 'Die Nachricht wurde als Werbung eingestuft.',
        'text' => 'Der Spamfilter hat angeschlagen – meist wegen Links oder bestimmter Begriffe. Bitte formuliere die Anfrage ohne Links neu oder schreib direkt an ' . $contact['email'] . '. Deine Eingaben stehen unten weiterhin im Formular.',
    ],
    'turnstile' => [
        'type' => 'error',
        'title' => 'Die Sicherheitsprüfung wurde nicht bestätigt.',
        'text' => 'Bitte sende das Formular noch einmal ab; dafür muss JavaScript aktiviert sein. Klappt es weiterhin nicht, schreib direkt an ' . $contact['email'] . ' oder ruf an unter ' . $contact['phone_display'] . '.',
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
$turnstile = turnstile_enabled($config) ? $config['turnstile'] : null;
// Farbschema des Turnstile-Widgets: Standard dunkel (reichi.com); eine helle Website setzt 'form_theme' => 'light'.
$turnstileTheme = (string) ($content['site']['form_theme'] ?? 'dark');
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
$subjectOptions = isset($labels['subject_options']) && is_array($labels['subject_options']) ? $labels['subject_options'] : null;
?>
      <?php if ($status !== null): ?>
        <div class="form-status form-status--<?= e($status['type']) ?>" role="<?= $status['type'] === 'error' ? 'alert' : 'status' ?>" tabindex="-1" id="form-status">
          <p class="form-status__title"><?= e($status['title']) ?></p>
          <p><?= e($status['text']) ?></p>
        </div>
      <?php endif; ?>

      <?php if ($formStatus !== 'sent'): ?>
      <form class="form" action="<?= e(url('/contact.php')) ?>" method="post"<?= $turnstile ? ' data-turnstile-sitekey="' . e($turnstile['site_key']) . '" data-turnstile-appearance="' . e($turnstile['appearance'] ?? 'always') . '"' . ($turnstileTheme !== 'dark' ? ' data-turnstile-theme="' . e($turnstileTheme) . '"' : '') : '' ?>>
        <input type="hidden" name="csrf_token" value="<?= e(csrf_token()) ?>">
        <input type="hidden" name="js_token" value="" data-js-token>

        <div class="form__trap" aria-hidden="true">
          <label for="f-website">Website</label>
          <input type="text" id="f-website" name="website" tabindex="-1" autocomplete="off" value="">
          <label for="f-email-confirm">E-Mail wiederholen</label>
          <input type="text" id="f-email-confirm" name="email_confirm" tabindex="-1" autocomplete="off" value="">
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
            <?php if ($subjectOptions !== null): ?>
            <select class="form__input form__select" id="f-subject" name="subject"<?= $err('subject') ? ' aria-invalid="true"' : '' ?><?= $describedBy('subject', false) ?>>
              <option value=""<?= ($formValues['subject'] ?? '') === '' ? ' selected' : '' ?>><?= e($labels['subject_placeholder'] ?? 'Bitte wählen') ?></option>
              <?php foreach ($subjectOptions as $option): ?>
                <option value="<?= e($option) ?>"<?= ($formValues['subject'] ?? '') === $option ? ' selected' : '' ?>><?= e($option) ?></option>
              <?php endforeach; ?>
            </select>
            <?php else: ?>
            <input class="form__input" type="text" id="f-subject" name="subject" value="<?= $field('subject') ?>" maxlength="<?= CONTACT_LIMITS['subject'] ?>" autocomplete="off"<?= $err('subject') ? ' aria-invalid="true"' : '' ?><?= $describedBy('subject', false) ?>>
            <?php endif; ?>
            <?php if ($err('subject')): ?><p class="form__error" id="f-subject-error"><?= e($err('subject')) ?></p><?php endif; ?>
          </div>
        </div>

        <div class="form__field<?= $err('message') ? ' form__field--error' : '' ?>">
          <label class="form__label" for="f-message"><?= e($labels['message']) ?> <span class="form__req" aria-hidden="true">*</span></label>
          <textarea class="form__input form__textarea" id="f-message" name="message" rows="7" required maxlength="<?= CONTACT_LIMITS['message'] ?>"<?= $err('message') ? ' aria-invalid="true"' : '' ?><?= $describedBy('message', true) ?>><?= $field('message') ?></textarea>
          <p class="form__hint" id="f-message-hint"><?= e($labels['message_hint']) ?></p>
          <?php if ($err('message')): ?><p class="form__error" id="f-message-error"><?= e($err('message')) ?></p><?php endif; ?>
        </div>

        <?php if ($turnstile): ?>
          <div class="form__turnstile" data-turnstile-widget>
            <noscript><p class="form__hint">Für die Sicherheitsprüfung (Cloudflare Turnstile) ist JavaScript nötig. Alternativ erreichst du mich direkt per E-Mail oder Telefon.</p></noscript>
          </div>
        <?php endif; ?>

        <div class="form__footer">
          <p class="form__privacy"><?= e($labels['privacy_note']) ?><?= $turnstile ? ' Zum Schutz vor Spam wird Cloudflare Turnstile eingesetzt.' : '' ?> <a href="<?= e(url('/datenschutz/')) ?>">Datenschutzerklärung</a></p>
          <button class="button button--primary" type="submit"><?= e($labels['submit']) ?> <?= icon('arrow-right', 'icon button__icon') ?></button>
        </div>
        <p class="form__required-note"><span aria-hidden="true">*</span> Pflichtfeld</p>
      </form>
      <?php else: ?>
        <p class="hire__again"><a class="button button--ghost" href="<?= e($anchor) ?>">Weitere Anfrage senden</a></p>
      <?php endif; ?>
