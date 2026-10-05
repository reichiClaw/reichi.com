<?php
/**
 * reichi.it – Startseite der IT-Abteilung (eine Seite, alle Abschnitte).
 * Dieser Ordner ist der Webroot der Domain reichi.it (im Hosting-Panel auf it/ gelegt);
 * der Code liegt gemeinsam mit reichi.com in ../app/.
 */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
define('SITE', 'it');
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
    'body_class' => 'page-home site-it',
];

render('header', ['page' => $page]);
render('it/hero');
render('it/services');
render('it/process');
render('it/why');
render('it/reference');
render('sections/projects');
render('it/contact', [
    'formStatus' => $formStatus,
    'formErrors' => is_array($formErrors) ? $formErrors : [],
    'formValues' => is_array($formValues) ? $formValues : [],
    'formMessage' => is_string($formMessage) ? $formMessage : null,
]);
render('footer', ['page' => $page]);
