<?php
/**
 * Sesion endurecida.
 *  - cookie httponly + samesite + secure (en prod)
 *  - storage en /storage/sessions (fuera de webroot)
 *  - regenerate id en login y privilege change
 *  - fingerprint IP+UA contra hijacking
 *  - idle + absolute timeout
 */

function secure_session_start() {
    if (session_status() === PHP_SESSION_ACTIVE) return;

    $sessionDir = STORAGE_PATH . '/sessions';
    if (!is_dir($sessionDir)) @mkdir($sessionDir, 0700, true);
    session_save_path($sessionDir);

    session_name(SESSION_NAME);
    session_set_cookie_params([
        'lifetime' => 0,
        'path'     => '/',
        'domain'   => '',
        'secure'   => SESSION_SECURE,
        'httponly' => true,
        'samesite' => SESSION_SAMESITE,
    ]);

    ini_set('session.gc_maxlifetime', (string)SESSION_LIFETIME);

    session_start();

    _enforce_fingerprint();
    _enforce_idle_timeout();
    _enforce_absolute_timeout();
}

function session_regenerate_for_login() {
    session_regenerate_id(true);
    $_SESSION['_started_at']   = time();
    $_SESSION['_last_activity']= time();
    $_SESSION['_fingerprint']  = _compute_fingerprint();
}

function session_destroy_secure() {
    $_SESSION = [];
    if (ini_get('session.use_cookies')) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', [
            'expires'  => time() - 42000,
            'path'     => $params['path'],
            'domain'   => $params['domain'],
            'secure'   => $params['secure'],
            'httponly' => $params['httponly'],
            'samesite' => $params['samesite'] ?? 'Strict',
        ]);
    }
    session_destroy();
}

function _compute_fingerprint() {
    $ip = $_SERVER['REMOTE_ADDR']     ?? '';
    $ua = $_SERVER['HTTP_USER_AGENT'] ?? '';
    return hash_hmac('sha256', $ip . '|' . $ua, APP_KEY);
}

function _enforce_fingerprint() {
    if (empty($_SESSION['_fingerprint'])) {
        $_SESSION['_fingerprint'] = _compute_fingerprint();
        return;
    }
    if (!hash_equals($_SESSION['_fingerprint'], _compute_fingerprint())) {
        error_log('[security] session fingerprint mismatch ip=' . ($_SERVER['REMOTE_ADDR'] ?? ''));
        session_destroy_secure();
        session_start();
    }
}

function _enforce_idle_timeout() {
    $now = time();
    $last = $_SESSION['_last_activity'] ?? $now;
    if ($now - $last > SESSION_IDLE_TIMEOUT) {
        session_destroy_secure();
        session_start();
        return;
    }
    $_SESSION['_last_activity'] = $now;
}

function _enforce_absolute_timeout() {
    $started = $_SESSION['_started_at'] ?? null;
    if ($started === null) {
        $_SESSION['_started_at'] = time();
        return;
    }
    if (time() - (int)$started > SESSION_LIFETIME) {
        session_destroy_secure();
        session_start();
    }
}
