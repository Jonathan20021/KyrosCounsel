<?php
function views_ctrl_create($params) {
    $name = trim($_POST['name'] ?? '');
    $module = $_POST['module'] ?? '';
    $filters = $_POST['filters_json'] ?? '';
    $shared = !empty($_POST['shared']);

    if (mb_strlen($name) < 2 || !in_array($module, ['cases', 'clients', 'tasks'], true)) {
        flash_set('error', 'Datos invalidos.');
        redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('cases'));
    }
    // Validar JSON
    json_decode($filters);
    if (json_last_error() !== JSON_ERROR_NONE) $filters = '{}';

    tenant_insert('saved_views', [
        'user_id' => $shared ? null : ($_SESSION['user_id'] ?? null),
        'module' => $module,
        'name' => $name,
        'filters_json' => $filters,
        'icon' => trim($_POST['icon'] ?? '') ?: null,
    ]);
    flash_set('success', 'Vista guardada.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url($module));
}

function views_ctrl_delete($params) {
    $id = (int)$params['id'];
    $v = tenant_first('saved_views', ['id' => $id]);
    if (!$v) abort(404);
    // Solo el creador o un admin pueden borrar
    $isOwner = (int)$v['user_id'] === (int)($_SESSION['user_id'] ?? 0);
    $isShared = $v['user_id'] === null;
    if (!$isOwner && (!$isShared || !can('settings.manage'))) {
        abort(403, 'No puedes borrar esta vista.');
    }
    tenant_delete('saved_views', ['id' => $id]);
    flash_set('success', 'Vista eliminada.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('cases'));
}

/** Lee vistas disponibles (propias + compartidas) para el usuario actual y modulo */
function saved_views_for($module) {
    $tid = current_tenant()['id'] ?? null;
    $uid = $_SESSION['user_id'] ?? null;
    if (!$tid || !$uid) return [];
    return db_select(
        'SELECT * FROM saved_views
         WHERE tenant_id = :t AND module = :m
           AND (user_id = :u OR user_id IS NULL)
         ORDER BY (user_id IS NULL) ASC, sort_order ASC, name ASC',
        ['t' => $tid, 'm' => $module, 'u' => $uid]
    );
}
