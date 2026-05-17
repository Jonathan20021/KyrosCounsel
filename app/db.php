<?php
/**
 * Capa de base de datos - PDO endurecido + multi-tenant.
 *
 * Reglas obligatorias:
 *   1. NUNCA concatenes variables en SQL. Solo prepared statements.
 *   2. Para tablas que tienen columna tenant_id (users, clients, cases, ...)
 *      usa los helpers tenant_*  : ellos inyectan tenant_id automaticamente.
 *   3. Para tablas globales (tenants, plans) usa db_select / db_one / db_run.
 *
 * El truco anti-fuga: los helpers tenant_* no aceptan que el caller pase
 * tenant_id; lo agregan ellos solos desde current_tenant().
 */

/** Tablas que llevan tenant_id obligatorio */
$GLOBALS['TENANT_TABLES'] = [
    'users', 'clients', 'cases', 'case_status_history',
    'case_beneficiaries', 'case_payments', 'case_appointments', 'case_evidence',
    'case_time_entries',
    'notifications', 'client_portal_tokens', 'case_messages', 'email_templates', 'saved_views',
    'client_sessions', 'payment_links', 'payment_plans', 'client_password_resets',
    'documents', 'tasks', 'notes',
    'workflows', 'workflow_runs',
    'subscriptions', 'audit_log', 'email_log',
];

function get_db() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = sprintf('mysql:host=%s;port=%d;dbname=%s;charset=%s',
        DB_HOST, DB_PORT, DB_NAME, DB_CHARSET);

    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,  // CRITICO: real prepared statements
        PDO::ATTR_PERSISTENT         => false,
        PDO::MYSQL_ATTR_INIT_COMMAND =>
            "SET NAMES " . DB_CHARSET . " COLLATE " . DB_COLLATION . ", " .
            "sql_mode='STRICT_ALL_TABLES,NO_ZERO_DATE,NO_ZERO_IN_DATE,ERROR_FOR_DIVISION_BY_ZERO'",
    ]);
    return $pdo;
}

// =====================================================================
// Helpers globales (NO inyectan tenant_id)
// Usar SOLO para tablas globales (tenants, rate_limits, plans).
// =====================================================================

function db_select($sql, $params = []) {
    $stmt = get_db()->prepare($sql);
    _bind_params($stmt, $params);
    $stmt->execute();
    return $stmt->fetchAll();
}

function db_one($sql, $params = []) {
    $stmt = get_db()->prepare($sql);
    _bind_params($stmt, $params);
    $stmt->execute();
    $row = $stmt->fetch();
    return $row === false ? null : $row;
}

function db_run($sql, $params = []) {
    $stmt = get_db()->prepare($sql);
    _bind_params($stmt, $params);
    $stmt->execute();
    return $stmt->rowCount();
}

function db_last_id() {
    return get_db()->lastInsertId();
}

// =====================================================================
// Helpers tenant-scoped: AUTOMATICAMENTE filtran/insertan tenant_id.
// =====================================================================

function tenant_select($table, $columns = ['*'], $where = []) {
    _assert_tenant_table($table);
    $tid = _require_tenant_id();
    $where['tenant_id'] = $tid;

    $cols = implode(', ', array_map('_quote_ident', $columns));
    list($whereSql, $params) = _build_where($where);
    $sql = "SELECT {$cols} FROM " . _quote_ident($table) . $whereSql;
    return db_select($sql, $params);
}

function tenant_first($table, $where = []) {
    _assert_tenant_table($table);
    $where['tenant_id'] = _require_tenant_id();
    list($whereSql, $params) = _build_where($where);
    $sql = "SELECT * FROM " . _quote_ident($table) . $whereSql . " LIMIT 1";
    return db_one($sql, $params);
}

function tenant_count($table, $where = []) {
    _assert_tenant_table($table);
    $where['tenant_id'] = _require_tenant_id();
    list($whereSql, $params) = _build_where($where);
    $sql = "SELECT COUNT(*) AS c FROM " . _quote_ident($table) . $whereSql;
    $row = db_one($sql, $params);
    return (int)($row['c'] ?? 0);
}

function tenant_insert($table, $data) {
    _assert_tenant_table($table);
    $data['tenant_id'] = _require_tenant_id(); // sobrescribe lo que venga del caller
    $cols = array_keys($data);
    $placeholders = array_map(function ($c) { return ':' . $c; }, $cols);
    $sql = 'INSERT INTO ' . _quote_ident($table) .
           ' (' . implode(', ', array_map('_quote_ident', $cols)) . ')' .
           ' VALUES (' . implode(', ', $placeholders) . ')';
    db_run($sql, $data);
    return get_db()->lastInsertId();
}

function tenant_update($table, $data, $where) {
    _assert_tenant_table($table);
    if (empty($where)) {
        throw new RuntimeException('UPDATE sin WHERE bloqueado por seguridad.');
    }
    $where['tenant_id'] = _require_tenant_id();
    unset($data['tenant_id']); // nadie puede mover registros entre tenants

    $sets = [];
    $params = [];
    foreach ($data as $col => $val) {
        $sets[] = _quote_ident($col) . ' = :set_' . $col;
        $params['set_' . $col] = $val;
    }
    list($whereSql, $whereParams) = _build_where($where, 'w_');
    $sql = 'UPDATE ' . _quote_ident($table) . ' SET ' . implode(', ', $sets) . $whereSql;
    return db_run($sql, array_merge($params, $whereParams));
}

function tenant_delete($table, $where) {
    _assert_tenant_table($table);
    if (empty($where)) {
        throw new RuntimeException('DELETE sin WHERE bloqueado por seguridad.');
    }
    $where['tenant_id'] = _require_tenant_id();
    list($whereSql, $params) = _build_where($where);
    $sql = 'DELETE FROM ' . _quote_ident($table) . $whereSql;
    return db_run($sql, $params);
}

// =====================================================================
// Internos
// =====================================================================

function _bind_params(PDOStatement $stmt, array $params) {
    foreach ($params as $key => $val) {
        $name = is_int($key) ? $key + 1 : ':' . $key;
        if (is_int($val))      $type = PDO::PARAM_INT;
        elseif (is_bool($val)) $type = PDO::PARAM_BOOL;
        elseif (is_null($val)) $type = PDO::PARAM_NULL;
        else                   $type = PDO::PARAM_STR;
        $stmt->bindValue($name, $val, $type);
    }
}

function _build_where(array $where, $prefix = '') {
    if (empty($where)) return ['', []];
    $parts = [];
    $params = [];
    foreach ($where as $col => $val) {
        $param = $prefix . $col;
        if (is_null($val)) {
            $parts[] = _quote_ident($col) . ' IS NULL';
        } else {
            $parts[] = _quote_ident($col) . ' = :' . $param;
            $params[$param] = $val;
        }
    }
    return [' WHERE ' . implode(' AND ', $parts), $params];
}

function _quote_ident($name) {
    if ($name === '*') return '*';
    if (!preg_match('/^[a-zA-Z0-9_\.]+$/', $name)) {
        throw new InvalidArgumentException("Identificador invalido: {$name}");
    }
    if (strpos($name, '.') !== false) {
        $parts = explode('.', $name);
        $parts = array_map(function ($p) { return '`' . $p . '`'; }, $parts);
        return implode('.', $parts);
    }
    return '`' . $name . '`';
}

function _assert_tenant_table($table) {
    if (!in_array($table, $GLOBALS['TENANT_TABLES'], true)) {
        throw new RuntimeException(
            "Tabla '{$table}' no esta declarada como tenant-scoped. " .
            "Agregala a TENANT_TABLES o usa db_select/db_run para tablas globales."
        );
    }
}

function _require_tenant_id() {
    $t = current_tenant();
    if (!$t) {
        throw new RuntimeException(
            'Operacion tenant-scoped sin tenant en contexto. ' .
            'Asegurate de llamar resolve_tenant() antes.'
        );
    }
    return (int)$t['id'];
}

function db_transaction($callback) {
    $pdo = get_db();
    $pdo->beginTransaction();
    try {
        $r = $callback();
        $pdo->commit();
        return $r;
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}
