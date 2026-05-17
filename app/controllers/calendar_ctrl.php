<?php
function calendar_ctrl_week($params) {
    $tid = current_tenant()['id'];
    // Inicio de semana (lunes) navegable via ?w=YYYY-MM-DD
    $week_start_str = $_GET['w'] ?? '';
    $base = $week_start_str && preg_match('/^\d{4}-\d{2}-\d{2}$/', $week_start_str)
        ? strtotime($week_start_str) : time();
    // Lunes de esa semana
    $monday = strtotime('monday this week', $base);
    $monday_str = date('Y-m-d', $monday);
    $sunday_str = date('Y-m-d', strtotime('+6 days', $monday));
    $prev = date('Y-m-d', strtotime('-7 days', $monday));
    $next = date('Y-m-d', strtotime('+7 days', $monday));

    // Tareas con due en el rango
    $tasks = db_select(
        'SELECT t.*, c.case_number, u.name AS assignee_name, u.id AS assignee_id_value FROM tasks t
         LEFT JOIN cases c ON c.id = t.case_id
         LEFT JOIN users u ON u.id = t.assignee_id
         WHERE t.tenant_id = :t AND t.due_date BETWEEN :a AND :b
         ORDER BY t.due_date ASC, t.priority DESC',
        ['t' => $tid, 'a' => $monday_str, 'b' => $sunday_str]
    );

    // Citas en el rango
    $appts = db_select(
        'SELECT a.*, c.case_number FROM case_appointments a
         JOIN cases c ON c.id = a.case_id
         WHERE a.tenant_id = :t AND DATE(a.starts_at) BETWEEN :a AND :b
         ORDER BY a.starts_at ASC',
        ['t' => $tid, 'a' => $monday_str, 'b' => $sunday_str]
    );

    // Filtro por abogado
    $filter_attorney = (int)($_GET['attorney'] ?? 0);
    if ($filter_attorney) {
        $tasks = array_filter($tasks, fn($x) => (int)($x['assignee_id'] ?? 0) === $filter_attorney);
    }

    // Index por dia
    $byDate = [];
    for ($i = 0; $i < 7; $i++) {
        $d = date('Y-m-d', strtotime("+{$i} days", $monday));
        $byDate[$d] = ['tasks' => [], 'appts' => []];
    }
    foreach ($tasks as $t) if (isset($byDate[$t['due_date']])) $byDate[$t['due_date']]['tasks'][] = $t;
    foreach ($appts as $a) {
        $d = substr($a['starts_at'], 0, 10);
        if (isset($byDate[$d])) $byDate[$d]['appts'][] = $a;
    }

    $attorneys = db_select(
        'SELECT id, name FROM users WHERE tenant_id = :t AND status = "active" AND role IN ("attorney","tenant_admin","paralegal") ORDER BY name',
        ['t' => $tid]
    );

    render_with_layout('tenant', 'calendar.week', [
        'title' => 'Calendario semanal',
        'monday_str' => $monday_str, 'sunday_str' => $sunday_str,
        'prev' => $prev, 'next' => $next,
        'by_date' => $byDate, 'attorneys' => $attorneys,
        'filter_attorney' => $filter_attorney,
    ]);
}

function calendar_ctrl_index($params) {
    $tid = current_tenant()['id'];
    // Mes navegable via ?ym=2026-04
    $ym = $_GET['ym'] ?? date('Y-m');
    if (!preg_match('/^\d{4}-\d{2}$/', $ym)) $ym = date('Y-m');
    $first = $ym . '-01';
    $last  = date('Y-m-t', strtotime($first));

    $tasks = db_select(
        'SELECT t.*, c.case_number, u.name AS assignee_name FROM tasks t
         LEFT JOIN cases c ON c.id = t.case_id
         LEFT JOIN users u ON u.id = t.assignee_id
         WHERE t.tenant_id = :t AND t.due_date BETWEEN :a AND :b
         ORDER BY t.due_date ASC',
        ['t' => $tid, 'a' => $first, 'b' => $last]
    );
    $cases = db_select(
        'SELECT id, case_number, title, filed_at AS due_date, "filed" AS marker FROM cases
         WHERE tenant_id = :t1 AND filed_at BETWEEN :a1 AND :b1
         UNION ALL
         SELECT id, case_number, title, decision_at AS due_date, "decision" AS marker FROM cases
         WHERE tenant_id = :t2 AND decision_at BETWEEN :a2 AND :b2',
        ['t1' => $tid, 'a1' => $first, 'b1' => $last,
         't2' => $tid, 'a2' => $first, 'b2' => $last]
    );

    // Index por fecha
    $byDate = [];
    foreach ($tasks as $t) $byDate[$t['due_date']]['tasks'][] = $t;
    foreach ($cases as $c) $byDate[$c['due_date']]['cases'][] = $c;

    render_with_layout('tenant', 'calendar.index', [
        'title' => 'Calendario',
        'ym' => $ym, 'first' => $first, 'last' => $last,
        'by_date' => $byDate,
    ]);
}
