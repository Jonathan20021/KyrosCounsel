<?php
function public_ctrl_landing($params) {
    if (is_logged_in()) {
        $u = current_user();
        if ($u['role'] === 'super_admin') redirect(url('admin'));
        $t = db_one('SELECT slug FROM tenants WHERE id = :id LIMIT 1',
            ['id' => $_SESSION['user_tenant_id']]);
        if ($t) redirect(url('t/' . $t['slug'] . '/dashboard'));
    }
    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');
    render_with_layout('public', 'landing.index', ['title' => APP_NAME, 'plans' => $plans]);
}

function public_ctrl_pricing($params) {
    $plans = db_select('SELECT * FROM plans WHERE is_active = 1 ORDER BY sort_order ASC');
    render_with_layout('public', 'landing.pricing', ['title' => 'Precios', 'plans' => $plans]);
}
