<?php
/**
 * Diagnostico TEMPORAL para 500 en produccion.
 * Borrar este archivo apenas se resuelva el problema.
 *
 * Acceso: https://counsel.kyrosrd.com/_debug.php
 */

// Forzar visibilidad de errores SOLO en esta pagina.
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

header('Content-Type: text/plain; charset=utf-8');

$ok = "[OK]   ";
$bad = "[FAIL] ";
$warn = "[WARN] ";

echo "=== KyrosCounsel diagnostico ===\n\n";

echo "PHP version: " . PHP_VERSION . "\n";
echo "SAPI: " . PHP_SAPI . "\n";
echo "HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '(none)') . "\n";
echo "HTTPS: " . ($_SERVER['HTTPS'] ?? '(off)') . "\n";
echo "DOCUMENT_ROOT: " . ($_SERVER['DOCUMENT_ROOT'] ?? '?') . "\n";
echo "SCRIPT_FILENAME: " . ($_SERVER['SCRIPT_FILENAME'] ?? '?') . "\n";
echo "\n";

// PHP minimo
echo (version_compare(PHP_VERSION, '8.0.0', '>=') ? $ok : $bad);
echo "PHP 8.0+ requerido\n";

// Extensiones
$exts = ['pdo','pdo_mysql','sodium','openssl','mbstring','json','session','filter'];
foreach ($exts as $e) {
    echo (extension_loaded($e) ? $ok : $bad) . "ext: {$e}\n";
}
echo "\n";

// Cargar config
echo "-- Cargando config.php --\n";
try {
    define('BASE_PATH', __DIR__);
    require __DIR__ . '/config.php';
    echo $ok . "config.php cargado\n";
    echo "  APP_ENV   = " . APP_ENV . "\n";
    echo "  APP_DEBUG = " . (APP_DEBUG ? 'true' : 'false') . "\n";
    echo "  APP_URL   = " . APP_URL . "\n";
    echo "  DB_HOST   = " . DB_HOST . "\n";
    echo "  DB_NAME   = " . DB_NAME . "\n";
    echo "  DB_USER   = " . DB_USER . "\n";
    echo "  SESSION_SECURE = " . (SESSION_SECURE ? 'true' : 'false') . "\n";
} catch (Throwable $e) {
    echo $bad . "config: " . $e->getMessage() . "\n";
    exit;
}
echo "\n";

// Conexion DB
echo "-- Probando DB --\n";
try {
    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s', DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5,
    ]);
    $n = (int)$pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    echo $ok . "DB conectada. users count = {$n}\n";
} catch (Throwable $e) {
    echo $bad . "DB: " . $e->getMessage() . "\n";
}
echo "\n";

// Permisos
echo "-- Permisos --\n";
$paths = [
    __DIR__,
    __DIR__ . '/storage',
    __DIR__ . '/storage/sessions',
    __DIR__ . '/storage/logs',
    __DIR__ . '/uploads',
];
foreach ($paths as $p) {
    if (!file_exists($p)) {
        echo $bad . "no existe: {$p}\n";
        continue;
    }
    $perm = substr(sprintf('%o', fileperms($p)), -4);
    $w = is_writable($p) ? 'writable' : 'NOT writable';
    echo (is_writable($p) ? $ok : $warn) . "{$p}  perms={$perm}  {$w}\n";
}
echo "\n";

// Sodium quick check
echo "-- Sodium --\n";
if (function_exists('sodium_crypto_secretbox_keygen')) {
    try {
        $k = base64_decode(ENCRYPTION_KEY, true);
        if ($k === false || strlen($k) !== SODIUM_CRYPTO_SECRETBOX_KEYBYTES) {
            echo $bad . "ENCRYPTION_KEY invalida (len=" . ($k===false?'?':strlen($k)) . " esperado=" . SODIUM_CRYPTO_SECRETBOX_KEYBYTES . ")\n";
        } else {
            echo $ok . "ENCRYPTION_KEY valida (32 bytes)\n";
        }
    } catch (Throwable $e) {
        echo $bad . "sodium: " . $e->getMessage() . "\n";
    }
} else {
    echo $bad . "sodium_crypto_secretbox_keygen no disponible\n";
}
echo "\n";

// Probar bootstrap completo
echo "-- Bootstrap completo --\n";
try {
    require __DIR__ . '/app/security.php';
    echo $ok . "app/security.php\n";
    require __DIR__ . '/app/helpers.php';
    echo $ok . "app/helpers.php\n";
    require __DIR__ . '/app/db.php';
    echo $ok . "app/db.php\n";
    require __DIR__ . '/app/session.php';
    echo $ok . "app/session.php\n";
    require __DIR__ . '/app/auth.php';
    echo $ok . "app/auth.php\n";
} catch (Throwable $e) {
    echo $bad . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "  en " . $e->getFile() . ":" . $e->getLine() . "\n";
}

echo "\n=== fin ===\n";
