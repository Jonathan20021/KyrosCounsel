<?php
/**
 * Export a CSV (UTF-8 BOM + comma). Compatible con Excel.
 * Permisos: cada export valida su propio perm via require_perm.
 */

function exports_ctrl_csv($params) {
    $kind = $params['kind'] ?? '';
    $tid = current_tenant()['id'];
    $now = date('Ymd_His');

    switch ($kind) {
        case 'clients':
            require_perm('clients.view');
            $rows = db_select('SELECT id, first_name, last_name, email, phone, nationality, country_residence, status, created_at
                               FROM clients WHERE tenant_id = :t ORDER BY last_name, first_name', ['t' => $tid]);
            $headers = ['ID','Nombre','Apellido','Email','Telefono','Nacionalidad','Residencia','Estado','Creado'];
            $mapper = function($r){ return [
                $r['id'], $r['first_name'], $r['last_name'], $r['email'] ?? '', $r['phone'] ?? '',
                $r['nationality'] ?? '', $r['country_residence'] ?? '', $r['status'], $r['created_at']
            ]; };
            _csv_emit('clientes_' . $now, $headers, $rows, $mapper);
            return;

        case 'cases':
            require_perm('cases.view');
            $rows = db_select(
                'SELECT c.id, c.case_number, c.title, c.case_type, c.country, c.status, c.priority,
                        c.opened_at, c.filed_at, c.decision_at,
                        cl.first_name, cl.last_name, u.name AS attorney
                 FROM cases c JOIN clients cl ON cl.id = c.client_id
                 LEFT JOIN users u ON u.id = c.attorney_id
                 WHERE c.tenant_id = :t ORDER BY c.opened_at DESC', ['t' => $tid]);
            $headers = ['ID','Numero','Titulo','Tipo','Pais','Estado','Prioridad','Apertura','Presentado','Decision','Cliente nombre','Cliente apellido','Abogado'];
            $mapper = function($r){ return [
                $r['id'], $r['case_number'], $r['title'], $r['case_type'], $r['country'],
                case_status_label($r['status']), CASE_PRIORITIES[$r['priority']] ?? $r['priority'],
                $r['opened_at'], $r['filed_at'] ?? '', $r['decision_at'] ?? '',
                $r['first_name'], $r['last_name'], $r['attorney'] ?? ''
            ]; };
            _csv_emit('casos_' . $now, $headers, $rows, $mapper);
            return;

        case 'tasks':
            require_perm('tasks.view');
            $rows = db_select(
                'SELECT t.id, t.title, t.priority, t.status, t.due_date, t.completed_at, t.created_at,
                        c.case_number, u.name AS assignee
                 FROM tasks t LEFT JOIN cases c ON c.id = t.case_id
                 LEFT JOIN users u ON u.id = t.assignee_id
                 WHERE t.tenant_id = :t ORDER BY t.id DESC', ['t' => $tid]);
            $headers = ['ID','Titulo','Prioridad','Estado','Vence','Completada','Caso','Asignado','Creada'];
            $mapper = function($r){ return [
                $r['id'], $r['title'], CASE_PRIORITIES[$r['priority']] ?? $r['priority'], $r['status'],
                $r['due_date'] ?? '', $r['completed_at'] ?? '', $r['case_number'] ?? '', $r['assignee'] ?? '', $r['created_at']
            ]; };
            _csv_emit('tareas_' . $now, $headers, $rows, $mapper);
            return;

        case 'audit':
            require_perm('users.view');
            $rows = db_select(
                'SELECT a.created_at, u.name AS actor, a.event, a.ip, a.context_json
                 FROM audit_log a LEFT JOIN users u ON u.id = a.actor_user_id
                 WHERE a.tenant_id = :t ORDER BY a.created_at DESC LIMIT 5000', ['t' => $tid]);
            $headers = ['Fecha','Actor','Evento','IP','Contexto'];
            $mapper = function($r){ return [
                $r['created_at'], $r['actor'] ?? 'Sistema', $r['event'], $r['ip'] ?? '', $r['context_json'] ?? ''
            ]; };
            _csv_emit('auditoria_' . $now, $headers, $rows, $mapper);
            return;
    }
    abort(404);
}

function _csv_emit($filename, $headers, $rows, $mapper) {
    while (ob_get_level()) ob_end_clean();
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    header('Cache-Control: no-store');

    $out = fopen('php://output', 'w');
    fwrite($out, "\xEF\xBB\xBF"); // UTF-8 BOM para Excel
    fputcsv($out, $headers);
    foreach ($rows as $r) fputcsv($out, $mapper($r));
    fclose($out);

    audit_record('export.csv', $_SESSION['user_id'] ?? null, ['kind' => $filename, 'rows' => count($rows)]);
    exit;
}
