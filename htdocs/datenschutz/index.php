<?php
/**
 * Datenschutzerklärung – beschreibt ausschließlich die tatsächlich implementierte Verarbeitung.
 * Mit [BESTÄTIGEN] markierte Stellen sind vom Inhaber bzw. Hoster zu prüfen (siehe MIGRATION.md).
 */

declare(strict_types=1);

define('PUBLIC_DIR', dirname(__DIR__));
// app/ liegt normalerweise im Webroot; alternativ eine Ebene darüber (siehe README).
require is_file(PUBLIC_DIR . '/app/bootstrap.php') ? PUBLIC_DIR . '/app/bootstrap.php' : dirname(PUBLIC_DIR) . '/app/bootstrap.php';

$contact = $content['contact'];
$rl = $config['rate_limit'];
$retentionHours = (int) round((int) $rl['retention_seconds'] / 3600);
$page = [
    'title' => 'Datenschutzerklärung | Christian Reichinger',
    'description' => 'Datenschutzerklärung von reichi.com: Kontaktformular, Server-Protokolle und technisch notwendiger Session-Cookie.',
    'path' => '/datenschutz/',
    'body_class' => 'page-legal',
];

render('header', ['page' => $page]);
?>
<article class="section legal">
  <div class="section__inner legal__inner">
    <header class="section__head">
      <p class="eyebrow">Rechtliches</p>
      <h1 class="section__title">Datenschutzerklärung</h1>
      <p class="section__note">Diese Website kommt ohne Tracking, Analyse-Dienste, eingebettete Inhalte Dritter und Werbe-Cookies aus. Hier steht, welche Daten tatsächlich verarbeitet werden.</p>
    </header>

    <div class="legal__body">
      <section class="legal__block">
        <h2>Verantwortlicher</h2>
        <address class="legal__address">
          <strong><?= e($contact['name']) ?></strong><br>
          <?= e($contact['street']) ?><br>
          <?= e($contact['zip']) ?> <?= e($contact['city']) ?>, <?= e($contact['country']) ?><br>
          <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a> ·
          <a href="<?= e(tel_href($contact['phone_href'])) ?>"><?= e($contact['phone_display']) ?></a>
        </address>
      </section>

      <section class="legal__block">
        <h2>Hosting und Server-Protokolle</h2>
        <p>Beim Aufruf dieser Website verarbeitet der Webserver technisch notwendige Verbindungsdaten: IP-Adresse, Datum und Uhrzeit des Zugriffs, aufgerufene Adresse, übertragene Datenmenge, Browsertyp und -version, Betriebssystem sowie die zuvor besuchte Seite (Referrer). Diese Daten sind für den Betrieb und die Sicherheit der Website erforderlich (Art. 6 Abs. 1 lit. f DSGVO) und werden nicht mit anderen Datenquellen zusammengeführt oder ausgewertet, solange keine rechtswidrige Nutzung vorliegt.</p>
        <p>Die Website wird bei folgendem Anbieter betrieben: <mark class="legal__todo">[BESTÄTIGEN: Hosting-Anbieter, Firmensitz, Speicherdauer der Server-Logs laut Hoster]</mark>. Mit dem Anbieter besteht ein Vertrag zur Auftragsverarbeitung, sofern gesetzlich erforderlich.</p>
      </section>

      <section class="legal__block">
        <h2>Kontaktformular</h2>
        <p>Über das Formular unter „Anfrage“ können Name, E-Mail-Adresse, optional eine Telefonnummer, ein Betreff und eine Nachricht übermittelt werden. Diese Angaben werden ausschließlich dazu verwendet, die Anfrage zu beantworten. Rechtsgrundlage ist die Anbahnung bzw. Durchführung eines Vertrags (Art. 6 Abs. 1 lit. b DSGVO) und mein berechtigtes Interesse an der Beantwortung von Anfragen (Art. 6 Abs. 1 lit. f DSGVO).</p>
        <p>Technisch wird die Anfrage vom Webserver als E-Mail an <?= e($contact['email']) ?> gesendet. Die Formulareingaben werden dabei <strong>nicht</strong> in einer Datenbank oder in Dateien auf dem Webserver gespeichert; sie liegen nach dem Versand nur noch im E-Mail-Postfach. Anfragen bewahre ich so lange auf, wie es für die Bearbeitung und eine mögliche Zusammenarbeit erforderlich ist, längstens jedoch <mark class="legal__todo">[BESTÄTIGEN: Aufbewahrungsdauer, z. B. bis zum Abschluss der Anfrage bzw. nach den steuer- und unternehmensrechtlichen Fristen]</mark>.</p>
        <p>Die Übermittlung an das Postfach erfolgt über den Mailserver des Hosting-Anbieters <mark class="legal__todo">[BESTÄTIGEN: eigener Mailserver oder externer E-Mail-Anbieter]</mark>.</p>

        <h3>Schutz vor Missbrauch</h3>
        <p>Um das Formular vor automatisiertem Spam zu schützen, werden drei Maßnahmen eingesetzt, die keine externen Dienste (kein CAPTCHA-Anbieter) benötigen:</p>
        <ul class="legal__list">
          <li>Ein für Menschen unsichtbares Formularfeld, das nur von automatisierten Programmen ausgefüllt wird.</li>
          <li>Eine serverseitige Begrenzung der Anzahl von Absendevorgängen: Dafür wird aus der IP-Adresse mit einem geheimen Schlüssel ein nicht rückrechenbarer Hashwert gebildet und zusammen mit den Zeitpunkten der letzten Absendevorgänge außerhalb des öffentlichen Webverzeichnisses gespeichert. Die IP-Adresse selbst wird dabei nicht gespeichert. Die Einträge werden nach spätestens <?= $retentionHours ?> Stunden automatisch gelöscht. Erlaubt sind höchstens <?= (int) $rl['max_per_window'] ?> Anfragen innerhalb von <?= (int) round((int) $rl['window_seconds'] / 60) ?> Minuten je Verbindung.</li>
          <li>Ein Sitzungs-Token (CSRF-Schutz), das sicherstellt, dass das Formular tatsächlich von dieser Website abgesendet wurde.</li>
        </ul>
        <p>Schlägt der Versand fehl, wird ausschließlich Zeitpunkt und technische Fehlermeldung protokolliert – ohne Formularinhalte oder IP-Adresse.</p>
      </section>

      <section class="legal__block">
        <h2>Cookies</h2>
        <p>Diese Website verwendet einen einzigen, technisch notwendigen Cookie mit dem Namen <code>reichi_session</code>. Er wird gesetzt, wenn die Startseite mit dem Kontaktformular aufgerufen wird, enthält nur eine zufällige Sitzungskennung und dient dem Schutz des Formulars (CSRF-Token) sowie der Anzeige der Statusmeldung nach dem Absenden. Er wird beim Schließen des Browsers gelöscht, enthält keine personenbezogenen Daten und wird nicht zur Wiedererkennung oder Analyse genutzt. Eine Einwilligung ist dafür nicht erforderlich (§ 165 Abs. 3 TKG 2021). Es werden keine Analyse-, Marketing- oder Drittanbieter-Cookies gesetzt; deshalb gibt es auch keinen Cookie-Banner.</p>
      </section>

      <section class="legal__block">
        <h2>Externe Links</h2>
        <p>Die Website verlinkt auf externe Angebote (z. B. Instagram, Websites von Bands, Festivals und verbundenen Projekten). Es werden keine Inhalte, Skripte, Schriften oder Bilder von fremden Servern eingebunden; erst beim Anklicken eines Links verlassen Sie diese Website und es gelten die Datenschutzbestimmungen des jeweiligen Anbieters.</p>
      </section>

      <section class="legal__block">
        <h2>Ihre Rechte</h2>
        <p>Sie haben das Recht auf Auskunft über die zu Ihrer Person gespeicherten Daten, deren Herkunft und Empfänger sowie den Zweck der Verarbeitung, außerdem auf Berichtigung, Löschung, Einschränkung der Verarbeitung, Datenübertragbarkeit und Widerspruch. Wenden Sie sich dazu an <a href="mailto:<?= e($contact['email']) ?>"><?= e($contact['email']) ?></a>. Wenn Sie der Ansicht sind, dass die Verarbeitung Ihrer Daten gegen das Datenschutzrecht verstößt, können Sie sich bei der österreichischen Datenschutzbehörde (<a href="https://www.dsb.gv.at/" rel="noopener noreferrer" target="_blank">dsb.gv.at</a>) beschweren.</p>
      </section>

      <section class="legal__block">
        <h2>Stand</h2>
        <p>Diese Erklärung beschreibt den Stand der technischen Umsetzung dieser Website und wird angepasst, wenn sich die Verarbeitung ändert.</p>
      </section>
    </div>
  </div>
</article>
<?php
render('footer', ['page' => $page]);
