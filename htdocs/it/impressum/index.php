<?php
/** reichi.it – Impressum (gleicher Medieninhaber wie reichi.com; Text in app/templates/legal/impressum.php). */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
define('SITE', 'it');
require dirname(PUBLIC_DIR) . '/app/bootstrap.php';

$page = [
    'title' => 'Impressum | reichi.it – Christian Reichinger',
    'description' => 'Impressum von reichi.it – Christian Reichinger, Event-IT, Aurolzmünster, Oberösterreich.',
    'path' => '/impressum/',
    'body_class' => 'page-legal site-it',
];

render('header', ['page' => $page]);
render('legal/impressum');
render('footer', ['page' => $page]);
