<?php
$u = current_user();
$t = current_tenant();
$current_path = $_SERVER['REQUEST_URI'] ?? '';

// Anuncios globales activos para este tenant
$_announcements = [];
try { $_announcements = announcements_active_for($t['status'] ?? 'active'); } catch (Throwable $e) {}

// Counters para sidebar (cached en sesión por 60s para no consultar en cada request)
$_now = time();
if (!isset($_SESSION['_sidebar_counters_at']) || ($_now - $_SESSION['_sidebar_counters_at']) > 60) {
    try {
        $_SESSION['_sidebar_counters'] = [
            'cases_open'     => (int)(db_one("SELECT COUNT(*) c FROM cases WHERE tenant_id = :t AND status NOT IN ('approved','denied','closed','withdrawn')", ['t'=>$t['id']])['c'] ?? 0),
            'tasks_pending'  => (int)(db_one("SELECT COUNT(*) c FROM tasks WHERE tenant_id = :t AND status = 'pending'", ['t'=>$t['id']])['c'] ?? 0),
            'tasks_overdue'  => (int)(db_one("SELECT COUNT(*) c FROM tasks WHERE tenant_id = :t AND status = 'pending' AND due_date < CURDATE()", ['t'=>$t['id']])['c'] ?? 0),
        ];
        $_SESSION['_sidebar_counters_at'] = $_now;
    } catch (Throwable $e) {
        $_SESSION['_sidebar_counters'] = ['cases_open'=>0,'tasks_pending'=>0,'tasks_overdue'=>0];
    }
}
$_sb = $_SESSION['_sidebar_counters'];

// Notificaciones persistentes desde tabla
$_persistent = [];
try { $_persistent = notifications_for_user(15); } catch (Throwable $e) {}
$_notif_count = 0;
$_notif = [];
foreach ($_persistent as $n) {
    if ($n['read_at'] === null) $_notif_count++;
    $_notif[] = [
        'id'    => (int)$n['id'],
        'type'  => $n['type'],
        'title' => $n['title'],
        'detail'=> $n['body'] ?? '',
        'url'   => $n['url'] ?? tenant_url('dashboard'),
        'severity' => $n['severity'],
        'read'  => $n['read_at'] !== null,
        'when'  => $n['created_at'],
    ];
}

// Sintetizar notificaciones live (no persistentes) si no hay reales
if (empty($_notif)) {
    try {
        $tid = $t['id'];
        $rows = db_select(
            'SELECT t.id, t.title, t.due_date FROM tasks t
             WHERE t.tenant_id = :t AND t.status = "pending"
               AND t.due_date IS NOT NULL AND t.due_date <= CURDATE()
             ORDER BY t.due_date ASC LIMIT 5',
            ['t' => $tid]
        );
        foreach ($rows as $r) {
            $_notif[] = ['type' => 'task_overdue', 'title' => $r['title'], 'detail' => 'Vencida ' . $r['due_date'], 'url' => tenant_url('tasks'), 'severity' => 'danger', 'read' => false];
            $_notif_count++;
        }
        $rfes = db_select(
            'SELECT id, case_number, title FROM cases
             WHERE tenant_id = :t AND status = "rfe" ORDER BY updated_at DESC LIMIT 3',
            ['t' => $tid]
        );
        foreach ($rfes as $r) {
            $_notif[] = ['type' => 'case_rfe', 'title' => 'RFE en ' . $r['case_number'], 'detail' => $r['title'], 'url' => tenant_url('cases/' . $r['id']), 'severity' => 'warning', 'read' => false];
            $_notif_count++;
        }
    } catch (Throwable $e) {}
}

// Iconos SVG reutilizables
$ICON = [
    'dashboard' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>',
    'clients'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>',
    'cases'     => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>',
    'documents' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>',
    'tasks'     => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>',
    'calendar'  => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2" ry="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>',
    'templates' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>',
    'workflows' => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>',
    'reports'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>',
    'finance'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>',
    'billing'   => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>',
    'audit'     => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'users'     => '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>',
];

// Items agrupados por sección
$groups = [
    'Operación' => [
        ['Dashboard',     tenant_url('dashboard'),  'dashboard',  null,           $ICON['dashboard']],
        ['Clientes',      tenant_url('clients'),    'clients',    'clients.view', $ICON['clients']],
        ['Casos',         tenant_url('cases'),      'cases',      'cases.view',   $ICON['cases']],
        ['Documentos',    tenant_url('documents'),  'documents',  'documents.view', $ICON['documents']],
    ],
    'Productividad' => [
        ['Tareas',        tenant_url('tasks'),      'tasks',      'tasks.view',     $ICON['tasks']],
        ['Calendario',    tenant_url('calendar'),   'calendar',   'tasks.view',     $ICON['calendar']],
        ['Plantillas',    tenant_url('templates'),  'templates',  'settings.manage', $ICON['templates']],
        ['Workflows',     tenant_url('workflows'),  'workflows',  'workflows.view', $ICON['workflows']],
    ],
    'Finanzas' => [
        ['Reportes',      tenant_url('reports'),    'reports',    'cases.view',     $ICON['reports']],
        ['Finanzas',      tenant_url('finance'),    'finance',    'billing.view',   $ICON['finance']],
        ['Facturación',   tenant_url('billing'),    'billing',    'billing.view',   $ICON['billing']],
    ],
    'Sistema' => [
        ['Usuarios',      tenant_url('users'),      'users',      'users.view',     $ICON['users']],
        ['Auditoría',     tenant_url('audit'),      'audit',      'users.view',     $ICON['audit']],
    ],
];
?>
<!DOCTYPE html>
<html lang="es" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Dashboard') ?> · <?= e(APP_NAME) ?></title>
    <?php require VIEWS_PATH . '/common/theme_init.php'; ?>
    <script nonce="<?= e(csp_nonce()) ?>">
        window.tailwind = window.tailwind || {};
        window.tailwind.config = {
            darkMode: 'class',
            theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] } } }
        };
    </script>
    <script src="https://cdn.tailwindcss.com" nonce="<?= e(csp_nonce()) ?>"></script>
    <link rel="stylesheet" href="<?= e(url('assets/app.css?v=' . filemtime(BASE_PATH . '/assets/app.css'))) ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
</head>
<body class="text-slate-900 antialiased bg-slate-50">

<?php $err = flash_get('error'); $ok = flash_get('success'); ?>
<?php if ($err): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 6000)"><?= e($err) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 6000)"><?= e($ok) ?></div>
<?php endif; ?>

<div class="kyros-shell flex min-h-screen" x-data="{ mobileMenu: false, openMenu: null }">
    <!-- ========== BACKDROP DE DROPDOWNS (cierra al click) ========== -->
    <div x-show="openMenu" x-cloak @click="openMenu = null" class="fixed inset-0 z-20"></div>

    <!-- ========== SIDEBAR (overlay en mobile) ========== -->
    <div x-show="mobileMenu" x-cloak @click="mobileMenu = false" class="lg:hidden fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm"></div>
    <aside class="kyros-sidebar fixed inset-y-0 left-0 z-50 bg-slate-900 text-slate-100 border-r border-slate-800 transform transition-all duration-200 ease-out flex flex-col lg:sticky lg:top-0 lg:h-screen lg:translate-x-0"
           :class="mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'">

        <!-- Brand -->
        <div class="kyros-sidebar-brand flex h-16 items-center px-4 border-b border-slate-800 flex-shrink-0">
            <a href="<?= e(tenant_url('dashboard')) ?>" class="flex items-center gap-2.5 min-w-0 flex-1">
                <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/30 flex-shrink-0">K</div>
                <div class="kyros-sidebar-label min-w-0">
                    <div class="text-sm font-semibold tracking-tight truncate"><?= e(APP_NAME) ?></div>
                    <div class="text-[10px] text-slate-400 uppercase tracking-wider">v1.0 · operacional</div>
                </div>
            </a>
        </div>

        <!-- Tenant chip -->
        <div class="kyros-sidebar-tenant px-4 py-3 border-b border-slate-800 flex-shrink-0">
            <div class="flex items-center gap-2">
                <span class="kyros-pulse-dot flex-shrink-0" title="Tenant: <?= e($t['name']) ?>"></span>
                <div class="kyros-sidebar-label flex-1 min-w-0">
                    <div class="text-[10px] uppercase text-slate-400 tracking-wider">Tenant activo</div>
                    <div class="font-medium text-sm truncate"><?= e($t['name']) ?></div>
                    <div class="text-xs text-slate-400 font-mono truncate"><?= e($t['slug']) ?> · <?= e($t['country']) ?></div>
                </div>
            </div>
        </div>

        <!-- Nav -->
        <nav class="flex-1 px-2 py-3 space-y-4 overflow-y-auto kyros-sidebar-nav">
            <?php foreach ($groups as $groupName => $items):
                // filtrar items por permisos
                $visible = array_filter($items, fn($it) => !$it[3] || can($it[3]));
                if (empty($visible)) continue;
            ?>
            <div>
                <div class="kyros-sidebar-group-title px-3 mb-1 text-[10px] font-semibold uppercase tracking-wider text-slate-500"><?= e($groupName) ?></div>
                <div class="space-y-0.5">
                    <?php foreach ($visible as [$label, $href, $key, $perm, $icon]):
                        $active = strpos($current_path, '/' . $key) !== false;
                        // Badge counter dinámico
                        $_badge = null; $_badgeColor = '';
                        if ($key === 'cases' && $_sb['cases_open'] > 0) {
                            $_badge = $_sb['cases_open'];
                            $_badgeColor = 'bg-slate-700 text-slate-200';
                        } elseif ($key === 'tasks' && $_sb['tasks_pending'] > 0) {
                            $_badge = $_sb['tasks_pending'];
                            $_badgeColor = $_sb['tasks_overdue'] > 0
                                ? 'bg-rose-500 text-white animate-pulse'
                                : 'bg-slate-700 text-slate-200';
                        }
                    ?>
                    <a href="<?= e($href) ?>" @click="mobileMenu = false"
                       class="nav-item <?= $active ? 'active' : '' ?>"
                       data-tooltip="<?= e($label) ?><?= $_badge ? ' (' . $_badge . ')' : '' ?>">
                        <span class="nav-item-icon text-slate-400"><?= $icon ?></span>
                        <span class="nav-item-label flex-1"><?= e($label) ?></span>
                        <?php if ($_badge !== null): ?>
                        <span class="nav-item-label inline-flex h-4 min-w-[18px] items-center justify-center rounded-full text-[10px] font-bold px-1 <?= $_badgeColor ?>" <?= $key === 'tasks' && $_sb['tasks_overdue'] > 0 ? 'title="' . $_sb['tasks_overdue'] . ' vencidas"' : '' ?>><?= $_badge ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </nav>

        <!-- Footer: ayuda + perfil + collapse toggle -->
        <div class="border-t border-slate-800 p-2 flex-shrink-0 space-y-1">
            <!-- Banner de ayuda: solo cuando expandido -->
            <a href="<?= e(url('training.html')) ?>" target="_blank" rel="noopener"
               class="kyros-sidebar-label block rounded-lg p-2.5 mb-1 transition group"
               style="background: linear-gradient(135deg, rgba(99,102,241,0.18), rgba(139,92,246,0.10)); border: 1px solid rgba(99,102,241,0.25);">
                <div class="flex items-center gap-2">
                    <span class="text-base">🎓</span>
                    <div class="flex-1 min-w-0">
                        <div class="text-[11px] font-bold text-indigo-200">¿Eres nuevo?</div>
                        <div class="text-[10px] text-indigo-300/80">Aprende en 25 min →</div>
                    </div>
                </div>
            </a>
            <a href="<?= e(tenant_url('profile')) ?>" class="kyros-sidebar-profile flex items-center gap-2.5 p-2 rounded-lg hover:bg-slate-800 transition" data-tooltip="<?= e($u['name']) ?>">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?>
                </div>
                <div class="kyros-sidebar-label flex-1 min-w-0">
                    <div class="text-sm font-medium truncate"><?= e($u['name']) ?></div>
                    <div class="text-xs text-slate-400 truncate"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></div>
                </div>
            </a>
            <button type="button"
                    @click="$store.sidebar.toggle()"
                    class="kyros-collapse-btn hidden lg:flex w-full items-center gap-2.5 p-2 rounded-lg hover:bg-slate-800 transition text-slate-400 hover:text-slate-200"
                    data-tooltip="Expandir menú (Ctrl+B)"
                    title="Colapsar / Expandir (Ctrl+B)">
                <svg class="kyros-collapse-icon flex-shrink-0 transition-transform duration-200" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                <span class="kyros-sidebar-label text-xs font-medium">Colapsar menú</span>
            </button>
        </div>
    </aside>

    <!-- ========== CONTENIDO ========== -->
    <div class="flex-1 flex flex-col min-w-0">
        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center px-3 sm:px-4 lg:px-6 gap-2 sm:gap-3 lg:gap-4 sticky top-0 z-30">
            <!-- Hamburguesa (mobile) -->
            <button type="button" @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition flex-shrink-0" aria-label="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>

            <!-- Logo (mobile only) -->
            <div class="lg:hidden flex items-center gap-2 flex-shrink-0">
                <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-sm font-bold">K</div>
            </div>

            <!-- Comando rápido + Buscador -->
            <button type="button" data-action="open-cmdk" class="flex-1 max-w-md flex items-center gap-2 pl-3 pr-2 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm text-slate-500 hover:bg-white hover:border-indigo-300 transition text-left min-w-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" class="flex-shrink-0"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <span class="flex-1 truncate"><span class="hidden sm:inline">Buscar o navegar...</span><span class="sm:hidden">Buscar</span></span>
                <kbd class="hidden md:inline-block px-1.5 py-0.5 text-[10px] font-mono rounded border border-slate-200 bg-white text-slate-500 flex-shrink-0">Ctrl+K</kbd>
            </button>

            <!-- Ayuda / Academia -->
            <a href="<?= e(url('training.html')) ?>" target="_blank" rel="noopener"
               class="p-2 rounded-lg hover:bg-slate-100 transition flex-shrink-0 relative" data-tt="Academia · entrenamiento guiado" data-tt-pos="bottom">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                <span class="absolute -top-0.5 -right-0.5 h-2 w-2 rounded-full bg-emerald-500 ring-2 ring-white animate-pulse"></span>
            </a>

            <!-- Toggle dark mode -->
            <button type="button" data-action="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 transition flex-shrink-0" data-tt="Cambiar tema" data-tt-pos="bottom">
                <svg class="hidden dark:block" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                <svg class="block dark:hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            </button>

            <!-- Notificaciones -->
            <div class="relative flex-shrink-0 z-30">
                <button @click.stop="openMenu = openMenu === 'notif' ? null : 'notif'"
                        class="relative p-2 rounded-lg hover:bg-slate-100 transition" :class="openMenu === 'notif' ? 'bg-slate-100' : ''" data-tt="Notificaciones" data-tt-pos="bottom">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 01-3.46 0"/></svg>
                    <?php if ($_notif_count > 0): ?>
                    <span class="absolute top-0.5 right-0.5 inline-flex h-4 min-w-[16px] items-center justify-center rounded-full text-white text-[10px] font-bold px-1 ring-2 ring-white"
                          style="background: linear-gradient(135deg, #ef4444 0%, #dc2626 100%);"><?= (int)$_notif_count ?></span>
                    <?php endif; ?>
                </button>
                <div x-show="openMenu === 'notif'" x-cloak @click.stop
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="kyros-dropdown w-[360px] right-0 mt-2 z-30">
                    <div class="kyros-dropdown-header flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <h3 class="text-sm font-semibold">Notificaciones</h3>
                            <?php if ($_notif_count > 0): ?>
                            <span class="badge-soft badge-danger"><?= (int)$_notif_count ?> nuevas</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($_notif_count > 0): ?>
                        <form method="POST" action="<?= e(tenant_url('notifications/read')) ?>">
                            <?= csrf_field() ?>
                            <button class="text-[11px] font-semibold text-indigo-600 hover:text-indigo-500">Marcar todas leidas</button>
                        </form>
                        <?php endif; ?>
                    </div>
                    <ul class="max-h-[360px] overflow-y-auto">
                        <?php if (empty($_notif)): ?>
                        <li class="px-6 py-10 text-center">
                            <div class="mx-auto h-12 w-12 rounded-full flex items-center justify-center mb-3" style="background: hsl(var(--surface-2));">
                                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="color: hsl(var(--fg-muted));"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
                            </div>
                            <p class="text-sm font-medium">Todo al día</p>
                            <p class="text-xs mt-1" style="color: hsl(var(--fg-muted));">Sin alertas pendientes</p>
                        </li>
                        <?php else:
                            foreach ($_notif as $n):
                                $isOverdue = $n['type'] === 'task_overdue';
                                $iconBg   = $isOverdue ? 'background: linear-gradient(135deg, #fee2e2, #fecaca); color: #b91c1c;' : 'background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;';
                                $iconSvg  = $isOverdue
                                    ? '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
                                    : '<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
                        ?>
                        <li class="kyros-notif-item">
                            <a href="<?= e($n['url']) ?>" class="flex items-start gap-3 p-3.5">
                                <div class="flex-shrink-0 h-9 w-9 rounded-lg flex items-center justify-center" style="<?= $iconBg ?>"><?= $iconSvg ?></div>
                                <div class="flex-1 min-w-0">
                                    <div class="text-sm font-semibold truncate"><?= e($n['title']) ?></div>
                                    <div class="text-xs mt-0.5 truncate" style="color: hsl(var(--fg-muted));"><?= e($n['detail']) ?></div>
                                </div>
                                <span class="flex-shrink-0 mt-1 h-2 w-2 rounded-full" style="background: <?= $isOverdue ? '#ef4444' : '#f59e0b' ?>"></span>
                            </a>
                        </li>
                        <?php endforeach; endif; ?>
                    </ul>
                    <?php if (!empty($_notif)): ?>
                    <div class="kyros-dropdown-footer">
                        <a href="<?= e(tenant_url('tasks?filter=open')) ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Ver todas las tareas →</a>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <?php if (can('clients.create') || can('cases.create')): ?>
            <!-- Botón crear rápido -->
            <div class="relative flex-shrink-0 z-30">
                <button @click.stop="openMenu = openMenu === 'create' ? null : 'create'"
                        class="btn btn-primary" :class="openMenu === 'create' ? 'shadow-lg shadow-indigo-500/30' : ''">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="transition" :class="openMenu === 'create' ? 'rotate-45' : ''"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                    <span class="hidden sm:inline">Crear</span>
                </button>
                <div x-show="openMenu === 'create'" x-cloak @click.stop
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="kyros-dropdown w-72 right-0 mt-2 z-30">
                    <div class="kyros-dropdown-header">
                        <h3 class="text-sm font-semibold">Crear nuevo</h3>
                        <p class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">Acción rápida</p>
                    </div>
                    <div class="p-2 space-y-0.5">
                        <?php if (can('clients.create')): ?>
                        <a href="<?= e(tenant_url('clients/new')) ?>" class="kyros-action-item group">
                            <span class="kyros-action-icon" style="background: linear-gradient(135deg, #ddd6fe, #c4b5fd); color: #6d28d9;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><line x1="20" y1="8" x2="20" y2="14"/><line x1="23" y1="11" x2="17" y2="11"/></svg>
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-semibold">Nuevo cliente</span>
                                <span class="block text-xs" style="color: hsl(var(--fg-muted));">Agregar persona al CRM</span>
                            </span>
                            <span class="kyros-action-arrow">→</span>
                        </a>
                        <?php endif; ?>
                        <?php if (can('cases.create')): ?>
                        <a href="<?= e(tenant_url('cases/new')) ?>" class="kyros-action-item group">
                            <span class="kyros-action-icon" style="background: linear-gradient(135deg, #c7d2fe, #a5b4fc); color: #4338ca;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-semibold">Nuevo caso</span>
                                <span class="block text-xs" style="color: hsl(var(--fg-muted));">I-130, I-485, asilo, residencia...</span>
                            </span>
                            <span class="kyros-action-arrow">→</span>
                        </a>
                        <?php endif; ?>
                        <?php if (can('tasks.create')): ?>
                        <a href="<?= e(tenant_url('tasks')) ?>" class="kyros-action-item group">
                            <span class="kyros-action-icon" style="background: linear-gradient(135deg, #bbf7d0, #86efac); color: #15803d;">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                            </span>
                            <span class="flex-1 min-w-0">
                                <span class="block text-sm font-semibold">Nueva tarea</span>
                                <span class="block text-xs" style="color: hsl(var(--fg-muted));">Pendiente con plazo</span>
                            </span>
                            <span class="kyros-action-arrow">→</span>
                        </a>
                        <?php endif; ?>
                    </div>
                    <div class="kyros-dropdown-footer">
                        <span class="text-xs flex items-center gap-1" style="color: hsl(var(--fg-muted));">
                            <kbd class="px-1.5 py-0.5 text-[10px] font-mono rounded border" style="border-color: hsl(var(--border)); background: hsl(var(--surface-2));">Ctrl+K</kbd>
                            <span>para más opciones</span>
                        </span>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Profile dropdown -->
            <div class="relative flex-shrink-0 z-30">
                <button @click.stop="openMenu = openMenu === 'profile' ? null : 'profile'"
                        class="flex items-center gap-2 p-1 pl-1 pr-2 rounded-lg hover:bg-slate-100 transition"
                        :class="openMenu === 'profile' ? 'bg-slate-100' : ''" title="<?= e($u['name']) ?>">
                    <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-sm font-semibold">
                        <?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?>
                    </div>
                    <svg class="hidden sm:block text-slate-500" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </button>
                <div x-show="openMenu === 'profile'" x-cloak @click.stop
                     style="display: none;"
                     x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-1" x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-100" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
                     class="kyros-dropdown w-64 right-0 mt-2 z-30">
                    <div class="kyros-dropdown-header">
                        <div class="flex items-center gap-3">
                            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0">
                                <?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="text-sm font-semibold truncate"><?= e($u['name']) ?></div>
                                <div class="text-xs truncate" style="color: hsl(var(--fg-muted));"><?= e($u['email'] ?? '') ?></div>
                            </div>
                        </div>
                    </div>
                    <div class="p-1.5 space-y-0.5">
                        <a href="<?= e(tenant_url('profile')) ?>" class="kyros-menu-item flex items-center gap-2.5 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: hsl(var(--fg-muted));"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                            <span>Mi perfil</span>
                        </a>
                        <a href="<?= e(tenant_url('profile/twofa')) ?>" class="kyros-menu-item flex items-center gap-2.5 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: hsl(var(--fg-muted));"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                            <span>Seguridad (2FA)</span>
                        </a>
                        <a href="<?= e(url('training.html')) ?>" target="_blank" rel="noopener" class="kyros-menu-item flex items-center gap-2.5 rounded-lg">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: hsl(var(--fg-muted));"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg>
                            <span>Academia <span class="ml-auto text-[10px] bg-emerald-50 text-emerald-700 px-1.5 py-0.5 rounded font-bold">Nuevo</span></span>
                        </a>
                        <button type="button" data-action="theme-toggle" class="kyros-menu-item w-full text-left flex items-center gap-2.5 rounded-lg">
                            <svg class="dark:hidden" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: hsl(var(--fg-muted));"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                            <svg class="hidden dark:block" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" style="color: hsl(var(--fg-muted));"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
                            <span class="dark:hidden">Tema oscuro</span>
                            <span class="hidden dark:inline">Tema claro</span>
                        </button>
                    </div>
                    <div class="border-t border-slate-200 dark:border-slate-700 p-1.5">
                        <form method="POST" action="<?= e(url('logout')) ?>">
                            <?= csrf_field() ?>
                            <button class="kyros-menu-item w-full text-left flex items-center gap-2.5 rounded-lg text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                                <span>Cerrar sesión</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <!-- ========== BANNERS DE ANUNCIOS GLOBALES ========== -->
        <?php foreach ($_announcements as $_a):
            $_styles = [
                'info'    => ['bg-blue-50','border-blue-200','text-blue-800','text-blue-600'],
                'success' => ['bg-emerald-50','border-emerald-200','text-emerald-800','text-emerald-600'],
                'warning' => ['bg-amber-50','border-amber-200','text-amber-800','text-amber-600'],
                'danger'  => ['bg-rose-50','border-rose-200','text-rose-800','text-rose-600'],
            ];
            $_s = $_styles[$_a['severity']] ?? $_styles['info'];
        ?>
        <div class="<?= $_s[0] ?> border-b <?= $_s[1] ?> px-4 py-2.5"
             x-data="{ show: !localStorage.getItem('kn-ann-<?= (int)$_a['id'] ?>') }" x-show="show" x-cloak>
            <div class="mx-auto max-w-7xl flex items-start gap-3">
                <span class="<?= $_s[3] ?> mt-0.5 flex-shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 11-5.8-1.6"/></svg>
                </span>
                <div class="flex-1 text-sm <?= $_s[2] ?>">
                    <strong class="font-semibold"><?= e($_a['title']) ?></strong>
                    <span class="ml-1 opacity-90"><?= e($_a['body']) ?></span>
                </div>
                <button @click="localStorage.setItem('kn-ann-<?= (int)$_a['id'] ?>', '1'); show = false" class="<?= $_s[3] ?> hover:opacity-70 flex-shrink-0" title="Cerrar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
        <?php endforeach; ?>

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8 fade-in">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
</div>

<?php require VIEWS_PATH . '/common/command_palette.php'; ?>

<!-- ============ QUICK TASK MODAL (Ctrl+Shift+T) ============ -->
<?php if (can('tasks.create')): ?>
<div x-data="{
        open: false, title: '', priority: 'normal', dueDate: '',
        focus() { this.$nextTick(() => this.$refs.title?.focus()); }
     }"
     @keydown.window="(($event.ctrlKey || $event.metaKey) && $event.shiftKey && ($event.key === 'T' || $event.key === 't')) && (open = true, focus(), $event.preventDefault())"
     @keydown.escape.window="open = false">
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex items-start justify-center pt-[15vh] p-4">
        <form @click.stop method="POST" action="<?= e(tenant_url('tasks')) ?>"
              x-show="open"
              x-transition:enter="transition ease-out duration-200"
              x-transition:enter-start="opacity-0 -translate-y-4 scale-95"
              x-transition:enter-end="opacity-100 translate-y-0 scale-100"
              class="w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
            <?= csrf_field() ?>
            <div class="px-5 py-3 border-b border-slate-200 flex items-center gap-2">
                <span class="text-emerald-500">✓</span>
                <h3 class="font-bold text-sm">Nueva tarea rápida</h3>
                <span class="ml-auto text-[10px] uppercase tracking-wider text-slate-400 font-mono">Ctrl ⇧ T</span>
            </div>
            <div class="p-4 space-y-3">
                <input x-ref="title" name="title" required x-model="title" placeholder="¿Qué necesitas hacer?"
                       class="w-full px-3 py-2.5 text-base rounded-lg border border-slate-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/30 focus:outline-none">
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Prioridad</label>
                        <select name="priority" x-model="priority" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                            <option value="urgent">Urgente</option>
                            <option value="high">Alta</option>
                            <option value="normal" selected>Normal</option>
                            <option value="low">Baja</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Fecha</label>
                        <input name="due_date" type="date" x-model="dueDate" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    </div>
                </div>
            </div>
            <div class="px-4 py-3 bg-slate-50 border-t border-slate-200 flex items-center gap-2">
                <span class="text-[10px] text-slate-500 flex-1">Para más opciones, ve a <a href="<?= e(tenant_url('tasks')) ?>" class="text-indigo-600 font-semibold hover:underline">/tasks</a></span>
                <button type="button" @click="open = false" class="text-xs font-medium text-slate-500 hover:text-slate-900 px-3 py-1.5 rounded-lg hover:bg-slate-200 transition">Cancelar</button>
                <button type="submit" :disabled="!title" class="rounded-lg bg-slate-900 hover:bg-slate-800 px-4 py-1.5 text-xs font-bold text-white shadow-md disabled:opacity-50">Crear ⏎</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<!-- ============ MODAL GLOBAL DE ATAJOS (Shift+?) ============ -->
<div x-data="{ open: false }"
     @keydown.window="(($event.shiftKey && $event.key === '?') || ($event.key === '?' && !$event.ctrlKey && !$event.metaKey && !['INPUT','TEXTAREA'].includes(document.activeElement?.tagName))) && (open = !open, $event.preventDefault())"
     @keydown.escape.window="open = false">
    <div x-show="open" x-cloak @click="open = false" class="fixed inset-0 z-[60] bg-slate-900/60 backdrop-blur-sm flex items-center justify-center p-4">
        <div @click.stop x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             class="w-full max-w-lg rounded-2xl bg-white shadow-2xl overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-200 flex items-center justify-between">
                <div>
                    <h3 class="font-bold text-lg">Atajos de teclado</h3>
                    <p class="text-xs text-slate-500">Acelera tu navegación</p>
                </div>
                <button @click="open = false" class="p-1.5 rounded hover:bg-slate-100 text-slate-400">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
            <div class="divide-y divide-slate-100 max-h-[60vh] overflow-y-auto">
                <?php
                $shortcuts = [
                    ['Navegación', [
                        ['Ctrl K',     'Comando rápido (búsqueda global)'],
                        ['Ctrl B',     'Colapsar/expandir sidebar'],
                        ['Shift ?',    'Mostrar este modal'],
                        ['Esc',        'Cerrar modal/dropdown'],
                    ]],
                    ['Acciones rápidas', [
                        ['Ctrl Shift T', 'Nueva tarea rápida (modal)'],
                        ['Ctrl K → "nuevo cliente"', 'Crear cliente'],
                        ['Ctrl K → "nuevo caso"',    'Crear caso'],
                    ]],
                    ['Tablas y formularios', [
                        ['Tab',         'Siguiente campo'],
                        ['Shift Tab',   'Campo anterior'],
                        ['Enter',       'Confirmar / abrir selección'],
                    ]],
                    ['Verificación 2FA', [
                        ['← →',         'Navegar entre cajas OTP'],
                        ['Backspace',   'Caja anterior'],
                        ['Ctrl V',      'Pegar código completo'],
                    ]],
                ];
                foreach ($shortcuts as [$group, $items]): ?>
                <div class="px-5 py-3">
                    <div class="text-[10px] uppercase tracking-widest font-bold text-slate-500 mb-2"><?= e($group) ?></div>
                    <ul class="space-y-1.5">
                        <?php foreach ($items as [$keys, $desc]): ?>
                        <li class="flex items-center justify-between text-sm gap-3">
                            <span class="text-slate-700 flex-1"><?= e($desc) ?></span>
                            <span class="flex gap-1 flex-shrink-0">
                                <?php foreach (explode(' ', $keys) as $k): ?>
                                <kbd class="inline-block px-1.5 py-0.5 text-[11px] font-mono rounded border border-slate-300 bg-slate-100 shadow-[0_1px_0_#cbd5e1] font-semibold"><?= e($k) ?></kbd>
                                <?php endforeach; ?>
                            </span>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </div>
                <?php endforeach; ?>
            </div>
            <div class="px-5 py-3 border-t border-slate-200 bg-slate-50 text-xs text-slate-500 flex items-center justify-between">
                <span>¿Quieres aprender más?</span>
                <a href="<?= e(url('training.html')) ?>" target="_blank" class="font-semibold text-indigo-600 hover:text-indigo-500">Abrir Academia →</a>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
// Atajo de teclado: Ctrl+B / Cmd+B para toggle del sidebar
document.addEventListener('keydown', function(e) {
    if ((e.ctrlKey || e.metaKey) && (e.key === 'b' || e.key === 'B')) {
        e.preventDefault();
        if (window.Alpine && Alpine.store('sidebar')) Alpine.store('sidebar').toggle();
    }
});
</script>
</body>
</html>
