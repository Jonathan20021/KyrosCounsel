<?php
function billing_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $tenant = db_one('SELECT t.*, p.name AS plan_name, p.code AS plan_code, p.price_usd, p.max_users, p.max_cases, p.max_storage_mb
                       FROM tenants t LEFT JOIN plans p ON p.id = t.plan_id WHERE t.id = :id',
        ['id' => $tid]);
    $sub = db_one('SELECT * FROM subscriptions WHERE tenant_id = :t ORDER BY id DESC LIMIT 1',
        ['t' => $tid]);
    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');

    $usage = [
        'users'  => (int)(db_one('SELECT COUNT(*) c FROM users WHERE tenant_id = :t', ['t' => $tid])['c'] ?? 0),
        'cases'  => (int)(db_one('SELECT COUNT(*) c FROM cases WHERE tenant_id = :t', ['t' => $tid])['c'] ?? 0),
        'storage_mb' => (int)round(((int)(db_one('SELECT COALESCE(SUM(size_bytes),0) s FROM documents WHERE tenant_id = :t', ['t' => $tid])['s'] ?? 0)) / 1024 / 1024),
    ];

    render_with_layout('tenant', 'billing.index', [
        'title' => 'Facturacion',
        'tenant' => $tenant, 'sub' => $sub, 'plans' => $plans, 'usage' => $usage,
    ]);
}

function billing_ctrl_change_plan($params) {
    $tid = current_tenant()['id'];
    $planId = (int)($_POST['plan_id'] ?? 0);
    $plan = db_one('SELECT * FROM plans WHERE id = :id AND is_active = 1', ['id' => $planId]);
    if (!$plan) { flash_set('error', 'Plan invalido.'); redirect(tenant_url('billing')); }

    db_run('UPDATE tenants SET plan_id = :p, status = "active" WHERE id = :t',
        ['p' => $planId, 't' => $tid]);
    db_run('UPDATE subscriptions SET plan_id = :p, status = "active" WHERE tenant_id = :t',
        ['p' => $planId, 't' => $tid]);
    audit_record('billing.plan_changed', $_SESSION['user_id'] ?? null, ['plan' => $plan['code']]);
    flash_set('success', 'Plan actualizado a ' . $plan['name']);
    redirect(tenant_url('billing'));
}
