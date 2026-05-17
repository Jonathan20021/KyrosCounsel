<?php
function tasks_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $filter = $_GET['filter'] ?? 'open';
    $where = 't.tenant_id = :t';
    if ($filter === 'open')      $where .= ' AND t.status IN ("pending","in_progress")';
    elseif ($filter === 'mine')  $where .= ' AND t.assignee_id = ' . (int)($_SESSION['user_id'] ?? 0) . ' AND t.status IN ("pending","in_progress")';
    elseif ($filter === 'done')  $where .= ' AND t.status = "done"';

    $tasks = db_select(
        "SELECT t.*, u.name AS assignee_name, c.case_number FROM tasks t
         LEFT JOIN users u ON u.id = t.assignee_id
         LEFT JOIN cases c ON c.id = t.case_id
         WHERE {$where}
         ORDER BY (t.due_date IS NULL), t.due_date ASC, t.id DESC LIMIT 500",
        ['t' => $tid]
    );

    $attorneys = db_select(
        'SELECT id, name FROM users WHERE tenant_id = :t AND status = "active" ORDER BY name',
        ['t' => $tid]
    );
    $cases = db_select(
        'SELECT id, case_number, title FROM cases WHERE tenant_id = :t ORDER BY updated_at DESC LIMIT 100',
        ['t' => $tid]
    );

    render_with_layout('tenant', 'tasks.index', [
        'title' => 'Tareas', 'tasks' => $tasks, 'filter' => $filter,
        'attorneys' => $attorneys, 'cases' => $cases,
    ]);
}

function tasks_ctrl_board($params) {
    $tid = current_tenant()['id'];
    $rows = db_select(
        'SELECT t.*, c.case_number, u.name AS assignee_name FROM tasks t
         LEFT JOIN cases c ON c.id = t.case_id
         LEFT JOIN users u ON u.id = t.assignee_id
         WHERE t.tenant_id = :t ORDER BY t.priority DESC, t.due_date ASC',
        ['t' => $tid]
    );
    $byStatus = ['pending' => [], 'in_progress' => [], 'done' => [], 'cancelled' => []];
    foreach ($rows as $r) {
        if (isset($byStatus[$r['status']])) $byStatus[$r['status']][] = $r;
    }
    render_with_layout('tenant', 'tasks.board', [
        'title' => 'Tablero de tareas', 'by_status' => $byStatus,
    ]);
}

function tasks_ctrl_move($params) {
    header('Content-Type: application/json');
    $id = (int)$params['id'];
    $newStatus = $_POST['status'] ?? '';
    if (!in_array($newStatus, ['pending','in_progress','done','cancelled'], true)) {
        http_response_code(400); echo json_encode(['ok' => false]); exit;
    }
    $existing = tenant_first('tasks', ['id' => $id]);
    if (!$existing) { http_response_code(404); echo json_encode(['ok' => false]); exit; }
    if ($existing['status'] === $newStatus) {
        echo json_encode(['ok' => true, 'unchanged' => true]); exit;
    }
    $patch = ['status' => $newStatus];
    if ($newStatus === 'done')    $patch['completed_at'] = date('Y-m-d H:i:s');
    if ($newStatus !== 'done')    $patch['completed_at'] = null;
    tenant_update('tasks', $patch, ['id' => $id]);
    audit_record('task.status_changed', $_SESSION['user_id'] ?? null, ['id' => $id, 'to' => $newStatus]);
    if ($newStatus === 'done') {
        workflow_dispatch('task.completed', ['task_id' => $id, 'case_id' => $existing['case_id']]);
    }
    echo json_encode(['ok' => true, 'new_status' => $newStatus]);
    exit;
}

function tasks_ctrl_create($params) {
    $title = trim($_POST['title'] ?? '');
    if (mb_strlen($title) < 2) {
        flash_set('error', 'Titulo requerido.');
        redirect(tenant_url('tasks'));
    }
    $id = tenant_insert('tasks', [
        'case_id'    => (int)($_POST['case_id'] ?? 0) ?: null,
        'title'      => $title,
        'description'=> trim($_POST['description'] ?? '') ?: null,
        'assignee_id'=> (int)($_POST['assignee_id'] ?? 0) ?: null,
        'due_date'   => trim($_POST['due_date'] ?? '') ?: null,
        'priority'   => in_array($_POST['priority'] ?? 'normal', ['low','normal','high','urgent'], true)
                            ? $_POST['priority'] : 'normal',
        'status'     => 'pending',
        'created_by' => $_SESSION['user_id'] ?? null,
    ]);
    audit_record('task.created', $_SESSION['user_id'] ?? null, ['id' => $id]);
    // Notificar al asignado si no fue el creador
    $assignee = (int)($_POST['assignee_id'] ?? 0);
    if ($assignee && $assignee !== (int)($_SESSION['user_id'] ?? 0)) {
        notify($assignee, 'task.assigned', 'Nueva tarea asignada: ' . $title, [
            'body' => 'Te asignaron una tarea' . (!empty($_POST['due_date']) ? ' con vencimiento ' . $_POST['due_date'] : ''),
            'url'  => tenant_url('tasks?filter=mine'),
            'severity' => 'info',
        ]);
    }
    workflow_dispatch('task.created', ['task_id' => $id, 'case_id' => $_POST['case_id'] ?? null]);
    flash_set('success', 'Tarea creada.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('tasks'));
}

function tasks_ctrl_toggle($params) {
    $id = (int)$params['id'];
    $t = tenant_first('tasks', ['id' => $id]);
    if (!$t) abort(404);
    $newStatus = $t['status'] === 'done' ? 'pending' : 'done';
    $patch = ['status' => $newStatus];
    if ($newStatus === 'done') $patch['completed_at'] = date('Y-m-d H:i:s');
    tenant_update('tasks', $patch, ['id' => $id]);
    if ($newStatus === 'done') {
        workflow_dispatch('task.completed', ['task_id' => $id, 'case_id' => $t['case_id']]);
    }
    flash_set('success', $newStatus === 'done' ? 'Tarea completada.' : 'Tarea reabierta.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('tasks'));
}

function tasks_ctrl_delete($params) {
    tenant_delete('tasks', ['id' => (int)$params['id']]);
    flash_set('success', 'Tarea eliminada.');
    redirect(tenant_url('tasks'));
}
