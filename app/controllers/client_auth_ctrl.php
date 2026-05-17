<?php
/**
 * Login propio del cliente: email + password contra clients.portal_password_hash.
 * Sesiones independientes en client_sessions (cookie 'kyros_csid').
 */

const CLIENT_COOKIE = 'kyros_csid';

function client_session_resolve() {
    if (!isset($_COOKIE[CLIENT_COOKIE])) return null;
    $hash = hash('sha256', $_COOKIE[CLIENT_COOKIE]);
    $sess = db_one(
        'SELECT s.*, c.first_name, c.last_name, c.email, c.tenant_id AS c_tenant_id,
                t.name AS tenant_name, t.country AS tenant_country, t.brand_color
         FROM client_sessions s
         JOIN clients c ON c.id = s.client_id
         JOIN tenants t ON t.id = c.tenant_id
         WHERE s.session_hash = :h AND s.revoked_at IS NULL AND s.expires_at > NOW()
         LIMIT 1',
        ['h' => $hash]
    );
    return $sess ?: null;
}

function client_auth_ctrl_show_login($params) {
    if (client_session_resolve()) redirect(url('cliente'));
    render_with_layout('app', 'cliente.login', ['title' => 'Acceso cliente']);
}

function client_auth_ctrl_do_login($params) {
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    $pass  = $_POST['password'] ?? '';
    $ip    = $_SERVER['REMOTE_ADDR'] ?? '';

    if (!is_valid_email($email) || $pass === '') {
        flash_set('error', 'Credenciales invalidas.');
        redirect(url('cliente/login'));
    }
    if (!rate_limit_check('client_login:' . $ip . ':' . $email, 5, 900)) {
        flash_set('error', 'Demasiados intentos. Espera 15 minutos.');
        redirect(url('cliente/login'));
    }

    $client = db_one(
        'SELECT id, tenant_id, first_name, last_name, email, portal_password_hash, portal_enabled
         FROM clients WHERE email = :e LIMIT 1', ['e' => $email]
    );

    $dummy = '$argon2i$v=19$m=65536,t=4,p=2$ZHVtbXlzYWx0ZHVtbXlzYWx0$AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA';
    if (!$client || !$client['portal_enabled'] || !$client['portal_password_hash']) {
        password_hash_verify($pass, $dummy);
        flash_set('error', 'Credenciales incorrectas.');
        redirect(url('cliente/login'));
    }
    if (!password_hash_verify($pass, $client['portal_password_hash'])) {
        flash_set('error', 'Credenciales incorrectas.');
        redirect(url('cliente/login'));
    }

    // Crea sesion (30 dias)
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    db_run(
        'INSERT INTO client_sessions (tenant_id, client_id, session_hash, ip, user_agent, expires_at)
         VALUES (:t, :c, :h, :ip, :ua, DATE_ADD(NOW(), INTERVAL 30 DAY))',
        [
            't' => $client['tenant_id'], 'c' => $client['id'], 'h' => $hash,
            'ip' => $ip,
            'ua' => substr($_SERVER['HTTP_USER_AGENT'] ?? '', 0, 500),
        ]
    );
    db_run('UPDATE clients SET portal_last_login_at = NOW() WHERE id = :id', ['id' => $client['id']]);

    setcookie(CLIENT_COOKIE, $token, [
        'expires'  => time() + 86400 * 30,
        'path'     => '/',
        'domain'   => '',
        'secure'   => SESSION_SECURE,
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    flash_set('success', 'Bienvenido, ' . $client['first_name']);
    redirect(url('cliente'));
}

function client_auth_ctrl_do_logout($params) {
    $sess = client_session_resolve();
    if ($sess) {
        db_run('UPDATE client_sessions SET revoked_at = NOW() WHERE id = :id', ['id' => $sess['id']]);
    }
    setcookie(CLIENT_COOKIE, '', ['expires' => time() - 3600, 'path' => '/']);
    redirect(url('cliente/login'));
}

function client_auth_ctrl_show_forgot($params) {
    render_with_layout('app', 'cliente.forgot', ['title' => 'Recuperar acceso']);
}

function client_auth_ctrl_submit_forgot($params) {
    $email = mb_strtolower(trim($_POST['email'] ?? ''));
    if (!is_valid_email($email)) {
        flash_set('error', 'Email invalido.');
        redirect(url('cliente/forgot'));
    }
    $ip = $_SERVER['REMOTE_ADDR'] ?? '';
    if (!rate_limit_check('forgot:' . $ip, 3, 600)) {
        flash_set('error', 'Demasiadas solicitudes. Espera unos minutos.');
        redirect(url('cliente/forgot'));
    }
    $client = db_one(
        'SELECT id, tenant_id, first_name FROM clients WHERE email = :e AND portal_enabled = 1 LIMIT 1',
        ['e' => $email]
    );
    if ($client) {
        $token = bin2hex(random_bytes(32));
        $hash = hash('sha256', $token);
        db_run(
            'INSERT INTO client_password_resets (tenant_id, client_id, token_hash, expires_at)
             VALUES (:t, :c, :h, DATE_ADD(NOW(), INTERVAL 60 MINUTE))',
            ['t' => $client['tenant_id'], 'c' => $client['id'], 'h' => $hash]
        );
        $resetUrl = url('cliente/reset/' . $token);
        $body = '<h2>Hola ' . e($client['first_name']) . ',</h2>'
              . '<p>Recibimos una solicitud para resetear tu contrasena del portal.</p>'
              . '<p><a href="' . e($resetUrl) . '" style="background:#4f46e5;color:white;padding:10px 20px;border-radius:8px;text-decoration:none;">Cambiar contrasena</a></p>'
              . '<p>O copia este link: <code>' . e($resetUrl) . '</code></p>'
              . '<p style="color:#64748b;font-size:12px;">Este link expira en 60 minutos. Si no solicitaste este cambio, ignora este email.</p>';
        send_email($email, 'Recuperar acceso al portal', $body, 'client_password_reset', $client['tenant_id']);
    }
    flash_set('success', 'Si el email existe en nuestro sistema, recibiras un link de recuperacion.');
    redirect(url('cliente/login'));
}

function client_auth_ctrl_show_reset($params) {
    $token = $params['token'];
    $hash = hash('sha256', $token);
    $reset = db_one(
        'SELECT * FROM client_password_resets WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW()',
        ['h' => $hash]
    );
    if (!$reset) abort(403, 'Link invalido o expirado.');
    render_with_layout('app', 'cliente.reset', ['title' => 'Nueva contrasena', 'token' => $token]);
}

function client_auth_ctrl_do_reset($params) {
    $token = $params['token'];
    $hash = hash('sha256', $token);
    $reset = db_one(
        'SELECT * FROM client_password_resets WHERE token_hash = :h AND used_at IS NULL AND expires_at > NOW()',
        ['h' => $hash]
    );
    if (!$reset) abort(403, 'Link invalido o expirado.');
    $pass = $_POST['password'] ?? '';
    $pass2 = $_POST['password2'] ?? '';
    if (strlen($pass) < 8 || $pass !== $pass2) {
        flash_set('error', 'Contrasena minimo 8 caracteres y debe coincidir.');
        redirect(url('cliente/reset/' . $token));
    }
    db_run('UPDATE clients SET portal_password_hash = :h WHERE id = :id',
        ['h' => password_hash_make($pass), 'id' => $reset['client_id']]);
    db_run('UPDATE client_password_resets SET used_at = NOW() WHERE id = :id', ['id' => $reset['id']]);
    db_run('UPDATE client_sessions SET revoked_at = NOW() WHERE client_id = :c AND revoked_at IS NULL',
        ['c' => $reset['client_id']]);
    audit_record('client.password_reset', null, ['client_id' => $reset['client_id']],
        (int)$reset['tenant_id']);
    flash_set('success', 'Contrasena actualizada. Inicia sesion con la nueva.');
    redirect(url('cliente/login'));
}

function client_auth_ctrl_dashboard($params) {
    $sess = client_session_resolve();
    if (!$sess) redirect(url('cliente/login'));

    $tid = (int)$sess['c_tenant_id'];
    $clientId = (int)$sess['client_id'];
    $cases = db_select(
        'SELECT id, case_number, title, case_type, status, priority, opened_at, filed_at, decision_at,
                uscis_receipt, biometrics_at, interview_at
         FROM cases WHERE tenant_id = :t AND client_id = :c
         ORDER BY opened_at DESC',
        ['t' => $tid, 'c' => $clientId]
    );
    // Pagos pendientes con payment_link
    $pendingPayments = db_select(
        'SELECT p.*, c.case_number, pl.token AS pay_token, pl.status AS pay_status
         FROM case_payments p
         JOIN cases c ON c.id = p.case_id
         LEFT JOIN payment_links pl ON pl.payment_id = p.id AND pl.status = "open" AND pl.expires_at > NOW()
         WHERE p.tenant_id = :t AND c.client_id = :c AND p.status = "pending"
         ORDER BY p.created_at DESC',
        ['t' => $tid, 'c' => $clientId]
    );

    render_with_layout('app', 'cliente.dashboard', [
        'title' => 'Mi cuenta — ' . $sess['tenant_name'],
        'session' => $sess, 'cases' => $cases, 'pending_payments' => $pendingPayments,
    ]);
}
