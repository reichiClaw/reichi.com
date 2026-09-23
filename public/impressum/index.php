<?php
/**
 * Impressum – Geschäftsdaten laut alter Website (vom Inhaber vor Livegang zu bestätigen).
 * Die Datenschutzhinweise der alten Seite sind in /datenschutz/ neu gefasst.
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
require dirname(__DIR__, 2) . '/app/bootstrap.php';

$contact = $content['contact'];
$page = [
    'title' => 'Impressum | Christian Reichinger',
    'description' => 'Impressum von reichi.com – Christian Reichinger, Tontechniker / Sound Engineer, Aurolzmünster, Oberösterreich.',
    'path' => '/impressum/',
    'body_class' => 'page-legal',
];

render('header', ['page' => $page]);
?>
<article class="section legal">
  <div class="section__inner legal__inner">
    <header class="section__head">
      <p class="eyebrow">Rechtliches</p>
      <h1 class="section__title">Impressum</h1>
      <p class="section__note">Informationspflicht laut §5 E-Commerce-Gesetz, §14 Unternehmensgesetzbuch, §63 Gewerbeordnung und Offenlegungspflicht laut §25 Mediengesetz.</p>
    </header>

    <div class="legal__body">
      <section class="legal__block">
        <h2>Medieninhaber und Herausgeber</h2>
        <address class="legal__address">
          <strong><?= e($contact['name']) ?></strong><br>
          <?= e($contact['street']) ?><br>
          <?= e($contact['zip']) ?> <?= e($contact['city']) ?><br>
          <?= e($contact['country']) ?>
        </address>
        <dl class="legal__dl">
          <dt>Tel.</dt><dd><a href="<?= e(tel_href($contact['phone_href'])) ?>"><?= e($contact['phone_display']) ?></a></dd>
          <dt>E-Mail</dt><dd><a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a></dd>
          <dt>UID-Nummer</dt><dd><?= e($contact['uid']) ?></dd>
        </dl>
        <p>Diese Seite ist auch über folgende Domains erreichbar:</p>
        <ul class="legal__list">
          <?php foreach ($contact['alias_domains'] as $domain): ?>
            <li><a href="https://<?= e($domain) ?>/"><?= e($domain) ?></a></li>
          <?php endforeach; ?>
        </ul>
      </section>

      <section class="legal__block">
        <h2>Haftungsausschluss</h2>
        <p>Dieser Haftungsausschluss ist als Teil des Internetangebots zu betrachten, von dem aus auf diese Webseite verwiesen wurde. Sofern Teile oder einzelne Formulierungen dieses Textes der geltenden Rechtslage nicht, nicht mehr oder nicht vollständig entsprechen sollten, bleiben die übrigen Teile des Dokuments in ihrem Inhalt und ihrer Gültigkeit davon unberührt.</p>

        <h3>Haftung für Inhalte dieser Webseite</h3>
        <p>Die Inhalte dieser Webseite wurden mit größtmöglicher Sorgfalt erstellt. Für die Richtigkeit, Vollständigkeit und Aktualität der Inhalte übernehme ich jedoch keine Gewähr. Als Diensteanbieter bin ich für eigene Inhalte auf diesen Seiten nach den allgemeinen Gesetzen verantwortlich. Ich bin jedoch nicht verpflichtet, übermittelte oder gespeicherte fremde Informationen zu überwachen oder nach Umständen zu forschen, die auf eine rechtswidrige Tätigkeit hinweisen. Verpflichtungen zur Entfernung oder Sperrung der Nutzung von Informationen nach den allgemeinen Gesetzen bleiben hiervon unberührt. Eine diesbezügliche Haftung ist jedoch erst ab dem Zeitpunkt der Kenntnis einer konkreten Rechtsverletzung möglich. Bei Bekanntwerden von entsprechenden Rechtsverletzungen werde ich diese Inhalte umgehend entfernen.</p>

        <h3>Haftung für Links auf Webseiten Dritter</h3>
        <p>Dieses Angebot enthält Links zu externen Websites. Auf den Inhalt dieser externen Webseiten habe ich keinerlei Einfluss. Deshalb kann ich für diese fremden Inhalte auch keine Gewähr übernehmen. Für die Inhalte der verlinkten Seiten ist stets der jeweilige Anbieter oder Betreiber der Seiten verantwortlich. Die verlinkten Seiten wurden zum Zeitpunkt der Verlinkung auf mögliche Rechtsverstöße überprüft. Rechtswidrige Inhalte waren zum Zeitpunkt der Verlinkung nicht erkennbar. Eine permanente inhaltliche Kontrolle der verlinkten Seiten ist jedoch ohne konkrete Anhaltspunkte einer Rechtsverletzung nicht zumutbar. Bei Bekanntwerden von Rechtsverletzungen werde ich derartige Links umgehend entfernen.</p>

        <h3>Urheberrecht</h3>
        <p>Ich bin bemüht, stets die Urheberrechte anderer zu beachten bzw. auf selbst erstellte sowie lizenzfreie Werke zurückzugreifen. Die von mir erstellten Inhalte und Werke auf dieser Webseite unterliegen dem Urheberrecht. Beiträge Dritter sind als solche gekennzeichnet. Die Vervielfältigung, Bearbeitung, Verbreitung und jede Art der Verwertung außerhalb der Grenzen des Urheberrechts bedürfen der schriftlichen Zustimmung des jeweiligen Autors bzw. Erstellers. Downloads und Kopien dieser Seite sind nur für den privaten, nicht kommerziellen Gebrauch gestattet.</p>
      </section>

      <section class="legal__block">
        <h2>Datenschutz</h2>
        <p>Hinweise zur Verarbeitung personenbezogener Daten auf dieser Website (Kontaktformular, Server-Protokolle, technisch notwendiger Session-Cookie) finden sich in der <a href="/datenschutz/">Datenschutzerklärung</a>.</p>
      </section>

      <section class="legal__block" id="bildnachweise">
        <h2>Bildnachweise</h2>
        <ul class="legal__list">
          <?php foreach ($content['image_credits'] as $credit): ?>
            <li><?= e($credit) ?></li>
          <?php endforeach; ?>
        </ul>
      </section>
    </div>
  </div>
</article>
<?php
render('footer', ['page' => $page]);
