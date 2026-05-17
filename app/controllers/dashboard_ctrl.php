<?php
function dashboard_ctrl_index($params) {
    $tid = current_tenant()['id'];

    // ---- KPIs principales ----
    $stats = [
        'clients'        => (int)(db_one('SELECT COUNT(*) c FROM clients WHERE tenant_id = :t', ['t' => $tid])['c'] ?? 0),
        'cases'          => (int)(db_one('SELECT COUNT(*) c FROM cases WHERE tenant_id = :t', ['t' => $tid])['c'] ?? 0),
        'cases_open'     => (int)(db_one(
            'SELECT COUNT(*) c FROM cases WHERE tenant_id = :t AND status NOT IN ("approved","denied","closed","withdrawn")',
            ['t' => $tid])['c'] ?? 0),
        'tasks_pending'  => (int)(db_one('SELECT COUNT(*) c FROM tasks WHERE tenant_id = :t AND status = "pending"', ['t' => $tid])['c'] ?? 0),
        'tasks_overdue'  => (int)(db_one(
            'SELECT COUNT(*) c FROM tasks WHERE tenant_id = :t AND status = "pending" AND due_date < CURDATE()',
            ['t' => $tid])['c'] ?? 0),
        'documents'      => (int)(db_one('SELECT COUNT(*) c FROM documents WHERE tenant_id = :t', ['t' => $tid])['c'] ?? 0),
        'success_rate'   => 0,
    ];
    $closed = (int)(db_one('SELECT COUNT(*) c FROM cases WHERE tenant_id = :t AND status IN ("approved","denied")', ['t' => $tid])['c'] ?? 0);
    $approved = (int)(db_one('SELECT COUNT(*) c FROM cases WHERE tenant_id = :t AND status = "approved"', ['t' => $tid])['c'] ?? 0);
    $stats['success_rate'] = $closed > 0 ? round($approved / $closed * 100) : 0;

    // ---- Sparklines: ultimos 14 dias ----
    $clients14 = db_select(
        'SELECT DATE(created_at) d, COUNT(*) c FROM clients
         WHERE tenant_id = :t AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
         GROUP BY DATE(created_at) ORDER BY d ASC',
        ['t' => $tid]
    );
    $cases14 = db_select(
        'SELECT DATE(created_at) d, COUNT(*) c FROM cases
         WHERE tenant_id = :t AND created_at >= DATE_SUB(CURDATE(), INTERVAL 14 DAY)
         GROUP BY DATE(created_at) ORDER BY d ASC',
        ['t' => $tid]
    );
    $sparkClients = _build_spark($clients14);
    $sparkCases   = _build_spark($cases14);

    // ---- Casos por estado ----
    $byStatus = db_select(
        'SELECT status, COUNT(*) c FROM cases WHERE tenant_id = :t GROUP BY status ORDER BY c DESC',
        ['t' => $tid]
    );

    // ---- Casos por tipo (top 8) ----
    $byType = db_select(
        'SELECT case_type, COUNT(*) c FROM cases WHERE tenant_id = :t GROUP BY case_type ORDER BY c DESC LIMIT 8',
        ['t' => $tid]
    );

    // ---- Casos creados ultimos 30 dias (area chart) ----
    $createdSeries = db_select(
        'SELECT DATE(created_at) d, COUNT(*) c FROM cases
         WHERE tenant_id = :t AND created_at >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)
         GROUP BY DATE(created_at) ORDER BY d ASC',
        ['t' => $tid]
    );

    // ---- Distribucion de carga por abogado ----
    $byAttorney = db_select(
        'SELECT u.id, u.name, COUNT(c.id) total,
                SUM(CASE WHEN c.status NOT IN ("approved","denied","closed","withdrawn") THEN 1 ELSE 0 END) abiertos
         FROM users u
         LEFT JOIN cases c ON c.attorney_id = u.id AND c.tenant_id = :t1
         WHERE u.tenant_id = :t2 AND u.role IN ("attorney","tenant_admin","paralegal") AND u.status = "active"
         GROUP BY u.id, u.name ORDER BY total DESC LIMIT 6',
        ['t1' => $tid, 't2' => $tid]
    );

    // ---- Actividad reciente (audit log) ----
    $activity = db_select(
        'SELECT a.event, a.context_json, a.created_at, u.name AS actor_name
         FROM audit_log a LEFT JOIN users u ON u.id = a.actor_user_id
         WHERE a.tenant_id = :t ORDER BY a.created_at DESC LIMIT 10',
        ['t' => $tid]
    );

    // ---- Casos recientes ----
    $recent = db_select(
        'SELECT c.id, c.case_number, c.title, c.status, c.priority, c.updated_at, c.case_type,
                cl.first_name, cl.last_name, u.name AS attorney_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         LEFT JOIN users u ON u.id = c.attorney_id
         WHERE c.tenant_id = :t
         ORDER BY c.updated_at DESC LIMIT 6',
        ['t' => $tid]
    );

    // ---- Proximas tareas ----
    $upcoming = db_select(
        'SELECT t.*, u.name AS assignee_name, c.case_number FROM tasks t
         LEFT JOIN users u ON u.id = t.assignee_id
         LEFT JOIN cases c ON c.id = t.case_id
         WHERE t.tenant_id = :t AND t.status = "pending"
         ORDER BY (t.due_date IS NULL), t.due_date ASC LIMIT 6',
        ['t' => $tid]
    );

    // ---- Deadlines USCIS proximos 30 dias ----
    $deadlines = db_select(
        'SELECT c.id, c.case_number, c.title, cl.first_name, cl.last_name,
                "biometrics" AS kind, c.biometrics_at AS deadline_at FROM cases c
         JOIN clients cl ON cl.id = c.client_id
         WHERE c.tenant_id = :t1 AND c.biometrics_at IS NOT NULL
           AND c.biometrics_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
         UNION ALL
         SELECT c.id, c.case_number, c.title, cl.first_name, cl.last_name,
                "interview" AS kind, c.interview_at AS deadline_at FROM cases c
         JOIN clients cl ON cl.id = c.client_id
         WHERE c.tenant_id = :t2 AND c.interview_at IS NOT NULL
           AND c.interview_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
         UNION ALL
         SELECT c.id, c.case_number, c.title, cl.first_name, cl.last_name,
                "rfe" AS kind, c.rfe_due_at AS deadline_at FROM cases c
         JOIN clients cl ON cl.id = c.client_id
         WHERE c.tenant_id = :t3 AND c.rfe_due_at IS NOT NULL
           AND c.rfe_due_at BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)
         ORDER BY deadline_at ASC LIMIT 8',
        ['t1' => $tid, 't2' => $tid, 't3' => $tid]
    );

    // ---- Balance financiero ----
    $finance = db_one(
        'SELECT
            COALESCE(SUM(CASE WHEN status = "paid" THEN amount_usd ELSE 0 END), 0) as paid,
            COALESCE(SUM(CASE WHEN status = "pending" THEN amount_usd ELSE 0 END), 0) as pending
         FROM case_payments WHERE tenant_id = :t', ['t' => $tid]
    );

    render_with_layout('tenant', 'tenant.dashboard', [
        'title' => 'Dashboard',
        'stats' => $stats,
        'spark_clients' => $sparkClients,
        'spark_cases'   => $sparkCases,
        'by_status' => $byStatus,
        'by_type' => $byType,
        'created_series' => $createdSeries,
        'by_attorney' => $byAttorney,
        'activity' => $activity,
        'recent' => $recent,
        'upcoming' => $upcoming,
        'deadlines' => $deadlines,
        'finance' => $finance,
    ]);
}

function _build_spark($rows) {
    // Rellena ultimos 14 dias con 0s donde falten
    $map = [];
    foreach ($rows as $r) $map[$r['d']] = (int)$r['c'];
    $out = [];
    for ($i = 13; $i >= 0; $i--) {
        $d = date('Y-m-d', strtotime("-{$i} days"));
        $out[] = ['d' => $d, 'c' => $map[$d] ?? 0];
    }
    return $out;
}
