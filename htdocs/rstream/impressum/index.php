<?php
/** rstream.at – Impressum (gleicher Medieninhaber wie reichi.com; Text in app/templates/legal/impressum.php). */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
define('SITE', 'rstream');
require dirname(PUBLIC_DIR) . '/app/bootstrap.php';

$page = [
    'title' => 'Impressum | rstream.at – Christian Reichinger',
    'description' => 'Impressum von rstream.at – Christian Reichinger, Livestream-Produktion, Aurolzmünster, Oberösterreich.',
    'path' => '/impressum/',
    'body_class' => 'page-legal site-rstream',
];

render('header', ['page' => $page]);
render('legal/impressum');
render('footer', ['page' => $page]);
