<?php
function templates_ctrl_index($params) {
    $tpls = tenant_select('email_templates');
    usort($tpls, fn($a, $b) => strcmp($a['name'], $b['name']));
    render_with_layout('tenant', 'templates.index', [
        'title' => 'Plantillas de email', 'templates' => $tpls,
    ]);
}

function templates_ctrl_new($params) {
    render_with_layout('tenant', 'templates.form', [
        'title' => 'Nueva plantilla', 'template' => null,
    ]);
}

function templates_ctrl_create($params) {
    $data = _templates_validate($_POST);
    if (is_string($data)) { flash_set('error', $data); redirect(tenant_url('templates/new')); }
    if (db_one('SELECT id FROM email_templates WHERE tenant_id = :t AND slug = :s',
        ['t' => current_tenant()['id'], 's' => $data['slug']])) {
        flash_set('error', 'Ya existe una plantilla con ese slug.');
        redirect(tenant_url('templates/new'));
    }
    $data['created_by'] = $_SESSION['user_id'] ?? null;
    tenant_insert('email_templates', $data);
    flash_set('success', 'Plantilla creada.');
    redirect(tenant_url('templates'));
}

function templates_ctrl_edit($params) {
    $tpl = tenant_first('email_templates', ['id' => (int)$params['id']]);
    if (!$tpl) abort(404);
    render_with_layout('tenant', 'templates.form', [
        'title' => 'Editar plantilla', 'template' => $tpl,
    ]);
}

function templates_ctrl_update($params) {
    $id = (int)$params['id'];
    if (!tenant_first('email_templates', ['id' => $id])) abort(404);
    $data = _templates_validate($_POST);
    if (is_string($data)) { flash_set('error', $data); redirect(tenant_url('templates/' . $id . '/edit')); }
    tenant_update('email_templates', $data, ['id' => $id]);
    flash_set('success', 'Plantilla actualizada.');
    redirect(tenant_url('templates'));
}

function templates_ctrl_delete($params) {
    tenant_delete('email_templates', ['id' => (int)$params['id']]);
    flash_set('success', 'Plantilla eliminada.');
    redirect(tenant_url('templates'));
}

function templates_ctrl_seed($params) {
    $tid = current_tenant()['id'];
    $existing = array_column(tenant_select('email_templates', ['slug']), 'slug');
    $count = 0;
    foreach (DEFAULT_TEMPLATES as $tpl) {
        if (in_array($tpl['slug'], $existing, true)) continue;
        tenant_insert('email_templates', array_merge($tpl, [
            'is_active' => 1,
            'created_by' => $_SESSION['user_id'] ?? null,
        ]));
        $count++;
    }
    flash_set('success', "$count plantillas estandar agregadas.");
    redirect(tenant_url('templates'));
}

function _templates_validate($post) {
    $name = trim($post['name'] ?? '');
    $slug = mb_strtolower(trim($post['slug'] ?? ''));
    $subject = trim($post['subject'] ?? '');
    $body = trim($post['body_html'] ?? '');
    if (mb_strlen($name) < 2) return 'Nombre requerido.';
    if (!preg_match('/^[a-z0-9_-]+$/', $slug)) return 'Slug invalido (solo a-z 0-9 _ -).';
    if (mb_strlen($subject) < 2) return 'Asunto requerido.';
    if (mb_strlen($body) < 10) return 'Cuerpo muy corto.';
    return [
        'name' => $name, 'slug' => $slug, 'subject' => $subject, 'body_html' => $body,
        'category' => trim($post['category'] ?? '') ?: null,
        'is_active' => isset($post['is_active']) ? 1 : 0,
    ];
}
