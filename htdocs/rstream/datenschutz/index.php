<?php
/**
 * rstream.at – Datenschutzerklärung (Text in app/templates/legal/datenschutz.php;
 * mit [BESTÄTIGEN] markierte Stellen sind vom Inhaber bzw. Hoster zu prüfen, siehe MIGRATION.md).
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
define('SITE', 'rstream');
require dirname(PUBLIC_DIR) . '/app/bootstrap.php';
require APP_DIR . '/contact.php';

$page = [
    'title' => 'Datenschutzerklärung | rstream.at – Christian Reichinger',
    'description' => 'Datenschutzerklärung von rstream.at: Kontaktformular, Server-Protokolle und technisch notwendiger Session-Cookie.',
    'path' => '/datenschutz/',
    'body_class' => 'page-legal site-rstream',
];

render('header', ['page' => $page]);
render('legal/datenschutz');
render('footer', ['page' => $page]);
