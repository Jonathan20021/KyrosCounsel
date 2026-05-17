<?php
/**
 * Auth helpers: hashing, rate limit, guards.
 * El flujo de login esta en app/controllers/auth_ctrl.php.
 */

function password_hash_make($password) {
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_ARGON2I;
    return password_hash($password, $algo, [
        'memory_cost' => HASH_MEMORY_COST,
        'time_cost'   => HASH_TIME_COST,
        'threads'     => HASH_THREADS,
    ]);
}

function password_hash_verify($password, $hash) {
    return password_verify($password, $hash);
}

function password_needs_upgrade($hash) {
    $algo = defined('PASSWORD_ARGON2ID') ? PASSWORD_ARGON2ID : PASSWORD_ARGON2I;
    return password_needs_rehash($hash, $algo, [
        'memory_cost' => HASH_MEMORY_COST,
        'time_cost'   => HASH_TIME_COST,
        'threads'     => HASH_THREADS,
    ]);
}

function rate_limit_check($key, $max, $window) {
    $bucket = hash('sha256', $key);
    $now = time();
    db_run('DELETE FROM rate_limits WHERE bucket = :b AND created_at < :w',
        ['b' => $bucket, 'w' => $now - $window]);
    $row = db_one('SELECT COUNT(*) AS c FROM rate_limits WHERE bucket = :b', ['b' => $bucket]);
    if ((int)($row['c'] ?? 0) >= $max) return false;
    db_run('INSERT INTO rate_limits (bucket, created_at) VALUES (:b, :t)',
        ['b' => $bucket, 't' => $now]);
    return true;
}

function rate_limit_clear($key) {
    db_run('DELETE FROM rate_limits WHERE bucket = :b', ['b' => hash('sha256', $key)]);
}

function do_logout() {
    $uid = $_SESSION['user_id'] ?? null;
    if ($uid) audit_record(AUDIT_LOGOUT, (int)$uid);
    session_destroy_secure();
}

function is_logged_in() { return !empty($_SESSION['user_id']); }

function require_login() {
    if (!is_logged_in()) {
        flash_set('error', 'Debes iniciar sesion.');
        redirect(url('login'));
    }
}

function require_super_admin() {
    require_login();
    if (($_SESSION['user_role'] ?? null) !== 'super_admin') {
        audit_record(AUDIT_PERMISSION_DENIED, $_SESSION['user_id'] ?? null,
            ['required' => 'super_admin', 'actual' => $_SESSION['user_role'] ?? null]);
        abort(403, 'Solo super administradores.');
    }
}

function require_guest() {
    if (is_logged_in()) {
        if (($_SESSION['user_role'] ?? null) === 'super_admin') redirect(url('admin'));
        $t = db_one('SELECT slug FROM tenants WHERE id = :id LIMIT 1',
            ['id' => $_SESSION['user_tenant_id']]);
        if ($t) redirect(url('t/' . $t['slug'] . '/dashboard'));
        redirect(url('/'));
    }
}

function current_user() { return $_SESSION['user'] ?? null; }
