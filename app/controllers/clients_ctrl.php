<?php
function clients_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $where = ['tenant_id = :t'];
    $bind = ['t' => $tid];

    $q           = trim($_GET['q'] ?? '');
    $status      = $_GET['status'] ?? '';
    $nationality = $_GET['nationality'] ?? '';
    $residence   = $_GET['residence'] ?? '';

    if ($q !== '') {
        $idx = pii_blind_index($q);
        $where[] = '(passport_index = :idx1 OR alien_number_index = :idx2
                     OR LOWER(first_name) LIKE :ln1 OR LOWER(last_name) LIKE :ln2 OR LOWER(email) LIKE :ln3)';
        $bind['idx1'] = $idx; $bind['idx2'] = $idx;
        $like = '%' . mb_strtolower($q) . '%';
        $bind['ln1'] = $like; $bind['ln2'] = $like; $bind['ln3'] = $like;
    }
    if ($status && in_array($status, ['active','inactive','archived'], true)) {
        $where[] = 'status = :st'; $bind['st'] = $status;
    }
    if ($nationality && isset(COUNTRIES[$nationality])) {
        $where[] = 'nationality = :nat'; $bind['nat'] = $nationality;
    }
    if ($residence && isset(COUNTRIES[$residence])) {
        $where[] = 'country_residence = :res'; $bind['res'] = $residence;
    }

    $whereSql = implode(' AND ', $where);
    $clients = db_select(
        "SELECT * FROM clients WHERE {$whereSql} ORDER BY last_name, first_name LIMIT 500",
        $bind
    );

    render_with_layout('tenant', 'clients.index', [
        'title' => 'Clientes', 'clients' => $clients,
        'filters' => ['q' => $q, 'status' => $status, 'nationality' => $nationality, 'residence' => $residence],
    ]);
}

function clients_ctrl_new($params) {
    render_with_layout('tenant', 'clients.form', [
        'title' => 'Nuevo cliente', 'client' => null,
    ]);
}

function clients_ctrl_create($params) {
    $data = clients_ctrl_validate($_POST);
    if (is_string($data)) {
        flash_set('error', $data);
        $_SESSION['_old'] = $_POST;
        redirect(tenant_url('clients/new'));
    }
    $tid = current_tenant()['id'];
    $data['created_by'] = $_SESSION['user_id'] ?? null;
    $id = tenant_insert('clients', $data);
    audit_record('client.created', $_SESSION['user_id'] ?? null, ['id' => $id]);
    workflow_dispatch('client.created', [
        'client_id' => $id,
        'first_name' => $data['first_name'],
        'last_name'  => $data['last_name'],
        'client_email' => $data['email'] ?? null,
        'nationality' => $data['nationality'] ?? null,
    ]);
    flash_set('success', 'Cliente creado.');
    redirect(tenant_url('clients/' . $id));
}

function clients_ctrl_show($params) {
    $id = (int)$params['id'];
    $c = tenant_first('clients', ['id' => $id]);
    if (!$c) abort(404);

    // Descifrar PII para mostrar
    $tid = current_tenant()['id'];
    $c['passport']     = $c['passport_enc']     ? pii_decrypt($c['passport_enc'], $tid) : '';
    $c['alien_number'] = $c['alien_number_enc'] ? pii_decrypt($c['alien_number_enc'], $tid) : '';
    $c['date_of_birth']= $c['date_of_birth_enc']? pii_decrypt($c['date_of_birth_enc'], $tid) : '';

    $cases = db_select(
        'SELECT id, case_number, title, status, priority, opened_at FROM cases
         WHERE tenant_id = :t AND client_id = :c ORDER BY opened_at DESC',
        ['t' => $tid, 'c' => $id]
    );
    $docs = db_select(
        'SELECT id, name, category, size_bytes, created_at FROM documents
         WHERE tenant_id = :t AND client_id = :c ORDER BY created_at DESC',
        ['t' => $tid, 'c' => $id]
    );
    $portal_tokens = db_select(
        'SELECT * FROM client_portal_tokens
         WHERE tenant_id = :t AND client_id = :c
         ORDER BY (revoked_at IS NULL) DESC, expires_at DESC LIMIT 5',
        ['t' => $tid, 'c' => $id]
    );
    // Token recien creado (one-shot)
    $portal_token_once = null;
    if (($_SESSION['_portal_token_client_id'] ?? 0) === $id && !empty($_SESSION['_portal_token_once'])) {
        $portal_token_once = $_SESSION['_portal_token_once'];
        unset($_SESSION['_portal_token_once'], $_SESSION['_portal_token_client_id']);
    }
    // Password temporal del portal login (one-shot)
    $portal_pass_once = null;
    if (($_SESSION['_client_pass_id'] ?? 0) === $id && !empty($_SESSION['_client_pass_once'])) {
        $portal_pass_once = $_SESSION['_client_pass_once'];
        unset($_SESSION['_client_pass_once'], $_SESSION['_client_pass_id']);
    }

    render_with_layout('tenant', 'clients.show', [
        'title' => $c['first_name'] . ' ' . $c['last_name'],
        'client' => $c, 'cases' => $cases, 'docs' => $docs,
        'portal_tokens' => $portal_tokens, 'portal_token_once' => $portal_token_once,
        'portal_pass_once' => $portal_pass_once,
    ]);
}

function clients_ctrl_edit($params) {
    $id = (int)$params['id'];
    $c = tenant_first('clients', ['id' => $id]);
    if (!$c) abort(404);
    $tid = current_tenant()['id'];
    $c['passport']     = $c['passport_enc']     ? pii_decrypt($c['passport_enc'], $tid) : '';
    $c['alien_number'] = $c['alien_number_enc'] ? pii_decrypt($c['alien_number_enc'], $tid) : '';
    $c['date_of_birth']= $c['date_of_birth_enc']? pii_decrypt($c['date_of_birth_enc'], $tid) : '';

    render_with_layout('tenant', 'clients.form', [
        'title' => 'Editar cliente', 'client' => $c,
    ]);
}

function clients_ctrl_update($params) {
    $id = (int)$params['id'];
    if (!tenant_first('clients', ['id' => $id])) abort(404);
    $data = clients_ctrl_validate($_POST);
    if (is_string($data)) {
        flash_set('error', $data);
        redirect(tenant_url('clients/' . $id . '/edit'));
    }
    tenant_update('clients', $data, ['id' => $id]);
    audit_record('client.updated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    flash_set('success', 'Cliente actualizado.');
    redirect(tenant_url('clients/' . $id));
}

function clients_ctrl_generate_portal_token($params) {
    $id = (int)$params['id'];
    $client = tenant_first('clients', ['id' => $id]);
    if (!$client) abort(404);

    // Genera token 64 hex chars; guarda solo el hash
    $token = bin2hex(random_bytes(32));
    $hash = hash('sha256', $token);
    $days = max(1, min(365, (int)($_POST['days'] ?? 30)));

    tenant_insert('client_portal_tokens', [
        'client_id'  => $id,
        'token_hash' => $hash,
        'expires_at' => date('Y-m-d H:i:s', strtotime("+{$days} days")),
        'created_by' => $_SESSION['user_id'] ?? null,
    ]);
    audit_record('client.portal_token_created', $_SESSION['user_id'] ?? null, ['client_id' => $id, 'days' => $days]);

    // Token plaintext solo se muestra una vez (flash)
    $_SESSION['_portal_token_once'] = $token;
    $_SESSION['_portal_token_client_id'] = $id;
    flash_set('success', 'Link generado. Copialo ahora — no se mostrara de nuevo.');
    redirect(tenant_url('clients/' . $id) . '#portal');
}

function clients_ctrl_enable_portal_login($params) {
    $id = (int)$params['id'];
    $client = tenant_first('clients', ['id' => $id]);
    if (!$client) abort(404);
    if (!$client['email']) {
        flash_set('error', 'El cliente debe tener email registrado.');
        redirect(tenant_url('clients/' . $id) . '#portal');
    }
    // Genera password aleatoria
    $plainPass = strtoupper(substr(bin2hex(random_bytes(4)), 0, 4)) . '-' . substr(bin2hex(random_bytes(4)), 0, 6);
    $hash = password_hash_make($plainPass);
    tenant_update('clients', [
        'portal_password_hash' => $hash,
        'portal_enabled'       => 1,
    ], ['id' => $id]);

    audit_record('client.portal_login_enabled', $_SESSION['user_id'] ?? null, ['client_id' => $id]);
    $_SESSION['_client_pass_once'] = $plainPass;
    $_SESSION['_client_pass_id'] = $id;
    flash_set('success', 'Login del cliente habilitado. Comparte la contrasena temporal AHORA.');
    redirect(tenant_url('clients/' . $id) . '#portal');
}

function clients_ctrl_revoke_portal_token($params) {
    $id = (int)$params['id'];
    $ptid = (int)$params['ptid'];
    tenant_update('client_portal_tokens',
        ['revoked_at' => date('Y-m-d H:i:s')],
        ['id' => $ptid, 'client_id' => $id]
    );
    audit_record('client.portal_token_revoked', $_SESSION['user_id'] ?? null, ['client_id' => $id, 'token_id' => $ptid]);
    flash_set('success', 'Acceso revocado.');
    redirect(tenant_url('clients/' . $id) . '#portal');
}

function clients_ctrl_delete($params) {
    $id = (int)$params['id'];
    tenant_delete('clients', ['id' => $id]);
    audit_record('client.deleted', $_SESSION['user_id'] ?? null, ['id' => $id]);
    flash_set('success', 'Cliente eliminado.');
    redirect(tenant_url('clients'));
}

// ---- helpers ----

function clients_ctrl_validate($post) {
    $first = trim($post['first_name'] ?? '');
    $last  = trim($post['last_name']  ?? '');
    $email = mb_strtolower(trim($post['email'] ?? ''));
    if (mb_strlen($first) < 1 || mb_strlen($last) < 1) return 'Nombre y apellido son requeridos.';
    if ($email !== '' && !is_valid_email($email)) return 'Email invalido.';

    $passport     = trim($post['passport'] ?? '');
    $alienNumber  = trim($post['alien_number'] ?? '');
    $dob          = trim($post['date_of_birth'] ?? '');
    $tid = current_tenant()['id'];

    return [
        'first_name'        => $first,
        'last_name'         => $last,
        'email'             => $email ?: null,
        'phone'             => trim($post['phone'] ?? '') ?: null,
        'nationality'       => isset(COUNTRIES[$post['nationality'] ?? '']) ? $post['nationality'] : null,
        'country_residence' => isset(COUNTRIES[$post['country_residence'] ?? '']) ? $post['country_residence'] : null,
        'passport_enc'       => $passport     ? pii_encrypt($passport, $tid)    : null,
        'passport_index'     => $passport     ? pii_blind_index($passport)      : null,
        'alien_number_enc'   => $alienNumber  ? pii_encrypt($alienNumber, $tid) : null,
        'alien_number_index' => $alienNumber  ? pii_blind_index($alienNumber)   : null,
        'date_of_birth_enc'  => $dob          ? pii_encrypt($dob, $tid)         : null,
        'address'            => trim($post['address'] ?? '') ?: null,
        'notes'              => trim($post['notes'] ?? '') ?: null,
        'status'             => in_array($post['status'] ?? 'active', ['active','inactive','archived'], true)
                                    ? $post['status'] : 'active',
    ];
}
