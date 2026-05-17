<?php
function audit_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $page = max(1, (int)($_GET['page'] ?? 1));
    $perPage = 50;
    $offset = ($page - 1) * $perPage;
    $event = trim($_GET['event'] ?? '');
    $where = 'a.tenant_id = :t';
    $bind = ['t' => $tid];
    if ($event !== '') { $where .= ' AND a.event = :e'; $bind['e'] = $event; }

    $rows = db_select(
        "SELECT a.*, u.name AS actor_name FROM audit_log a
         LEFT JOIN users u ON u.id = a.actor_user_id
         WHERE {$where} ORDER BY a.created_at DESC LIMIT {$perPage} OFFSET {$offset}",
        $bind
    );
    $total = (int)(db_one("SELECT COUNT(*) c FROM audit_log a WHERE {$where}", $bind)['c'] ?? 0);
    $events = db_select(
        'SELECT event, COUNT(*) c FROM audit_log WHERE tenant_id = :t GROUP BY event ORDER BY c DESC LIMIT 15',
        ['t' => $tid]
    );

    render_with_layout('tenant', 'audit.index', [
        'title' => 'Auditoria',
        'rows' => $rows, 'events' => $events, 'page' => $page, 'total' => $total,
        'per_page' => $perPage, 'event' => $event,
    ]);
}
