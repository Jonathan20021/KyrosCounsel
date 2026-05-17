<?php
function profile_ctrl_show($params) {
    $u = db_one('SELECT * FROM users WHERE id = :id', ['id' => $_SESSION['user_id']]);
    if (!$u) abort(404);
    render_with_layout('tenant', 'profile.show', ['title' => 'Mi perfil', 'me' => $u]);
}

function profile_ctrl_change_password($params) {
    $current = $_POST['current_password'] ?? '';
    $new1 = $_POST['new_password'] ?? '';
    $new2 = $_POST['new_password2'] ?? '';
    if ($new1 !== $new2) { flash_set('error', 'Las contrasenas no coinciden.'); redirect(tenant_url('profile')); }
    if (strlen($new1) < 8) { flash_set('error', 'Minimo 8 caracteres.'); redirect(tenant_url('profile')); }
    $u = db_one('SELECT password_hash FROM users WHERE id = :id', ['id' => $_SESSION['user_id']]);
    if (!password_hash_verify($current, $u['password_hash'])) {
        flash_set('error', 'Contrasena actual incorrecta.'); redirect(tenant_url('profile'));
    }
    db_run('UPDATE users SET password_hash = :h WHERE id = :id',
        ['h' => password_hash_make($new1), 'id' => $_SESSION['user_id']]);
    audit_record('user.password_changed', $_SESSION['user_id']);
    flash_set('success', 'Contrasena actualizada.');
    redirect(tenant_url('profile'));
}

function profile_ctrl_show_2fa_setup($params) {
    $u = db_one('SELECT * FROM users WHERE id = :id', ['id' => $_SESSION['user_id']]);
    if (!$u) abort(404);
    if (!empty($u['two_factor_enabled'])) {
        render_with_layout('tenant', 'profile.twofa_enabled', ['title' => '2FA activado', 'me' => $u]);
        return;
    }
    // Genera (o reusa) un secreto pendiente en sesion
    if (empty($_SESSION['_pending_2fa_secret'])) {
        $_SESSION['_pending_2fa_secret'] = totp_generate_secret();
    }
    $secret = $_SESSION['_pending_2fa_secret'];
    $uri = totp_provisioning_uri($secret, $u['email']);
    render_with_layout('tenant', 'profile.twofa_setup', [
        'title' => 'Activar 2FA', 'me' => $u,
        'secret' => $secret, 'qr' => totp_qr_url($uri),
    ]);
}

function profile_ctrl_enable_2fa($params) {
    $secret = $_SESSION['_pending_2fa_secret'] ?? '';
    if (!$secret) { flash_set('error', 'Sesion 2FA expirada. Reintenta.'); redirect(tenant_url('profile/2fa')); }
    $code = preg_replace('/\D/', '', $_POST['code'] ?? '');
    if (!totp_verify($secret, $code)) { flash_set('error', 'Codigo invalido.'); redirect(tenant_url('profile/2fa')); }
    $enc = pii_encrypt($secret);
    db_run('UPDATE users SET two_factor_secret = :s, two_factor_enabled = 1 WHERE id = :id',
        ['s' => $enc, 'id' => $_SESSION['user_id']]);
    unset($_SESSION['_pending_2fa_secret']);
    audit_record('user.2fa_enabled', $_SESSION['user_id']);
    flash_set('success', '2FA activado.');
    redirect(tenant_url('profile'));
}

function profile_ctrl_disable_2fa($params) {
    db_run('UPDATE users SET two_factor_secret = NULL, two_factor_enabled = 0 WHERE id = :id',
        ['id' => $_SESSION['user_id']]);
    audit_record('user.2fa_disabled', $_SESSION['user_id']);
    flash_set('success', '2FA desactivado.');
    redirect(tenant_url('profile'));
}
