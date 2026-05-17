<?php
/**
 * KyrosCounsel - Configuracion central.
 *
 * En produccion, mueve estos valores a variables de entorno o a un archivo
 * fuera del webroot. En XAMPP local los dejamos aqui por simplicidad.
 *
 * Importante: cambia APP_KEY y ENCRYPTION_KEY antes de cualquier despliegue real.
 * Genera nuevas con:
 *   php -r "echo bin2hex(random_bytes(32));"
 *   php -r "echo base64_encode(sodium_crypto_secretbox_keygen());"
 */

// ----- App -----
define('APP_NAME', 'KyrosCounsel');
define('APP_ENV',  'local');                // 'local' o 'production'
define('APP_DEBUG', true);                  // false en produccion
define('APP_URL',  'http://localhost/KyrosCounsel');
define('APP_TIMEZONE', 'America/Santo_Domingo');

// ----- Claves criptograficas (CAMBIAR EN PRODUCCION) -----
define('APP_KEY', '7f3a9d2e8b1c4f6a5d8e3b9c2f7a4d1e6b8c3f9a2d5e7b4c1f8a6d3e9b2c5f7a');
define('ENCRYPTION_KEY', 'EQUjz8mKpVzQyL4nXcBhA2Sv9TfRdGwYx7eUkPsM3Ng=');

// ----- Base de datos -----
define('DB_HOST',     '127.0.0.1');
define('DB_PORT',     3306);
define('DB_NAME',     'kyroscounsel');
define('DB_USER',     'root');
define('DB_PASS',     '');
define('DB_CHARSET',  'utf8mb4');
define('DB_COLLATION','utf8mb4_unicode_ci');

// ----- Sesion -----
define('SESSION_NAME', 'kyros_sid');
define('SESSION_LIFETIME', 7200);           // 2 horas absoluto
define('SESSION_IDLE_TIMEOUT', 1800);       // 30 min idle
define('SESSION_SECURE', false);            // true en produccion (HTTPS)
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
