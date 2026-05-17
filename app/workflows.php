<?php
/**
 * Motor de workflows simple basado en eventos.
 *
 * Eventos disparados por la app (en controllers):
 *   - client.created
 *   - case.created
 *   - case.status_changed
 *   - task.created
 *   - task.completed
 *   - document.uploaded
 *
 * Cada workflow tiene:
 *   - trigger_event (string)
 *   - conditions_json (array de "field op value", evaluados con AND)
 *   - actions_json (array de acciones: create_task, send_email, set_priority)
 */

function workflow_dispatch($event, $payload) {
    $t = current_tenant();
    if (!$t) return;

    try {
        $workflows = tenant_select('workflows', ['*'], ['trigger_event' => $event, 'is_active' => 1]);
    } catch (Throwable $e) {
        return; // tabla aun no creada
    }

    foreach ($workflows as $wf) {
        $cond = $wf['conditions_json'] ? json_decode($wf['conditions_json'], true) : [];
        if (!_workflow_match_conditions($cond, $payload)) {
            tenant_insert('workflow_runs', [
                'workflow_id' => (int)$wf['id'], 'event' => $event,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'status' => 'skipped',
            ]);
            continue;
        }
        $actions = $wf['actions_json'] ? json_decode($wf['actions_json'], true) : [];
        try {
            foreach ($actions as $action) {
                _workflow_exec_action($action, $payload);
            }
            tenant_insert('workflow_runs', [
                'workflow_id' => (int)$wf['id'], 'event' => $event,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'status' => 'success',
            ]);
        } catch (Throwable $e) {
            app_log('[workflow.failed] ' . $e->getMessage());
            tenant_insert('workflow_runs', [
                'workflow_id' => (int)$wf['id'], 'event' => $event,
                'payload_json' => json_encode($payload, JSON_UNESCAPED_UNICODE),
                'status' => 'failed',
                'error_message' => $e->getMessage(),
            ]);
        }
    }
}

function _workflow_match_conditions($cond, $payload) {
    if (empty($cond)) return true;
    foreach ($cond as $rule) {
        $field = $rule['field'] ?? null;
        $op    = $rule['op'] ?? '=';
        $val   = $rule['value'] ?? null;
        $actual = $payload[$field] ?? null;
        switch ($op) {
            case '=':  if ($actual != $val) return false; break;
            case '!=': if ($actual == $val) return false; break;
            case 'in': if (!in_array($actual, (array)$val, false)) return false; break;
            default:   if ($actual != $val) return false;
        }
    }
    return true;
}

function _workflow_exec_action($action, $payload) {
    $type = $action['type'] ?? '';
    switch ($type) {
        case 'create_task':
            tenant_insert('tasks', [
                'case_id'    => $payload['case_id'] ?? null,
                'client_id'  => $payload['client_id'] ?? null,
                'title'      => $action['title'] ?? 'Tarea automatica',
                'description'=> $action['description'] ?? null,
                'assignee_id'=> $action['assignee_id'] ?? ($payload['attorney_id'] ?? null),
                'due_date'   => $action['due_in_days']
                    ? date('Y-m-d', strtotime('+' . (int)$action['due_in_days'] . ' days'))
                    : null,
                'priority'   => $action['priority'] ?? 'normal',
                'status'     => 'pending',
            ]);
            return;

        case 'send_email':
            $to = $action['to'] ?? ($payload['client_email'] ?? null);
            if (!$to) return;
            $subject = _workflow_template($action['subject'] ?? 'Notificacion', $payload);
            $body    = _workflow_template($action['body']    ?? '', $payload);
            send_email($to, $subject, '<div style="font-family:sans-serif">' . nl2br(e($body)) . '</div>',
                'workflow', current_tenant()['id']);
            return;

        case 'set_priority':
            if (!empty($payload['case_id'])) {
                tenant_update('cases',
                    ['priority' => $action['priority'] ?? 'high'],
                    ['id' => (int)$payload['case_id']]);
            }
            return;
    }
}

function _workflow_template($tpl, $payload) {
    return preg_replace_callback('/\{\{(\w+)\}\}/', function ($m) use ($payload) {
        return (string)($payload[$m[1]] ?? '');
    }, $tpl);
}
