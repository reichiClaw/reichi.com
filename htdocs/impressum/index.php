<?php
/**
 * Impressum – Geschäftsdaten laut alter Website (vom Inhaber vor Livegang zu bestätigen).
 * Der Text steht in app/templates/legal/impressum.php (gemeinsam mit reichi.it).
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(PUBLIC_DIR . '/app/bootstrap.php') ? PUBLIC_DIR . '/app/bootstrap.php' : dirname(PUBLIC_DIR) . '/app/bootstrap.php';

$page = [
    'title' => 'Impressum | reichi – Christian Reichinger',
    'description' => 'Impressum von reichi.com – Christian Reichinger, Tontechniker / Sound Engineer, Aurolzmünster, Oberösterreich.',
    'path' => '/impressum/',
    'body_class' => 'page-legal',
];

render('header', ['page' => $page]);
render('legal/impressum');
render('footer', ['page' => $page]);
