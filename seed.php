<?php
/**
 * Seeder.
 *  CLI:   "C:/xampp/php/php.exe" seed.php
 *  Web:   http://localhost/KyrosCounsel/seed.php  (solo en local)
 */

declare(strict_types=1);
define('BASE_PATH', __DIR__);
require BASE_PATH . '/config.php';
require APP_PATH . '/security.php';
require APP_PATH . '/helpers.php';
require APP_PATH . '/db.php';
require APP_PATH . '/audit.php';
require APP_PATH . '/auth.php';

if (PHP_SAPI !== 'cli' && APP_ENV === 'production') {
    http_response_code(403); exit('Bloqueado en produccion.');
}

if (PHP_SAPI !== 'cli') header('Content-Type: text/plain; charset=UTF-8');

$pdo = get_db();
$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
foreach (['workflow_runs','workflows','email_log','notes','tasks','documents','case_status_history','cases','clients','subscriptions','audit_log','rate_limits','users','tenants'] as $tbl) {
    try { $pdo->exec("TRUNCATE TABLE {$tbl}"); } catch (Throwable $e) {}
}
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');
echo "Tablas limpias.\n";

// 1) Tenant demo (DR)
db_run('INSERT INTO tenants (id, slug, name, country, status, plan_id, settings_json)
        VALUES (1, "bufete-demo", "Bufete Demo de Inmigracion", "DO", "active",
                (SELECT id FROM plans WHERE code = "pro"),
                JSON_OBJECT("locale","es"))');
echo "Tenant 1: bufete-demo\n";

// 2) Tenant demo 2 (US)
db_run('INSERT INTO tenants (id, slug, name, country, status, plan_id)
        VALUES (2, "miami-immigration", "Miami Immigration Law", "US", "active",
                (SELECT id FROM plans WHERE code = "basic"))');
echo "Tenant 2: miami-immigration\n";

// 3) Usuarios
$users = [
    [null, 'super_admin@kyroscounsel.com', 'Super Administrador', 'Admin123!',  'super_admin'],
    [1,    'admin@bufete-demo.com',         'Maria Admin',          'Bufete123!', 'tenant_admin'],
    [1,    'abogado@bufete-demo.com',       'Juan Abogado',         'Abogado123!','attorney'],
    [1,    'paralegal@bufete-demo.com',     'Ana Paralegal',        'Paralegal123!','paralegal'],
    [2,    'admin@miami-immigration.com',   'Robert Lawyer',        'Miami123!',  'tenant_admin'],
];
foreach ($users as [$tid, $email, $name, $pass, $role]) {
    db_run('INSERT INTO users (tenant_id, email, name, password_hash, role, status)
            VALUES (:t, :e, :n, :h, :r, "active")',
        ['t' => $tid, 'e' => $email, 'n' => $name, 'h' => password_hash_make($pass), 'r' => $role]);
    echo "User: {$email} / {$pass} ({$role})\n";
}

// 4) Suscripcion para bufete-demo
db_run('INSERT INTO subscriptions (tenant_id, plan_id, status, starts_at, next_billing_at)
        VALUES (1, (SELECT id FROM plans WHERE code = "pro"), "active", NOW(),
                DATE_ADD(NOW(), INTERVAL 30 DAY))');

// 5) Clientes demo (con PII cifrada)
function _enc($v, $tid) { return $v ? pii_encrypt($v, $tid) : null; }
function _idx($v) { return $v ? pii_blind_index($v) : null; }

$clients = [
    [1, 'Carlos',  'Mendez',     'carlos@example.com',  '+1809555000', 'DO', 'DO', 'P12345678', 'A012345678', '1985-03-12'],
    [1, 'Lucia',   'Rodriguez',  'lucia@example.com',   '+1809555111', 'DO', 'US', 'P87654321', 'A098765432', '1990-07-25'],
    [1, 'Pedro',   'Santos',     null,                   '+1809555222', 'DO', 'DO', null,         null,        '1978-11-03'],
    [1, 'Andrea',  'Perez',      'andrea@example.com',  '+1849555333', 'VE', 'DO', 'V99887766',  null,        '1995-05-18'],
    [2, 'John',    'Smith',      'john@example.com',    '+13055551234','US', 'US', null, 'A123456789', '1980-01-01'],
    [2, 'Maria',   'Garcia',     'maria@example.com',   '+13055552345','MX', 'US', 'P55667788', 'A234567890', '1992-09-09'],
];
foreach ($clients as [$tid, $fn, $ln, $em, $ph, $nat, $res, $pp, $an, $dob]) {
    db_run('INSERT INTO clients (tenant_id, first_name, last_name, email, phone, nationality, country_residence,
                passport_enc, passport_index, alien_number_enc, alien_number_index, date_of_birth_enc, status, created_by)
            VALUES (:t,:fn,:ln,:em,:ph,:nat,:res,:pe,:pi,:ae,:ai,:de,"active",:cb)',
        ['t' => $tid, 'fn' => $fn, 'ln' => $ln, 'em' => $em, 'ph' => $ph,
         'nat' => $nat, 'res' => $res,
         'pe' => _enc($pp, $tid), 'pi' => _idx($pp),
         'ae' => _enc($an, $tid), 'ai' => _idx($an),
         'de' => _enc($dob, $tid),
         'cb' => 2,
        ]);
}
echo "Clientes creados: " . count($clients) . "\n";

// 6) Casos demo
$cases = [
    [1, 1, 'CASE-2026-0001', 'Peticion familiar - hermano', 'I-130',  'US', 'preparing', 'high',   3, '2026-02-01'],
    [1, 1, 'CASE-2026-0002', 'Ajuste de estatus',           'I-485',  'US', 'filed',     'normal', 3, '2026-01-15'],
    [1, 2, 'CASE-2026-0003', 'Naturalizacion',              'N-400',  'US', 'rfe',       'urgent', 3, '2025-11-20'],
    [1, 3, 'CASE-2026-0004', 'Residencia permanente DR',    'RES_PERM','DO','intake',    'normal', 4, '2026-03-10'],
    [1, 4, 'CASE-2026-0005', 'Asilo politico',              'I-589',  'US', 'preparing', 'high',   3, '2026-02-20'],
    [2, 5, 'CASE-2026-0010', 'Residencia por matrimonio',   'I-130',  'US', 'preparing', 'normal', 5, '2026-01-30'],
    [2, 6, 'CASE-2026-0011', 'Ajuste de estatus',           'I-485',  'US', 'filed',     'high',   5, '2026-02-05'],
];
foreach ($cases as [$tid, $clientId, $num, $title, $type, $country, $status, $prio, $att, $opened]) {
    db_run('INSERT INTO cases (tenant_id, client_id, case_number, title, case_type, country, status, priority, attorney_id, opened_at, created_by)
            VALUES (:t,:c,:n,:tt,:tp,:co,:s,:p,:a,:o,:cb)',
        ['t'=>$tid,'c'=>$clientId,'n'=>$num,'tt'=>$title,'tp'=>$type,'co'=>$country,'s'=>$status,'p'=>$prio,'a'=>$att,'o'=>$opened,'cb'=>2]);
    $caseId = (int)$pdo->lastInsertId();
    db_run('INSERT INTO case_status_history (tenant_id, case_id, from_status, to_status, changed_by, note, created_at)
            VALUES (:t,:c,NULL,"intake",:cb,"Caso creado", :ts)',
        ['t'=>$tid,'c'=>$caseId,'cb'=>2,'ts'=>$opened . ' 09:00:00']);
    if ($status !== 'intake') {
        db_run('INSERT INTO case_status_history (tenant_id, case_id, from_status, to_status, changed_by, note)
                VALUES (:t,:c,"intake",:s,:cb,"Avance")',
            ['t'=>$tid,'c'=>$caseId,'s'=>$status,'cb'=>2]);
    }
}
echo "Casos creados: " . count($cases) . "\n";

// 7) Tareas demo
$tasks = [
    [1, 1, 'Recolectar acta de nacimiento', 'pending',  3, '+5 days',  'high'],
    [1, 1, 'Solicitar certificado policial', 'pending', 3, '+10 days', 'normal'],
    [1, 2, 'Preparar formulario I-485', 'in_progress',  3, '+2 days',  'urgent'],
    [1, 3, 'Responder RFE',              'pending',     3, '+1 days',  'urgent'],
    [1, null, 'Revisar correos pendientes','pending',   2, null,        'low'],
];
foreach ($tasks as [$tid, $caseId, $title, $status, $assignee, $dueExpr, $prio]) {
    $due = $dueExpr ? date('Y-m-d', strtotime($dueExpr)) : null;
    db_run('INSERT INTO tasks (tenant_id, case_id, title, assignee_id, due_date, priority, status, created_by)
            VALUES (:t,:c,:tt,:a,:d,:p,:s,:cb)',
        ['t'=>$tid,'c'=>$caseId,'tt'=>$title,'a'=>$assignee,'d'=>$due,'p'=>$prio,'s'=>$status,'cb'=>2]);
}
echo "Tareas creadas: " . count($tasks) . "\n";

// 8) Notas demo
db_run('INSERT INTO notes (tenant_id, case_id, client_id, body, author_id) VALUES
    (1, 1, 1, "Cliente envio pasaporte por email. Pendiente verificar autenticidad.", 3),
    (1, 2, 1, "Documentacion completa. Listo para presentar.", 3),
    (1, 3, 2, "USCIS pidio evidencia adicional de buena conducta.", 3)');

// 9) Workflow demo
db_run('INSERT INTO workflows (tenant_id, name, trigger_event, conditions_json, actions_json, is_active, created_by)
        VALUES (1, "Crear tarea de seguimiento al crear cliente", "client.created", "[]",
                JSON_ARRAY(JSON_OBJECT("type","create_task","title","Llamar a nuevo cliente para presentacion","due_in_days",2,"priority","high")),
                1, 2)');
echo "Workflow demo creado.\n";

echo "\n[OK] Seed completo.\n";
echo "\nLogin: " . APP_URL . "/login\n\n";
echo "Credenciales:\n";
echo "  Super admin:    super_admin@kyroscounsel.com / Admin123!\n";
echo "  Bufete demo:    admin@bufete-demo.com         / Bufete123!\n";
echo "  Abogado:        abogado@bufete-demo.com       / Abogado123!\n";
echo "  Paralegal:      paralegal@bufete-demo.com     / Paralegal123!\n";
echo "  Miami Law:      admin@miami-immigration.com   / Miami123!\n";
