<?php
/**
 * Gemeinsamer Einstiegspunkt aller öffentlichen PHP-Dateien.
 * Lädt Konfiguration, Hilfsfunktionen und Inhalte, setzt Sicherheits-Header.
 */

declare(strict_types=1);

// Direktaufruf über den Browser unterbinden (zusätzlich zur .htaccess in app/).
if (!defined('PUBLIC_DIR')) {
    http_response_code(404);
    exit;
}

if (PHP_VERSION_ID < 80100) {
    http_response_code(500);
    header('Content-Type: text/plain; charset=utf-8');
    exit('Diese Website benötigt PHP 8.1 oder neuer.');
}

define('APP_DIR', __DIR__);

// Keine internen Fehlermeldungen an Besucher ausgeben.
ini_set('display_errors', '0');
ini_set('log_errors', '1');
error_reporting(E_ALL);

mb_internal_encoding('UTF-8');
date_default_timezone_set('Europe/Vienna');

$configFile = APP_DIR . '/config.php';
$config = require file_exists($configFile) ? $configFile : APP_DIR . '/config.example.php';
$config['using_example_config'] = !file_exists($configFile);

if (!is_dir($config['storage_dir']) && !@mkdir($config['storage_dir'], 0755, true)) {
    // Fallback auf das Systemtemp-Verzeichnis, damit die Seite auch ohne
    // beschreibbares Storage funktioniert (Rate-Limit dann weniger dauerhaft).
    $config['storage_dir'] = sys_get_temp_dir() . '/reichi-storage';
}

require APP_DIR . '/helpers.php';
$content = require APP_DIR . '/content.php';

if ((string) ($config['secret'] ?? '') === '') {
    // Kein Secret konfiguriert: einmalig erzeugen und im Storage ablegen, damit
    // die Installation per FTP ohne Shell auskommt.
    $config['secret'] = ensure_secret($config['storage_dir']);
}

send_security_headers();
