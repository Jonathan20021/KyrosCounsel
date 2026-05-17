<?php
function users_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $users = db_select(
        'SELECT id, email, name, role, status, last_login_at, two_factor_enabled, created_at
         FROM users WHERE tenant_id = :t ORDER BY name',
        ['t' => $tid]
    );
    render_with_layout('tenant', 'users.index', ['title' => 'Usuarios', 'users' => $users]);
}

function users_ctrl_new($params) {
    render_with_layout('tenant', 'users.form', ['title' => 'Nuevo usuario']);
}

function users_ctrl_create($params) {
    $tid = current_tenant()['id'];
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $name  = trim($_POST['name'] ?? '');
    $role  = $_POST['role'] ?? 'staff';
    $pass  = $_POST['password'] ?? '';

    if (!is_valid_email($email))      { flash_set('error', 'Email invalido.'); redirect(tenant_url('users/new')); }
    if (mb_strlen($name) < 2)         { flash_set('error', 'Nombre invalido.'); redirect(tenant_url('users/new')); }
    if (!in_array($role, ['tenant_admin','attorney','paralegal','staff'], true)) {
        flash_set('error', 'Rol invalido.'); redirect(tenant_url('users/new'));
    }
    if (strlen($pass) < 8) { flash_set('error', 'Contrasena minima 8 caracteres.'); redirect(tenant_url('users/new')); }
    if (db_one('SELECT id FROM users WHERE email = :e', ['e' => $email])) {
        flash_set('error', 'Email ya registrado.'); redirect(tenant_url('users/new'));
    }

    db_run(
        'INSERT INTO users (tenant_id, email, name, password_hash, role, status)
         VALUES (:t, :e, :n, :h, :r, "active")',
        ['t' => $tid, 'e' => $email, 'n' => $name, 'h' => password_hash_make($pass), 'r' => $role]
    );
    audit_record(AUDIT_USER_CREATED, $_SESSION['user_id'] ?? null, ['email' => $email, 'role' => $role]);

    send_email($email, 'Te han invitado a ' . APP_NAME,
        '<p>Hola ' . e($name) . ',</p>' .
        '<p>Tu cuenta en <strong>' . e(current_tenant()['name']) . '</strong> esta lista.</p>' .
        '<p>Inicia sesion en: <a href="' . e(url('login')) . '">' . e(url('login')) . '</a></p>' .
        '<p>Tu contrasena temporal te la dara tu administrador.</p>',
        'user_invite', $tid);

    flash_set('success', 'Usuario creado.');
    redirect(tenant_url('users'));
}

function users_ctrl_toggle_status($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    if ((int)($_SESSION['user_id'] ?? 0) === $id) {
        flash_set('error', 'No puedes cambiar tu propio estado.');
        redirect(tenant_url('users'));
    }
    $u = db_one('SELECT id, status FROM users WHERE id = :id AND tenant_id = :t',
        ['id' => $id, 't' => $tid]);
    if (!$u) abort(404);
    $new = $u['status'] === 'active' ? 'suspended' : 'active';
    db_run('UPDATE users SET status = :s WHERE id = :id AND tenant_id = :t',
        ['s' => $new, 'id' => $id, 't' => $tid]);
    audit_record(AUDIT_USER_UPDATED, $_SESSION['user_id'] ?? null, ['id' => $id, 'status' => $new]);
    flash_set('success', 'Usuario ' . ($new === 'active' ? 'activado' : 'suspendido') . '.');
    redirect(tenant_url('users'));
}
