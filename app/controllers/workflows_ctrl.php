<?php
function workflows_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $workflows = tenant_select('workflows');
    $runs = db_select(
        'SELECT r.*, w.name AS workflow_name FROM workflow_runs r
         JOIN workflows w ON w.id = r.workflow_id
         WHERE r.tenant_id = :t ORDER BY r.created_at DESC LIMIT 50',
        ['t' => $tid]
    );
    render_with_layout('tenant', 'workflows.index', [
        'title' => 'Workflows',
        'workflows' => $workflows, 'runs' => $runs,
    ]);
}

function workflows_ctrl_create($params) {
    $name    = trim($_POST['name'] ?? '');
    $event   = $_POST['trigger_event'] ?? '';
    $valid   = ['client.created','case.created','case.status_changed','task.completed','document.uploaded'];
    if (mb_strlen($name) < 3 || !in_array($event, $valid, true)) {
        flash_set('error', 'Datos invalidos.'); redirect(tenant_url('workflows'));
    }
    $actionType = $_POST['action_type'] ?? 'create_task';
    if ($actionType === 'create_task') {
        $action = [
            'type' => 'create_task',
            'title' => $_POST['action_title'] ?? 'Tarea automatica',
            'due_in_days' => (int)($_POST['action_due_days'] ?? 7),
            'priority' => $_POST['action_priority'] ?? 'normal',
        ];
    } elseif ($actionType === 'send_email') {
        $action = [
            'type' => 'send_email',
            'subject' => $_POST['action_subject'] ?? 'Notificacion',
            'body' => $_POST['action_body'] ?? '',
        ];
    } else {
        flash_set('error', 'Accion no soportada.'); redirect(tenant_url('workflows'));
    }
    tenant_insert('workflows', [
        'name' => $name,
        'trigger_event' => $event,
        'conditions_json' => json_encode([]),
        'actions_json' => json_encode([$action]),
        'is_active' => 1,
        'created_by' => $_SESSION['user_id'] ?? null,
    ]);
    flash_set('success', 'Workflow creado.');
    redirect(tenant_url('workflows'));
}

function workflows_ctrl_toggle($params) {
    $id = (int)$params['id'];
    $w = tenant_first('workflows', ['id' => $id]);
    if (!$w) abort(404);
    tenant_update('workflows', ['is_active' => $w['is_active'] ? 0 : 1], ['id' => $id]);
    flash_set('success', 'Workflow ' . ($w['is_active'] ? 'desactivado' : 'activado'));
    redirect(tenant_url('workflows'));
}
