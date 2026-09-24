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

// Honeypot: Das Feld ist für Menschen unsichtbar; ist es gefüllt, wird still „Erfolg“ vorgetäuscht.
if (!empty($_POST['website'])) {
    flash_set('contact_status', 'sent');
    header('Location: ' . $redirect, true, 303);
    exit;
}

// Zeitfalle: Formulare, die in unter drei Sekunden nach dem Rendern abgeschickt werden, sind fast immer Bots.
$renderedAt = (int) ($_SESSION['contact_form_rendered'] ?? 0);
if ($renderedAt > 0 && time() - $renderedAt < 3) {
    flash_set('contact_status', 'sent');
    header('Location: ' . $redirect, true, 303);
    exit;
}

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

$limited = contact_rate_limit($config);
if ($limited !== null) {
    flash_set('contact_status', 'limited');
    flash_set('contact_message', $limited);
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
