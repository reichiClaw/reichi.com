<?php
/**
 * Datenschutzerklärung – beschreibt ausschließlich die tatsächlich implementierte Verarbeitung.
 * Der Text steht in app/templates/legal/datenschutz.php (gemeinsam mit reichi.it);
 * mit [BESTÄTIGEN] markierte Stellen sind vom Inhaber bzw. Hoster zu prüfen (siehe MIGRATION.md).
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(PUBLIC_DIR . '/app/bootstrap.php') ? PUBLIC_DIR . '/app/bootstrap.php' : dirname(PUBLIC_DIR) . '/app/bootstrap.php';
require APP_DIR . '/contact.php';

$page = [
    'title' => 'Datenschutzerklärung | reichi – Christian Reichinger',
    'description' => 'Datenschutzerklärung von reichi.com: Kontaktformular, Server-Protokolle und technisch notwendiger Session-Cookie.',
    'path' => '/datenschutz/',
    'body_class' => 'page-legal',
];

render('header', ['page' => $page]);
render('legal/datenschutz');
render('footer', ['page' => $page]);
