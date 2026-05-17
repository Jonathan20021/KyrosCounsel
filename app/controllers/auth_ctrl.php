<?php
/**
 * Auth: login (con 2FA opcional), logout.
 */

function auth_ctrl_show_login($params) {
    render_with_layout('app', 'auth.login', ['title' => 'Iniciar sesion']);
}

function auth_ctrl_do_login($params) {
    $email = $_POST['email'] ?? '';
    $pass  = $_POST['password'] ?? '';
    $_SESSION['_old']['email'] = $email;
    $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    [$ok, $result] = attempt_login_pre_2fa($email, $pass, $ip);
    if (!$ok) {
        flash_set('error', $result);
        redirect(url('login'));
    }

    $user = $result;

    if (!empty($user['two_factor_enabled']) && !empty($user['two_factor_secret'])) {
        // No completamos sesion aun. Guardamos pre-auth state.
        $_SESSION['_pre_2fa_user_id'] = (int)$user['id'];
        $_SESSION['_pre_2fa_at']      = time();
        redirect(url('login/2fa'));
    }

    // Sin 2FA: completamos login
    finalize_login($user, $ip);
    redirect_after_login($user);
}

function auth_ctrl_show_2fa($params) {
    if (empty($_SESSION['_pre_2fa_user_id']) || (time() - ($_SESSION['_pre_2fa_at'] ?? 0)) > 300) {
        unset($_SESSION['_pre_2fa_user_id'], $_SESSION['_pre_2fa_at']);
        redirect(url('login'));
    }
    render_with_layout('app', 'auth.twofa', ['title' => 'Verificacion 2FA']);
}

function auth_ctrl_verify_2fa($params) {
    if (empty($_SESSION['_pre_2fa_user_id'])) redirect(url('login'));
    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
    $uid  = (int)$_SESSION['_pre_2fa_user_id'];
    $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

    if (!rate_limit_check('2fa:' . $ip . ':' . $uid, 8, 600)) {
        flash_set('error', 'Demasiados intentos. Espera 10 minutos.');
        redirect(url('login/2fa'));
    }

    $user = db_one('SELECT * FROM users WHERE id = :id LIMIT 1', ['id' => $uid]);
    if (!$user || empty($user['two_factor_secret'])) {
        unset($_SESSION['_pre_2fa_user_id']);
        flash_set('error', 'Sesion invalida.');
        redirect(url('login'));
    }

    $secret = pii_decrypt($user['two_factor_secret']);
    if (!totp_verify($secret, $code)) {
        audit_record(AUDIT_LOGIN_FAILED, $uid, ['reason' => '2fa_invalid'],
            $user['tenant_id'] ? (int)$user['tenant_id'] : null);
        flash_set('error', 'Codigo 2FA invalido.');
        redirect(url('login/2fa'));
    }

    unset($_SESSION['_pre_2fa_user_id'], $_SESSION['_pre_2fa_at']);
    finalize_login($user, $ip);
    redirect_after_login($user);
}

function auth_ctrl_do_logout($params) {
    do_logout();
    redirect(url('login'));
}

// ---- helpers de login ----

function attempt_login_pre_2fa($email, $password, $ip) {
    $email = mb_strtolower(trim((string)$email));
    if (!is_valid_email($email) || $password === '') return [false, 'Credenciales invalidas.'];

    $rlKey = 'login:' . $ip . ':' . $email;
    if (!rate_limit_check($rlKey, LOGIN_MAX_ATTEMPTS, LOGIN_WINDOW_SECONDS)) {
        security_log('login.rate_limited', ['ip' => $ip, 'email' => $email]);
        return [false, 'Demasiados intentos. Intenta en 15 minutos.'];
    }

    $user = db_one('SELECT * FROM users WHERE email = :e LIMIT 1', ['e' => $email]);

    $dummy = '$argon2i$v=19$m=65536,t=4,p=2$ZHVtbXlzYWx0ZHVtbXlzYWx0$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    if (!$user) {
        password_hash_verify($password, $dummy);
        audit_record(AUDIT_LOGIN_FAILED, null, ['email' => $email, 'reason' => 'unknown_user']);
        return [false, 'Credenciales incorrectas.'];
    }
    if (!password_hash_verify($password, $user['password_hash'])) {
        audit_record(AUDIT_LOGIN_FAILED, (int)$user['id'], ['reason' => 'wrong_password'],
            $user['tenant_id'] ? (int)$user['tenant_id'] : null);
        return [false, 'Credenciales incorrectas.'];
    }
    if ($user['status'] !== 'active') {
        audit_record(AUDIT_LOGIN_FAILED, (int)$user['id'], ['reason' => 'inactive'],
            $user['tenant_id'] ? (int)$user['tenant_id'] : null);
        return [false, 'Tu cuenta esta inactiva.'];
    }

    if (password_needs_upgrade($user['password_hash'])) {
        db_run('UPDATE users SET password_hash = :h WHERE id = :id',
            ['h' => password_hash_make($password), 'id' => $user['id']]);
    }
    rate_limit_clear($rlKey);
    return [true, $user];
}

function finalize_login($user, $ip) {
    session_regenerate_for_login();
    $_SESSION['user_id']        = (int)$user['id'];
    $_SESSION['user_tenant_id'] = $user['tenant_id'] !== null ? (int)$user['tenant_id'] : null;
    $_SESSION['user_role']      = $user['role'];
    $_SESSION['user'] = [
        'id' => (int)$user['id'], 'name' => $user['name'],
        'email' => $user['email'], 'role' => $user['role'],
    ];
    db_run('UPDATE users SET last_login_at = :t, last_login_ip = :ip WHERE id = :id',
        ['t' => date('Y-m-d H:i:s'), 'ip' => $ip, 'id' => $user['id']]);
    audit_record(AUDIT_LOGIN_SUCCESS, (int)$user['id'], null,
        $user['tenant_id'] ? (int)$user['tenant_id'] : null);
}

function redirect_after_login($user) {
    if ($user['role'] === 'super_admin') redirect(url('admin'));
    $t = db_one('SELECT slug FROM tenants WHERE id = :id LIMIT 1', ['id' => $user['tenant_id']]);
    redirect(url('t/' . ($t['slug'] ?? '') . '/dashboard'));
}
