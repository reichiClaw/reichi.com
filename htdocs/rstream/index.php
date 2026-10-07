<?php
/**
 * rstream.at – Startseite der Streaming-Abteilung (eine Seite, alle Abschnitte).
 * Dieser Ordner ist der Webroot der Domain rstream.at (im Hosting-Panel auf rstream/ gelegt);
 * der Code liegt gemeinsam mit reichi.com und reichi.it in ../app/.
 */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
define('SITE', 'rstream');
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/contact.php';

// Session für CSRF-Token und Formularstatus (Post/Redirect/Get).
start_session();
header('Cache-Control: private, no-store');

$formStatus = flash_get('contact_status');
$formErrors = flash_get('contact_errors', []);
$formValues = flash_get('contact_values', []);
$formMessage = flash_get('contact_message');

if ($formStatus === null || !isset($_SESSION['contact_form_rendered'])) {
    // Zeitstempel für die Zeitfalle des Formulars; bei Fehler-Rerender nicht zurücksetzen.
    $_SESSION['contact_form_rendered'] = time();
}

$page = [
    'title' => $content['site']['title'],
    'description' => $content['site']['description'],
    'path' => '/',
    'is_home' => true,
    'body_class' => 'page-home site-rstream',
];

render('header', ['page' => $page]);
render('rstream/hero');
render('sections/services');
render('sections/process');
render('sections/why');
render('rstream/usecases');
render('sections/projects');
render('sections/inquiry', [
    'formStatus' => $formStatus,
    'formErrors' => is_array($formErrors) ? $formErrors : [],
    'formValues' => is_array($formValues) ? $formValues : [],
    'formMessage' => is_string($formMessage) ? $formMessage : null,
]);
render('footer', ['page' => $page]);
