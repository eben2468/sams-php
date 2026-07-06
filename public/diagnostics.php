<?php

/*
|--------------------------------------------------------------------------
| SAMS — Live diagnostics (TEMPORARY)
|--------------------------------------------------------------------------
| Upload this file with the rest of the app, then open it directly:
|     https://YOUR-DOMAIN/diagnostics.php      (or /public/diagnostics.php)
|
| Copy the whole output and send it back. DELETE this file afterwards — it
| exposes server paths and should not stay on a production site.
*/

header('Content-Type: text/plain; charset=UTF-8');

$root = dirname(__DIR__);
require $root . '/app/autoload.php';

function line(string $label, $value): void
{
    echo str_pad($label, 26) . ': ' . (is_bool($value) ? ($value ? 'yes' : 'no') : $value) . "\n";
}

echo "==================== SAMS LIVE DIAGNOSTICS ====================\n\n";

echo "----- PHP / SERVER -----\n";
line('PHP version', PHP_VERSION);
line('OS', PHP_OS);
line('HTTP_HOST', $_SERVER['HTTP_HOST'] ?? '(none)');
line('HTTPS', !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'on' : 'off');
line('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'] ?? '(none)');
line('SCRIPT_NAME', $_SERVER['SCRIPT_NAME'] ?? '(none)');
line('SCRIPT_FILENAME', $_SERVER['SCRIPT_FILENAME'] ?? '(none)');
line('REQUEST_URI', $_SERVER['REQUEST_URI'] ?? '(none)');
line('mod_rewrite loaded', function_exists('apache_get_modules') ? (in_array('mod_rewrite', apache_get_modules()) ? 'yes' : 'NO') : 'unknown (not mod_php)');
line('project root (disk)', $root);
echo "\n";

echo "----- URL GENERATION (base_uri) -----\n";
$base = base_uri();
line('base_uri()', $base === '' ? '(empty = site root)' : $base);
line('asset(css/app.css)', asset('css/app.css'));
line('sample media URL', media('branding/example.png'));
echo "\n";

echo "----- UPLOADS DIRECTORY -----\n";
$uploads = $root . '/public/uploads';
line('uploads path', $uploads);
line('uploads exists', is_dir($uploads));
line('uploads writable', is_writable($uploads));
$branding = $uploads . '/branding';
line('branding exists', is_dir($branding));
if (is_dir($branding)) {
    $files = array_values(array_diff(scandir($branding), ['.', '..']));
    line('branding file count', count($files));
    foreach ($files as $f) {
        echo '    - ' . $f . ' (' . filesize($branding . '/' . $f) . " bytes)\n";
    }
}
echo "\n";

echo "----- DATABASE / SETTINGS -----\n";
try {
    $logo = \App\Models\SystemSetting::get('logo');
    $name = \App\Models\SystemSetting::get('app_name');
    line('DB connection', 'OK');
    line('app_name setting', $name === null ? '(not set)' : $name);
    line('logo setting', $logo === null ? '(not set)' : $logo);

    // Database-stored logo (the filesystem-independent path).
    try {
        $dbLogo = \App\Core\Database::selectOne("SELECT `mime`, LENGTH(`data`) AS len, `updated_at` FROM `app_files` WHERE `name` = 'logo'");
        if ($dbLogo) {
            line('DB logo (app_files)', 'present — ' . $dbLogo['mime'] . ', ' . $dbLogo['len'] . ' bytes');
            line('=> DB logo URL', $base . '/branding/logo');
        } else {
            line('DB logo (app_files)', '(no row)');
        }
    } catch (\Throwable $e) {
        line('DB logo (app_files)', 'table missing — run the app_files CREATE TABLE');
    }

    if ($logo && $logo !== 'db') {
        $logoFile = $uploads . '/' . ltrim($logo, '/');
        line('logo file path', $logoFile);
        line('logo file exists', is_file($logoFile));
        line('logo file readable', is_file($logoFile) && is_readable($logoFile));
        line('=> logo URL in pages', media($logo));
    }

    line('=> brand_logo() resolves', brand_logo() ?? '(null — default icon shown)');
} catch (\Throwable $e) {
    line('DB connection', 'FAILED');
    line('error', $e->getMessage());
}
echo "\n";

echo "----- ROUTE TESTS (open these in the browser) -----\n";
$origin = rtrim((!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' ? 'https://' : 'http://') . ($_SERVER['HTTP_HOST'] ?? ''), '/');
echo '    sample CSV : ' . $origin . $base . "/students/sample-csv\n";
echo '    media probe: ' . $origin . $base . "/media?f=branding/example.png  (expect 'Not found' = route works)\n";
echo "\n";

echo "==============================================================\n";
echo "Send this entire output back, then DELETE public/diagnostics.php\n";
