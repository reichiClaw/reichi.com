<?php
/**
 * reichi.com – Konfiguration
 *
 * Die echte Konfiguration heißt app/config.php (im Upload-ZIP bereits enthalten,
 * im Git-Repository nicht versioniert). Diese Beispieldatei ist die Vorlage dafür:
 * einfach kopieren/umbenennen und die Werte anpassen. Fehlt app/config.php,
 * verwendet die Website diese Beispielwerte.
 *
 * Alle Texte, Referenzen und Bilder stehen in app/content.php.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

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

    // Mailversand über die PHP-Funktion mail() des Hosters. Auf false setzen, wenn
    // (noch) kein Mailsystem vorhanden ist – das Formular meldet dann ehrlich, dass
    // keine Zustellung möglich war, und zeigt die direkten Kontaktdaten.
    'mail_enabled' => true,

    // Beschreibbares Verzeichnis für Rate-Limit-Daten, Fehlerprotokoll und das Secret.
    // Standard: der Ordner storage/ neben app/ (per .htaccess gesperrt). Wer Dateien
    // oberhalb des Webroots ablegen kann, verschiebt app/ und storage/ dorthin.
    'storage_dir' => dirname(__DIR__) . '/storage',

    // Serverseitige Begrenzung des Formulars je Absender (IP-Hash).
    'rate_limit' => [
        'max_per_window' => 5,      // Anfragen …
        'window_seconds' => 3600,   // … pro Stunde
        'min_interval'   => 20,     // Mindestabstand in Sekunden zwischen zwei Anfragen
        'retention_seconds' => 86400, // Rate-Limit-Dateien älter als 24 h werden gelöscht
    ],

    // Geheimer Zufallswert (mind. 32 Zeichen) zum Hashen von IP-Adressen für das Rate-Limit.
    // Leer lassen: die Website erzeugt beim ersten Aufruf automatisch ein Secret und
    // speichert es in storage/secret.php. Alternativ hier einen eigenen Wert eintragen.
    'secret' => '',

    // Fehler der Mailfunktion protokollieren (nur Zeitstempel + Fehlermeldung, keine Inhalte).
    'log_mail_failures' => true,

    // Auf true setzen, wenn die Website ausschließlich über HTTPS ausgeliefert wird
    // (Session-Cookie erhält dann das Secure-Flag auch hinter einem Proxy).
    'force_secure_cookie' => false,
];
