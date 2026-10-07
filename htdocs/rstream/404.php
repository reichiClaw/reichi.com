<?php
/** rstream.at – Fehlerseite „Nicht gefunden“ (per Rewrite in rstream/.htaccess eingebunden). */

declare(strict_types=1);

define('PUBLIC_DIR', __DIR__);
define('SITE', 'rstream');
require dirname(__DIR__) . '/app/bootstrap.php';

http_response_code(404);

$page = [
    'title' => 'Seite nicht gefunden | rstream.at',
    'description' => 'Diese Seite gibt es auf rstream.at nicht.',
    'path' => '/404.php',
    'noindex' => true,
    'body_class' => 'page-legal site-rstream',
];

render('header', ['page' => $page]);
?>
<section class="section legal">
  <div class="section__inner legal__inner">
    <header class="section__head">
      <p class="eyebrow">404</p>
      <h1 class="section__title">Kein Signal auf diesem Eingang.</h1>
      <p class="section__note">Die Adresse gibt es auf rstream.at nicht. Alles Wesentliche steht auf der Startseite.</p>
    </header>
    <div class="hero__actions">
      <a class="button button--primary" href="<?= e(url('/')) ?>">Zur Startseite <?= icon('arrow-right', 'icon button__icon') ?></a>
      <a class="button button--ghost" href="<?= e(url('/#anfrage')) ?>">Anfrage senden</a>
    </div>
  </div>
</section>
<?php
render('footer', ['page' => $page]);
