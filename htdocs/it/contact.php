<?php
/**
 * reichi.it – Formularverarbeitung (nur POST); Logik in ../app/contact-handler.php.
 * Nach der Verarbeitung wird immer zur Startseite (#anfrage) weitergeleitet.
 */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
define('SITE', 'it');
require dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/contact-handler.php';
