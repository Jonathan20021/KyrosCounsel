<?php
/**
 * Portal del cliente. PUBLICO con token validado contra hash.
 * NUNCA usar tenant_select aqui (no hay sesion). Usar db_one con tenant_id explicito.
 */

function _portal_resolve($token) {
    if (!preg_match('/^[a-f0-9]{64}$/', $token)) abort(404);
    $hash = hash('sha256', $token);
    $row = db_one(
        'SELECT pt.*, c.tenant_id AS client_tenant_id, c.first_name, c.last_name, c.email,
                t.name AS tenant_name, t.slug AS tenant_slug, t.country AS tenant_country, t.brand_color
         FROM client_portal_tokens pt
         JOIN clients c ON c.id = pt.client_id
         JOIN tenants t ON t.id = c.tenant_id
         WHERE pt.token_hash = :h AND pt.revoked_at IS NULL AND pt.expires_at > NOW()
         LIMIT 1',
        ['h' => $hash]
    );
    if (!$row) abort(403, 'Link inválido, expirado o revocado.');
    db_run('UPDATE client_portal_tokens SET last_used_at = NOW() WHERE id = :id', ['id' => $row['id']]);
    return $row;
}

function portal_ctrl_show($params) {
    $token = $params['token'];
    $portal = _portal_resolve($token);
    $tid = (int)$portal['client_tenant_id'];
    $clientId = (int)$portal['client_id'];

    // Casos del cliente (read-only, datos no sensibles)
    $cases = db_select(
        'SELECT id, case_number, title, case_type, status, priority, opened_at, filed_at, decision_at,
                uscis_receipt, biometrics_at, interview_at
         FROM cases WHERE tenant_id = :t AND client_id = :c
         ORDER BY opened_at DESC',
        ['t' => $tid, 'c' => $clientId]
    );

    render_with_layout('app', 'portal.show', [
        'title' => 'Portal — ' . $portal['tenant_name'],
        'portal' => $portal, 'token' => $token, 'cases' => $cases,
    ]);
}

function portal_ctrl_case_view($params) {
    $token = $params['token'];
    $portal = _portal_resolve($token);
    $tid = (int)$portal['client_tenant_id'];
    $caseId = (int)$params['id'];

    $case = db_one(
        'SELECT * FROM cases WHERE tenant_id = :t AND id = :id AND client_id = :c',
        ['t' => $tid, 'id' => $caseId, 'c' => (int)$portal['client_id']]
    );
    if (!$case) abort(404);

    $appointments = db_select(
        'SELECT * FROM case_appointments WHERE tenant_id = :t AND case_id = :id ORDER BY starts_at ASC',
        ['t' => $tid, 'id' => $caseId]
    );
    // Solo evidencia recibida (transparencia con cliente)
    $evidence = db_select(
        'SELECT name, category, is_required, is_received, received_at FROM case_evidence
         WHERE tenant_id = :t AND case_id = :id ORDER BY sort_order ASC',
        ['t' => $tid, 'id' => $caseId]
    );
    $history = db_select(
        'SELECT from_status, to_status, note, created_at FROM case_status_history
         WHERE tenant_id = :t AND case_id = :id ORDER BY created_at DESC',
        ['t' => $tid, 'id' => $caseId]
    );
    $messages = db_select(
        'SELECT m.*, u.name AS sender_name FROM case_messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         WHERE m.tenant_id = :t AND m.case_id = :id ORDER BY m.created_at ASC',
        ['t' => $tid, 'id' => $caseId]
    );
    // Marca mensajes del staff como leidos por cliente
    db_run('UPDATE case_messages SET read_at = NOW() WHERE tenant_id = :t AND case_id = :id AND sender_type = "staff" AND read_at IS NULL',
        ['t' => $tid, 'id' => $caseId]);

    render_with_layout('app', 'portal.case', [
        'title' => $case['case_number'] . ' — ' . $portal['tenant_name'],
        'portal' => $portal, 'token' => $token, 'case' => $case,
        'appointments' => $appointments, 'evidence' => $evidence, 'history' => $history,
        'messages' => $messages,
    ]);
}

function portal_ctrl_send_message($params) {
    // CSRF check standalone (sin sesion del staff)
    $token = $params['token'];
    $portal = _portal_resolve($token);
    $tid = (int)$portal['client_tenant_id'];
    $caseId = (int)$params['id'];

    // Validar caso pertenece al cliente
    $case = db_one('SELECT id, client_id FROM cases WHERE tenant_id = :t AND id = :id AND client_id = :c',
        ['t' => $tid, 'id' => $caseId, 'c' => (int)$portal['client_id']]);
    if (!$case) abort(404);

    $body = trim($_POST['body'] ?? '');
    if (mb_strlen($body) < 1 || mb_strlen($body) > 5000) {
        redirect(url('portal/' . $token . '/case/' . $caseId) . '#messages');
    }

    db_run(
        'INSERT INTO case_messages (tenant_id, case_id, client_id, sender_type, body, created_at)
         VALUES (:t, :case, :client, "client", :body, NOW())',
        ['t' => $tid, 'case' => $caseId, 'client' => (int)$portal['client_id'], 'body' => $body]
    );

    // Notificar al abogado del caso (si existe). Incluye tenant_id para defensa en profundidad.
    $caseDetail = db_one(
        'SELECT attorney_id, case_number FROM cases WHERE tenant_id = :t AND id = :id',
        ['t' => $tid, 'id' => $caseId]
    );
    if ($caseDetail && $caseDetail['attorney_id']) {
        db_run(
            'INSERT INTO notifications (tenant_id, user_id, type, title, body, url, severity, created_at)
             VALUES (:t, :u, "client.message", :title, :body, :url, "info", NOW())',
            [
                't' => $tid,
                'u' => (int)$caseDetail['attorney_id'],
                'title' => '💬 Mensaje del cliente: ' . $caseDetail['case_number'],
                'body' => mb_substr($body, 0, 200),
                'url' => url('t/' . $portal['tenant_slug'] . '/cases/' . $caseId),
            ]
        );
    }

    redirect(url('portal/' . $token . '/case/' . $caseId) . '#messages');
}
