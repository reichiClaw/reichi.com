<?php
/**
 * Seitenkopf: <head>, Skip-Link, Header mit Navigation.
 * Erwartet $page = ['title' => …, 'description' => …, 'path' => '/…', 'is_home' => bool, 'noindex' => bool]
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$site = $content['site'];
$contact = $content['contact'];
$pageTitle = $page['title'] ?? $site['title'];
$pageDescription = $page['description'] ?? $site['description'];
$canonical = absolute_url($page['path'] ?? '/');
$isHome = !empty($page['is_home']);
$bodyClass = $page['body_class'] ?? '';
// Optionale Abweichungen je Website (reichi.it): Zusatz zur Wortmarke, Farben, weitere Dateien.
$brandSuffix = (string) ($site['brand_suffix'] ?? '');
// Sichtbare Form des Zusatzes – darf Inline-SVG enthalten (reichi.it: Blitz statt Punkt), sonst der Text
$brandSuffixHtml = isset($site['brand_suffix_html']) ? (string) $site['brand_suffix_html'] : e($brandSuffix);
$brandLabel = $site['brand'] . $brandSuffix . ' – Startseite';
$themeColor = (string) ($site['theme_color'] ?? '#0d0d10');
$personImage = (string) ($site['person_image'] ?? '/assets/images/photos/portrait-hood-800.jpg');

$person = [
    '@type' => 'Person',
    '@id' => absolute_url('/') . '#person',
    'name' => $contact['name'],
    'alternateName' => 'reichi',
    'jobTitle' => $site['job_title'] ?? 'Tontechniker / Sound Engineer, Tourmanager',
    'knowsAbout' => $site['knows_about'] ?? ['Live Sound Mixing', 'Front of House', 'Tourmanagement', 'Live Recording'],
    'url' => absolute_url('/'),
    'image' => str_starts_with($personImage, 'http') ? $personImage : absolute_url($personImage),
    'email' => 'mailto:' . $contact['email'],
    'telephone' => $contact['phone_display'],
    'address' => [
        '@type' => 'PostalAddress',
        'streetAddress' => $contact['street'],
        'postalCode' => $contact['zip'],
        'addressLocality' => $contact['city'],
        'addressRegion' => 'Oberösterreich',
        'addressCountry' => 'AT',
    ],
    'sameAs' => array_merge(array_column($content['social'], 'url'), $site['same_as'] ?? []),
];
// WebSite-Markup nur auf der Startseite (Site-Name-Präferenz laut Google Search Central).
$jsonLd = $isHome
    ? [
        '@context' => 'https://schema.org',
        '@graph' => [
            [
                '@type' => 'WebSite',
                '@id' => absolute_url('/') . '#website',
                'name' => $site['brand'] . $brandSuffix,
                'alternateName' => $site['website_alternate_names'] ?? [$site['name'], 'reichi.com'],
                'url' => absolute_url('/'),
                'inLanguage' => $site['lang'],
                'publisher' => ['@id' => $person['@id']],
            ],
            $person,
        ],
    ]
    : ['@context' => 'https://schema.org'] + $person;
?>
<!DOCTYPE html>
<html lang="<?= e($site['lang']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<?php // Eine Installation in einem Unterordner ist eine Testumgebung und soll nicht indexiert werden. ?>
<?php if (!empty($page['noindex']) || base_path() !== ''): ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="<?= e($themeColor) ?>">
<meta name="color-scheme" content="<?= e($site['color_scheme'] ?? 'dark') ?>">
<meta property="og:locale" content="<?= e($site['locale']) ?>">
<meta property="og:type" content="<?= $isHome ? e($site['og_type_home'] ?? 'profile') : 'website' ?>">
<meta property="og:site_name" content="<?= e($site['brand'] . $brandSuffix) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(absolute_url($site['share_image'])) ?>">
<meta property="og:image:width" content="<?= (int) $site['share_image_width'] ?>">
<meta property="og:image:height" content="<?= (int) $site['share_image_height'] ?>">
<meta property="og:image:alt" content="<?= e($site['share_image_alt'] ?? $content['photos']['stage-red']['alt']) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="<?= e(url('/favicon.ico')) ?>" sizes="48x48">
<link rel="icon" href="<?= e(url('/assets/images/icons/favicon-32x32.png')) ?>" sizes="32x32" type="image/png">
<link rel="icon" href="<?= e(url('/assets/images/icons/favicon-16x16.png')) ?>" sizes="16x16" type="image/png">
<link rel="apple-touch-icon" href="<?= e(url('/assets/images/icons/apple-touch-icon.png')) ?>">
<link rel="mask-icon" href="<?= e(url('/assets/images/icons/safari-pinned-tab.svg')) ?>" color="<?= e($themeColor) ?>">
<link rel="manifest" href="<?= e(url('/site.webmanifest')) ?>">
<?php if ($isHome && !empty($content['hero']['photo'])): ?>
<?php $heroPhoto = $content['photos'][$content['hero']['photo']]; $heroAnim = hero_animation($config); ?>
<?php if ($heroAnim): ?>
<link rel="preload" as="image" fetchpriority="high" media="<?= e(HERO_MOUSE_MEDIA) ?>" href="<?= e($heroAnim['dir'] . '/center.webp') ?>" type="image/webp">
<?php endif; ?>
<link rel="preload" as="image" fetchpriority="high"<?= $heroAnim ? ' media="not all and ' . e(HERO_MOUSE_MEDIA) . '"' : '' ?> imagesrcset="<?= e(implode(', ', array_map(static fn(int $w): string => url('/assets/images/photos/' . $heroPhoto['file'] . '-' . $w . '.webp') . ' ' . $w . 'w', $heroPhoto['widths']))) ?>" imagesizes="(max-width: 47.99em) 100vw, 42vw" type="image/webp">
<?php endif; ?>
<?php foreach ($site['stylesheets'] ?? ['css/style.css'] as $sheet): ?>
<link rel="stylesheet" href="<?= e(asset($sheet)) ?>">
<?php endforeach; ?>
<?php foreach ($site['scripts'] ?? ['js/main.js'] as $script): ?>
<script src="<?= e(asset($script)) ?>" defer></script>
<?php endforeach; ?>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<a class="skip-link" href="#main">Zum Inhalt springen</a>

<header class="site-header" id="top">
  <div class="site-header__inner">
    <a class="brand" href="<?= e($isHome ? '#top' : url('/')) ?>" aria-label="<?= e($brandLabel) ?>">
      <?= logo_mark('brand__mark') ?>
      <span class="brand__word"><?= e($site['brand']) ?><?= $brandSuffix !== '' ? '<span class="brand__suffix">' . $brandSuffixHtml . '</span>' : '' ?></span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="nav-toggle__label">Menü</span>
    </button>

    <nav class="site-nav" id="site-nav" aria-label="Hauptnavigation">
      <ul class="site-nav__list">
        <?php foreach ($content['nav'] as $item): ?>
          <?php $href = $isHome ? substr($item['href'], 1) : url($item['href']); ?>
          <li><a href="<?= e($href) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="button button--small site-nav__cta" href="<?= e($isHome ? substr($content['nav_cta']['href'], 1) : url($content['nav_cta']['href'])) ?>"><?= e($content['nav_cta']['label']) ?></a>
    </nav>
  </div>
</header>

<main id="main" tabindex="-1">
