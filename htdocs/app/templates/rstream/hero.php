<?php
/**
 * rstream.at – Hero: Überschrift, englische Zeile, Leistungs-Chips, Einleitung, zwei Aktionen
 * und rechts ein stilisierter Multiviewer einer Livestream-Regie: Kopfzeile (Live-Punkt, Timecode,
 * Encoder), großes Programmbild mit Tonpegel daneben, vier Kamerakacheln mit Tally (rot = Programm,
 * grün = Vorschau) und ein Terminal-Streifen darunter. Die „Bilder“ sind stilisierte Szenen aus
 * CSS-Verläufen (rstream.css), keine Fotos. rstream.js lässt die Regie laufen: Timecode, Schnitte
 * und Blenden von der Vorschau ins Programm, Pegel, getippte Statuszeilen; mit Maus wird die
 * Kachel unter dem Zeiger zur Vorschau. Ohne JavaScript oder mit reduzierter Bewegung steht ein
 * fester Zustand (Cam 1 im Programm, Cam 2 in der Vorschau).
 * Die Klassen hero/hero__copy/hero__figure stammen aus style.css (Layout + Parallax aus main.js).
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

$hero = $content['hero'];
$cams = $hero['mv_cams'];
$pgm = $cams[0];
?>
<section class="hero rs-hero" aria-labelledby="hero-title">
  <div class="hero__inner">
    <div class="hero__copy">
      <p class="eyebrow hero__eyebrow"><?= e($hero['eyebrow']) ?></p>
      <h1 class="hero__title rs-hero__title" id="hero-title"><?= e($hero['headline_a']) ?> <span class="rs-hero__title-b"><?= e($hero['headline_b']) ?></span></h1>
      <p class="rs-hero__tagline" lang="en"><?= e($hero['tagline']) ?></p>
      <ul class="hero__roles" aria-label="Leistungen">
        <?php foreach ($hero['chips'] as $chip): ?>
          <li><?= e($chip) ?></li>
        <?php endforeach; ?>
      </ul>
      <p class="hero__intro"><?= e($hero['intro']) ?></p>
      <div class="hero__actions">
        <a class="button button--primary" href="<?= e($hero['primary']['href']) ?>"><?= e($hero['primary']['label']) ?> <?= icon('arrow-right', 'icon button__icon') ?></a>
        <a class="button button--ghost" href="<?= e($hero['secondary']['href']) ?>"><?= e($hero['secondary']['label']) ?> <?= icon('arrow-down', 'icon button__icon') ?></a>
      </div>
    </div>

    <figure class="hero__figure rs-mv" data-rs-mv>
      <div class="rs-mv__panel" role="img" aria-label="<?= e($hero['mv_label']) ?>">
        <div class="rs-mv__bar">
          <span class="rs-mv__live"><span class="rs-mv__live-dot"></span><?= e($hero['mv_live']) ?></span>
          <span class="rs-mv__tc">00:00:00:00</span>
          <span class="rs-mv__enc"><?= e($hero['mv_encoder']) ?></span>
        </div>
        <div class="rs-mv__main">
          <div class="rs-mv__tile rs-mv__pgm" data-scene="<?= e($pgm['scene']) ?>">
            <?php /* zwei Szenen-Ebenen: die obere wird bei einer Blende eingeblendet (rstream.js) */ ?>
            <span class="rs-mv__scene rs-scene rs-scene--<?= e($pgm['scene']) ?>"></span>
            <span class="rs-mv__scene rs-mv__scene--next rs-scene"></span>
            <span class="rs-mv__tag rs-mv__tag--pgm"><?= e($hero['mv_program']) ?></span>
            <span class="rs-mv__shot"><?= e($pgm['name']) ?> · <?= e($pgm['shot']) ?></span>
            <span class="rs-mv__safe"></span>
          </div>
          <div class="rs-mv__audio">
            <?php foreach ($hero['mv_audio'] as $ch): ?>
              <span class="rs-mv__meter"><span class="rs-mv__meter-fill"></span><span class="rs-mv__meter-ch"><?= e($ch) ?></span></span>
            <?php endforeach; ?>
          </div>
        </div>
        <div class="rs-mv__cams">
          <?php foreach ($cams as $i => $cam): ?>
            <div class="rs-mv__tile rs-mv__cam<?= $i === 0 ? ' is-pgm' : '' ?><?= $i === 1 ? ' is-pvw' : '' ?>" data-cam="<?= $i ?>" data-scene="<?= e($cam['scene']) ?>" data-name="<?= e($cam['name']) ?>" data-shot="<?= e($cam['shot']) ?>">
              <span class="rs-mv__scene rs-scene rs-scene--<?= e($cam['scene']) ?>"></span>
              <span class="rs-mv__tag"><?= e($cam['name']) ?></span>
              <span class="rs-mv__shot"><?= e($cam['shot']) ?></span>
              <span class="rs-mv__tally" data-pgm="<?= e($hero['mv_program']) ?>" data-pvw="<?= e($hero['mv_preview']) ?>"></span>
            </div>
          <?php endforeach; ?>
        </div>
      </div>
      <ul class="rs-mv__log" aria-hidden="true"
          data-event-cut="<?= e($hero['log_events']['cut']) ?>"
          data-kind-cut="<?= e($hero['log_events']['cut_kind']) ?>"
          data-kind-mix="<?= e($hero['log_events']['mix_kind']) ?>">
        <?php foreach ($hero['log_lines'] as $line): ?>
          <li><?= e($line) ?></li>
        <?php endforeach; ?>
      </ul>
      <figcaption class="hero__caption">
        <span class="hero__caption-label"><?= e($hero['mv_caption']) ?></span>
        <span class="hero__caption-meta"><span class="rs-mv__status" aria-hidden="true"></span><span class="rs-mv__meta"><?= e($hero['mv_meta']) ?></span></span>
      </figcaption>
    </figure>
  </div>
</section>
