<?php
/**
 * Bootstrap: carga config + runtime + headers + sesion + modulos.
 */

require_once BASE_PATH . '/config.php';

date_default_timezone_set(APP_TIMEZONE);
mb_internal_encoding('UTF-8');

ini_set('display_errors', APP_DEBUG ? '1' : '0');
ini_set('display_startup_errors', APP_DEBUG ? '1' : '0');
error_reporting(APP_DEBUG ? E_ALL : (E_ALL & ~E_DEPRECATED & ~E_NOTICE));

ini_set('expose_php', '0');
ini_set('session.use_strict_mode', '1');
ini_set('session.use_only_cookies', '1');
ini_set('session.use_trans_sid', '0');

ini_set('log_errors', '1');
ini_set('error_log', STORAGE_PATH . '/logs/php-errors.log');

require_once APP_PATH . '/security.php';
send_security_headers();

require_once APP_PATH . '/session.php';
secure_session_start();

require_once APP_PATH . '/helpers.php';
require_once APP_PATH . '/db.php';
require_once APP_PATH . '/audit.php';
require_once APP_PATH . '/auth.php';
require_once APP_PATH . '/tenant.php';
require_once APP_PATH . '/rbac.php';
require_once APP_PATH . '/countries.php';
require_once APP_PATH . '/totp.php';
require_once APP_PATH . '/resend.php';
require_once APP_PATH . '/workflows.php';
require_once APP_PATH . '/immigration.php';
require_once APP_PATH . '/notifications.php';
require_once APP_PATH . '/templates.php';
require_once APP_PATH . '/stripe.php';
require_once APP_PATH . '/admin.php';

// Modo mantenimiento — bloquea todo excepto super_admin y endpoints esenciales.
// Verificar después de cargar admin.php (necesita maintenance_mode_active()).
if (PHP_SAPI !== 'cli'
    && function_exists('maintenance_mode_active')
    && maintenance_mode_active()) {

    $reqPath = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
    $script  = $_SERVER['SCRIPT_NAME'] ?? '';
    $base    = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($base !== '' && strpos($reqPath, $base) === 0) {
        $reqPath = substr($reqPath, strlen($base));
    }
    $reqPath = '/' . trim($reqPath, '/');

    // Allowlist: el super admin sigue operando + login + logout + assets + healthcheck
    $isSuper = (($_SESSION['user_role'] ?? null) === 'super_admin');
    $allowed = $isSuper
        || strpos($reqPath, '/admin') === 0
        || in_array($reqPath, ['/login', '/logout'], true)
        || strpos($reqPath, '/assets') === 0
        || $reqPath === '/admin/health.json';

    if (!$allowed) {
        http_response_code(503);
        header('Retry-After: 600');
        $message = setting_get('maintenance_message', 'Estamos realizando mejoras. Volvemos pronto.');
        require VIEWS_PATH . '/common/maintenance.php';
        exit;
    }
}
