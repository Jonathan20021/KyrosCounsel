<?php
function cases_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $where = ['c.tenant_id = :t'];
    $bind = ['t' => $tid];

    $status   = $_GET['status']   ?? '';
    $priority = $_GET['priority'] ?? '';
    $type     = $_GET['type']     ?? '';
    $attorney = (int)($_GET['attorney'] ?? 0);
    $country  = $_GET['country']  ?? '';
    $q        = trim($_GET['q']   ?? '');
    $from     = $_GET['from']     ?? '';
    $to       = $_GET['to']       ?? '';

    if ($status && isset(CASE_STATUSES[$status]))     { $where[] = 'c.status = :st';   $bind['st'] = $status; }
    if ($priority && isset(CASE_PRIORITIES[$priority])){ $where[] = 'c.priority = :pr'; $bind['pr'] = $priority; }
    if ($type !== '')   { $where[] = 'c.case_type = :ct'; $bind['ct'] = $type; }
    if ($attorney > 0)  { $where[] = 'c.attorney_id = :att'; $bind['att'] = $attorney; }
    if ($country && isset(COUNTRIES[$country])) { $where[] = 'c.country = :co'; $bind['co'] = $country; }
    if ($q !== '') {
        $where[] = '(c.case_number LIKE :q OR c.title LIKE :q2 OR cl.first_name LIKE :q3 OR cl.last_name LIKE :q4)';
        $bind['q'] = '%' . $q . '%'; $bind['q2'] = '%' . $q . '%';
        $bind['q3'] = '%' . $q . '%'; $bind['q4'] = '%' . $q . '%';
    }
    if ($from && preg_match('/^\d{4}-\d{2}-\d{2}$/', $from)) { $where[] = 'c.opened_at >= :fr'; $bind['fr'] = $from; }
    if ($to   && preg_match('/^\d{4}-\d{2}-\d{2}$/', $to))   { $where[] = 'c.opened_at <= :to'; $bind['to'] = $to; }

    $whereSql = implode(' AND ', $where);
    $cases = db_select(
        "SELECT c.*, cl.first_name, cl.last_name, u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE {$whereSql} ORDER BY c.updated_at DESC LIMIT 500",
        $bind
    );

    $attorneys = db_select(
        'SELECT id, name FROM users WHERE tenant_id = :t AND role IN ("attorney","tenant_admin","paralegal") AND status = "active" ORDER BY name',
        ['t' => $tid]
    );
    // Tipos disponibles segun pais del tenant
    $availableTypes = case_types_for(current_tenant()['country']);

    require_once APP_PATH . '/controllers/views_ctrl.php';
    $saved_views = saved_views_for('cases');

    render_with_layout('tenant', 'cases.index', [
        'title' => 'Casos', 'cases' => $cases,
        'filters' => [
            'status' => $status, 'priority' => $priority, 'type' => $type,
            'attorney' => $attorney, 'country' => $country, 'q' => $q,
            'from' => $from, 'to' => $to,
        ],
        'attorneys' => $attorneys,
        'available_types' => $availableTypes,
        'saved_views' => $saved_views,
    ]);
}

function cases_ctrl_board($params) {
    $tid = current_tenant()['id'];
    $rows = db_select(
        'SELECT c.id, c.case_number, c.title, c.status, c.priority, c.case_type, c.opened_at,
                cl.first_name, cl.last_name, u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t ORDER BY c.priority DESC, c.updated_at DESC',
        ['t' => $tid]
    );
    $byStatus = [];
    foreach (CASE_STATUSES as $code => $_) $byStatus[$code] = [];
    foreach ($rows as $r) $byStatus[$r['status']][] = $r;

    render_with_layout('tenant', 'cases.board', [
        'title' => 'Tablero Kanban', 'by_status' => $byStatus,
    ]);
}

function cases_ctrl_move($params) {
    header('Content-Type: application/json');
    $id = (int)$params['id'];
    $newStatus = $_POST['status'] ?? '';
    if (!isset(CASE_STATUSES[$newStatus])) {
        http_response_code(400);
        echo json_encode(['ok' => false, 'error' => 'Estado invalido']); exit;
    }
    $existing = tenant_first('cases', ['id' => $id]);
    if (!$existing) { http_response_code(404); echo json_encode(['ok' => false]); exit; }

    if ($existing['status'] === $newStatus) {
        echo json_encode(['ok' => true, 'unchanged' => true]); exit;
    }

    $patch = ['status' => $newStatus];
    if ($newStatus === 'filed' && empty($existing['filed_at'])) $patch['filed_at'] = date('Y-m-d');
    if (in_array($newStatus, ['approved','denied'], true))      $patch['decision_at'] = date('Y-m-d');
    tenant_update('cases', $patch, ['id' => $id]);
    tenant_insert('case_status_history', [
        'case_id' => $id, 'from_status' => $existing['status'], 'to_status' => $newStatus,
        'changed_by' => $_SESSION['user_id'] ?? null, 'note' => 'Cambio via kanban',
    ]);
    audit_record('case.status_changed', $_SESSION['user_id'] ?? null,
        ['id' => $id, 'from' => $existing['status'], 'to' => $newStatus, 'via' => 'kanban']);
    workflow_dispatch('case.status_changed', [
        'case_id' => $id, 'client_id' => $existing['client_id'],
        'from' => $existing['status'], 'to' => $newStatus,
        'attorney_id' => $existing['attorney_id'],
    ]);
    echo json_encode(['ok' => true, 'new_status' => $newStatus]);
    exit;
}

function cases_ctrl_new($params) {
    $tid = current_tenant()['id'];
    $clients = tenant_select('clients', ['id','first_name','last_name'], ['status' => 'active']);
    usort($clients, fn($a, $b) => strcmp($a['last_name'], $b['last_name']));
    $attorneys = db_select(
        'SELECT id, name FROM users WHERE tenant_id = :t AND role IN ("tenant_admin","attorney") AND status = "active"',
        ['t' => $tid]
    );
    render_with_layout('tenant', 'cases.form', [
        'title' => 'Nuevo caso', 'case' => null,
        'clients' => $clients, 'attorneys' => $attorneys,
        'preselect_client' => (int)($_GET['client_id'] ?? 0),
    ]);
}

function cases_ctrl_create($params) {
    $tid = current_tenant()['id'];
    $data = cases_ctrl_validate($_POST);
    if (is_string($data)) { flash_set('error', $data); redirect(tenant_url('cases/new')); }
    if (!$data['case_number']) {
        $data['case_number'] = 'CASE-' . date('Y') . '-' . str_pad((string)(tenant_count('cases') + 1), 4, '0', STR_PAD_LEFT);
    }
    $data['created_by'] = $_SESSION['user_id'] ?? null;

    // Auto-populate filing fee suggested
    $autoSetup = !empty($_POST['auto_setup']);
    if ($autoSetup) {
        $data['filing_fee_usd'] = filing_fee($data['case_type']);
    }

    $id = tenant_insert('cases', $data);

    tenant_insert('case_status_history', [
        'case_id' => $id, 'from_status' => null, 'to_status' => $data['status'],
        'changed_by' => $_SESSION['user_id'] ?? null, 'note' => 'Caso creado',
    ]);

    // Auto-populate evidencia + tareas + filing fee desde plantillas
    $created_extras = ['evidence' => 0, 'tasks' => 0];
    if ($autoSetup) {
        // Evidencia
        $evTemplate = evidence_template($data['case_type']);
        foreach ($evTemplate as $i => [$name, $cat, $req]) {
            tenant_insert('case_evidence', [
                'case_id' => $id, 'name' => $name, 'category' => $cat,
                'is_required' => $req, 'is_received' => 0, 'sort_order' => $i,
            ]);
            $created_extras['evidence']++;
        }

        // Tareas
        $taskTemplate = task_template($data['case_type']);
        foreach ($taskTemplate as [$title, $days, $priority]) {
            tenant_insert('tasks', [
                'case_id'    => $id,
                'title'      => $title,
                'assignee_id'=> $data['attorney_id'] ?: null,
                'due_date'   => date('Y-m-d', strtotime("+{$days} days")),
                'priority'   => $priority,
                'status'     => 'pending',
                'created_by' => $_SESSION['user_id'] ?? null,
            ]);
            $created_extras['tasks']++;
        }

        // Filing fee como pago pendiente
        $fee = filing_fee($data['case_type']);
        if ($fee > 0) {
            tenant_insert('case_payments', [
                'case_id' => $id,
                'concept' => 'Filing fee USCIS ' . $data['case_type'],
                'category' => 'filing_fee',
                'amount_usd' => $fee,
                'status' => 'pending',
                'created_by' => $_SESSION['user_id'] ?? null,
            ]);
        }
    }

    audit_record('case.created', $_SESSION['user_id'] ?? null,
        ['id' => $id, 'auto_setup' => $autoSetup, 'extras' => $created_extras]);
    workflow_dispatch('case.created', [
        'case_id' => $id, 'client_id' => $data['client_id'],
        'case_type' => $data['case_type'], 'attorney_id' => $data['attorney_id'],
    ]);

    if ($autoSetup && ($created_extras['evidence'] > 0 || $created_extras['tasks'] > 0)) {
        flash_set('success', sprintf('Caso creado. Auto-populado: %d items de evidencia + %d tareas.',
            $created_extras['evidence'], $created_extras['tasks']));
    } else {
        flash_set('success', 'Caso creado.');
    }
    redirect(tenant_url('cases/' . $id));
}

function cases_ctrl_show($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone AS client_phone,
                u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id',
        ['t' => $tid, 'id' => $id]
    );
    if (!$c) abort(404);
    $history = db_select(
        'SELECT h.*, u.name AS changed_by_name FROM case_status_history h
         LEFT JOIN users u ON u.id = h.changed_by
         WHERE h.tenant_id = :t AND h.case_id = :id ORDER BY h.created_at DESC',
        ['t' => $tid, 'id' => $id]
    );
    $tasks = db_select(
        'SELECT t.*, u.name AS assignee_name FROM tasks t LEFT JOIN users u ON u.id = t.assignee_id
         WHERE t.tenant_id = :t AND t.case_id = :id ORDER BY t.due_date ASC, t.id DESC',
        ['t' => $tid, 'id' => $id]
    );
    $notes = db_select(
        'SELECT n.*, u.name AS author_name FROM notes n LEFT JOIN users u ON u.id = n.author_id
         WHERE n.tenant_id = :t AND n.case_id = :id ORDER BY n.created_at DESC',
        ['t' => $tid, 'id' => $id]
    );
    $docs = db_select(
        'SELECT * FROM documents WHERE tenant_id = :t AND case_id = :id ORDER BY created_at DESC',
        ['t' => $tid, 'id' => $id]
    );
    $beneficiaries = db_select(
        'SELECT * FROM case_beneficiaries WHERE tenant_id = :t AND case_id = :id ORDER BY id ASC',
        ['t' => $tid, 'id' => $id]
    );
    $payments = db_select(
        'SELECT p.*, pl.token AS pay_token, pl.status AS pay_status, pl.expires_at AS pay_expires
         FROM case_payments p
         LEFT JOIN payment_links pl ON pl.payment_id = p.id AND pl.status IN ("open","paid")
         WHERE p.tenant_id = :t AND p.case_id = :id ORDER BY p.created_at DESC',
        ['t' => $tid, 'id' => $id]
    );
    // Token recien generado one-shot
    $pay_link_once = null;
    if (!empty($_SESSION['_pay_link_once']) && !empty($_SESSION['_pay_link_payment_id'])) {
        $pay_link_once = ['token' => $_SESSION['_pay_link_once'], 'pid' => (int)$_SESSION['_pay_link_payment_id']];
        unset($_SESSION['_pay_link_once'], $_SESSION['_pay_link_payment_id']);
    }
    $appointments = db_select(
        'SELECT * FROM case_appointments WHERE tenant_id = :t AND case_id = :id ORDER BY starts_at ASC',
        ['t' => $tid, 'id' => $id]
    );
    $evidence = db_select(
        'SELECT * FROM case_evidence WHERE tenant_id = :t AND case_id = :id ORDER BY sort_order ASC, id ASC',
        ['t' => $tid, 'id' => $id]
    );
    $timeEntries = db_select(
        'SELECT te.*, u.name AS user_name FROM case_time_entries te
         LEFT JOIN users u ON u.id = te.user_id
         WHERE te.tenant_id = :t AND te.case_id = :id ORDER BY te.entry_date DESC, te.id DESC',
        ['t' => $tid, 'id' => $id]
    );
    $messages = db_select(
        'SELECT m.*, u.name AS sender_name FROM case_messages m
         LEFT JOIN users u ON u.id = m.sender_user_id
         WHERE m.tenant_id = :t AND m.case_id = :id ORDER BY m.created_at ASC',
        ['t' => $tid, 'id' => $id]
    );
    // Marca mensajes del cliente como leidos al abrir el caso
    db_run('UPDATE case_messages SET read_at = NOW() WHERE tenant_id = :t AND case_id = :id AND sender_type = "client" AND read_at IS NULL',
        ['t' => $tid, 'id' => $id]);
    // Totales de tiempo
    $timeTotals = ['minutes' => 0, 'billable_minutes' => 0, 'billable_amount' => 0];
    foreach ($timeEntries as $te) {
        $timeTotals['minutes'] += (int)$te['minutes'];
        if ($te['is_billable']) {
            $timeTotals['billable_minutes'] += (int)$te['minutes'];
            $timeTotals['billable_amount'] += ((int)$te['minutes'] / 60) * (float)$te['hourly_rate_usd'];
        }
    }

    // Calcular totales financieros
    $totals = ['total' => 0, 'paid' => 0, 'pending' => 0];
    foreach ($payments as $p) {
        $totals['total'] += (float)$p['amount_usd'];
        if ($p['status'] === 'paid') $totals['paid'] += (float)$p['amount_usd'];
        else if ($p['status'] === 'pending') $totals['pending'] += (float)$p['amount_usd'];
    }
    // Evidencia: progreso
    $evReq = array_filter($evidence, fn($e) => (int)$e['is_required'] === 1);
    $evRecv = array_filter($evReq, fn($e) => (int)$e['is_received'] === 1);
    $evidenceProgress = count($evReq) > 0 ? round(count($evRecv) / count($evReq) * 100) : 0;

    render_with_layout('tenant', 'cases.show', [
        'title' => $c['case_number'], 'case' => $c,
        'history' => $history, 'tasks' => $tasks, 'notes' => $notes, 'docs' => $docs,
        'beneficiaries' => $beneficiaries, 'payments' => $payments,
        'appointments' => $appointments, 'evidence' => $evidence,
        'time_entries' => $timeEntries, 'time_totals' => $timeTotals,
        'messages' => $messages, 'pay_link_once' => $pay_link_once ?? null,
        'totals' => $totals, 'evidence_progress' => $evidenceProgress,
        'has_template' => !empty(evidence_template($c['case_type'])),
    ]);
}

function cases_ctrl_update_uscis($params) {
    $id = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $id])) abort(404);

    $receipt = strtoupper(trim($_POST['uscis_receipt'] ?? ''));
    if ($receipt !== '' && !is_valid_uscis_receipt($receipt)) {
        flash_set('error', 'Receipt invalido. Formato: 3 letras + 10 digitos (ej: EAC2412345678).');
        redirect(tenant_url('cases/' . $id) . '#uscis');
    }
    $sc = $receipt !== '' ? uscis_service_center_from_receipt($receipt) : ($_POST['service_center'] ?? null);

    tenant_update('cases', [
        'uscis_receipt'   => $receipt ?: null,
        'receipt_date'    => trim($_POST['receipt_date'] ?? '') ?: null,
        'biometrics_at'   => trim($_POST['biometrics_at'] ?? '') ?: null,
        'interview_at'    => trim($_POST['interview_at'] ?? '') ?: null,
        'rfe_due_at'      => trim($_POST['rfe_due_at'] ?? '') ?: null,
        'service_center'  => isset(SERVICE_CENTERS[$sc ?? '']) ? $sc : ($sc ?: null),
        'filing_fee_usd'  => (float)($_POST['filing_fee_usd'] ?? 0),
        'attorney_fee_usd'=> (float)($_POST['attorney_fee_usd'] ?? 0),
    ], ['id' => $id]);
    audit_record('case.uscis_updated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    flash_set('success', 'Datos USCIS actualizados.');
    redirect(tenant_url('cases/' . $id) . '#uscis');
}

function cases_ctrl_add_time($params) {
    $caseId = (int)$params['id'];
    $case = tenant_first('cases', ['id' => $caseId]);
    if (!$case) abort(404);

    $desc = trim($_POST['description'] ?? '');
    $hours = (float)($_POST['hours'] ?? 0);
    $minutes = (int)($_POST['minutes'] ?? 0);
    $totalMin = (int)round($hours * 60) + $minutes;

    if (mb_strlen($desc) < 2 || $totalMin < 1) {
        flash_set('error', 'Descripcion y tiempo requeridos.');
        redirect(tenant_url('cases/' . $caseId) . '#time');
    }

    // Rate del usuario
    $rate = (float)(db_one('SELECT hourly_rate_usd FROM users WHERE id = :id',
        ['id' => $_SESSION['user_id']])['hourly_rate_usd'] ?? 150);

    tenant_insert('case_time_entries', [
        'case_id'         => $caseId,
        'user_id'         => $_SESSION['user_id'],
        'description'     => $desc,
        'minutes'         => $totalMin,
        'hourly_rate_usd' => $rate,
        'is_billable'     => isset($_POST['is_billable']) ? 1 : 0,
        'is_billed'       => 0,
        'entry_date'      => trim($_POST['entry_date'] ?? '') ?: date('Y-m-d'),
    ]);
    flash_set('success', sprintf('Tiempo registrado: %dh %dm', intdiv($totalMin, 60), $totalMin % 60));
    redirect(tenant_url('cases/' . $caseId) . '#time');
}

function cases_ctrl_delete_time($params) {
    $caseId = (int)$params['id'];
    tenant_delete('case_time_entries', ['id' => (int)$params['tid'], 'case_id' => $caseId]);
    redirect(tenant_url('cases/' . $caseId) . '#time');
}

function cases_ctrl_bulk_action($params) {
    $action = $_POST['action'] ?? '';
    $ids = array_filter(array_map('intval', explode(',', $_POST['ids'] ?? '')));
    if (empty($ids)) {
        flash_set('error', 'No seleccionaste ningun caso.');
        redirect(tenant_url('cases'));
    }
    $tid = current_tenant()['id'];
    // Validar pertenencia al tenant
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $valid = db_select(
        "SELECT id FROM cases WHERE tenant_id = ? AND id IN ($placeholders)",
        array_merge([$tid], $ids)
    );
    $validIds = array_column($valid, 'id');
    if (empty($validIds)) {
        flash_set('error', 'Casos invalidos.');
        redirect(tenant_url('cases'));
    }

    $count = 0;
    if ($action === 'change_status') {
        $newStatus = $_POST['new_status'] ?? '';
        if (!isset(CASE_STATUSES[$newStatus])) {
            flash_set('error', 'Estado invalido.');
            redirect(tenant_url('cases'));
        }
        foreach ($validIds as $id) {
            $existing = tenant_first('cases', ['id' => $id]);
            tenant_update('cases', ['status' => $newStatus], ['id' => $id]);
            tenant_insert('case_status_history', [
                'case_id' => $id, 'from_status' => $existing['status'], 'to_status' => $newStatus,
                'changed_by' => $_SESSION['user_id'] ?? null, 'note' => 'Bulk update',
            ]);
            $count++;
        }
        audit_record('cases.bulk_status_change', $_SESSION['user_id'] ?? null,
            ['count' => $count, 'new_status' => $newStatus]);
        flash_set('success', "Estado actualizado en {$count} casos.");
    }
    elseif ($action === 'change_priority') {
        $prio = $_POST['new_priority'] ?? '';
        if (!isset(CASE_PRIORITIES[$prio])) {
            flash_set('error', 'Prioridad invalida.');
            redirect(tenant_url('cases'));
        }
        foreach ($validIds as $id) {
            tenant_update('cases', ['priority' => $prio], ['id' => $id]);
            $count++;
        }
        flash_set('success', "Prioridad actualizada en {$count} casos.");
    }
    elseif ($action === 'assign') {
        $att = (int)($_POST['attorney_id'] ?? 0);
        foreach ($validIds as $id) {
            tenant_update('cases', ['attorney_id' => $att ?: null], ['id' => $id]);
            $count++;
        }
        flash_set('success', "Abogado asignado a {$count} casos.");
    }
    else {
        flash_set('error', 'Accion no soportada.');
    }
    redirect(tenant_url('cases'));
}

function cases_ctrl_g28($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone AS client_phone,
                cl.nationality, cl.address, u.name AS attorney_name, u.email AS attorney_email
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $id]);
    if (!$c) abort(404);
    audit_record('case.g28_generated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    render_with_layout('app', 'cases.g28', [
        'title' => 'G-28 ' . $c['case_number'], 'case' => $c,
    ]);
}

function cases_ctrl_send_email_template($params) {
    $caseId = (int)$params['id'];
    $tplId = (int)($_POST['template_id'] ?? 0);
    $tid = current_tenant()['id'];
    $case = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone,
                u.name AS attorney_name, u.email AS attorney_email
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $caseId]);
    if (!$case || !$case['client_email']) {
        flash_set('error', 'El cliente no tiene email registrado.');
        redirect(tenant_url('cases/' . $caseId));
    }
    $tpl = tenant_first('email_templates', ['id' => $tplId]);
    if (!$tpl) {
        flash_set('error', 'Plantilla no encontrada.');
        redirect(tenant_url('cases/' . $caseId));
    }
    $ctx = [
        'client'   => ['first_name' => $case['first_name'], 'last_name' => $case['last_name'],
                       'email' => $case['client_email'], 'phone' => $case['phone']],
        'case'     => $case,
        'attorney' => ['name' => $case['attorney_name'], 'email' => $case['attorney_email']],
    ];
    $subject = template_render($tpl['subject'], $ctx);
    $body    = template_render($tpl['body_html'], $ctx);

    [$ok, $msg] = send_email($case['client_email'], $subject, $body, 'tpl:' . $tpl['slug'], $tid);
    if ($ok) {
        audit_record('case.email_sent', $_SESSION['user_id'] ?? null, ['case_id' => $caseId, 'template' => $tpl['slug']]);
        flash_set('success', 'Email enviado al cliente (' . ($msg === 'dry-run' ? 'modo dry-run' : 'OK') . ').');
    } else {
        flash_set('error', 'Error: ' . $msg);
    }
    redirect(tenant_url('cases/' . $caseId));
}

function cases_ctrl_cover_letter($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone AS client_phone,
                cl.nationality, cl.address, u.name AS attorney_name, u.email AS attorney_email
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $id]);
    if (!$c) abort(404);
    $beneficiaries = db_select(
        'SELECT * FROM case_beneficiaries WHERE tenant_id = :t AND case_id = :id',
        ['t' => $tid, 'id' => $id]);
    $evidence = db_select(
        'SELECT * FROM case_evidence WHERE tenant_id = :t AND case_id = :id AND is_required = 1
         ORDER BY sort_order ASC',
        ['t' => $tid, 'id' => $id]);
    audit_record('case.cover_letter_generated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    render_with_layout('app', 'cases.cover_letter', [
        'title' => 'Cover Letter ' . $c['case_number'],
        'case' => $c, 'beneficiaries' => $beneficiaries, 'evidence' => $evidence,
    ]);
}

function cases_ctrl_letter($params) {
    $id = (int)$params['id'];
    $kind = $params['kind'] ?? '';
    $allowed = ['rfe_response', 'affidavit_support', 'asylum_cover', 'demand_letter', 'engagement'];
    if (!in_array($kind, $allowed, true)) abort(404);

    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone AS client_phone,
                cl.nationality, cl.address, u.name AS attorney_name, u.email AS attorney_email
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $id]);
    if (!$c) abort(404);
    audit_record('case.letter_generated', $_SESSION['user_id'] ?? null, ['id' => $id, 'kind' => $kind]);
    render_with_layout('app', 'cases.letters.' . $kind, [
        'title' => ucfirst(str_replace('_', ' ', $kind)) . ' — ' . $c['case_number'],
        'case' => $c,
    ]);
}

function cases_ctrl_invoice($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.address,
                u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $id]);
    if (!$c) abort(404);
    $payments = db_select('SELECT * FROM case_payments WHERE tenant_id = :t AND case_id = :id ORDER BY created_at',
        ['t' => $tid, 'id' => $id]);
    $time_entries = db_select(
        'SELECT te.*, u.name AS user_name FROM case_time_entries te
         LEFT JOIN users u ON u.id = te.user_id
         WHERE te.tenant_id = :t AND te.case_id = :id AND te.is_billable = 1
         ORDER BY te.entry_date ASC', ['t' => $tid, 'id' => $id]);
    audit_record('case.invoice_generated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    render_with_layout('app', 'cases.invoice', [
        'title' => 'Factura ' . $c['case_number'], 'case' => $c,
        'payments' => $payments, 'time_entries' => $time_entries,
    ]);
}

function cases_ctrl_send_message_staff($params) {
    $caseId = (int)$params['id'];
    $case = tenant_first('cases', ['id' => $caseId]);
    if (!$case) abort(404);
    $body = trim($_POST['body'] ?? '');
    if (mb_strlen($body) < 1) {
        flash_set('error', 'Mensaje vacio.');
        redirect(tenant_url('cases/' . $caseId) . '#messages');
    }
    tenant_insert('case_messages', [
        'case_id' => $caseId, 'client_id' => (int)$case['client_id'],
        'sender_type' => 'staff', 'sender_user_id' => $_SESSION['user_id'] ?? null,
        'body' => $body,
    ]);
    audit_record('message.sent_to_client', $_SESSION['user_id'] ?? null, ['case_id' => $caseId]);
    flash_set('success', 'Mensaje enviado al cliente.');
    redirect(tenant_url('cases/' . $caseId) . '#messages');
}

function cases_ctrl_print_sheet($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $c = db_one(
        'SELECT c.*, cl.first_name, cl.last_name, cl.email AS client_email, cl.phone AS client_phone,
                cl.nationality, u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t AND c.id = :id', ['t' => $tid, 'id' => $id]);
    if (!$c) abort(404);
    $beneficiaries = db_select('SELECT * FROM case_beneficiaries WHERE tenant_id = :t AND case_id = :id', ['t' => $tid, 'id' => $id]);
    $payments      = db_select('SELECT * FROM case_payments WHERE tenant_id = :t AND case_id = :id ORDER BY created_at', ['t' => $tid, 'id' => $id]);
    $appointments  = db_select('SELECT * FROM case_appointments WHERE tenant_id = :t AND case_id = :id ORDER BY starts_at', ['t' => $tid, 'id' => $id]);
    $evidence      = db_select('SELECT * FROM case_evidence WHERE tenant_id = :t AND case_id = :id ORDER BY sort_order, id', ['t' => $tid, 'id' => $id]);
    $history       = db_select('SELECT h.*, u.name AS changed_by_name FROM case_status_history h LEFT JOIN users u ON u.id = h.changed_by WHERE h.tenant_id = :t AND h.case_id = :id ORDER BY h.created_at', ['t' => $tid, 'id' => $id]);
    $totals = ['total' => 0, 'paid' => 0];
    foreach ($payments as $p) {
        $totals['total'] += (float)$p['amount_usd'];
        if ($p['status'] === 'paid') $totals['paid'] += (float)$p['amount_usd'];
    }
    audit_record('case.printed', $_SESSION['user_id'] ?? null, ['id' => $id]);
    render_with_layout('app', 'cases.print', [
        'title' => 'Hoja ' . $c['case_number'], 'case' => $c,
        'beneficiaries' => $beneficiaries, 'payments' => $payments,
        'appointments' => $appointments, 'evidence' => $evidence,
        'history' => $history, 'totals' => $totals,
    ]);
}

function cases_ctrl_edit($params) {
    $id = (int)$params['id'];
    $c = tenant_first('cases', ['id' => $id]);
    if (!$c) abort(404);
    $tid = current_tenant()['id'];
    $clients = tenant_select('clients', ['id','first_name','last_name']);
    usort($clients, fn($a, $b) => strcmp($a['last_name'], $b['last_name']));
    $attorneys = db_select(
        'SELECT id, name FROM users WHERE tenant_id = :t AND role IN ("tenant_admin","attorney") AND status = "active"',
        ['t' => $tid]
    );
    render_with_layout('tenant', 'cases.form', [
        'title' => 'Editar ' . $c['case_number'], 'case' => $c,
        'clients' => $clients, 'attorneys' => $attorneys, 'preselect_client' => (int)$c['client_id'],
    ]);
}

function cases_ctrl_update($params) {
    $id = (int)$params['id'];
    $existing = tenant_first('cases', ['id' => $id]);
    if (!$existing) abort(404);
    $data = cases_ctrl_validate($_POST);
    if (is_string($data)) { flash_set('error', $data); redirect(tenant_url('cases/' . $id . '/edit')); }
    tenant_update('cases', $data, ['id' => $id]);
    audit_record('case.updated', $_SESSION['user_id'] ?? null, ['id' => $id]);
    flash_set('success', 'Caso actualizado.');
    redirect(tenant_url('cases/' . $id));
}

function cases_ctrl_change_status($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $existing = tenant_first('cases', ['id' => $id]);
    if (!$existing) abort(404);
    $newStatus = $_POST['status'] ?? '';
    if (!isset(CASE_STATUSES[$newStatus])) {
        flash_set('error', 'Estado invalido.');
        redirect(tenant_url('cases/' . $id));
    }
    $note = trim($_POST['note'] ?? '');
    $patch = ['status' => $newStatus];
    if ($newStatus === 'filed' && empty($existing['filed_at'])) $patch['filed_at'] = date('Y-m-d');
    if (in_array($newStatus, ['approved','denied'], true)) $patch['decision_at'] = date('Y-m-d');
    tenant_update('cases', $patch, ['id' => $id]);
    tenant_insert('case_status_history', [
        'case_id' => $id, 'from_status' => $existing['status'], 'to_status' => $newStatus,
        'changed_by' => $_SESSION['user_id'] ?? null, 'note' => $note ?: null,
    ]);
    audit_record('case.status_changed', $_SESSION['user_id'] ?? null,
        ['id' => $id, 'from' => $existing['status'], 'to' => $newStatus]);

    // Notificar al abogado si no fue quien hizo el cambio
    if ($existing['attorney_id'] && (int)$existing['attorney_id'] !== (int)($_SESSION['user_id'] ?? 0)) {
        $sev = in_array($newStatus, ['rfe','denied'], true) ? 'danger' :
               (in_array($newStatus, ['approved'], true) ? 'success' : 'info');
        notify((int)$existing['attorney_id'], 'case.status_changed',
            'Caso ' . case_status_label($newStatus) . ': ' . $existing['case_number'], [
                'body' => 'El estado cambió de ' . case_status_label($existing['status']) . ' a ' . case_status_label($newStatus),
                'url'  => tenant_url('cases/' . $id),
                'severity' => $sev,
            ]);
    }
    // RFE notifica a todos
    if ($newStatus === 'rfe') {
        notify_tenant('case.rfe', '⚠️ RFE recibida: ' . $existing['case_number'], [
            'body' => 'USCIS solicitó evidencia adicional. Revisar plazo.',
            'url'  => tenant_url('cases/' . $id),
            'severity' => 'warning',
        ]);
    }

    workflow_dispatch('case.status_changed', [
        'case_id' => $id, 'client_id' => $existing['client_id'],
        'from' => $existing['status'], 'to' => $newStatus,
        'attorney_id' => $existing['attorney_id'],
        'client_email' => db_one('SELECT email FROM clients WHERE id = :c AND tenant_id = :t',
            ['c' => $existing['client_id'], 't' => $tid])['email'] ?? null,
    ]);
    flash_set('success', 'Estado actualizado a ' . case_status_label($newStatus));
    redirect(tenant_url('cases/' . $id));
}

function cases_ctrl_add_note($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $existing = tenant_first('cases', ['id' => $id]);
    if (!$existing) abort(404);
    $body = trim($_POST['body'] ?? '');
    if ($body === '') { flash_set('error', 'Nota vacia.'); redirect(tenant_url('cases/' . $id)); }
    tenant_insert('notes', [
        'case_id' => $id, 'client_id' => $existing['client_id'],
        'body' => $body, 'author_id' => $_SESSION['user_id'] ?? null,
    ]);
    flash_set('success', 'Nota agregada.');
    redirect(tenant_url('cases/' . $id));
}

// ---- helpers ----

function cases_ctrl_validate($post) {
    $clientId = (int)($post['client_id'] ?? 0);
    $title    = trim($post['title'] ?? '');
    $type     = $post['case_type'] ?? '';
    $country  = strtoupper($post['country'] ?? current_tenant()['country']);
    if (!$clientId) return 'Cliente requerido.';
    if (mb_strlen($title) < 3) return 'Titulo muy corto.';
    $types = case_types_for($country);
    if (!isset($types[$type])) return 'Tipo de caso invalido.';
    if (!tenant_first('clients', ['id' => $clientId])) return 'Cliente invalido.';

    return [
        'client_id'   => $clientId,
        'case_number' => trim($post['case_number'] ?? '') ?: null,
        'title'       => $title,
        'case_type'   => $type,
        'country'     => $country,
        'status'      => isset(CASE_STATUSES[$post['status'] ?? '']) ? $post['status'] : 'intake',
        'priority'    => in_array($post['priority'] ?? 'normal', ['low','normal','high','urgent'], true)
                            ? $post['priority'] : 'normal',
        'attorney_id' => (int)($post['attorney_id'] ?? 0) ?: null,
        'opened_at'   => trim($post['opened_at'] ?? '') ?: date('Y-m-d'),
        'description' => trim($post['description'] ?? '') ?: null,
    ];
}
