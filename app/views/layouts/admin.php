<?php
$u = current_user();
$current_path = $_SERVER['REQUEST_URI'] ?? '';
$impersonating = is_impersonating();
?>
<!DOCTYPE html>
<html lang="es" class="bg-slate-50">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($title ?? 'Super Admin') ?> · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap">
    <?php require VIEWS_PATH . '/common/theme_init.php'; ?>
    <script nonce="<?= e(csp_nonce()) ?>">
      window.tailwind = window.tailwind || {};
      window.tailwind.config = { darkMode: 'class', theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] } } } };
    </script>
    <script src="https://cdn.tailwindcss.com" nonce="<?= e(csp_nonce()) ?>"></script>
    <link rel="stylesheet" href="<?= e(url('assets/app.css?v=' . filemtime(BASE_PATH . '/assets/app.css'))) ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/chart.js@4.4.3/dist/chart.umd.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
</head>
<body class="text-slate-900 antialiased bg-slate-50 font-sans">

<?php $err = flash_get('error'); $ok = flash_get('success'); ?>
<?php if ($err): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 8000)"><?= e($err) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 8000)"><?= e($ok) ?></div>
<?php endif; ?>

<div class="flex min-h-screen" x-data="{ mobileMenu: false, openMenu: null }">
    <!-- Backdrops -->
    <div x-show="openMenu" x-cloak @click="openMenu = null" class="fixed inset-0 z-20"></div>
    <div x-show="mobileMenu" x-cloak @click="mobileMenu = false" class="lg:hidden fixed inset-0 z-40 bg-slate-900/60 backdrop-blur-sm"></div>

    <!-- ============== SIDEBAR ============== -->
    <aside class="fixed inset-y-0 left-0 z-50 w-64 border-r transform transition-transform flex flex-col lg:sticky lg:top-0 lg:h-screen lg:translate-x-0 lg:flex lg:flex-col"
           :class="mobileMenu ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
           style="background: linear-gradient(180deg, #0c0a09 0%, #1c1917 60%, #292524 100%); border-color: rgba(120,53,15,0.4);">

        <div class="flex h-16 items-center px-5 border-b border-amber-950/40 flex-shrink-0">
            <a href="<?= e(url('admin')) ?>" class="flex items-center gap-2.5">
                <div class="relative flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white font-bold shadow-lg shadow-amber-500/30">
                    K
                    <span class="absolute -top-1 -right-1 inline-flex h-3 w-3 rounded-full bg-amber-400 ring-2 ring-stone-950"></span>
                </div>
                <div>
                    <div class="text-sm font-bold text-amber-50 tracking-tight">Super Admin</div>
                    <div class="text-[10px] text-amber-200/60 uppercase tracking-wider font-mono">control global · v2</div>
                </div>
            </a>
        </div>

        <nav class="flex-1 px-3 py-4 space-y-5 overflow-y-auto">
            <?php
            $groups = [
                'General' => [
                    ['Dashboard',       url('admin'),         '#^.*?/admin$#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="9"/><rect x="14" y="3" width="7" height="5"/><rect x="14" y="12" width="7" height="9"/><rect x="3" y="16" width="7" height="5"/></svg>'],
                    ['Tenants',         url('admin/tenants'), '#/admin/tenants#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>'],
                    ['Usuarios',        url('admin/users'),   '#/admin/users#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>'],
                ],
                'Monetización' => [
                    ['Planes',          url('admin/plans'),       '#/admin/plans#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M2 17l10 5 10-5"/><path d="M2 12l10 5 10-5"/><path d="M12 2L2 7l10 5 10-5-10-5z"/></svg>'],
                    ['Facturación',     url('admin/billing'),     '#/admin/billing#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="5" width="20" height="14" rx="2"/><line x1="2" y1="10" x2="22" y2="10"/></svg>'],
                    ['Licencias',       url('admin/licenses'),    '#/admin/licenses#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>'],
                ],
                'Operación' => [
                    ['Anuncios',        url('admin/announcements'), '#/admin/announcements#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 11l18-5v12L3 14v-3z"/><path d="M11.6 16.8a3 3 0 11-5.8-1.6"/></svg>'],
                    ['Auditoría',       url('admin/audit'),         '#/admin/audit#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>'],
                    ['Seguridad',       url('admin/security'),      '#/admin/security#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>'],
                ],
                'Sistema' => [
                    ['Configuración',   url('admin/system'),        '#/admin/system#',
                        '<svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M19.4 15a1.65 1.65 0 00.33 1.82l.06.06a2 2 0 11-2.83 2.83l-.06-.06a1.65 1.65 0 00-1.82-.33 1.65 1.65 0 00-1 1.51V21a2 2 0 11-4 0v-.09A1.65 1.65 0 009 19.4a1.65 1.65 0 00-1.82.33l-.06.06a2 2 0 11-2.83-2.83l.06-.06a1.65 1.65 0 00.33-1.82 1.65 1.65 0 00-1.51-1H3a2 2 0 110-4h.09A1.65 1.65 0 004.6 9a1.65 1.65 0 00-.33-1.82l-.06-.06a2 2 0 112.83-2.83l.06.06a1.65 1.65 0 001.82.33H9a1.65 1.65 0 001-1.51V3a2 2 0 114 0v.09a1.65 1.65 0 001 1.51 1.65 1.65 0 001.82-.33l.06-.06a2 2 0 112.83 2.83l-.06.06a1.65 1.65 0 00-.33 1.82V9a1.65 1.65 0 001.51 1H21a2 2 0 110 4h-.09a1.65 1.65 0 00-1.51 1z"/></svg>'],
                ],
            ];
            foreach ($groups as $groupName => $items): ?>
            <div>
                <div class="px-3 mb-1 text-[10px] font-bold uppercase tracking-widest text-amber-300/40"><?= e($groupName) ?></div>
                <div class="space-y-0.5">
                    <?php foreach ($items as [$label, $href, $match, $icon]):
                        $active = (bool)preg_match($match, $current_path);
                    ?>
                    <a href="<?= e($href) ?>" @click="mobileMenu = false"
                       class="relative flex items-center gap-3 rounded-lg px-3 py-2 text-sm font-medium transition <?= $active
                           ? 'bg-amber-500/15 text-amber-100 ring-1 ring-amber-500/30'
                           : 'text-amber-100/70 hover:bg-amber-900/30 hover:text-amber-50' ?>">
                        <?php if ($active): ?><span class="absolute -left-3 top-1/4 h-1/2 w-[3px] rounded-r bg-gradient-to-b from-amber-400 to-orange-500"></span><?php endif; ?>
                        <span class="<?= $active ? 'text-amber-300' : 'text-amber-300/50' ?>"><?= $icon ?></span>
                        <span><?= e($label) ?></span>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endforeach; ?>
        </nav>

        <!-- Footer: estado del sistema + perfil -->
        <div class="border-t border-amber-950/40 p-3 space-y-2 flex-shrink-0">
            <?php $maintActive = maintenance_mode_active(); ?>
            <div class="rounded-lg px-3 py-2 text-[11px] flex items-center justify-between <?= $maintActive ? 'bg-rose-500/15 text-rose-200' : 'bg-emerald-500/15 text-emerald-200' ?>">
                <span class="flex items-center gap-1.5">
                    <span class="h-1.5 w-1.5 rounded-full <?= $maintActive ? 'bg-rose-400' : 'bg-emerald-400 animate-pulse' ?>"></span>
                    <?= $maintActive ? 'Mantenimiento ON' : 'Sistema OK' ?>
                </span>
                <a href="<?= e(url('admin/system')) ?>" class="hover:underline">→</a>
            </div>

            <a href="#" class="flex items-center gap-2.5 p-2 rounded-lg hover:bg-amber-900/30 transition">
                <div class="h-9 w-9 rounded-full bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-sm font-semibold flex-shrink-0">
                    <?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium text-amber-50 truncate"><?= e($u['name']) ?></div>
                    <div class="text-[10px] text-amber-200/60 truncate font-mono"><?= e($u['email']) ?></div>
                </div>
            </a>
            <form method="POST" action="<?= e(url('logout')) ?>">
                <?= csrf_field() ?>
                <button class="w-full rounded-lg bg-amber-900/40 hover:bg-amber-900/60 text-amber-100 text-xs py-2 transition flex items-center justify-center gap-1.5">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    Cerrar sesión
                </button>
            </form>
        </div>
    </aside>

    <!-- ============== CONTENIDO ============== -->
    <div class="flex-1 flex flex-col min-w-0">

        <!-- Banner de impersonation (si aplica) -->
        <?php if ($impersonating): ?>
        <div class="bg-rose-600 text-white px-4 py-2 flex items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v3m0 4h.01M5.07 19h13.86a2 2 0 001.74-2.99l-6.94-12a2 2 0 00-3.46 0l-6.94 12A2 2 0 005.06 19z"/></svg>
                <strong>Modo Impersonation activo</strong>
                <span class="opacity-80 hidden md:inline">— estás operando como otro usuario</span>
            </div>
            <form method="POST" action="<?= e(url('admin/impersonate/stop')) ?>">
                <?= csrf_field() ?>
                <button class="rounded-lg bg-white/20 hover:bg-white/30 text-white text-xs font-bold px-3 py-1.5 transition">Salir →</button>
            </form>
        </div>
        <?php endif; ?>

        <!-- Topbar -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center px-3 sm:px-4 lg:px-6 gap-2 sm:gap-3 lg:gap-4 sticky top-0 z-30">
            <button type="button" @click="mobileMenu = !mobileMenu" class="lg:hidden p-2 rounded-lg hover:bg-slate-100 transition flex-shrink-0" aria-label="Menu">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
            </button>
            <div class="flex-1 min-w-0">
                <h2 class="text-sm font-semibold text-slate-700 truncate"><?= e($title ?? 'Panel Super Admin') ?></h2>
                <p class="text-xs text-slate-500 hidden sm:block">Vista global del SaaS · <?= date('Y-m-d H:i') ?></p>
            </div>

            <!-- Quick search por tenant -->
            <form method="GET" action="<?= e(url('admin/tenants')) ?>" class="hidden md:flex items-center gap-2 max-w-xs flex-1">
                <div class="relative w-full">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input name="q" placeholder="Buscar tenant..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500 transition">
                </div>
            </form>

            <!-- Theme toggle -->
            <button type="button" data-action="theme-toggle" class="p-2 rounded-lg hover:bg-slate-100 transition flex-shrink-0" title="Tema">
                <svg class="hidden dark:block" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/></svg>
                <svg class="block dark:hidden" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
            </button>

            <!-- Crear tenant rápido -->
            <a href="<?= e(url('admin/tenants/new')) ?>"
               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-2 text-sm font-semibold text-white shadow-md hover:shadow-lg transition flex-shrink-0"
               style="background: linear-gradient(135deg, #d97706 0%, #ea580c 100%);">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                <span class="hidden sm:inline">Nuevo tenant</span>
            </a>
        </header>

        <main class="flex-1">
            <div class="mx-auto max-w-7xl px-3 py-4 sm:px-4 sm:py-6 lg:px-8 lg:py-8 fade-in">
                <?= $content ?? '' ?>
            </div>
        </main>
    </div>
</div>
</body>
</html>
