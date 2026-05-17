<?php
function notifications_ctrl_mark_one_read($params) {
    mark_notification_read((int)$params['id']);
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('dashboard'));
}

function notifications_ctrl_mark_all_read($params) {
    mark_all_notifications_read();
    flash_set('success', 'Notificaciones marcadas como leidas.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('dashboard'));
}
