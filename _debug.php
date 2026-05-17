<?php
/**
 * Diagnostico v2 — replica bootstrap completo + carga de controllers + render landing.
 * BORRAR este archivo apenas se resuelva el 500.
 */

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

$ok  = "[OK]   ";
$bad = "[FAIL] ";

echo "=== KyrosCounsel diagnostico v2 ===\n\n";
echo "PHP: " . PHP_VERSION . "  SAPI: " . PHP_SAPI . "  HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? '?') . "\n\n";

define('BASE_PATH', __DIR__);

// --- Bootstrap completo paso a paso ---
$steps = [
    'config.php'              => __DIR__ . '/config.php',
    'app/security.php'        => __DIR__ . '/app/security.php',
    'app/session.php'         => __DIR__ . '/app/session.php',
    'app/helpers.php'         => __DIR__ . '/app/helpers.php',
    'app/db.php'              => __DIR__ . '/app/db.php',
    'app/audit.php'           => __DIR__ . '/app/audit.php',
    'app/auth.php'            => __DIR__ . '/app/auth.php',
    'app/tenant.php'          => __DIR__ . '/app/tenant.php',
    'app/rbac.php'            => __DIR__ . '/app/rbac.php',
    'app/countries.php'       => __DIR__ . '/app/countries.php',
    'app/totp.php'            => __DIR__ . '/app/totp.php',
    'app/resend.php'          => __DIR__ . '/app/resend.php',
    'app/workflows.php'       => __DIR__ . '/app/workflows.php',
    'app/immigration.php'     => __DIR__ . '/app/immigration.php',
    'app/notifications.php'   => __DIR__ . '/app/notifications.php',
    'app/templates.php'       => __DIR__ . '/app/templates.php',
    'app/stripe.php'          => __DIR__ . '/app/stripe.php',
    'app/admin.php'           => __DIR__ . '/app/admin.php',
    'app/router.php'          => __DIR__ . '/app/router.php',
];

echo "-- Existencia de archivos del bootstrap --\n";
$missing = [];
foreach ($steps as $name => $path) {
    if (!file_exists($path)) {
        echo $bad . "{$name} -- NO EXISTE\n";
        $missing[] = $name;
    } else {
        echo $ok . "{$name}\n";
    }
}
if ($missing) {
    echo "\n>>> Faltan archivos en el server. Subi: " . implode(', ', $missing) . "\n";
    echo "El 500 puede ser por require_once de un archivo inexistente.\n\n";
}

// --- Cargar igual que index.php ---
echo "\n-- Cargando como bootstrap.php --\n";
try {
    require_once __DIR__ . '/config.php';

    date_default_timezone_set(APP_TIMEZONE);
    mb_internal_encoding('UTF-8');
    ini_set('log_errors', '1');
    ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

    foreach ($steps as $name => $path) {
        if ($name === 'config.php' || !file_exists($path)) continue;
        require_once $path;
        echo $ok . "require {$name}\n";
    }

    // session start (lo que hace bootstrap real)
    if (function_exists('secure_session_start') && session_status() !== PHP_SESSION_ACTIVE) {
        @secure_session_start();
        echo $ok . "secure_session_start()\n";
    }
} catch (Throwable $e) {
    echo "\n" . $bad . get_class($e) . ": " . $e->getMessage() . "\n";
    echo "  in " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit;
}

// --- Cargar controllers como index.php ---
echo "\n-- Cargando controllers (glob) --\n";
$ctrls = glob(__DIR__ . '/app/controllers/*.php');
echo "  encontrados: " . count($ctrls) . "\n";
foreach ($ctrls as $f) {
    try {
        require_once $f;
        echo $ok . basename($f) . "\n";
    } catch (Throwable $e) {
        echo $bad . basename($f) . ": " . $e->getMessage() . " @ " . $e->getFile() . ":" . $e->getLine() . "\n";
    }
}

// --- Probar ejecutar landing ---
echo "\n-- Ejecutando public_ctrl_landing (silencioso) --\n";
if (!function_exists('public_ctrl_landing')) {
    echo $bad . "funcion no existe\n";
} else {
    try {
        ob_start();
        public_ctrl_landing([]);
        $html = ob_get_clean();
        echo $ok . "render OK, bytes=" . strlen($html) . "\n";
    } catch (Throwable $e) {
        echo $bad . get_class($e) . ": " . $e->getMessage() . "\n";
        echo "  in " . $e->getFile() . ":" . $e->getLine() . "\n";
        echo "  trace:\n" . $e->getTraceAsString() . "\n";
    }
}

// --- Ultimas lineas del error log ---
echo "\n-- storage/logs/php-errors.log (ultimas 40 lineas) --\n";
$logf = __DIR__ . '/storage/logs/php-errors.log';
if (file_exists($logf)) {
    $lines = @file($logf);
    if ($lines) {
        $tail = array_slice($lines, -40);
        echo implode('', $tail);
    } else {
        echo "(vacio)\n";
    }
} else {
    echo "(no existe {$logf})\n";
}

echo "\n=== fin ===\n";
