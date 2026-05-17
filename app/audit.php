<?php
/**
 * Audit log append-only.
 * Registra eventos sensibles: login, logout, cambios de privilegio, etc.
 */

const AUDIT_LOGIN_SUCCESS    = 'auth.login.success';
const AUDIT_LOGIN_FAILED     = 'auth.login.failed';
const AUDIT_LOGOUT           = 'auth.logout';
const AUDIT_USER_CREATED     = 'user.created';
const AUDIT_USER_UPDATED     = 'user.updated';
const AUDIT_TENANT_CREATED   = 'tenant.created';
const AUDIT_PERMISSION_DENIED= 'permission.denied';

function audit_record($event, $actor_user_id = null, $context = null, $tenant_id = null) {
    if ($tenant_id === null) {
        $t = current_tenant();
        if ($t) $tenant_id = (int)$t['id'];
    }

    $sql = 'INSERT INTO audit_log
                (tenant_id, actor_user_id, event, ip, user_agent, context_json, created_at)
            VALUES (:tenant_id, :actor, :event, :ip, :ua, :ctx, :ts)';

    db_run($sql, [
        'tenant_id' => $tenant_id,
        'actor'     => $actor_user_id,
        'event'     => $event,
        'ip'        => $_SERVER['REMOTE_ADDR'] ?? null,
        'ua'        => isset($_SERVER['HTTP_USER_AGENT'])
            ? substr($_SERVER['HTTP_USER_AGENT'], 0, 500) : null,
        'ctx'       => $context ? json_encode($context, JSON_UNESCAPED_UNICODE) : null,
        'ts'        => date('Y-m-d H:i:s'),
    ]);
}
