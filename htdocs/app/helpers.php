<?php
/**
 * Kleine Hilfsfunktionen für Templates, Sessions, CSRF und Bilder.
 * Keine Abhängigkeiten außer PHP selbst.
 */

declare(strict_types=1);

if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

/**
 * Liest das Secret aus storage/secret.php oder erzeugt es einmalig.
 * Als PHP-Datei abgelegt, damit der Inhalt selbst dann nicht ausgeliefert wird,
 * wenn der Schutz des storage/-Verzeichnisses per .htaccess fehlen sollte.
 */
function ensure_secret(string $storageDir): string
{
    $file = rtrim($storageDir, '/') . '/secret.php';
    if (is_file($file)) {
        $existing = @include $file;
        if (is_string($existing) && strlen($existing) >= 32) {
            return $existing;
        }
    }
    $secret = bin2hex(random_bytes(32));
    $php = "<?php\n// Automatisch erzeugt. Nicht veröffentlichen, nicht ändern.\nreturn '" . $secret . "';\n";
    if (@file_put_contents($file, $php, LOCK_EX) === false) {
        // Storage nicht beschreibbar: Secret nur für diesen Request; Rate-Limit-Dateien
        // entstehen dann ohnehin nicht dauerhaft.
        return $secret;
    }
    @chmod($file, 0600);
    // Konkurrierende erste Requests: die zuletzt geschriebene Datei gilt.
    $written = @include $file;
    return is_string($written) && strlen($written) >= 32 ? $written : $secret;
}

/** Geräte, auf denen die Kopfanimation läuft – muss zur Abfrage in main.js und style.css passen. */
const HERO_MOUSE_MEDIA = '(hover: hover) and (pointer: fine)';

/**
 * Daten der Hero-Kopfanimation (manifest.json aus tools/build-character-frames.py),
 * oder null, wenn in config.php 'hero_visual' nicht 'animation' ist oder die Bilder fehlen –
 * dann zeigt der Hero wie bisher das Foto.
 */
function hero_animation(array $config): ?array
{
    static $cache = false;
    if ($cache !== false) {
        return $cache;
    }
    $cache = null;
    if (($config['hero_visual'] ?? 'photo') !== 'animation') {
        return null;
    }
    $file = PUBLIC_DIR . '/assets/images/character/manifest.json';
    if (!is_file($file)) {
        return null;
    }
    $data = json_decode((string) file_get_contents($file), true);
    if (!is_array($data) || empty($data['frames']) || empty($data['face']) || !is_file(PUBLIC_DIR . '/assets/images/character/center.webp')) {
        return null;
    }
    $cache = [
        'frames' => (int) $data['frames'],
        'width' => (int) ($data['width'] ?? 720),
        'height' => (int) ($data['height'] ?? 720),
        'background' => preg_match('/^#[0-9a-f]{6}$/i', (string) ($data['background'] ?? '')) ? strtolower((string) $data['background']) : '#000000',
        'face_x' => (float) $data['face'][0],
        'face_y' => (float) $data['face'][1],
        'dir' => url('/assets/images/character'),
        // Gleicher Name bei neuem Bildsatz: die .htaccess cached WebP ein Jahr, daher ?v=
        'version' => dechex((int) filemtime($file)),
    ];
    return $cache;
}

/** HTML-Escaping für Textknoten und Attributwerte. */
function e(?string $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE | ENT_HTML5, 'UTF-8');
}

/**
 * URL-Präfix, unter dem der Webroot erreichbar ist ('' in der Domain-Wurzel,
 * '/test' bei Installation in einem Unterordner). Wird aus SCRIPT_NAME abgeleitet,
 * indem der Pfad des aufgerufenen Skripts relativ zum Webroot abgeschnitten wird;
 * in config.php kann 'base_path' den Wert fest vorgeben.
 */
function base_path(): string
{
    static $base = null;
    if ($base !== null) {
        return $base;
    }
    global $config;
    if (isset($config['base_path']) && is_string($config['base_path'])) {
        return $base = rtrim($config['base_path'], '/');
    }
    $normalize = static fn(string $p): string => preg_replace('#/+#', '/', str_replace('\\', '/', $p)) ?? $p;
    $scriptName = $normalize((string) ($_SERVER['SCRIPT_NAME'] ?? ''));
    $scriptFile = $normalize((string) ($_SERVER['SCRIPT_FILENAME'] ?? ''));
    $root = rtrim($normalize(PUBLIC_DIR), '/');
    $realRoot = realpath(PUBLIC_DIR);
    $realScript = $scriptFile !== '' ? realpath($scriptFile) : false;
    if ($realRoot !== false && $realScript !== false) {
        $root = rtrim($normalize($realRoot), '/');
        $scriptFile = $normalize($realScript);
    }
    $base = null;
    if ($scriptFile !== '' && str_starts_with($scriptFile, $root . '/')) {
        $relative = substr($scriptFile, strlen($root)); // z. B. /impressum/index.php
        if (str_ends_with($scriptName, $relative)) {
            $base = rtrim(substr($scriptName, 0, -strlen($relative)), '/');
        }
    }
    if ($base === null) {
        // Zweiter Versuch: Lage des Webroots relativ zum DOCUMENT_ROOT des Servers.
        $docRoot = rtrim($normalize((string) ($_SERVER['DOCUMENT_ROOT'] ?? '')), '/');
        if ($docRoot !== '' && str_starts_with($root . '/', $docRoot . '/')) {
            $base = rtrim(substr($root, strlen($docRoot)), '/');
        }
    }
    if ($base === null) {
        // Letzter Ausweg: Verzeichnis des aufgerufenen Skripts (korrekt für index.php im Webroot).
        $dir = $normalize(dirname($scriptName));
        $base = $dir === '/' || $dir === '.' ? '' : rtrim($dir, '/');
    }
    // Nur harmlose Zeichen zulassen; alles andere behandeln wie „in der Wurzel“.
    if ($base !== '' && !preg_match('#\A(/[A-Za-z0-9._~-]+)+\z#', $base)) {
        $base = '';
    }
    return $base;
}

/** Wurzelbezogener Pfad ('/impressum/', '/#hire', '/assets/…') → Pfad inkl. Basis-Präfix. */
function url(string $path = '/'): string
{
    $path = '/' . ltrim($path, '/');
    return base_path() . $path;
}

/** Absolute URL auf Basis von base_url (für canonical, og:url, Strukturdaten). */
function absolute_url(string $path = '/'): string
{
    global $config;
    $root = rtrim($config['base_url'], '/');
    // Zeigt eine eigene Domain direkt auf diesen Ordner (reichi.it → htdocs/it/), kennt sie den
    // lokalen Unterordner nicht – er gehört dann nicht in canonical, og:url & Co.
    if (!empty($config['own_domain'])) {
        return $root . '/' . ltrim($path, '/');
    }
    return $root . url($path);
}

/** Pfad zu einer Datei unter assets/ im Webroot, mit Cache-Busting per Änderungszeit. */
function asset(string $path): string
{
    $file = PUBLIC_DIR . '/assets/' . ltrim($path, '/');
    $version = is_file($file) ? '?v=' . dechex((int) filemtime($file)) : '';
    return url('/assets/' . ltrim($path, '/')) . $version;
}

/** Sicherheits-Header für alle Antworten. Funktioniert unabhängig vom Webserver. */
function send_security_headers(): void
{
    if (headers_sent()) {
        return;
    }
    header_remove('X-Powered-By');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: camera=(), microphone=(), geolocation=(), payment=(), usb=()');
    header('Cross-Origin-Opener-Policy: same-origin');
    // Nur mit konfiguriertem Cloudflare Turnstile werden dessen Script- und Frame-Quelle erlaubt
    // (laut Cloudflare-Dokumentation genügen script-src und frame-src).
    global $config;
    $t = $config['turnstile'] ?? [];
    $cf = is_array($t) && trim((string) ($t['site_key'] ?? '')) !== '' && trim((string) ($t['secret_key'] ?? '')) !== '';
    header(
        "Content-Security-Policy: default-src 'none'; script-src 'self'" . ($cf ? ' https://challenges.cloudflare.com' : '') . "; style-src 'self'; "
        . "img-src 'self' data:; font-src 'self'; connect-src 'self'; manifest-src 'self'; "
        . ($cf ? 'frame-src https://challenges.cloudflare.com; ' : '')
        . "form-action 'self'; base-uri 'self'; frame-ancestors 'none'; object-src 'none'"
        . (is_https() ? '; upgrade-insecure-requests' : '')
    );
}

function is_https(): bool
{
    global $config;
    if (!empty($config['force_secure_cookie'])) {
        return true;
    }
    if (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') {
        return true;
    }
    return ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '') === 'https';
}

/**
 * Session nur dort starten, wo sie gebraucht wird (Startseite mit Formular, Formularverarbeitung).
 * Der Cookie ist ein technisch notwendiger Session-Cookie (CSRF-Schutz, Statusmeldungen).
 */
function start_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }
    session_name('reichi_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'secure' => is_https(),
        'httponly' => true,
        'samesite' => 'Lax',
    ]);
    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    session_start();
}

function csrf_token(): string
{
    start_session();
    if (empty($_SESSION['csrf_token']) || !is_string($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function csrf_valid(?string $token): bool
{
    start_session();
    return is_string($token)
        && !empty($_SESSION['csrf_token'])
        && hash_equals($_SESSION['csrf_token'], $token);
}

/** Einmalige Statusmeldung (Flash) für den Post/Redirect/Get-Ablauf. */
function flash_set(string $key, mixed $value): void
{
    start_session();
    $_SESSION['flash'][$key] = $value;
}

function flash_get(string $key, mixed $default = null): mixed
{
    if (session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['flash'][$key])) {
        return $default;
    }
    $value = $_SESSION['flash'][$key];
    unset($_SESSION['flash'][$key]);
    return $value;
}

/**
 * Responsives <picture>-Element für ein Foto aus content.php.
 *
 * $photo = ['file' => 'stage-red', 'widths' => [640, 1024, ...], 'width' => 2048, 'height' => 1365,
 *           'alt' => '…', 'portrait' => ['widths' => [540, 900], 'width' => 1092, 'height' => 1365]]
 * $options: sizes (string), loading ('lazy'|'eager'), fetchpriority, class, portrait_media,
 *           alt (überschreibt $photo['alt']), lead_sources (fertige <source>-Elemente, die vor
 *           den Foto-Quellen stehen – z. B. ein anderes Bild für Geräte mit Maus),
 *           version (Cache-Bust, z. B. photo_version(); jpg/webp liegen ein Jahr immutable)
 */
function picture(array $photo, array $options = []): string
{
    $base = url('/assets/images/photos/' . $photo['file']);
    $sizes = $options['sizes'] ?? '100vw';
    $loading = $options['loading'] ?? 'lazy';
    $class = $options['class'] ?? '';
    $decoding = $loading === 'eager' ? 'sync' : 'async';
    $version = (string) ($options['version'] ?? '');
    $query = $version !== '' ? '?v=' . rawurlencode($version) : '';

    $srcset = static function (string $prefix, array $widths, string $ext) use ($query): string {
        return implode(', ', array_map(
            static fn(int $w): string => "{$prefix}-{$w}.{$ext}{$query} {$w}w",
            $widths
        ));
    };

    $html = '<picture>' . ($options['lead_sources'] ?? '');
    if (!empty($photo['portrait']) && !empty($options['portrait_media'])) {
        $media = e($options['portrait_media']);
        $pw = $photo['portrait']['widths'];
        $html .= '<source media="' . $media . '" type="image/webp" srcset="'
            . e($srcset($base . '-portrait', $pw, 'webp')) . '" sizes="' . e($sizes) . '">';
        $html .= '<source media="' . $media . '" type="image/jpeg" srcset="'
            . e($srcset($base . '-portrait', $pw, 'jpg')) . '" sizes="' . e($sizes) . '">';
    }
    $html .= '<source type="image/webp" srcset="' . e($srcset($base, $photo['widths'], 'webp'))
        . '" sizes="' . e($sizes) . '">';
    $largest = max($photo['widths']);
    $html .= '<img src="' . e("{$base}-{$largest}.jpg{$query}") . '" srcset="'
        . e($srcset($base, $photo['widths'], 'jpg')) . '" sizes="' . e($sizes) . '"'
        . ' width="' . (int) $photo['width'] . '" height="' . (int) $photo['height'] . '"'
        . ' alt="' . e($options['alt'] ?? $photo['alt']) . '" loading="' . e($loading) . '" decoding="' . $decoding . '"'
        . (!empty($options['fetchpriority']) ? ' fetchpriority="' . e($options['fetchpriority']) . '"' : '')
        . ($class !== '' ? ' class="' . e($class) . '"' : '')
        . '>';
    $html .= '</picture>';
    return $html;
}

/**
 * Cache-Bust für ein Foto. Hängt an der größten WebP-Variante: die Dateien sind
 * ein Jahr immutable, ein Austausch unter demselben Namen wäre sonst unsichtbar.
 */
function photo_version(array $photo): string
{
    $file = PUBLIC_DIR . '/assets/images/photos/' . $photo['file'] . '-' . max($photo['widths']) . '.webp';
    return is_file($file) ? dechex((int) filemtime($file)) : '';
}

/** Größte JPEG-Variante eines Fotos (Ziel für Lightbox / „Bild öffnen“). */
function photo_full_url(array $photo): string
{
    return url('/assets/images/photos/' . $photo['file'] . '-' . max($photo['widths']) . '.jpg');
}

/** <img> für ein Logo aus content.php (1x/2x, wenn vorhanden). */
function logo_img(array $logo, string $class = ''): string
{
    $dir = url('/assets/images/logos/');
    $attrs = ' width="' . (int) $logo['width'] . '" height="' . (int) $logo['height'] . '"'
        . ' alt="' . e($logo['alt']) . '" loading="lazy" decoding="async"'
        . ($class !== '' ? ' class="' . e($class) . '"' : '');
    if (!empty($logo['file2x'])) {
        return '<img src="' . e($dir . $logo['file']) . '" srcset="' . e($dir . $logo['file']) . ' 1x, '
            . e($dir . $logo['file2x']) . ' 2x"' . $attrs . '>';
    }
    return '<img src="' . e($dir . $logo['file']) . '"' . $attrs . '>';
}

/** Telefonnummer für tel:-Links (nur Ziffern und führendes Plus). */
function tel_href(string $number): string
{
    return 'tel:' . preg_replace('/[^0-9+]/', '', $number);
}

/** Inline-SVG-Icons (keine Icon-Bibliothek nötig). */
function icon(string $name, string $class = 'icon'): string
{
    $paths = [
        'arrow-right' => '<path d="M5 12h14M13 6l6 6-6 6"/>',
        'arrow-down' => '<path d="M12 5v14M6 13l6 6 6-6"/>',
        'external' => '<path d="M14 4h6v6M20 4l-9 9M19 14v5a1 1 0 0 1-1 1H5a1 1 0 0 1-1-1V6a1 1 0 0 1 1-1h5"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="1.5"/><path d="M3 7l9 6 9-6"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2z"/>',
        'pin' => '<path d="M12 21s-7-6.2-7-11.5A7 7 0 0 1 19 9.5C19 14.8 12 21 12 21z"/><circle cx="12" cy="9.5" r="2.5"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><circle cx="17.5" cy="6.5" r="1" fill="currentColor" stroke="none"/>',
        'close' => '<path d="M6 6l12 12M18 6L6 18"/>',
        'chevron-left' => '<path d="M15 5l-7 7 7 7"/>',
        'chevron-right' => '<path d="M9 5l7 7-7 7"/>',
        'expand' => '<path d="M4 9V4h5M15 4h5v5M20 15v5h-5M9 20H4v-5"/>',
    ];
    if (!isset($paths[$name])) {
        return '';
    }
    return '<svg class="' . e($class) . '" viewBox="0 0 24 24" width="24" height="24" fill="none" stroke="currentColor" '
        . 'stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true" focusable="false">'
        . $paths[$name] . '</svg>';
}

/** Vektor-Wortzeichen „R“ im Sechseck (Pfaddaten aus assets-src/originals/reichi-mark.svg, normiert auf 0 0 100 100 – siehe tools/mark.py). */
function logo_mark(string $class = 'mark'): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 100 100" width="40" height="40" aria-hidden="true" focusable="false">'
        . '<g fill="currentColor" fill-rule="evenodd">'
        . '<path d="M92.31 24.01L51.35 0.36C50.51 -0.12 49.49 -0.12 48.65 0.36L7.69 24.01C6.84 24.48 6.33 25.38 6.33 26.35L6.33 73.65C6.33 74.62 6.84 75.51 7.69 75.99L48.65 99.64C49.49 100.12 50.51 100.12 51.35 99.64L92.31 75.99C93.16 75.52 93.67 74.62 93.67 73.65L93.67 26.35C93.67 25.38 93.16 24.49 92.31 24.01ZM83.86 67.99C83.86 68.96 83.34 69.84 82.50 70.33L51.35 88.32C50.51 88.80 49.49 88.80 48.65 88.32L17.50 70.33C16.66 69.86 16.14 68.96 16.14 67.99L16.14 32.02C16.14 31.05 16.66 30.17 17.50 29.68L48.65 11.69C49.49 11.22 50.51 11.22 51.35 11.69L82.50 29.68C83.34 30.16 83.86 31.05 83.86 32.02L83.86 67.99Z"/>'
        . '<path d="M73.23 61.53L73.00 61.40L71.67 60.64C70.92 60.20 70.17 59.77 69.42 59.34C68.46 58.79 67.51 58.24 66.55 57.69C65.50 57.08 64.44 56.47 63.38 55.86C62.32 55.25 61.26 54.63 60.20 54.02C59.23 53.46 58.28 52.90 57.31 52.35C56.55 51.90 55.79 51.46 55.03 51.03C54.72 50.85 54.41 50.68 54.09 50.51C53.23 50.01 53.77 49.61 54.50 49.20C54.87 48.99 55.23 48.79 55.60 48.60C56.42 48.12 57.25 47.64 58.07 47.16C59.05 46.60 60.04 46.02 61.02 45.45C62.05 44.84 63.09 44.24 64.12 43.64C65.11 43.06 66.10 42.49 67.09 41.91C67.92 41.42 68.76 40.93 69.59 40.46C70.17 40.11 70.75 39.79 71.32 39.44C71.53 39.32 71.75 39.19 71.96 39.07C72.70 38.64 73.16 37.84 73.16 37.00C73.16 36.15 72.70 35.35 71.96 34.93L52.54 23.71C50.97 22.81 49.02 22.81 47.45 23.71L28.54 34.64C26.97 35.54 26.00 37.23 26.00 39.03L26.00 62.89C26.00 63.85 26.89 64.61 27.89 64.40C28.63 64.24 29.10 63.51 29.10 62.76L29.10 39.03C29.10 38.33 29.48 37.67 30.09 37.33L48.98 26.43C49.61 26.06 50.40 26.06 51.05 26.43C51.07 26.44 51.09 26.45 51.11 26.47C51.34 26.60 51.56 26.73 51.78 26.86C52.21 27.10 52.63 27.35 53.05 27.59C53.63 27.92 54.20 28.25 54.77 28.59C55.46 28.98 56.15 29.38 56.83 29.77C57.58 30.20 58.33 30.63 59.08 31.07L61.39 32.40C62.14 32.83 62.88 33.26 63.63 33.69C64.31 34.08 64.98 34.48 65.67 34.86C66.23 35.19 66.79 35.51 67.36 35.84L68.58 36.55C68.71 36.62 69.15 36.81 69.12 37.01C69.11 37.11 69.05 37.19 68.97 37.24C68.67 37.42 68.36 37.60 68.04 37.78L66.59 38.64C65.96 39.00 65.35 39.36 64.72 39.73C64.01 40.14 63.30 40.56 62.59 40.97C61.82 41.41 61.07 41.85 60.31 42.30C59.55 42.74 58.79 43.18 58.03 43.63C57.31 44.05 56.58 44.47 55.87 44.89C55.23 45.26 54.59 45.63 53.96 46.00C53.44 46.30 52.93 46.60 52.43 46.89C52.09 47.08 51.76 47.29 51.42 47.48C51.30 47.55 51.17 47.63 51.06 47.70C50.23 48.18 49.73 49.04 49.73 50.01C49.73 50.97 50.23 51.84 51.06 52.31L71.70 64.24C71.94 64.38 72.21 64.44 72.48 64.44C73.01 64.44 73.53 64.17 73.82 63.67C74.25 62.93 73.99 61.98 73.25 61.55L73.23 61.53Z"/>'
        . '</g></svg>';
}

/** Template einbinden; $vars stehen dort als lokale Variablen bereit. */
function render(string $template, array $vars = []): void
{
    global $config, $content;
    extract($vars, EXTR_SKIP);
    require APP_DIR . '/templates/' . $template . '.php';
}
