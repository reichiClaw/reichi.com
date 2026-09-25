<?php
/**
 * Formularverarbeitung (nur POST). Post/Redirect/Get: Nach der Verarbeitung wird
 * immer zur Startseite (#hire) weitergeleitet; Status, Fehler und Eingaben liegen
 * einmalig in der Session.
 */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(__DIR__ . '/app/bootstrap.php') ? __DIR__ . '/app/bootstrap.php' : dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/contact.php';

start_session();

$redirect = url('/#hire');

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
    header('Location: ' . $redirect, true, 303);
    exit;
}

// Honeypots: Beide Felder sind für Menschen unsichtbar; sind sie gefüllt, wird still „Erfolg“ vorgetäuscht.
if (!empty($_POST['website']) || !empty($_POST['email_confirm'])) {
    contact_spam_log($config, 'honeypot');
    flash_set('contact_status', 'sent');
    header('Location: ' . $redirect, true, 303);
    exit;
}

// Zeitfalle: Formulare, die zu schnell nach dem Rendern abgeschickt werden, sind fast immer Bots.
$renderedAt = (int) ($_SESSION['contact_form_rendered'] ?? 0);
$minSeconds = max(1, (int) ($config['spam']['min_seconds'] ?? 5));
if ($renderedAt > 0 && time() - $renderedAt < $minSeconds) {
    contact_spam_log($config, 'too-fast');
    flash_set('contact_status', 'sent');
    header('Location: ' . $redirect, true, 303);
    exit;
}

// Interaktions-Token: main.js schreibt das umgekehrte CSRF-Token in ein verstecktes Feld.
// Fehlt es, hat der Absender kein JavaScript ausgeführt – erlaubt, aber mit strengeren Regeln.
$jsSeen = isset($_POST['js_token'], $_SESSION['csrf_token'])
    && is_string($_POST['js_token'])
    && hash_equals(strrev($_SESSION['csrf_token']), $_POST['js_token']);

$result = contact_validate($_POST);
$values = $result['values'];
$errors = $result['errors'];

if (!csrf_valid($_POST['csrf_token'] ?? null)) {
    flash_set('contact_status', 'csrf');
    flash_set('contact_values', $values);
    header('Location: ' . $redirect, true, 303);
    exit;
}

if ($errors !== []) {
    flash_set('contact_status', 'invalid');
    flash_set('contact_errors', $errors);
    flash_set('contact_values', $values);
    header('Location: ' . $redirect, true, 303);
    exit;
}

$spam = contact_spam_check($values, $config, $jsSeen);
if ($spam !== null) {
    contact_spam_log($config, 'filter:' . $spam);
    flash_set('contact_status', 'spam');
    flash_set('contact_values', $values);
    header('Location: ' . $redirect, true, 303);
    exit;
}

$limited = contact_rate_limit($config);
if ($limited !== null) {
    flash_set('contact_status', 'limited');
    flash_set('contact_message', $limited);
    flash_set('contact_values', $values);
    header('Location: ' . $redirect, true, 303);
    exit;
}

if (turnstile_enabled($config) && !turnstile_verify($_POST['cf-turnstile-response'] ?? null, $config)) {
    flash_set('contact_status', 'turnstile');
    flash_set('contact_values', $values);
    header('Location: ' . $redirect, true, 303);
    exit;
}

if (contact_send($values, $config)) {
    // Token nach erfolgreichem Versand erneuern, damit ein Reload nicht erneut sendet.
    unset($_SESSION['csrf_token'], $_SESSION['contact_form_rendered']);
    flash_set('contact_status', 'sent');
} else {
    flash_set('contact_status', 'failed');
    flash_set('contact_values', $values);
}

header('Location: ' . $redirect, true, 303);
exit;
