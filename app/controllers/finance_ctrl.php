<?php
function finance_ctrl_index($params) {
    $tid = current_tenant()['id'];

    // KPIs
    $stats = [
        'total_billed' => 0, 'paid' => 0, 'pending' => 0, 'this_month' => 0,
    ];
    $kpis = db_one(
        'SELECT
            COALESCE(SUM(amount_usd),0) total_billed,
            COALESCE(SUM(CASE WHEN status="paid" THEN amount_usd ELSE 0 END),0) paid,
            COALESCE(SUM(CASE WHEN status="pending" THEN amount_usd ELSE 0 END),0) pending,
            COALESCE(SUM(CASE WHEN status="paid" AND DATE_FORMAT(paid_at,"%Y-%m") = DATE_FORMAT(CURDATE(),"%Y-%m") THEN amount_usd ELSE 0 END),0) this_month
         FROM case_payments WHERE tenant_id = :t', ['t' => $tid]
    );
    $stats = array_merge($stats, $kpis);

    // Ingresos por mes (últimos 12)
    $monthly = db_select(
        'SELECT DATE_FORMAT(paid_at, "%Y-%m") m,
                SUM(amount_usd) total
         FROM case_payments
         WHERE tenant_id = :t AND status = "paid" AND paid_at IS NOT NULL
           AND paid_at >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
         GROUP BY m ORDER BY m ASC', ['t' => $tid]
    );

    // Aging (Accounts Receivable buckets)
    $aging = db_one(
        'SELECT
            COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), created_at) < 30 THEN amount_usd ELSE 0 END),0) bucket_30,
            COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), created_at) BETWEEN 30 AND 59 THEN amount_usd ELSE 0 END),0) bucket_60,
            COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), created_at) BETWEEN 60 AND 89 THEN amount_usd ELSE 0 END),0) bucket_90,
            COALESCE(SUM(CASE WHEN DATEDIFF(CURDATE(), created_at) >= 90 THEN amount_usd ELSE 0 END),0) bucket_over
         FROM case_payments WHERE tenant_id = :t AND status = "pending"', ['t' => $tid]
    );

    // Por categoria
    $byCategory = db_select(
        'SELECT category, SUM(amount_usd) total, COUNT(*) c
         FROM case_payments WHERE tenant_id = :t
         GROUP BY category ORDER BY total DESC', ['t' => $tid]
    );

    // Top deudores (clientes con más pendiente)
    $topDebtors = db_select(
        'SELECT cl.id, cl.first_name, cl.last_name, cl.email,
                SUM(p.amount_usd) pending, COUNT(p.id) invoices,
                MIN(p.created_at) oldest
         FROM case_payments p
         JOIN cases c ON c.id = p.case_id
         JOIN clients cl ON cl.id = c.client_id
         WHERE p.tenant_id = :t AND p.status = "pending"
         GROUP BY cl.id, cl.first_name, cl.last_name, cl.email
         ORDER BY pending DESC LIMIT 10', ['t' => $tid]
    );

    // Pagos recientes
    $recent = db_select(
        'SELECT p.*, c.case_number, cl.first_name, cl.last_name
         FROM case_payments p
         JOIN cases c ON c.id = p.case_id
         JOIN clients cl ON cl.id = c.client_id
         WHERE p.tenant_id = :t AND p.status = "paid"
         ORDER BY p.paid_at DESC LIMIT 10', ['t' => $tid]
    );

    // Time entries no facturados
    $unbilledTime = db_one(
        'SELECT COALESCE(SUM(minutes/60.0 * hourly_rate_usd),0) total,
                COALESCE(SUM(minutes),0) minutes
         FROM case_time_entries
         WHERE tenant_id = :t AND is_billable = 1 AND is_billed = 0', ['t' => $tid]
    );

    render_with_layout('tenant', 'finance.index', [
        'title' => 'Finanzas',
        'stats' => $stats, 'monthly' => $monthly, 'aging' => $aging,
        'by_category' => $byCategory, 'top_debtors' => $topDebtors,
        'recent' => $recent, 'unbilled_time' => $unbilledTime,
    ]);
}
