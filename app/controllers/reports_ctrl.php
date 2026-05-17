<?php
function reports_ctrl_index($params) {
    $tid = current_tenant()['id'];

    // Casos por mes ultimos 12 meses
    $byMonth = db_select(
        'SELECT DATE_FORMAT(created_at, "%Y-%m") m, COUNT(*) c FROM cases
         WHERE tenant_id = :t AND created_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
         GROUP BY m ORDER BY m ASC',
        ['t' => $tid]
    );
    // Aprobacion vs denegacion por mes
    $approvalByMonth = db_select(
        'SELECT DATE_FORMAT(decision_at, "%Y-%m") m,
                SUM(CASE WHEN status = "approved" THEN 1 ELSE 0 END) approved,
                SUM(CASE WHEN status = "denied" THEN 1 ELSE 0 END) denied
         FROM cases WHERE tenant_id = :t AND decision_at IS NOT NULL
           AND decision_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
         GROUP BY m ORDER BY m ASC',
        ['t' => $tid]
    );
    // Casos por tipo + estado
    $typeStatus = db_select(
        'SELECT case_type, status, COUNT(*) c FROM cases
         WHERE tenant_id = :t GROUP BY case_type, status ORDER BY case_type, c DESC',
        ['t' => $tid]
    );
    // Tiempo promedio de resolucion por tipo
    $avgDays = db_select(
        'SELECT case_type, AVG(DATEDIFF(decision_at, opened_at)) avg_days, COUNT(*) c
         FROM cases WHERE tenant_id = :t AND decision_at IS NOT NULL
         GROUP BY case_type ORDER BY c DESC LIMIT 8',
        ['t' => $tid]
    );
    // Tareas: completadas vs creadas por semana (4 sem)
    $tasksWeek = db_select(
        'SELECT WEEK(created_at, 1) wk,
                SUM(CASE WHEN status = "done" THEN 1 ELSE 0 END) done,
                COUNT(*) total
         FROM tasks WHERE tenant_id = :t AND created_at >= DATE_SUB(CURDATE(), INTERVAL 8 WEEK)
         GROUP BY wk ORDER BY wk ASC',
        ['t' => $tid]
    );

    render_with_layout('tenant', 'reports.index', [
        'title' => 'Reportes',
        'by_month' => $byMonth,
        'approval' => $approvalByMonth,
        'type_status' => $typeStatus,
        'avg_days' => $avgDays,
        'tasks_week' => $tasksWeek,
    ]);
}
