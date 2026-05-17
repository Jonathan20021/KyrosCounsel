<?php
/**
 * KyrosCounsel - Configuracion central con auto-deteccion de entorno.
 *
 * Detecta automaticamente local (XAMPP) vs produccion segun:
 *  - Web:  si HTTP_HOST contiene "localhost" o "127.0.0.1"  -> local
 *  - CLI:  si la env var KYROS_ENV=production               -> produccion
 *                                                            (default local)
 *
 * Para correr seed.php contra produccion desde local:
 *   set KYROS_ENV=production && "C:/xampp/php/php.exe" seed.php   (CMD)
 *   $env:KYROS_ENV='production'; & "C:/xampp/php/php.exe" seed.php (PowerShell)
 *
 * Genera nuevas keys con:
 *   php -d extension=sodium -r "echo bin2hex(random_bytes(32)).PHP_EOL.base64_encode(sodium_crypto_secretbox_keygen()).PHP_EOL;"
 */

// ----- Deteccion de entorno -----
$__is_local = true;
if (PHP_SAPI === 'cli') {
    $__env_override = getenv('KYROS_ENV');
    if ($__env_override === 'production') $__is_local = false;
} else {
    $__host = $_SERVER['HTTP_HOST'] ?? '';
    if ($__host !== '' && !preg_match('/^(localhost|127\.0\.0\.1)/i', $__host)) {
        $__is_local = false;
    }
}

// ----- App -----
define('APP_NAME', 'KyrosCounsel');
define('APP_ENV',  $__is_local ? 'local' : 'production');
define('APP_DEBUG', $__is_local);
define('APP_TIMEZONE', 'America/Santo_Domingo');

if ($__is_local) {
    define('APP_URL', 'http://localhost/KyrosCounsel');
} else {
    // En produccion se autoresuelve por el host real (https forzado).
    $__scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $__host_p = $_SERVER['HTTP_HOST'] ?? 'kyroscounsel.com';
    define('APP_URL', $__scheme . '://' . $__host_p);
}

// ----- Claves criptograficas -----
if ($__is_local) {
    define('APP_KEY',        '7f3a9d2e8b1c4f6a5d8e3b9c2f7a4d1e6b8c3f9a2d5e7b4c1f8a6d3e9b2c5f7a');
    define('ENCRYPTION_KEY', 'EQUjz8mKpVzQyL4nXcBhA2Sv9TfRdGwYx7eUkPsM3Ng=');
} else {
    define('APP_KEY',        'e557e985489dd9cdf68ff65f5dbc18c1bbcd9752c59b5aae90923e60d700d462');
    define('ENCRYPTION_KEY', 'B12WZLvo0ta0bfzYPar990CUsZFG+k2okxsJa21xqYM=');
}

// ----- Base de datos -----
if ($__is_local) {
    define('DB_HOST', '127.0.0.1');
    define('DB_PORT', 3306);
    define('DB_NAME', 'kyroscounsel');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    define('DB_HOST', '129.121.81.172');
    define('DB_PORT', 3306);
    define('DB_NAME', 'neetjbte_counsel');
    define('DB_USER', 'neetjbte_counsel');
    define('DB_PASS', 'Hacker#2002');
}
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATION','utf8mb4_unicode_ci');

// ----- Sesion -----
define('SESSION_NAME', 'kyros_sid');
define('SESSION_LIFETIME', 7200);           // 2 horas absoluto
define('SESSION_IDLE_TIMEOUT', 1800);       // 30 min idle
define('SESSION_SECURE', !$__is_local);     // true en produccion (HTTPS)
define('SESSION_SAMESITE', 'Strict');

// ----- Argon2 (password hashing) -----
define('HASH_MEMORY_COST', 65536);
define('HASH_TIME_COST', 4);
define('HASH_THREADS', 2);

// ----- Rate limit login -----
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_WINDOW_SECONDS', 900);

// ----- Paths -----
if (!defined('BASE_PATH'))    define('BASE_PATH',    __DIR__);
if (!defined('APP_PATH'))     define('APP_PATH',     __DIR__ . '/app');
if (!defined('VIEWS_PATH'))   define('VIEWS_PATH',   __DIR__ . '/app/views');
if (!defined('STORAGE_PATH')) define('STORAGE_PATH', __DIR__ . '/storage');

unset($__is_local, $__env_override, $__host, $__scheme, $__host_p);
