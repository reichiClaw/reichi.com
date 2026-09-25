<?php
/**
 * Kontaktformular: Validierung, Missbrauchsschutz und Versand per PHP mail().
 *
 * Voraussetzung: ein funktionierendes Mailsystem auf dem Server (sendmail/Postfix
 * oder der vom Hoster konfigurierte Transport). Ohne dieses kann keine E-Mail
 * zugestellt werden – die Anwendung meldet das dann ehrlich.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

const CONTACT_LIMITS = [
    'name' => 120,
    'email' => 254,
    'phone' => 40,
    'subject' => 150,
    'message' => 5000,
];

/**
 * Prüft die Eingaben und liefert ['values' => [...], 'errors' => [...]].
 * Werte sind getrimmt und auf eine Zeile normalisiert (außer message).
 */
function contact_validate(array $post): array
{
    $values = [];
    $errors = [];

    foreach (array_keys(CONTACT_LIMITS) as $field) {
        $raw = $post[$field] ?? '';
        $raw = is_string($raw) ? $raw : '';
        // Steuerzeichen entfernen; Zeilenumbrüche nur in der Nachricht erlauben.
        $clean = $field === 'message'
            ? preg_replace('/[^\P{C}\n\t]/u', '', str_replace("\r\n", "\n", $raw))
            : preg_replace('/\p{C}+/u', ' ', $raw);
        $clean = trim((string) $clean);
        if (!mb_check_encoding($clean, 'UTF-8')) {
            $clean = '';
        }
        $values[$field] = $clean;
    }

    if ($values['name'] === '') {
        $errors['name'] = 'Bitte gib deinen Namen an.';
    } elseif (mb_strlen($values['name']) > CONTACT_LIMITS['name']) {
        $errors['name'] = 'Der Name ist zu lang (max. ' . CONTACT_LIMITS['name'] . ' Zeichen).';
    }

    if ($values['email'] === '') {
        $errors['email'] = 'Bitte gib deine E-Mail-Adresse an, damit ich antworten kann.';
    } elseif (
        mb_strlen($values['email']) > CONTACT_LIMITS['email']
        || filter_var($values['email'], FILTER_VALIDATE_EMAIL) === false
        || preg_match('/[\r\n\s,;<>"]/', $values['email'])
    ) {
        $errors['email'] = 'Diese E-Mail-Adresse sieht nicht gültig aus.';
    }

    if ($values['phone'] !== '') {
        if (mb_strlen($values['phone']) > CONTACT_LIMITS['phone'] || !preg_match('/^[0-9+()\/\-. ]+$/', $values['phone'])) {
            $errors['phone'] = 'Bitte nur Ziffern, Leerzeichen und die Zeichen + ( ) / - . verwenden.';
        }
    }

    if (mb_strlen($values['subject']) > CONTACT_LIMITS['subject']) {
        $errors['subject'] = 'Der Betreff ist zu lang (max. ' . CONTACT_LIMITS['subject'] . ' Zeichen).';
    }

    if ($values['message'] === '') {
        $errors['message'] = 'Bitte schreib ein paar Zeilen zu deiner Anfrage.';
    } elseif (mb_strlen($values['message']) < 10) {
        $errors['message'] = 'Die Nachricht ist sehr kurz – ein paar Details helfen mir bei der Antwort.';
    } elseif (mb_strlen($values['message']) > CONTACT_LIMITS['message']) {
        $errors['message'] = 'Die Nachricht ist zu lang (max. ' . CONTACT_LIMITS['message'] . ' Zeichen).';
    }

    return ['values' => $values, 'errors' => $errors];
}

/**
 * Eingebauter Spamfilter. Liefert null, wenn die Anfrage unauffällig ist, sonst den
 * Grund (Kurzcode) für das Protokoll. Die Regeln stehen in config.php unter 'spam'.
 * $jsSeen: der Browser hat JavaScript ausgeführt (Interaktions-Token stimmt).
 */
function contact_spam_check(array $values, array $config, bool $jsSeen): ?string
{
    $rules = $config['spam'] ?? [];
    $text = $values['subject'] . "\n" . $values['message'];
    $lower = mb_strtolower($text);

    // HTML- oder BBCode-Links haben in einer Anfrage nichts verloren.
    if (preg_match('/<\s*a\s|\[url[=\]]|\[link[=\]]|<\s*script/i', $text)) {
        return 'markup';
    }

    // Links zählen; ohne JavaScript ist gar keiner erlaubt.
    $links = preg_match_all('#https?://|\bwww\.#i', $text);
    $maxLinks = $jsSeen ? max(0, (int) ($rules['max_links'] ?? 1)) : 0;
    if ($links > $maxLinks) {
        return 'links';
    }

    // Ein Name mit Link oder ohne einen einzigen Buchstaben ist kein Name.
    if (preg_match('#https?://|www\.#i', $values['name']) || !preg_match('/\p{L}/u', $values['name'])) {
        return 'name';
    }

    foreach ((array) ($rules['blocked_terms'] ?? []) as $term) {
        $term = mb_strtolower(trim((string) $term));
        if ($term !== '' && str_contains($lower, $term)) {
            return 'term';
        }
    }

    // Überwiegend fremdes Schriftsystem (z. B. rein kyrillische Werbung).
    $scripts = (array) ($rules['reject_scripts'] ?? []);
    if ($scripts !== [] && preg_match_all('/\p{L}/u', $text) >= 10) {
        $letters = preg_match_all('/\p{L}/u', $text);
        $classes = implode('', array_map(
            static fn(string $s): string => '\p{' . preg_replace('/[^A-Za-z_]/', '', $s) . '}',
            $scripts
        ));
        $foreign = @preg_match_all('/[' . $classes . ']/u', $text);
        if ($foreign !== false && $letters > 0 && $foreign / $letters > 0.4) {
            return 'script';
        }
    }

    return null;
}

/** Protokolliert abgewiesene Versuche – nur Zeitpunkt, Grund und Absender-Hash (gekürzt). */
function contact_spam_log(array $config, string $reason): void
{
    if (empty($config['spam']['log'])) {
        return;
    }
    $dir = rtrim($config['storage_dir'], '/') . '/logs';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return;
    }
    $file = $dir . '/spam.log';
    if (is_file($file) && filesize($file) > 512 * 1024) {
        @rename($file, $file . '.1');
    }
    @file_put_contents(
        $file,
        '[' . date('c') . '] ' . $reason . ' ' . substr(contact_client_key($config), 0, 12) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

/** Turnstile ist aktiv, wenn beide Schlüssel konfiguriert sind. */
function turnstile_enabled(array $config): bool
{
    $t = $config['turnstile'] ?? [];
    return is_array($t) && trim((string) ($t['site_key'] ?? '')) !== '' && trim((string) ($t['secret_key'] ?? '')) !== '';
}

/**
 * Prüft das Turnstile-Token serverseitig bei Cloudflare (Siteverify). Gibt true nur bei
 * bestätigtem Erfolg zurück; ist der Dienst nicht erreichbar, gilt die Prüfung als
 * nicht bestanden (fail closed) – die Statusmeldung nennt dann die direkten Kontaktwege.
 */
function turnstile_verify(?string $token, array $config): bool
{
    if (!is_string($token) || $token === '' || strlen($token) > 2048) {
        return false;
    }
    $secret = (string) $config['turnstile']['secret_key'];
    $body = http_build_query([
        'secret' => $secret,
        'response' => $token,
        'remoteip' => $_SERVER['REMOTE_ADDR'] ?? '',
    ]);
    $url = 'https://challenges.cloudflare.com/turnstile/v0/siteverify';
    $raw = false;

    if (function_exists('curl_init')) {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $body,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded'],
        ]);
        $raw = curl_exec($ch);
        if ($raw === false) {
            contact_log($config, 'Turnstile: Siteverify nicht erreichbar (' . curl_error($ch) . ').');
        }
        curl_close($ch);
    } else {
        $ctx = stream_context_create(['http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => $body,
            'timeout' => 8,
        ]]);
        $raw = @file_get_contents($url, false, $ctx);
        if ($raw === false) {
            contact_log($config, 'Turnstile: Siteverify nicht erreichbar (allow_url_fopen/curl prüfen).');
        }
    }

    if (!is_string($raw)) {
        return false;
    }
    $data = json_decode($raw, true);
    if (!is_array($data) || empty($data['success'])) {
        contact_spam_log($config, 'turnstile:' . implode(',', (array) ($data['error-codes'] ?? ['unbekannt'])));
        return false;
    }
    // Das Token muss für dieses Formular auf diesem Host ausgestellt worden sein
    // (Cloudflares Testschlüssel liefern immer example.com und werden hier ausgenommen).
    $testing = !empty($data['metadata']['result_with_testing_key']);
    $host = strtolower(preg_replace('/:\d+$/', '', (string) ($_SERVER['HTTP_HOST'] ?? '')));
    $actionOk = !isset($data['action']) || $data['action'] === 'contact';
    $hostOk = !isset($data['hostname']) || strtolower((string) $data['hostname']) === $host;
    if (!$testing && (!$actionOk || !$hostOk)) {
        contact_spam_log($config, 'turnstile:host-or-action');
        return false;
    }
    return true;
}

/** Anonymisierter Schlüssel für das Rate-Limit: HMAC der IP mit dem konfigurierten Secret. */
function contact_client_key(array $config): string
{
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    $secret = (string) ($config['secret'] ?? '');
    if ($secret === '') {
        // Fallback ohne Secret: trotzdem nur ein Hash, kein Klartext auf der Platte.
        $secret = 'reichi-' . php_uname('n');
    }
    return hash_hmac('sha256', $ip, $secret);
}

/**
 * Konservatives Rate-Limit je Absender, dateibasiert mit flock().
 * Speichert nur Zeitstempel unter storage/ratelimit/<hash>.json, nie IP-Adressen.
 * Gibt null zurück, wenn erlaubt, sonst eine Fehlermeldung.
 */
function contact_rate_limit(array $config): ?string
{
    $limits = $config['rate_limit'];
    $dir = rtrim($config['storage_dir'], '/') . '/ratelimit';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        // Ohne beschreibbares Verzeichnis kann nicht gezählt werden; Formular bleibt nutzbar.
        return null;
    }

    $now = time();
    contact_rate_limit_gc($dir, $now, (int) $limits['retention_seconds']);

    $file = $dir . '/' . contact_client_key($config) . '.json';
    $fh = @fopen($file, 'c+');
    if ($fh === false) {
        return null;
    }
    try {
        if (!flock($fh, LOCK_EX)) {
            return null;
        }
        $raw = stream_get_contents($fh);
        $stamps = is_string($raw) && $raw !== '' ? json_decode($raw, true) : [];
        $stamps = is_array($stamps) ? array_values(array_filter(
            $stamps,
            static fn($t): bool => is_int($t) && $t > $now - (int) $limits['window_seconds']
        )) : [];

        $last = $stamps === [] ? 0 : max($stamps);
        $message = null;
        if ($last > $now - (int) $limits['min_interval']) {
            $message = 'Das ging sehr schnell. Bitte warte einen Moment, bevor du eine weitere Anfrage sendest.';
        } elseif (count($stamps) >= (int) $limits['max_per_window']) {
            $message = 'Von dieser Verbindung sind gerade viele Anfragen eingegangen. Bitte versuch es später noch einmal oder schreib direkt per E-Mail.';
        } else {
            $stamps[] = $now;
        }

        ftruncate($fh, 0);
        rewind($fh);
        fwrite($fh, json_encode($stamps));
        fflush($fh);
        flock($fh, LOCK_UN);
        return $message;
    } finally {
        fclose($fh);
    }
}

/** Alte Rate-Limit-Dateien löschen (probabilistisch, damit nicht jeder Aufruf das Verzeichnis liest). */
function contact_rate_limit_gc(string $dir, int $now, int $retention): void
{
    if (random_int(1, 10) !== 1) {
        return;
    }
    foreach (glob($dir . '/*.json') ?: [] as $file) {
        $mtime = @filemtime($file);
        if ($mtime !== false && $mtime < $now - $retention) {
            @unlink($file);
        }
    }
}

/** Wert für Mail-Header absichern: keine Zeilenumbrüche, kein Header-Injection. */
function contact_header_safe(string $value): string
{
    return trim(str_replace(["\r", "\n", "\0"], ' ', $value));
}

/**
 * Sendet die Anfrage. Gibt true zurück, wenn der Mailtransport die Nachricht angenommen hat.
 * Das ist KEINE Zustellbestätigung; die Statusmeldung im Template formuliert das entsprechend.
 */
function contact_send(array $values, array $config): bool
{
    if (empty($config['mail_enabled'])) {
        return false;
    }

    $to = filter_var($config['mail_to'], FILTER_VALIDATE_EMAIL);
    $from = filter_var($config['mail_from'], FILTER_VALIDATE_EMAIL);
    if ($to === false || $from === false) {
        contact_log($config, 'Konfiguration ungültig: mail_to oder mail_from ist keine gültige Adresse.');
        return false;
    }

    $subjectText = $values['subject'] !== '' ? $values['subject'] : 'Anfrage über die Website';
    $subject = contact_header_safe(($config['mail_subject_prefix'] ?? '') . $subjectText);
    $encodedSubject = mb_encode_mimeheader($subject, 'UTF-8', 'B', "\r\n");

    $fromName = mb_encode_mimeheader(contact_header_safe((string) $config['mail_from_name']), 'UTF-8', 'B', "\r\n");

    // Reply-To ausschließlich aus der bereits validierten Besucheradresse (nur Adresse, kein Anzeigename).
    $replyTo = filter_var(contact_header_safe($values['email']), FILTER_VALIDATE_EMAIL);

    $headers = [
        'From: ' . $fromName . ' <' . $from . '>',
        'MIME-Version: 1.0',
        'Content-Type: text/plain; charset=UTF-8',
        'Content-Transfer-Encoding: 8bit',
        'X-Mailer: reichi.com contact form',
        'Auto-Submitted: auto-generated',
    ];
    if ($replyTo !== false) {
        $headers[] = 'Reply-To: ' . $replyTo;
    }

    $lines = [
        'Neue Anfrage über reichi.com',
        '',
        'Name:     ' . $values['name'],
        'E-Mail:   ' . $values['email'],
        'Telefon:  ' . ($values['phone'] !== '' ? $values['phone'] : '–'),
        'Betreff:  ' . $subjectText,
        '',
        'Nachricht:',
        str_repeat('-', 40),
        $values['message'],
        str_repeat('-', 40),
        '',
        'Gesendet: ' . date('d.m.Y H:i:s') . ' (' . date_default_timezone_get() . ')',
    ];
    $body = wordwrap(implode("\r\n", $lines), 990, "\r\n", true);

    $params = '';
    $envelope = $config['mail_envelope_from'] ?? null;
    if (is_string($envelope) && filter_var($envelope, FILTER_VALIDATE_EMAIL) !== false) {
        $params = '-f' . $envelope;
    }

    $accepted = $params !== ''
        ? @mail($to, $encodedSubject, $body, implode("\r\n", $headers), $params)
        : @mail($to, $encodedSubject, $body, implode("\r\n", $headers));

    if (!$accepted) {
        $err = error_get_last();
        contact_log($config, 'mail() hat die Nachricht nicht angenommen.' . ($err ? ' ' . $err['message'] : ''));
    }
    return (bool) $accepted;
}

/** Protokolliert Versandfehler (nur Zeitstempel und Fehlermeldung, keine Formularinhalte). */
function contact_log(array $config, string $message): void
{
    if (empty($config['log_mail_failures'])) {
        return;
    }
    $dir = rtrim($config['storage_dir'], '/') . '/logs';
    if (!is_dir($dir) && !@mkdir($dir, 0750, true)) {
        return;
    }
    @file_put_contents(
        $dir . '/mail.log',
        '[' . date('c') . '] ' . contact_header_safe($message) . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}
