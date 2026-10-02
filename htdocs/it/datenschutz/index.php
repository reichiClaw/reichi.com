<?php
/**
 * reichi.it – Datenschutzerklärung (Text in app/templates/legal/datenschutz.php;
 * mit [BESTÄTIGEN] markierte Stellen sind vom Inhaber bzw. Hoster zu prüfen, siehe MIGRATION.md).
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
define('SITE', 'it');
require dirname(PUBLIC_DIR) . '/app/bootstrap.php';
require APP_DIR . '/contact.php';

$page = [
    'title' => 'Datenschutzerklärung | reichi.it – Christian Reichinger',
    'description' => 'Datenschutzerklärung von reichi.it: Kontaktformular, Server-Protokolle und technisch notwendiger Session-Cookie.',
    'path' => '/datenschutz/',
    'body_class' => 'page-legal site-it',
];

render('header', ['page' => $page]);
render('legal/datenschutz');
render('footer', ['page' => $page]);
