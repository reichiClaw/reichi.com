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

$jsonLd = [
    '@context' => 'https://schema.org',
    '@type' => 'Person',
    'name' => $contact['name'],
    'alternateName' => 'reichi',
    'jobTitle' => 'Tontechniker / Sound Engineer, Tour Manager',
    'url' => absolute_url('/'),
    'image' => absolute_url('/assets/images/photos/portrait-hood-800.jpg'),
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
    'sameAs' => array_column($content['social'], 'url'),
];
?>
<!DOCTYPE html>
<html lang="<?= e($site['lang']) ?>">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= e($pageTitle) ?></title>
<meta name="description" content="<?= e($pageDescription) ?>">
<?php if (!empty($page['noindex'])): ?>
<meta name="robots" content="noindex, follow">
<?php endif; ?>
<link rel="canonical" href="<?= e($canonical) ?>">
<meta name="theme-color" content="#0d0d10">
<meta name="color-scheme" content="dark">
<meta property="og:locale" content="<?= e($site['locale']) ?>">
<meta property="og:type" content="<?= $isHome ? 'profile' : 'website' ?>">
<meta property="og:site_name" content="<?= e($site['name']) ?>">
<meta property="og:title" content="<?= e($pageTitle) ?>">
<meta property="og:description" content="<?= e($pageDescription) ?>">
<meta property="og:url" content="<?= e($canonical) ?>">
<meta property="og:image" content="<?= e(absolute_url($site['share_image'])) ?>">
<meta property="og:image:width" content="<?= (int) $site['share_image_width'] ?>">
<meta property="og:image:height" content="<?= (int) $site['share_image_height'] ?>">
<meta property="og:image:alt" content="<?= e($content['photos']['stage-red']['alt']) ?>">
<meta name="twitter:card" content="summary_large_image">
<link rel="icon" href="/favicon.ico" sizes="48x48">
<link rel="icon" href="/assets/images/icons/favicon-32x32.png" sizes="32x32" type="image/png">
<link rel="icon" href="/assets/images/icons/favicon-16x16.png" sizes="16x16" type="image/png">
<link rel="apple-touch-icon" href="/assets/images/icons/apple-touch-icon.png">
<link rel="mask-icon" href="/assets/images/icons/safari-pinned-tab.svg" color="#0d0d10">
<link rel="manifest" href="/site.webmanifest">
<?php if ($isHome): ?>
<link rel="preload" as="image" fetchpriority="high" imagesrcset="/assets/images/photos/portrait-hood-480.webp 480w, /assets/images/photos/portrait-hood-600.webp 600w, /assets/images/photos/portrait-hood-800.webp 800w, /assets/images/photos/portrait-hood-1200.webp 1200w" imagesizes="(max-width: 47.99em) 100vw, 42vw" type="image/webp">
<?php endif; ?>
<link rel="stylesheet" href="<?= e(asset('css/style.css')) ?>">
<script src="<?= e(asset('js/main.js')) ?>" defer></script>
<script type="application/ld+json"><?= json_encode($jsonLd, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?></script>
</head>
<body<?= $bodyClass !== '' ? ' class="' . e($bodyClass) . '"' : '' ?>>
<a class="skip-link" href="#main">Zum Inhalt springen</a>

<header class="site-header" id="top">
  <div class="site-header__inner">
    <a class="brand" href="<?= $isHome ? '#top' : '/' ?>" aria-label="reichi – Startseite">
      <?= logo_mark('brand__mark') ?>
      <span class="brand__word">reichi</span>
    </a>

    <button class="nav-toggle" type="button" aria-expanded="false" aria-controls="site-nav">
      <span class="nav-toggle__bars" aria-hidden="true"><span></span><span></span><span></span></span>
      <span class="nav-toggle__label">Menü</span>
    </button>

    <nav class="site-nav" id="site-nav" aria-label="Hauptnavigation">
      <ul class="site-nav__list">
        <?php foreach ($content['nav'] as $item): ?>
          <?php $href = $isHome ? substr($item['href'], 1) : $item['href']; ?>
          <li><a href="<?= e($href) ?>"><?= e($item['label']) ?></a></li>
        <?php endforeach; ?>
      </ul>
      <a class="button button--small site-nav__cta" href="<?= e($isHome ? substr($content['nav_cta']['href'], 1) : $content['nav_cta']['href']) ?>"><?= e($content['nav_cta']['label']) ?></a>
    </nav>
  </div>
</header>

<main id="main" tabindex="-1">
