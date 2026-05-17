<?php
/**
 * Resolucion y contexto del tenant activo.
 *
 * Sprint 1: path-based (/t/{slug}/...).
 * Sprint 2: agregaremos resolucion por subdominio.
 *
 * Una vez resuelto, el tenant queda en $GLOBALS['current_tenant'] y NO se puede cambiar.
 */

function resolve_tenant($slug) {
    if (!is_valid_slug($slug)) {
        abort(400, 'Slug de tenant invalido.');
    }

    $tenant = db_one(
        'SELECT id, slug, name, status, country, plan_id FROM tenants WHERE slug = :s LIMIT 1',
        ['s' => $slug]
    );

    if (!$tenant) abort(404, 'Tenant no encontrado.');

    if ($tenant['status'] !== 'active') {
        security_log('tenant.access_inactive', [
            'slug' => $slug, 'status' => $tenant['status'],
            'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        abort(403, 'Este tenant esta inactivo.');
    }

    // Si hay sesion: el usuario debe pertenecer al tenant (excepto super_admin).
    $uid = $_SESSION['user_id'] ?? null;
    $role = $_SESSION['user_role'] ?? null;
    $userTid = $_SESSION['user_tenant_id'] ?? null;

    if ($uid && $role !== 'super_admin' && (int)$userTid !== (int)$tenant['id']) {
        security_log('tenant.cross_tenant_access_attempt', [
            'user_id'        => $uid,
            'user_tenant_id' => $userTid,
            'requested_tenant' => $tenant['id'],
            'ip'             => $_SERVER['REMOTE_ADDR'] ?? null,
        ]);
        abort(403, 'No tienes acceso a este tenant.');
    }

    if (isset($GLOBALS['current_tenant_locked'])) {
        // Ya fue resuelto en este request, no permitimos cambiarlo.
        return $GLOBALS['current_tenant'];
    }

    $GLOBALS['current_tenant'] = [
        'id'      => (int)$tenant['id'],
        'slug'    => $tenant['slug'],
        'name'    => $tenant['name'],
        'status'  => $tenant['status'],
        'country' => $tenant['country'],
        'plan_id' => $tenant['plan_id'] !== null ? (int)$tenant['plan_id'] : null,
    ];
    $GLOBALS['current_tenant_locked'] = true;

    return $GLOBALS['current_tenant'];
}

function current_tenant() {
    return $GLOBALS['current_tenant'] ?? null;
}
