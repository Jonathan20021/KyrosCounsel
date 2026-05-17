<?php
/**
 * KyrosCounsel - Front Controller.
 *
 * Router array-based. Cada ruta = [METODO, PATTERN, [archivo, funcion], middlewares].
 * Pattern usa {param} y {param:regex}. Match orden de declaracion.
 */

declare(strict_types=1);

define('BASE_PATH', __DIR__);
require BASE_PATH . '/app/bootstrap.php';

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    error_log('[fatal] ' . $e->getMessage() . ' in ' . $e->getFile() . ':' . $e->getLine());
    if (APP_DEBUG) {
        echo '<pre style="font-family:monospace;background:#1a1a1a;color:#f88;padding:20px;white-space:pre-wrap">';
        echo htmlspecialchars($e->getMessage() . "\n\n" . $e->getTraceAsString(), ENT_QUOTES);
        echo '</pre>';
    } else {
        echo '<h1>Internal Server Error</h1>';
    }
});

// ---- Routes ----
$routes = [
    // PUBLIC
    ['GET',  '/',                            ['public_ctrl', 'landing']],
    ['GET',  '/login',                       ['auth_ctrl',   'show_login'],     ['guest']],
    ['POST', '/login',                       ['auth_ctrl',   'do_login'],       ['guest']],
    ['GET',  '/login/2fa',                   ['auth_ctrl',   'show_2fa'],       []],
    ['POST', '/login/2fa',                   ['auth_ctrl',   'verify_2fa'],     []],
    ['POST', '/logout',                      ['auth_ctrl',   'do_logout'],      []],

    // Portal del cliente (PUBLICO con token)
    ['GET',  '/portal/{token:[a-f0-9]{64}}',         ['portal_ctrl', 'show']],
    ['GET',  '/portal/{token:[a-f0-9]{64}}/case/{id:\d+}', ['portal_ctrl', 'case_view']],
    ['POST', '/portal/{token:[a-f0-9]{64}}/case/{id:\d+}/message', ['portal_ctrl', 'send_message']],

    // Portal con login propio del cliente (email + password)
    ['GET',  '/cliente/login',                        ['client_auth_ctrl', 'show_login']],
    ['POST', '/cliente/login',                        ['client_auth_ctrl', 'do_login']],
    ['POST', '/cliente/logout',                       ['client_auth_ctrl', 'do_logout']],
    ['GET',  '/cliente',                              ['client_auth_ctrl', 'dashboard']],
    ['GET',  '/cliente/forgot',                       ['client_auth_ctrl', 'show_forgot']],
    ['POST', '/cliente/forgot',                       ['client_auth_ctrl', 'submit_forgot']],
    ['GET',  '/cliente/reset/{token:[a-f0-9]{64}}',   ['client_auth_ctrl', 'show_reset']],
    ['POST', '/cliente/reset/{token:[a-f0-9]{64}}',   ['client_auth_ctrl', 'do_reset']],

    // Payment links (publico con token unico)
    ['GET',  '/pay/{token:[a-f0-9]{40}}',              ['pay_ctrl', 'show']],
    ['POST', '/pay/{token:[a-f0-9]{40}}/checkout',     ['pay_ctrl', 'checkout']],
    ['GET',  '/pay/{token:[a-f0-9]{40}}/success',      ['pay_ctrl', 'success']],
    ['GET',  '/pay/{token:[a-f0-9]{40}}/cancel',       ['pay_ctrl', 'cancel']],
    ['GET',  '/onboarding',                  ['onboarding_ctrl', 'show'],       ['guest']],
    ['POST', '/onboarding',                  ['onboarding_ctrl', 'submit'],     ['guest']],
    ['GET',  '/pricing',                     ['public_ctrl', 'pricing']],

    // SUPER ADMIN — Dashboard
    ['GET',  '/admin',                       ['admin_ctrl', 'dashboard'],       ['super_admin']],

    // Health JSON (público, sin auth — para monitoreo externo)
    ['GET',  '/admin/health.json',           ['admin_ctrl', 'health'],          []],

    // Exportes CSV
    ['GET',  '/admin/export/tenants.csv',    ['admin_ctrl', 'export_tenants'],  ['super_admin']],
    ['GET',  '/admin/export/audit.csv',      ['admin_ctrl', 'export_audit'],    ['super_admin']],
    ['GET',  '/admin/export/invoices.csv',   ['admin_ctrl', 'export_invoices'], ['super_admin']],

    // Tenants
    ['GET',  '/admin/tenants',               ['admin_ctrl', 'tenants_index'],   ['super_admin']],
    ['GET',  '/admin/tenants/new',           ['admin_ctrl', 'tenants_new'],     ['super_admin']],
    ['POST', '/admin/tenants',               ['admin_ctrl', 'tenants_create'],  ['super_admin']],
    ['POST', '/admin/tenants/bulk',          ['admin_ctrl', 'tenants_bulk'],    ['super_admin']],
    ['GET',  '/admin/tenants/{id:\d+}',      ['admin_ctrl', 'tenants_show'],    ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}',      ['admin_ctrl', 'tenants_update'],  ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/status',       ['admin_ctrl','tenants_status'],         ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/extend-trial', ['admin_ctrl','tenants_extend_trial'],   ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/change-plan',  ['admin_ctrl','tenants_change_plan'],    ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/reset-password',['admin_ctrl','tenants_reset_password'],['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/delete',       ['admin_ctrl','tenants_delete'],         ['super_admin']],
    ['POST', '/admin/tenants/{id:\d+}/impersonate',  ['admin_ctrl','impersonate_start'],      ['super_admin']],
    ['POST', '/admin/impersonate/stop',              ['admin_ctrl','impersonate_stop'],       []],

    // Plans
    ['GET',  '/admin/plans',                  ['admin_ctrl', 'plans_index'],   ['super_admin']],
    ['GET',  '/admin/plans/new',              ['admin_ctrl', 'plans_new'],     ['super_admin']],
    ['POST', '/admin/plans',                  ['admin_ctrl', 'plans_save'],    ['super_admin']],
    ['GET',  '/admin/plans/{id:\d+}/edit',    ['admin_ctrl', 'plans_edit'],    ['super_admin']],
    ['POST', '/admin/plans/{id:\d+}',         ['admin_ctrl', 'plans_save'],    ['super_admin']],
    ['POST', '/admin/plans/{id:\d+}/toggle',  ['admin_ctrl', 'plans_toggle'],  ['super_admin']],

    // Users (cross-tenant)
    ['GET',  '/admin/users',                  ['admin_ctrl', 'users_index'],   ['super_admin']],
    ['POST', '/admin/users/{id:\d+}/disable', ['admin_ctrl', 'users_disable'], ['super_admin']],
    ['POST', '/admin/users/{id:\d+}/enable',  ['admin_ctrl', 'users_enable'],  ['super_admin']],

    // Licencias
    ['GET',  '/admin/licenses',                  ['admin_ctrl', 'licenses_index'],  ['super_admin']],
    ['POST', '/admin/licenses',                  ['admin_ctrl', 'licenses_create'], ['super_admin']],
    ['POST', '/admin/licenses/{id:\d+}/revoke',  ['admin_ctrl', 'licenses_revoke'], ['super_admin']],

    // Auditoría global
    ['GET',  '/admin/audit',                  ['admin_ctrl', 'audit_index'],   ['super_admin']],

    // Billing
    ['GET',  '/admin/billing',                  ['admin_ctrl', 'billing_index'],              ['super_admin']],
    ['POST', '/admin/billing/{id:\d+}/mark',    ['admin_ctrl', 'billing_invoice_mark'],       ['super_admin']],
    ['POST', '/admin/billing/generate',         ['admin_ctrl', 'billing_generate_invoices'],  ['super_admin']],

    // Announcements
    ['GET',  '/admin/announcements',                  ['admin_ctrl', 'announcements_index'],  ['super_admin']],
    ['POST', '/admin/announcements',                  ['admin_ctrl', 'announcements_save'],   ['super_admin']],
    ['POST', '/admin/announcements/{id:\d+}',         ['admin_ctrl', 'announcements_save'],   ['super_admin']],
    ['POST', '/admin/announcements/{id:\d+}/toggle',  ['admin_ctrl', 'announcements_toggle'], ['super_admin']],
    ['POST', '/admin/announcements/{id:\d+}/delete',  ['admin_ctrl', 'announcements_delete'], ['super_admin']],

    // System
    ['GET',  '/admin/system',                 ['admin_ctrl', 'system_index'],              ['super_admin']],
    ['POST', '/admin/system/settings',        ['admin_ctrl', 'system_settings_save'],      ['super_admin']],
    ['POST', '/admin/system/maintenance',     ['admin_ctrl', 'system_maintenance_toggle'], ['super_admin']],
    ['POST', '/admin/system/cache/clear',     ['admin_ctrl', 'system_cache_clear'],        ['super_admin']],

    // Security
    ['GET',  '/admin/security',               ['admin_ctrl', 'security_index'], ['super_admin']],

    // TENANT AREA - dashboard
    ['GET',  '/t/{tenant}/dashboard',        ['dashboard_ctrl', 'index'],       ['auth','tenant']],
    ['GET',  '/t/{tenant}/reports',          ['reports_ctrl',   'index'],       ['auth','tenant','perm:cases.view']],
    ['GET',  '/t/{tenant}/calendar',         ['calendar_ctrl',  'index'],       ['auth','tenant','perm:tasks.view']],
    ['GET',  '/t/{tenant}/audit',            ['audit_ctrl',     'index'],       ['auth','tenant','perm:users.view']],
    ['GET',  '/t/{tenant}/export/{kind:[a-z]+}',['exports_ctrl', 'csv'],         ['auth','tenant']],

    // TENANT AREA - clients
    ['GET',  '/t/{tenant}/clients',                  ['clients_ctrl','index'],   ['auth','tenant','perm:clients.view']],
    ['GET',  '/t/{tenant}/clients/new',              ['clients_ctrl','new'],     ['auth','tenant','perm:clients.create']],
    ['POST', '/t/{tenant}/clients',                  ['clients_ctrl','create'],  ['auth','tenant','perm:clients.create']],
    ['GET',  '/t/{tenant}/clients/{id:\d+}',         ['clients_ctrl','show'],    ['auth','tenant','perm:clients.view']],
    ['GET',  '/t/{tenant}/clients/{id:\d+}/edit',    ['clients_ctrl','edit'],    ['auth','tenant','perm:clients.update']],
    ['POST', '/t/{tenant}/clients/{id:\d+}',         ['clients_ctrl','update'],  ['auth','tenant','perm:clients.update']],
    ['POST', '/t/{tenant}/clients/{id:\d+}/delete',  ['clients_ctrl','delete'],  ['auth','tenant','perm:clients.delete']],
    ['POST', '/t/{tenant}/clients/{id:\d+}/portal',  ['clients_ctrl','generate_portal_token'], ['auth','tenant','perm:clients.update']],
    ['POST', '/t/{tenant}/clients/{id:\d+}/portal/enable',  ['clients_ctrl','enable_portal_login'], ['auth','tenant','perm:clients.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/payments/{pid:\d+}/link', ['payments_ctrl','generate_link'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/plans',     ['payments_ctrl','create_plan'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/plans/{plid:\d+}/cancel', ['payments_ctrl','cancel_plan'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/clients/{id:\d+}/portal/{ptid:\d+}/revoke', ['clients_ctrl','revoke_portal_token'], ['auth','tenant','perm:clients.update']],

    // G-28 PDF
    ['GET',  '/t/{tenant}/cases/{id:\d+}/g28',       ['cases_ctrl','g28'],       ['auth','tenant','perm:cases.view']],
    ['GET',  '/t/{tenant}/cases/{id:\d+}/invoice',   ['cases_ctrl','invoice'],   ['auth','tenant','perm:cases.view']],

    // Mensajeria desde el caso (staff side)
    ['POST', '/t/{tenant}/cases/{id:\d+}/messages', ['cases_ctrl','send_message_staff'], ['auth','tenant','perm:cases.update']],

    // Dashboard financiero
    ['GET',  '/t/{tenant}/finance',                  ['finance_ctrl','index'],   ['auth','tenant','perm:billing.view']],

    // Email templates
    ['GET',  '/t/{tenant}/templates',                ['templates_ctrl','index'], ['auth','tenant','perm:settings.manage']],
    ['GET',  '/t/{tenant}/templates/new',            ['templates_ctrl','new'],   ['auth','tenant','perm:settings.manage']],
    ['POST', '/t/{tenant}/templates',                ['templates_ctrl','create'],['auth','tenant','perm:settings.manage']],
    ['GET',  '/t/{tenant}/templates/{id:\d+}/edit',  ['templates_ctrl','edit'],  ['auth','tenant','perm:settings.manage']],
    ['POST', '/t/{tenant}/templates/{id:\d+}',       ['templates_ctrl','update'],['auth','tenant','perm:settings.manage']],
    ['POST', '/t/{tenant}/templates/{id:\d+}/delete',['templates_ctrl','delete'],['auth','tenant','perm:settings.manage']],
    ['POST', '/t/{tenant}/templates/seed',           ['templates_ctrl','seed'],  ['auth','tenant','perm:settings.manage']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/send-email',['cases_ctrl','send_email_template'], ['auth','tenant','perm:cases.update']],

    // Calendario semanal
    ['GET',  '/t/{tenant}/calendar/week',            ['calendar_ctrl','week'],   ['auth','tenant','perm:tasks.view']],

    // Cover letter + cartas adicionales
    ['GET',  '/t/{tenant}/cases/{id:\d+}/cover-letter', ['cases_ctrl','cover_letter'], ['auth','tenant','perm:cases.view']],
    ['GET',  '/t/{tenant}/cases/{id:\d+}/letter/{kind:[a-z_]+}', ['cases_ctrl','letter'], ['auth','tenant','perm:cases.view']],

    // Busqueda global live (JSON)
    ['GET',  '/t/{tenant}/search',                   ['search_ctrl','live'],     ['auth','tenant']],

    // Saved views
    ['POST', '/t/{tenant}/views',                    ['views_ctrl','create'],    ['auth','tenant']],
    ['POST', '/t/{tenant}/views/{id:\d+}/delete',    ['views_ctrl','delete'],    ['auth','tenant']],

    // Notificaciones
    ['POST', '/t/{tenant}/notifications/read',       ['notifications_ctrl','mark_all_read'], ['auth','tenant']],
    ['POST', '/t/{tenant}/notifications/{id:\d+}/read', ['notifications_ctrl','mark_one_read'], ['auth','tenant']],

    // TENANT AREA - cases
    ['GET',  '/t/{tenant}/cases',                    ['cases_ctrl','index'],     ['auth','tenant','perm:cases.view']],
    ['GET',  '/t/{tenant}/cases/board',              ['cases_ctrl','board'],     ['auth','tenant','perm:cases.view']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/move',      ['cases_ctrl','move'],      ['auth','tenant','perm:cases.update']],
    ['GET',  '/t/{tenant}/cases/new',                ['cases_ctrl','new'],       ['auth','tenant','perm:cases.create']],
    ['POST', '/t/{tenant}/cases',                    ['cases_ctrl','create'],    ['auth','tenant','perm:cases.create']],
    ['GET',  '/t/{tenant}/cases/{id:\d+}',           ['cases_ctrl','show'],      ['auth','tenant','perm:cases.view']],
    ['GET',  '/t/{tenant}/cases/{id:\d+}/edit',      ['cases_ctrl','edit'],      ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}',           ['cases_ctrl','update'],    ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/status',    ['cases_ctrl','change_status'],['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/notes',     ['cases_ctrl','add_note'],  ['auth','tenant','perm:notes.create']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/uscis',     ['cases_ctrl','update_uscis'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/time',      ['cases_ctrl','add_time'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/time/{tid:\d+}/delete', ['cases_ctrl','delete_time'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/bulk',                ['cases_ctrl','bulk_action'], ['auth','tenant','perm:cases.update']],
    ['GET',  '/t/{tenant}/cases/{id:\d+}/print',     ['cases_ctrl','print_sheet'],['auth','tenant','perm:cases.view']],

    // Beneficiarios
    ['POST', '/t/{tenant}/cases/{id:\d+}/beneficiaries',         ['beneficiaries_ctrl','create'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/beneficiaries/{bid:\d+}/delete', ['beneficiaries_ctrl','delete'], ['auth','tenant','perm:cases.update']],

    // Pagos
    ['POST', '/t/{tenant}/cases/{id:\d+}/payments',  ['payments_ctrl','create'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/payments/{pid:\d+}/toggle', ['payments_ctrl','toggle_paid'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/payments/{pid:\d+}/delete', ['payments_ctrl','delete'], ['auth','tenant','perm:cases.update']],

    // Citas
    ['POST', '/t/{tenant}/cases/{id:\d+}/appointments', ['appointments_ctrl','create'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/appointments/{aid:\d+}/delete', ['appointments_ctrl','delete'], ['auth','tenant','perm:cases.update']],

    // Evidencia / checklist
    ['POST', '/t/{tenant}/cases/{id:\d+}/evidence/seed',['evidence_ctrl','seed_template'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/evidence',     ['evidence_ctrl','create'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/evidence/{eid:\d+}/toggle', ['evidence_ctrl','toggle'], ['auth','tenant','perm:cases.update']],
    ['POST', '/t/{tenant}/cases/{id:\d+}/evidence/{eid:\d+}/delete', ['evidence_ctrl','delete'], ['auth','tenant','perm:cases.update']],

    // TENANT AREA - documents
    ['GET',  '/t/{tenant}/documents',                ['documents_ctrl','index'], ['auth','tenant','perm:documents.view']],
    ['POST', '/t/{tenant}/documents/upload',         ['documents_ctrl','upload'],['auth','tenant','perm:documents.create']],
    ['GET',  '/t/{tenant}/documents/{id:\d+}/download',['documents_ctrl','download'],['auth','tenant','perm:documents.view']],
    ['POST', '/t/{tenant}/documents/{id:\d+}/delete',['documents_ctrl','delete'],['auth','tenant','perm:documents.delete']],

    // TENANT AREA - tasks
    ['GET',  '/t/{tenant}/tasks',                    ['tasks_ctrl','index'],     ['auth','tenant','perm:tasks.view']],
    ['POST', '/t/{tenant}/tasks',                    ['tasks_ctrl','create'],    ['auth','tenant','perm:tasks.create']],
    ['POST', '/t/{tenant}/tasks/{id:\d+}/toggle',    ['tasks_ctrl','toggle'],    ['auth','tenant','perm:tasks.update']],
    ['GET',  '/t/{tenant}/tasks/board',              ['tasks_ctrl','board'],     ['auth','tenant','perm:tasks.view']],
    ['POST', '/t/{tenant}/tasks/{id:\d+}/move',      ['tasks_ctrl','move'],      ['auth','tenant','perm:tasks.update']],
    ['POST', '/t/{tenant}/tasks/{id:\d+}/delete',    ['tasks_ctrl','delete'],    ['auth','tenant','perm:tasks.update']],

    // TENANT AREA - users
    ['GET',  '/t/{tenant}/users',                    ['users_ctrl','index'],     ['auth','tenant','perm:users.view']],
    ['GET',  '/t/{tenant}/users/new',                ['users_ctrl','new'],       ['auth','tenant','perm:users.create']],
    ['POST', '/t/{tenant}/users',                    ['users_ctrl','create'],    ['auth','tenant','perm:users.create']],
    ['POST', '/t/{tenant}/users/{id:\d+}/status',    ['users_ctrl','toggle_status'],['auth','tenant','perm:users.update']],

    // TENANT AREA - workflows
    ['GET',  '/t/{tenant}/workflows',                ['workflows_ctrl','index'], ['auth','tenant','perm:workflows.view']],
    ['POST', '/t/{tenant}/workflows',                ['workflows_ctrl','create'],['auth','tenant','perm:workflows.create']],
    ['POST', '/t/{tenant}/workflows/{id:\d+}/toggle',['workflows_ctrl','toggle'],['auth','tenant','perm:workflows.update']],

    // TENANT AREA - billing
    ['GET',  '/t/{tenant}/billing',                  ['billing_ctrl','index'],   ['auth','tenant','perm:billing.view']],
    ['POST', '/t/{tenant}/billing/change-plan',      ['billing_ctrl','change_plan'],['auth','tenant','perm:billing.manage']],

    // TENANT AREA - settings/perfil
    ['GET',  '/t/{tenant}/profile',                  ['profile_ctrl','show'],    ['auth','tenant']],
    ['POST', '/t/{tenant}/profile/password',         ['profile_ctrl','change_password'],['auth','tenant']],
    ['GET',  '/t/{tenant}/profile/2fa',              ['profile_ctrl','show_2fa_setup'],['auth','tenant']],
    ['POST', '/t/{tenant}/profile/2fa/enable',       ['profile_ctrl','enable_2fa'],['auth','tenant']],
    ['POST', '/t/{tenant}/profile/2fa/disable',      ['profile_ctrl','disable_2fa'],['auth','tenant']],
];

// ---- Cargar controladores ----
foreach (glob(APP_PATH . '/controllers/*.php') as $f) require_once $f;

// ---- Dispatch ----
require APP_PATH . '/router.php';
router_dispatch($routes);
