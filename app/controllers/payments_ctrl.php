<?php
function payments_ctrl_create($params) {
    $caseId = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $caseId])) abort(404);
    $concept = trim($_POST['concept'] ?? '');
    $amount  = (float)($_POST['amount_usd'] ?? 0);
    $cat     = $_POST['category'] ?? 'attorney_fee';
    if (mb_strlen($concept) < 2 || $amount <= 0) {
        flash_set('error', 'Concepto y monto requeridos.');
        redirect(tenant_url('cases/' . $caseId) . '#payments');
    }
    if (!in_array($cat, ['attorney_fee','filing_fee','biometrics','translation','other'], true)) $cat = 'other';
    $status = ($_POST['status'] ?? 'pending') === 'paid' ? 'paid' : 'pending';
    tenant_insert('case_payments', [
        'case_id' => $caseId,
        'concept' => $concept,
        'category'=> $cat,
        'amount_usd' => $amount,
        'paid_at' => $status === 'paid' ? ($_POST['paid_at'] ?? date('Y-m-d')) : null,
        'method'  => in_array($_POST['method'] ?? '', ['cash','transfer','card','check','other'], true) ? $_POST['method'] : null,
        'reference' => trim($_POST['reference'] ?? '') ?: null,
        'status'  => $status,
        'notes'   => trim($_POST['notes'] ?? '') ?: null,
        'created_by' => $_SESSION['user_id'] ?? null,
    ]);
    flash_set('success', 'Pago registrado.');
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}

function payments_ctrl_toggle_paid($params) {
    $caseId = (int)$params['id'];
    $pid = (int)$params['pid'];
    $p = tenant_first('case_payments', ['id' => $pid, 'case_id' => $caseId]);
    if (!$p) abort(404);
    if ($p['status'] === 'paid') {
        tenant_update('case_payments', ['status' => 'pending', 'paid_at' => null], ['id' => $pid]);
    } else {
        tenant_update('case_payments', ['status' => 'paid', 'paid_at' => date('Y-m-d')], ['id' => $pid]);
    }
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}

function payments_ctrl_create_plan($params) {
    $caseId = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $caseId])) abort(404);
    $name = trim($_POST['name'] ?? '');
    $total = (float)($_POST['total_usd'] ?? 0);
    $count = max(1, min(36, (int)($_POST['installments_count'] ?? 1)));
    $startDate = trim($_POST['start_date'] ?? '') ?: date('Y-m-d');
    $intervalDays = max(7, min(180, (int)($_POST['interval_days'] ?? 30)));
    $concept = trim($_POST['concept'] ?? '') ?: 'Honorarios';
    if (mb_strlen($name) < 2 || $total <= 0 || $count < 1) {
        flash_set('error', 'Datos invalidos.');
        redirect(tenant_url('cases/' . $caseId) . '#payments');
    }
    if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $startDate)) $startDate = date('Y-m-d');

    $perInstallment = round($total / $count, 2);
    $lastInstallment = round($total - ($perInstallment * ($count - 1)), 2);

    db_transaction(function () use ($caseId, $name, $total, $count, $startDate, $intervalDays, $perInstallment, $lastInstallment, $concept) {
        $planId = tenant_insert('payment_plans', [
            'case_id' => $caseId, 'name' => $name, 'total_usd' => $total,
            'installments_count' => $count, 'status' => 'active',
            'created_by' => $_SESSION['user_id'] ?? null,
        ]);
        $start = strtotime($startDate);
        for ($i = 1; $i <= $count; $i++) {
            $amount = ($i === $count) ? $lastInstallment : $perInstallment;
            $due = date('Y-m-d', strtotime("+" . (($i - 1) * $intervalDays) . " days", $start));
            tenant_insert('case_payments', [
                'case_id' => $caseId,
                'concept' => sprintf('%s · Cuota %d/%d', $concept, $i, $count),
                'category' => 'attorney_fee',
                'amount_usd' => $amount,
                'status' => 'pending',
                'payment_plan_id' => $planId,
                'installment_no' => $i,
                'due_date' => $due,
                'created_by' => $_SESSION['user_id'] ?? null,
            ]);
        }
    });
    audit_record('payment_plan.created', $_SESSION['user_id'] ?? null,
        ['case_id' => $caseId, 'total' => $total, 'count' => $count]);
    flash_set('success', sprintf('Plan creado: %d cuotas de $%s.', $count, number_format($perInstallment, 2)));
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}

function payments_ctrl_cancel_plan($params) {
    $caseId = (int)$params['id'];
    $plid = (int)$params['plid'];
    $plan = tenant_first('payment_plans', ['id' => $plid, 'case_id' => $caseId]);
    if (!$plan) abort(404);
    tenant_update('payment_plans', ['status' => 'cancelled'], ['id' => $plid]);
    db_run('DELETE FROM case_payments WHERE tenant_id = :t AND payment_plan_id = :pl AND status = "pending"',
        ['t' => current_tenant()['id'], 'pl' => $plid]);
    audit_record('payment_plan.cancelled', $_SESSION['user_id'] ?? null, ['plan_id' => $plid]);
    flash_set('success', 'Plan cancelado. Cuotas pendientes eliminadas.');
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}

function payments_ctrl_generate_link($params) {
    $caseId = (int)$params['id'];
    $pid = (int)$params['pid'];
    $tid = current_tenant()['id'];
    $p = tenant_first('case_payments', ['id' => $pid, 'case_id' => $caseId]);
    if (!$p) abort(404);
    if ($p['status'] === 'paid') {
        flash_set('error', 'Este pago ya esta marcado como pagado.');
        redirect(tenant_url('cases/' . $caseId) . '#payments');
    }

    // Reusa link abierto si existe
    $existing = db_one(
        'SELECT * FROM payment_links WHERE tenant_id = :t AND payment_id = :p AND status = "open" AND expires_at > NOW()',
        ['t' => $tid, 'p' => $pid]
    );
    if ($existing) {
        $_SESSION['_pay_link_once'] = $existing['token'];
        $_SESSION['_pay_link_payment_id'] = $pid;
        flash_set('success', 'Link de pago disponible.');
        redirect(tenant_url('cases/' . $caseId) . '#payments');
    }

    $token = bin2hex(random_bytes(20));
    tenant_insert('payment_links', [
        'payment_id' => $pid,
        'case_id'    => $caseId,
        'token'      => $token,
        'amount_usd' => $p['amount_usd'],
        'currency'   => 'USD',
        'status'     => 'open',
        'expires_at' => date('Y-m-d H:i:s', strtotime('+30 days')),
        'created_by' => $_SESSION['user_id'] ?? null,
    ]);
    audit_record('payment.link_generated', $_SESSION['user_id'] ?? null, ['payment_id' => $pid]);
    $_SESSION['_pay_link_once'] = $token;
    $_SESSION['_pay_link_payment_id'] = $pid;
    flash_set('success', 'Link de pago generado.');
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}

function payments_ctrl_delete($params) {
    $caseId = (int)$params['id'];
    tenant_delete('case_payments', ['id' => (int)$params['pid'], 'case_id' => $caseId]);
    flash_set('success', 'Pago eliminado.');
    redirect(tenant_url('cases/' . $caseId) . '#payments');
}
