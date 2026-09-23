<?php
/**
 * reichi.com – Konfiguration (Beispiel)
 *
 * Kopiere diese Datei nach app/config.php und trage dort die echten Werte ein.
 * app/config.php wird nicht versioniert. Fehlt sie, verwendet die Website
 * diese Beispielwerte (mit deaktiviertem Mailversand, siehe unten).
 *
 * Alle Texte, Referenzen und Bilder stehen in app/content.php.
 */

declare(strict_types=1);

return [
    // Kanonische Adresse ohne abschließenden Slash. Wird für <link rel="canonical">,
    // Open-Graph-Tags, sitemap.xml und die Weiterleitung nach dem Formularversand verwendet.
    'base_url' => 'https://www.reichi.com',

    // Empfängeradresse für Anfragen über das Kontaktformular.
    'mail_to' => 'reichi@reichi.com',

    // Absenderadresse. MUSS auf der Domain der Website liegen (SPF/DMARC),
    // die Antwortadresse des Besuchers landet ausschließlich im Reply-To.
    'mail_from' => 'website@reichi.com',
    'mail_from_name' => 'reichi.com Website',

    // Envelope-Sender für sendmail (-f). Meist identisch mit mail_from.
    // Auf null setzen, wenn der Hoster den Parameter nicht erlaubt.
    'mail_envelope_from' => 'website@reichi.com',

    // Präfix für die Betreffzeile eingehender Anfragen.
    'mail_subject_prefix' => '[reichi.com] ',

    // Mailversand ganz abschalten (z. B. lokal oder solange kein Mailsystem eingerichtet ist).
    // Das Formular meldet dann ehrlich, dass keine Zustellung möglich war, und zeigt
    // die direkten Kontaktdaten. Für den Livebetrieb auf true setzen.
    'mail_enabled' => false,

    // Beschreibbares Verzeichnis außerhalb des Webroots für Rate-Limit-Daten und Fehlerprotokolle.
    'storage_dir' => dirname(__DIR__) . '/storage',

    // Serverseitige Begrenzung des Formulars je Absender (IP-Hash).
    'rate_limit' => [
        'max_per_window' => 5,      // Anfragen …
        'window_seconds' => 3600,   // … pro Stunde
        'min_interval'   => 20,     // Mindestabstand in Sekunden zwischen zwei Anfragen
        'retention_seconds' => 86400, // Rate-Limit-Dateien älter als 24 h werden gelöscht
    ],

    // Zufälliger geheimer Wert (mind. 32 Zeichen), z. B. per `openssl rand -hex 32`.
    // Wird zum Hashen von IP-Adressen für das Rate-Limit verwendet. Im Beispiel absichtlich leer.
    'secret' => '',

    // Fehler der Mailfunktion protokollieren (nur Zeitstempel + Fehlermeldung, keine Inhalte).
    'log_mail_failures' => true,

    // Auf true setzen, wenn die Website ausschließlich über HTTPS ausgeliefert wird
    // (Session-Cookie erhält dann das Secure-Flag auch hinter einem Proxy).
    'force_secure_cookie' => false,
];
