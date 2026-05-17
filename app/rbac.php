<?php
/**
 * RBAC: matriz de permisos por rol.
 *
 * Politica del sistema (any-plan, any-role): TODOS los modulos visibles para todos los staff.
 * Las acciones destructivas (delete, manage de billing/users) se reservan al tenant_admin.
 */

const PERM = [
    'tenant_admin' => [
        'users.manage', 'clients.manage', 'cases.manage', 'cases.delete',
        'documents.manage', 'documents.delete',
        'tasks.manage', 'notes.manage',
        'workflows.manage',
        'billing.view', 'billing.manage',
        'settings.manage',
    ],
    'attorney' => [
        'clients.manage', 'cases.manage',
        'documents.manage',
        'tasks.manage', 'notes.manage',
        'workflows.view', 'workflows.create', 'workflows.update',
        'users.view',
        'billing.view',
        'settings.manage',
    ],
    'paralegal' => [
        'clients.manage', 'cases.manage',
        'documents.manage',
        'tasks.manage', 'notes.manage',
        'workflows.view', 'workflows.create', 'workflows.update',
        'users.view',
        'billing.view',
        'settings.manage',
    ],
    'staff' => [
        'clients.view', 'clients.create', 'clients.update',
        'cases.view', 'cases.create', 'cases.update',
        'documents.view', 'documents.create',
        'tasks.manage', 'notes.manage',
        'workflows.view',
        'users.view',
        'billing.view',
        'settings.manage',
    ],
    'client' => [
        'cases.view',
    ],
];

function role_has($role, $perm) {
    if ($role === 'super_admin') return true;
    if (!isset(PERM[$role])) return false;
    foreach (PERM[$role] as $p) {
        if ($p === $perm) return true;
        // wildcards basicos: cases.manage cubre cases.view/create/update
        if (substr($p, -7) === '.manage') {
            $base = substr($p, 0, -7);
            if (strpos($perm, $base . '.') === 0) return true;
        }
    }
    return false;
}

function can($perm) {
    $role = $_SESSION['user_role'] ?? null;
    if (!$role) return false;
    return role_has($role, $perm);
}

function require_perm($perm) {
    require_login();
    if (!can($perm)) {
        audit_record(AUDIT_PERMISSION_DENIED, $_SESSION['user_id'] ?? null, [
            'required' => $perm, 'role' => $_SESSION['user_role'] ?? null,
        ]);
        abort(403, 'No tienes permisos para esta accion.');
    }
}

function require_tenant_admin() { require_perm('users.manage'); }
