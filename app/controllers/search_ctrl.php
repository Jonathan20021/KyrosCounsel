<?php
/**
 * Busqueda live: clientes + casos + notas + tareas. Devuelve JSON.
 * Cmd+K llama a este endpoint con ?q=...
 */
function search_ctrl_live($params) {
    header('Content-Type: application/json; charset=UTF-8');
    $tid = current_tenant()['id'];
    $q = trim($_GET['q'] ?? '');

    if (mb_strlen($q) < 2) {
        echo json_encode(['ok' => true, 'results' => []]);
        exit;
    }

    $like = '%' . $q . '%';
    $idx = pii_blind_index($q);
    $results = [];

    // Clientes (por nombre, email, blind index para passport/A-num)
    $clients = db_select(
        'SELECT id, first_name, last_name, email, nationality
         FROM clients
         WHERE tenant_id = :t
           AND (LOWER(first_name) LIKE LOWER(:q1) OR LOWER(last_name) LIKE LOWER(:q2)
                OR LOWER(email) LIKE LOWER(:q3) OR passport_index = :idx OR alien_number_index = :idx2)
         ORDER BY last_name, first_name LIMIT 8',
        ['t' => $tid, 'q1' => $like, 'q2' => $like, 'q3' => $like, 'idx' => $idx, 'idx2' => $idx]
    );
    foreach ($clients as $c) {
        $results[] = [
            'kind' => 'client',
            'icon' => '👤',
            'title' => $c['last_name'] . ', ' . $c['first_name'],
            'subtitle' => ($c['email'] ?? '') . ($c['nationality'] ? ' · ' . country_name($c['nationality']) : ''),
            'url' => tenant_url('clients/' . $c['id']),
        ];
    }

    // Casos
    $cases = db_select(
        'SELECT c.id, c.case_number, c.title, c.case_type, c.status, cl.first_name, cl.last_name
         FROM cases c JOIN clients cl ON cl.id = c.client_id
         WHERE c.tenant_id = :t
           AND (c.case_number LIKE :q1 OR LOWER(c.title) LIKE LOWER(:q2)
                OR LOWER(cl.first_name) LIKE LOWER(:q3) OR LOWER(cl.last_name) LIKE LOWER(:q4)
                OR c.uscis_receipt = :q5)
         ORDER BY c.updated_at DESC LIMIT 8',
        ['t' => $tid, 'q1' => $like, 'q2' => $like, 'q3' => $like, 'q4' => $like, 'q5' => strtoupper($q)]
    );
    foreach ($cases as $cs) {
        $results[] = [
            'kind' => 'case',
            'icon' => '📂',
            'title' => $cs['case_number'] . ' — ' . $cs['title'],
            'subtitle' => $cs['first_name'] . ' ' . $cs['last_name'] . ' · ' . $cs['case_type'] . ' · ' . case_status_label($cs['status']),
            'url' => tenant_url('cases/' . $cs['id']),
        ];
    }

    // Tareas
    $tasks = db_select(
        'SELECT t.id, t.title, t.due_date, t.case_id, t.status, c.case_number
         FROM tasks t LEFT JOIN cases c ON c.id = t.case_id
         WHERE t.tenant_id = :t AND LOWER(t.title) LIKE LOWER(:q1)
         ORDER BY t.due_date ASC LIMIT 5',
        ['t' => $tid, 'q1' => $like]
    );
    foreach ($tasks as $tk) {
        $results[] = [
            'kind' => 'task',
            'icon' => '✓',
            'title' => $tk['title'],
            'subtitle' => 'Tarea · ' . $tk['status'] . ($tk['case_number'] ? ' · ' . $tk['case_number'] : '') . ($tk['due_date'] ? ' · vence ' . $tk['due_date'] : ''),
            'url' => $tk['case_id'] ? tenant_url('cases/' . $tk['case_id']) : tenant_url('tasks'),
        ];
    }

    // Notas (busca en body)
    $notes = db_select(
        'SELECT n.id, n.case_id, n.body, c.case_number FROM notes n
         LEFT JOIN cases c ON c.id = n.case_id
         WHERE n.tenant_id = :t AND LOWER(n.body) LIKE LOWER(:q1)
         ORDER BY n.created_at DESC LIMIT 5',
        ['t' => $tid, 'q1' => $like]
    );
    foreach ($notes as $n) {
        $excerpt = mb_substr(strip_tags($n['body']), 0, 80);
        $results[] = [
            'kind' => 'note',
            'icon' => '📝',
            'title' => $excerpt . (mb_strlen($n['body']) > 80 ? '...' : ''),
            'subtitle' => 'Nota' . ($n['case_number'] ? ' · ' . $n['case_number'] : ''),
            'url' => $n['case_id'] ? tenant_url('cases/' . $n['case_id']) : '#',
        ];
    }

    echo json_encode(['ok' => true, 'q' => $q, 'results' => $results]);
    exit;
}
