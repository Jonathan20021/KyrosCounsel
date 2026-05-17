<?php
/**
 * Onboarding publico: registro de un nuevo bufete (tenant) + admin inicial.
 * Crea: tenant en estado 'trial' + user tenant_admin.
 */

function onboarding_ctrl_show($params) {
    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');
    render_with_layout('public', 'onboarding.form', [
        'title' => 'Crear cuenta',
        'plans' => $plans,
        'selected_plan' => $_GET['plan'] ?? 'pro',
    ]);
}

function onboarding_ctrl_submit($params) {
    $firm     = trim($_POST['firm_name'] ?? '');
    $slug     = mb_strtolower(trim($_POST['slug'] ?? ''));
    $country  = strtoupper(trim($_POST['country'] ?? 'US'));
    $planCode = $_POST['plan'] ?? 'basic';
    $name     = trim($_POST['admin_name'] ?? '');
    $email    = mb_strtolower(trim($_POST['admin_email'] ?? ''));
    $pass     = $_POST['admin_password'] ?? '';

    foreach (['firm_name','slug','admin_name','admin_email','admin_password'] as $k) {
        $_SESSION['_old'][$k] = $_POST[$k] ?? '';
    }

    $errors = [];
    if (mb_strlen($firm) < 3) $errors[] = 'Nombre del bufete muy corto.';
    if (!is_valid_slug($slug)) $errors[] = 'Slug invalido (solo a-z, 0-9, -).';
    if (!isset(COUNTRIES[$country])) $errors[] = 'Pais invalido.';
    if (mb_strlen($name) < 2) $errors[] = 'Nombre muy corto.';
    if (!is_valid_email($email)) $errors[] = 'Email invalido.';
    if (strlen($pass) < 8) $errors[] = 'Contrasena debe tener al menos 8 caracteres.';

    if ($errors) {
        flash_set('error', implode(' ', $errors));
        redirect(url('onboarding'));
    }

    if (db_one('SELECT id FROM tenants WHERE slug = :s', ['s' => $slug])) {
        flash_set('error', 'Ese slug ya esta tomado.');
        redirect(url('onboarding'));
    }
    if (db_one('SELECT id FROM users WHERE email = :e', ['e' => $email])) {
        flash_set('error', 'Ese email ya esta registrado.');
        redirect(url('onboarding'));
    }

    $plan = db_one('SELECT * FROM plans WHERE code = :c', ['c' => $planCode]) ?:
            db_one('SELECT * FROM plans WHERE code = "basic"');

    db_transaction(function () use ($firm, $slug, $country, $plan, $name, $email, $pass) {
        db_run(
            'INSERT INTO tenants (slug, name, country, status, plan_id, trial_ends_at, settings_json)
             VALUES (:s, :n, :c, "trial", :p, :te, :st)',
            [
                's'  => $slug, 'n' => $firm, 'c' => $country,
                'p'  => (int)$plan['id'],
                'te' => date('Y-m-d H:i:s', strtotime('+14 days')),
                'st' => json_encode(['locale' => 'es']),
            ]
        );
        $tenantId = (int)get_db()->lastInsertId();

        db_run(
            'INSERT INTO users (tenant_id, email, name, password_hash, role, status)
             VALUES (:t, :e, :n, :h, "tenant_admin", "active")',
            [
                't' => $tenantId, 'e' => $email, 'n' => $name,
                'h' => password_hash_make($pass),
            ]
        );

        db_run(
            'INSERT INTO subscriptions (tenant_id, plan_id, status, starts_at, next_billing_at)
             VALUES (:t, :p, "trial", NOW(), :nb)',
            ['t' => $tenantId, 'p' => (int)$plan['id'], 'nb' => date('Y-m-d H:i:s', strtotime('+14 days'))]
        );

        audit_record(AUDIT_TENANT_CREATED, null,
            ['slug' => $slug, 'plan' => $plan['code']], $tenantId);
    });

    send_email(
        $email,
        'Bienvenido a ' . APP_NAME,
        '<h2>Bienvenido, ' . e($name) . '</h2>' .
        '<p>Tu bufete <strong>' . e($firm) . '</strong> esta listo. Tienes 14 dias de prueba.</p>' .
        '<p><a href="' . e(url('login')) . '">Iniciar sesion</a></p>'
    );

    flash_set('success', 'Cuenta creada. Inicia sesion con tu email y contrasena.');
    unset($_SESSION['_old']);
    redirect(url('login'));
}
