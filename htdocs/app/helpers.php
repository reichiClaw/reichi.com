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
    return rtrim($config['base_url'], '/') . url($path);
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
 * $options: sizes (string), loading ('lazy'|'eager'), fetchpriority, class, portrait_media
 */
function picture(array $photo, array $options = []): string
{
    $base = url('/assets/images/photos/' . $photo['file']);
    $sizes = $options['sizes'] ?? '100vw';
    $loading = $options['loading'] ?? 'lazy';
    $class = $options['class'] ?? '';
    $decoding = $loading === 'eager' ? 'sync' : 'async';

    $srcset = static function (string $prefix, array $widths, string $ext): string {
        return implode(', ', array_map(
            static fn(int $w): string => "{$prefix}-{$w}.{$ext} {$w}w",
            $widths
        ));
    };

    $html = '<picture>';
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
    $html .= '<img src="' . e("{$base}-{$largest}.jpg") . '" srcset="'
        . e($srcset($base, $photo['widths'], 'jpg')) . '" sizes="' . e($sizes) . '"'
        . ' width="' . (int) $photo['width'] . '" height="' . (int) $photo['height'] . '"'
        . ' alt="' . e($photo['alt']) . '" loading="' . e($loading) . '" decoding="' . $decoding . '"'
        . (!empty($options['fetchpriority']) ? ' fetchpriority="' . e($options['fetchpriority']) . '"' : '')
        . ($class !== '' ? ' class="' . e($class) . '"' : '')
        . '>';
    $html .= '</picture>';
    return $html;
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

/** Vektor-Wortzeichen „R“ (aus dem vorhandenen safari-pinned-tab.svg der alten Website). */
function logo_mark(string $class = 'mark'): string
{
    return '<svg class="' . e($class) . '" viewBox="0 0 100 100" width="40" height="40" aria-hidden="true" focusable="false">'
        . '<g transform="translate(0,100) scale(0.1,-0.1)" fill="currentColor">'
        . '<path d="M290 883 c-107 -63 -202 -118 -210 -123 -12 -7 -15 -47 -16 -242 -1 -128 1 -242 3 -254 4 -18 71 -62 248 -160 11 -6 54 -31 95 -56 41 -25 83 -44 93 -42 19 3 419 231 427 244 3 5 5 119 5 255 0 206 -3 248 -15 255 -70 45 -402 231 -417 234 -10 1 -106 -49 -213 -111z m373 -90 c83 -49 157 -94 162 -101 10 -13 14 -376 5 -385 -13 -13 -321 -187 -330 -187 -15 0 -313 172 -325 188 -6 8 -10 85 -10 192 0 147 3 183 16 195 20 21 301 184 317 184 7 1 81 -38 165 -86z"/>'
        . '<path d="M380 709 c-58 -34 -108 -66 -112 -72 -12 -19 -9 -265 3 -272 6 -4 13 -5 15 -2 3 3 6 56 6 119 0 62 4 121 9 130 9 18 178 118 199 118 14 0 180 -92 180 -100 0 -3 -40 -29 -90 -58 -64 -37 -90 -58 -90 -72 0 -14 29 -36 103 -79 96 -57 137 -73 137 -52 0 8 -37 37 -65 51 -49 25 -125 74 -125 80 0 4 37 30 83 56 111 65 118 77 64 108 -137 81 -184 106 -197 106 -8 0 -62 -27 -120 -61z"/>'
        . '</g></svg>';
}

/** Template einbinden; $vars stehen dort als lokale Variablen bereit. */
function render(string $template, array $vars = []): void
{
    global $config, $content;
    extract($vars, EXTR_SKIP);
    require APP_DIR . '/templates/' . $template . '.php';
}
