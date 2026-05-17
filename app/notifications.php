<?php
/**
 * Helper de notificaciones in-app.
 * Crear con notify(), leer con notifications_for_user(), marcar leidas.
 */

function notify($user_id, $type, $title, $opts = []) {
    if (!current_tenant()) return;
    tenant_insert('notifications', [
        'user_id'  => $user_id,
        'type'     => $type,
        'title'    => $title,
        'body'     => $opts['body'] ?? null,
        'url'      => $opts['url'] ?? null,
        'icon'     => $opts['icon'] ?? null,
        'severity' => in_array($opts['severity'] ?? 'info', ['info','success','warning','danger'], true)
                          ? $opts['severity'] : 'info',
    ]);
}

/** Broadcast: notifica a todos los usuarios activos del tenant */
function notify_tenant($type, $title, $opts = []) {
    $t = current_tenant();
    if (!$t) return;
    $tid = (int)$t['id'];
    $users = db_select('SELECT id FROM users WHERE tenant_id = :t AND status = "active"', ['t' => $tid]);
    foreach ($users as $u) notify((int)$u['id'], $type, $title, $opts);
}

/** Trae notificaciones del usuario actual (top N), incluyendo broadcast (user_id null) */
function notifications_for_user($limit = 20) {
    $uid = $_SESSION['user_id'] ?? null;
    $tid = current_tenant()['id'];
    if (!$uid) return [];
    return db_select(
        'SELECT * FROM notifications
         WHERE tenant_id = :t AND (user_id = :u OR user_id IS NULL)
         ORDER BY (read_at IS NULL) DESC, created_at DESC
         LIMIT ' . (int)$limit,
        ['t' => $tid, 'u' => $uid]
    );
}

function notifications_unread_count() {
    $uid = $_SESSION['user_id'] ?? null;
    $tid = current_tenant()['id'] ?? null;
    if (!$uid || !$tid) return 0;
    return (int)(db_one(
        'SELECT COUNT(*) c FROM notifications
         WHERE tenant_id = :t AND (user_id = :u OR user_id IS NULL) AND read_at IS NULL',
        ['t' => $tid, 'u' => $uid]
    )['c'] ?? 0);
}

function mark_notification_read($id) {
    $uid = $_SESSION['user_id'] ?? null;
    $tid = current_tenant()['id'];
    db_run(
        'UPDATE notifications SET read_at = NOW()
         WHERE id = :id AND tenant_id = :t AND (user_id = :u OR user_id IS NULL) AND read_at IS NULL',
        ['id' => $id, 't' => $tid, 'u' => $uid]
    );
}

function mark_all_notifications_read() {
    $uid = $_SESSION['user_id'] ?? null;
    $tid = current_tenant()['id'];
    db_run(
        'UPDATE notifications SET read_at = NOW()
         WHERE tenant_id = :t AND (user_id = :u OR user_id IS NULL) AND read_at IS NULL',
        ['t' => $tid, 'u' => $uid]
    );
}
