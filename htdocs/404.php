<?php
/** Fehlerseite „Nicht gefunden“ – per ErrorDocument (.htaccess) bzw. error_page (nginx) eingebunden. */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(__DIR__ . '/app/bootstrap.php') ? __DIR__ . '/app/bootstrap.php' : dirname(__DIR__) . '/app/bootstrap.php';

http_response_code(404);

$page = [
    'title' => 'Seite nicht gefunden | reichi',
    'description' => 'Diese Seite gibt es auf reichi.com nicht (mehr).',
    'path' => '/404.php',
    'noindex' => true,
    'body_class' => 'page-legal',
];

render('header', ['page' => $page]);
?>
<section class="section legal">
  <div class="section__inner legal__inner">
    <header class="section__head">
      <p class="eyebrow">404</p>
      <h1 class="section__title">Diese Seite gibt es nicht.</h1>
      <p class="section__note">Vielleicht ein alter Link von der früheren Website. Alles Wesentliche steht auf der Startseite.</p>
    </header>
    <div class="hero__actions">
      <a class="button button--primary" href="/">Zur Startseite <?= icon('arrow-right', 'icon button__icon') ?></a>
      <a class="button button--ghost" href="/#hire">Anfrage senden</a>
    </div>
  </div>
</section>
<?php
render('footer', ['page' => $page]);
