<?php
/**
 * Formularverarbeitung (nur POST) – die eigentliche Logik steht in app/contact-handler.php.
 * Nach der Verarbeitung wird immer zur Startseite (#hire) weitergeleitet.
 */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(__DIR__ . '/app/bootstrap.php') ? __DIR__ . '/app/bootstrap.php' : dirname(__DIR__) . '/app/bootstrap.php';
require APP_DIR . '/contact-handler.php';
