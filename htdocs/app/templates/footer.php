<?php
/** Seitenfuß mit Kontakt, Navigation, rechtlichen Links und Bildnachweis. */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$contact = $content['contact'];
$isHome = !empty($page['is_home']);
$site = $content['site'];
$brandSuffix = (string) ($site['brand_suffix'] ?? '');
$brandSuffixHtml = isset($site['brand_suffix_html']) ? (string) $site['brand_suffix_html'] : e($brandSuffix);
$hireHref = $content['nav_cta']['href'];
// „Mehr“-Spalte: eigene Linkliste der Website oder (reichi.com) die verbundenen Projekte.
$moreLinks = $content['footer']['links'] ?? array_map(
    static fn(array $p): array => ['label' => $p['name'], 'url' => $p['url']],
    $content['projects']['items'] ?? []
);
?>
</main>

<footer class="site-footer">
  <div class="site-footer__inner">
    <div class="site-footer__brand">
      <a class="brand brand--footer" href="<?= e(url('/')) ?>" aria-label="<?= e($site['brand'] . $brandSuffix) ?> – Startseite">
        <?= logo_mark('brand__mark') ?>
        <span class="brand__word"><?= e($site['brand']) ?><?= $brandSuffix !== '' ? '<span class="brand__suffix">' . $brandSuffixHtml . '</span>' : '' ?></span>
      </a>
      <p class="site-footer__meta"><?= e($contact['roles']) ?></p>
      <p class="site-footer__meta"><?= e($contact['availability']) ?></p>
    </div>

    <div class="site-footer__col">
      <h2 class="site-footer__heading">Kontakt</h2>
      <address class="site-footer__address">
        <?= e($contact['name']) ?><br>
        <?= e($contact['street']) ?><br>
        <?= e($contact['zip']) ?> <?= e($contact['city']) ?><br>
        <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a><br>
        <a href="<?= e(tel_href($contact['phone_href'])) ?>"><?= e($contact['phone_display']) ?></a>
      </address>
    </div>

    <div class="site-footer__col">
      <h2 class="site-footer__heading">Navigation</h2>
      <ul class="site-footer__links">
        <?php foreach ($content['nav'] as $item): ?>
          <li><a href="<?= e($isHome ? substr($item['href'], 1) : url($item['href'])) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e($isHome ? substr($hireHref, 1) : url($hireHref)) ?>"><?= e($content['nav_cta']['label']) ?></a></li>
      </ul>
    </div>

    <div class="site-footer__col">
      <h2 class="site-footer__heading">Mehr</h2>
      <ul class="site-footer__links">
        <?php foreach ($content['social'] as $s): ?>
          <li><a href="<?= e($s['url']) ?>" rel="noopener noreferrer me" target="_blank"><?= e($s['label']) ?> <span class="muted"><?= e($s['handle']) ?></span></a></li>
        <?php endforeach; ?>
        <?php foreach ($moreLinks as $p): ?>
          <li><a href="<?= e($p['url']) ?>" rel="noopener noreferrer<?= !empty($p['me']) ? ' me' : '' ?>" target="_blank"><?= e($p['label']) ?><?= !empty($p['note']) ? ' <span class="muted">' . e($p['note']) . '</span>' : '' ?></a></li>
        <?php endforeach; ?>
        <li><a href="<?= e(url('/impressum/')) ?>">Impressum</a></li>
        <li><a href="<?= e(url('/datenschutz/')) ?>">Datenschutz</a></li>
      </ul>
    </div>
  </div>

  <div class="site-footer__bottom">
    <?php if (!empty($content['footer']['credits'])): ?>
    <p class="site-footer__credits"><?= e($content['footer']['credits']) ?></p>
    <?php endif; ?>
    <p class="site-footer__copy">&copy; <?= date('Y') ?> <?= e($contact['name']) ?></p>
  </div>
</footer>

<?php if ($isHome && !empty($content['gallery'])): ?>
<dialog class="lightbox" id="lightbox" aria-label="Bild in Großansicht">
  <figure class="lightbox__figure">
    <img class="lightbox__img" src="" alt="" width="1200" height="800">
    <figcaption class="lightbox__caption"></figcaption>
  </figure>
  <button class="lightbox__close" type="button" data-lightbox-close aria-label="Schließen"><?= icon('close') ?></button>
  <button class="lightbox__nav lightbox__nav--prev" type="button" data-lightbox-prev aria-label="Vorheriges Bild"><?= icon('chevron-left') ?></button>
  <button class="lightbox__nav lightbox__nav--next" type="button" data-lightbox-next aria-label="Nächstes Bild"><?= icon('chevron-right') ?></button>
</dialog>
<?php endif; ?>
</body>
</html>
