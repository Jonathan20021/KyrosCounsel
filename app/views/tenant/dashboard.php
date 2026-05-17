<?php
$u = current_user();
$t = current_tenant();

// Onboarding checklist — solo se muestra si quedan tareas por hacer
$_onb = [
    'has_clients'   => (int)(db_one('SELECT COUNT(*) c FROM clients   WHERE tenant_id = :t', ['t'=>$t['id']])['c'] ?? 0) > 0,
    'has_cases'     => (int)(db_one('SELECT COUNT(*) c FROM cases     WHERE tenant_id = :t', ['t'=>$t['id']])['c'] ?? 0) > 0,
    'has_documents' => (int)(db_one('SELECT COUNT(*) c FROM documents WHERE tenant_id = :t', ['t'=>$t['id']])['c'] ?? 0) > 0,
    'has_templates' => (int)(db_one('SELECT COUNT(*) c FROM email_templates WHERE tenant_id = :t', ['t'=>$t['id']])['c'] ?? 0) > 0,
    'has_workflow'  => (int)(db_one('SELECT COUNT(*) c FROM workflows WHERE tenant_id = :t AND is_active = 1', ['t'=>$t['id']])['c'] ?? 0) > 0,
    'has_2fa'       => !empty($u['two_factor_enabled']),
    'team_size'     => (int)(db_one('SELECT COUNT(*) c FROM users WHERE tenant_id = :t', ['t'=>$t['id']])['c'] ?? 0),
];
$_onb_done = array_sum([$_onb['has_clients'], $_onb['has_cases'], $_onb['has_documents'], $_onb['has_templates'], $_onb['has_workflow'], $_onb['has_2fa'], ($_onb['team_size'] >= 2)]);
$_onb_total = 7;
$_onb_pct = $_onb_total > 0 ? round($_onb_done / $_onb_total * 100) : 0;

// ===== PULSO DEL BUFETE =====
// Score 0-100 basado en señales operativas (mejor cuanto más alto)
$_pulse_signals = [];
$_pulse_total_max = 0;

// 1. Setup completado (peso 20)
$_pulse_signals['setup'] = ['score' => round($_onb_done / $_onb_total * 20), 'max' => 20, 'label' => 'Setup', 'detail' => "$_onb_done/$_onb_total tareas"];
$_pulse_total_max += 20;

// 2. Tareas vencidas (peso 25, penaliza)
$_overdue = (int)($stats['tasks_overdue'] ?? 0);
$_pulse_signals['overdue'] = [
    'score' => $_overdue === 0 ? 25 : max(0, 25 - $_overdue * 3),
    'max'   => 25,
    'label' => 'Plazos al día',
    'detail'=> $_overdue === 0 ? 'Sin tareas vencidas' : "$_overdue vencidas",
];
$_pulse_total_max += 25;

// 3. Cobros pendientes (peso 20)
$_pending_pct = 0;
if (isset($finance) && (float)$finance['paid'] + (float)$finance['pending'] > 0) {
    $_pending_pct = (float)$finance['pending'] / ((float)$finance['paid'] + (float)$finance['pending']);
}
$_pulse_signals['finance'] = [
    'score' => round((1 - min(1, $_pending_pct)) * 20),
    'max'   => 20,
    'label' => 'Cobros',
    'detail'=> $_pending_pct > 0 ? round((1 - $_pending_pct) * 100) . '% cobrado' : 'Sin pagos',
];
$_pulse_total_max += 20;

// 4. 2FA del usuario (peso 15)
$_pulse_signals['security'] = [
    'score' => $_onb['has_2fa'] ? 15 : 0,
    'max'   => 15,
    'label' => 'Seguridad',
    'detail'=> $_onb['has_2fa'] ? '2FA activo' : 'Sin 2FA',
];
$_pulse_total_max += 15;

// 5. Workflows activos (peso 10)
$_pulse_signals['automation'] = [
    'score' => $_onb['has_workflow'] ? 10 : 0,
    'max'   => 10,
    'label' => 'Automatización',
    'detail'=> $_onb['has_workflow'] ? 'Workflow activo' : 'Sin workflows',
];
$_pulse_total_max += 10;

// 6. Casos cerrados con éxito (peso 10)
$_success_score = round(((int)($stats['success_rate'] ?? 0) / 100) * 10);
$_pulse_signals['success'] = [
    'score' => $_success_score,
    'max'   => 10,
    'label' => 'Tasa éxito',
    'detail'=> ($stats['success_rate'] ?? 0) . '% aprobados',
];
$_pulse_total_max += 10;

$_pulse_score = array_sum(array_column($_pulse_signals, 'score'));
$_pulse_pct = $_pulse_total_max > 0 ? round($_pulse_score / $_pulse_total_max * 100) : 0;
$_pulse_color = $_pulse_pct >= 75 ? 'emerald' : ($_pulse_pct >= 50 ? 'amber' : 'rose');
$_pulse_label = $_pulse_pct >= 75 ? 'Excelente' : ($_pulse_pct >= 50 ? 'En camino' : 'Necesita atención');
?>

<!-- ============ HERO HEADER ============ -->
<section class="kyros-hero p-6 lg:p-8 mb-6">
    <div class="relative flex flex-wrap items-end justify-between gap-4">
        <div>
            <p class="kyros-eyebrow">Resumen general</p>
            <h1 class="kyros-display mt-2">
                Hola, <?= e(explode(' ', $u['name'])[0]) ?>
                <span class="inline-block animate-wave origin-bottom-right">👋</span>
            </h1>
            <div class="mt-3 flex items-center gap-2 flex-wrap text-sm" style="color: hsl(var(--fg-muted));">
                <span class="font-medium" style="color: hsl(var(--fg));"><?= e($t['name']) ?></span>
                <span>·</span>
                <span><?= e(country_name($t['country'])) ?></span>
                <span>·</span>
                <span class="font-mono"><?= e(date('l, d M Y')) ?></span>
            </div>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <span class="badge-soft badge-success"><span class="kyros-pulse-dot"></span>Sistema operativo</span>
            <?php if (can('cases.create')): ?>
            <a href="<?= e(tenant_url('cases/new')) ?>" class="btn btn-primary">+ Nuevo caso</a>
            <?php endif; ?>
        </div>
    </div>
</section>

<!-- ============ ONBOARDING CHECKLIST (solo si quedan items pendientes) ============ -->
<?php if ($_onb_done < $_onb_total): ?>
<div class="mb-6" x-data="{ open: <?= $_onb_done < 3 ? 'true' : 'false' ?>, dismissed: localStorage.getItem('kn-onb-dismissed') === '1' }" x-show="!dismissed" x-cloak>
    <div class="kyros-card overflow-hidden">
        <button @click="open = !open" class="w-full p-4 flex items-center gap-3 hover:bg-slate-50 transition text-left">
            <div class="flex h-10 w-10 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white items-center justify-center text-lg shadow-md flex-shrink-0">🚀</div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <h3 class="font-semibold">Configura tu bufete</h3>
                    <span class="text-[10px] font-bold uppercase tracking-wider text-slate-500">
                        <?= $_onb_done ?>/<?= $_onb_total ?> · <?= $_onb_pct ?>%
                    </span>
                </div>
                <div class="mt-1 h-1.5 bg-slate-200 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-indigo-500 to-violet-500 transition-all" style="width: <?= $_onb_pct ?>%"></div>
                </div>
            </div>
            <button @click.stop="localStorage.setItem('kn-onb-dismissed','1'); dismissed=true" class="p-1.5 rounded text-slate-400 hover:text-slate-700 hover:bg-slate-100" title="Ocultar">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
            <svg :class="open ? 'rotate-180' : ''" class="text-slate-400 transition flex-shrink-0" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div x-show="open" x-cloak class="border-t border-slate-200 p-3 space-y-1.5">
            <?php
            $items = [
                ['has_clients',   'Crea tu primer cliente',     'Registra a tu primera persona representada.', tenant_url('clients/new'),  '👤'],
                ['has_cases',     'Crea tu primer caso',         'I-130, I-485, asilo... cualquier tipo.',      tenant_url('cases/new'),    '📁'],
                ['has_documents', 'Sube tu primer documento',    'Pasaporte, I-94, acta...',                    tenant_url('documents'),    '📎'],
                ['has_templates', 'Carga las plantillas de email','Click "Sembrar" para tener las pre-armadas.', tenant_url('templates'),    '✉️'],
                ['has_workflow',  'Activa un workflow',          'Automatiza tareas y emails.',                 tenant_url('workflows'),    '🔄'],
                ['team_size',     'Invita un miembro al equipo', 'Trabaja en colaboración.',                    tenant_url('users/new'),    '👥', 2],
                ['has_2fa',       'Activa 2FA en tu cuenta',     'El paso de seguridad más importante.',        tenant_url('profile/twofa'), '🛡️'],
            ];
            foreach ($items as $it):
                $key = $it[0];
                $minVal = $it[5] ?? 1;
                $done = is_int($_onb[$key]) ? ($_onb[$key] >= $minVal) : (bool)$_onb[$key];
            ?>
            <a href="<?= e($it[3]) ?>" class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-slate-50 transition group">
                <div class="h-7 w-7 rounded-full flex items-center justify-center flex-shrink-0 transition <?= $done ? 'bg-emerald-500 text-white' : 'bg-slate-100 text-slate-400 group-hover:bg-indigo-100 group-hover:text-indigo-600' ?>">
                    <?php if ($done): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                    <?php else: ?>
                    <span class="text-xs"><?= $it[4] ?></span>
                    <?php endif; ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium <?= $done ? 'text-slate-400 line-through' : '' ?>"><?= e($it[1]) ?></div>
                    <div class="text-xs text-slate-500"><?= e($it[2]) ?></div>
                </div>
                <?php if (!$done): ?>
                <span class="text-xs font-semibold text-indigo-600 group-hover:text-indigo-500 flex-shrink-0">→</span>
                <?php endif; ?>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- ============ PULSO DEL BUFETE ============ -->
<div class="mb-6">
    <div class="kyros-card p-5">
        <div class="flex items-start gap-5 flex-wrap">
            <!-- Score visual: ring SVG -->
            <div class="relative flex-shrink-0" style="width: 96px; height: 96px;">
                <svg viewBox="0 0 100 100" class="w-full h-full -rotate-90">
                    <circle cx="50" cy="50" r="42" fill="none" stroke="hsl(var(--border))" stroke-width="8"/>
                    <circle cx="50" cy="50" r="42" fill="none"
                        stroke="<?= $_pulse_color === 'emerald' ? '#10b981' : ($_pulse_color === 'amber' ? '#f59e0b' : '#ef4444') ?>"
                        stroke-width="8"
                        stroke-linecap="round"
                        stroke-dasharray="263.89"
                        stroke-dashoffset="<?= 263.89 - (263.89 * $_pulse_pct / 100) ?>"
                        style="transition: stroke-dashoffset 1s ease-out;"
                    />
                </svg>
                <div class="absolute inset-0 flex flex-col items-center justify-center">
                    <div class="text-2xl font-bold leading-none" style="color: <?= $_pulse_color === 'emerald' ? '#10b981' : ($_pulse_color === 'amber' ? '#f59e0b' : '#ef4444') ?>"><?= $_pulse_pct ?></div>
                    <div class="text-[8px] uppercase tracking-wider font-bold" style="color: hsl(var(--fg-muted));">/ 100</div>
                </div>
            </div>

            <div class="flex-1 min-w-[260px]">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-bold text-lg">Pulso del bufete</h3>
                    <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[11px] font-bold uppercase tracking-wider"
                          style="background: <?= $_pulse_color === 'emerald' ? 'rgba(16,185,129,0.12)' : ($_pulse_color === 'amber' ? 'rgba(245,158,11,0.12)' : 'rgba(239,68,68,0.12)') ?>;
                                 color: <?= $_pulse_color === 'emerald' ? '#047857' : ($_pulse_color === 'amber' ? '#b45309' : '#b91c1c') ?>;">
                        <span class="h-1.5 w-1.5 rounded-full" style="background: currentColor;"></span>
                        <?= e($_pulse_label) ?>
                    </span>
                </div>
                <p class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">Score combinado de operación, plazos, cobros, seguridad y automatización.</p>

                <!-- Mini-bars con cada señal -->
                <div class="mt-3 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    <?php foreach ($_pulse_signals as $key => $sig):
                        $pct = $sig['max'] > 0 ? round($sig['score'] / $sig['max'] * 100) : 0;
                        $color = $pct >= 80 ? '#10b981' : ($pct >= 50 ? '#f59e0b' : '#ef4444');
                    ?>
                    <div class="rounded-lg p-2.5" style="background: hsl(var(--surface-2));">
                        <div class="flex items-center justify-between text-[11px] mb-1">
                            <span class="font-semibold"><?= e($sig['label']) ?></span>
                            <span class="font-mono" style="color: <?= $color ?>;"><?= $sig['score'] ?>/<?= $sig['max'] ?></span>
                        </div>
                        <div class="h-1 rounded-full overflow-hidden" style="background: hsl(var(--border));">
                            <div class="h-full rounded-full transition-all" style="width: <?= $pct ?>%; background: <?= $color ?>;"></div>
                        </div>
                        <div class="text-[10px] mt-1 truncate" style="color: hsl(var(--fg-muted));"><?= e($sig['detail']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============ ACADEMIA WIDGET (solo aparece si el user no la ha terminado) ============ -->
<div class="mb-6 kyros-academy-widget" x-data="{
        progress: 0, completed: 0, total: 16, remaining: 0, dismissed: false,
        init() {
            try {
                var d = JSON.parse(localStorage.getItem('kn-academy') || '{}');
                this.completed = (d.completed || []).length;
                this.progress = Math.round((this.completed / this.total) * 100);
                this.remaining = this.total - this.completed;
                this.dismissed = localStorage.getItem('kn-academy-widget-dismissed') === '1' && this.completed === 0;
            } catch (e) {}
        },
        dismiss() { localStorage.setItem('kn-academy-widget-dismissed','1'); this.dismissed = true; }
    }" x-show="!dismissed && completed < total" x-cloak>
    <div class="relative rounded-2xl overflow-hidden p-5 sm:p-6 text-white" style="background: linear-gradient(135deg,#1e1b4b 0%,#4338ca 50%,#7c3aed 100%);">
        <div class="absolute inset-0 pointer-events-none opacity-20"
             style="background-image: linear-gradient(rgba(255,255,255,0.1) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.1) 1px, transparent 1px); background-size: 32px 32px;"></div>
        <div class="relative flex flex-wrap items-center gap-4">
            <div class="flex-shrink-0 h-12 w-12 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center text-2xl">🎓</div>
            <div class="flex-1 min-w-[200px]">
                <div class="flex items-center gap-2 mb-1">
                    <span class="inline-flex items-center gap-1 rounded-full bg-emerald-400/20 border border-emerald-400/40 px-2 py-0.5 text-[10px] font-bold uppercase tracking-wider text-emerald-200">
                        <span x-show="completed === 0">Nuevo</span>
                        <span x-show="completed > 0 && completed < total" x-cloak>En progreso</span>
                    </span>
                    <span class="text-[11px] font-mono opacity-70" x-text="`${completed}/${total} lecciones · ${progress}%`"></span>
                </div>
                <div class="font-bold text-lg" x-text="completed === 0 ? 'Aprende KyrosCounsel en 25 minutos' : 'Continúa donde lo dejaste'"></div>
                <div class="text-xs opacity-80 mt-0.5" x-text="completed === 0 ? '12 módulos guiados con mockups, atajos y quiz final' : `Te faltan ${remaining} lecciones para terminar`"></div>
                <!-- progress bar -->
                <div x-show="completed > 0" x-cloak class="mt-2.5 h-1 bg-white/20 rounded-full overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-emerald-400 to-teal-300 transition-all" :style="`width: ${progress}%`"></div>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <a href="<?= e(url('training.html')) ?>" target="_blank"
                   class="inline-flex items-center gap-1.5 rounded-lg bg-white text-indigo-700 px-4 py-2 text-sm font-bold shadow-lg hover:shadow-xl hover:scale-[1.02] transition">
                    <span x-text="completed === 0 ? 'Empezar' : 'Continuar'"></span>
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <button @click="dismiss()" x-show="completed === 0" class="text-white/60 hover:text-white p-2" title="Ocultar">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                </button>
            </div>
        </div>
    </div>
</div>

<style nonce="<?= e(csp_nonce()) ?>">
@keyframes wave { 0%,100% { transform: rotate(0); } 25% { transform: rotate(20deg); } 75% { transform: rotate(-15deg); } }
.animate-wave { animation: wave 2s ease-in-out infinite; display: inline-block; }
</style>

<!-- ============ BENTO KPIs (top row) ============ -->
<div class="bento mb-6">
    <a href="<?= e(tenant_url('clients')) ?>" class="bento-1 bento-md-6 bento-lg-3 kyros-stat group" style="text-decoration:none;">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="kyros-eyebrow">Clientes</div>
                <div class="mt-1 kyros-number text-3xl font-bold" style="color: hsl(var(--fg));"><?= (int)$stats['clients'] ?></div>
            </div>
            <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #ddd6fe 0%, #a5b4fc 100%); color: #4338ca;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
            </div>
        </div>
        <div class="kyros-spark"><canvas id="sparkClients"></canvas></div>
    </a>

    <a href="<?= e(tenant_url('cases')) ?>" class="bento-1 bento-md-6 bento-lg-3 kyros-stat group" style="text-decoration:none;">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="kyros-eyebrow">Casos</div>
                <div class="mt-1 kyros-number text-3xl font-bold" style="color: hsl(var(--fg));"><?= (int)$stats['cases'] ?></div>
                <div class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">
                    <span class="font-semibold text-amber-600"><?= (int)$stats['cases_open'] ?></span> abiertos
                </div>
            </div>
            <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #ede9fe 0%, #c4b5fd 100%); color: #6d28d9;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 7H4a2 2 0 00-2 2v9a2 2 0 002 2h16a2 2 0 002-2V9a2 2 0 00-2-2z"/><path d="M16 21V5a2 2 0 00-2-2h-4a2 2 0 00-2 2v16"/></svg>
            </div>
        </div>
        <div class="kyros-spark"><canvas id="sparkCases"></canvas></div>
    </a>

    <a href="<?= e(tenant_url('tasks')) ?>" class="bento-1 bento-md-6 bento-lg-3 kyros-stat group" style="text-decoration:none;">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="kyros-eyebrow">Tareas pendientes</div>
                <div class="mt-1 kyros-number text-3xl font-bold" style="color: hsl(var(--fg));"><?= (int)$stats['tasks_pending'] ?></div>
                <?php if ($stats['tasks_overdue'] > 0): ?>
                <div class="text-xs mt-0.5"><span class="badge-soft badge-danger"><?= (int)$stats['tasks_overdue'] ?> vencidas</span></div>
                <?php else: ?>
                <div class="text-xs mt-0.5 text-emerald-600 font-medium">✓ ninguna vencida</div>
                <?php endif; ?>
            </div>
            <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%); color: #047857;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="9 11 12 14 22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </div>
        </div>
        <div class="kyros-spark flex items-end gap-0.5">
            <?php for ($i = 0; $i < 16; $i++):
                $h = 20 + (sin($i * 0.7) * 30 + 30);
            ?>
            <span class="flex-1 rounded-sm" style="height: <?= $h ?>%; background: linear-gradient(180deg, hsl(142 71% 65%), hsl(142 71% 45%)); opacity: 0.85;"></span>
            <?php endfor; ?>
        </div>
    </a>

    <div class="bento-1 bento-md-6 bento-lg-3 kyros-stat">
        <div class="flex items-start justify-between mb-3">
            <div>
                <div class="kyros-eyebrow">Tasa aprobacion</div>
                <div class="mt-1 kyros-number text-3xl font-bold" style="color: hsl(var(--fg));"><?= (int)$stats['success_rate'] ?>%</div>
                <div class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">de casos cerrados</div>
            </div>
            <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #fed7aa 0%, #fb923c 100%); color: #c2410c;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 12h-4l-3 9L9 3l-3 9H2"/></svg>
            </div>
        </div>
        <div class="relative h-9 flex items-center">
            <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden" style="background: hsl(var(--surface-2));">
                <div class="h-full rounded-full transition-all duration-700"
                     style="width: <?= (int)$stats['success_rate'] ?>%; background: linear-gradient(90deg, #34d399 0%, #10b981 100%);"></div>
            </div>
        </div>
    </div>
</div>

<!-- ============ DEADLINES + BALANCE ============ -->
<?php if (!empty($deadlines) || (!empty($finance) && (float)$finance['pending'] > 0)): ?>
<div class="bento mb-6">
    <?php if (!empty($deadlines)): ?>
    <div class="bento-1 bento-lg-8 kyros-card overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between" style="border-bottom: 1px solid hsl(var(--border));">
            <div class="flex items-center gap-3">
                <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #fee2e2 0%, #fca5a5 100%); color: #b91c1c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
                <div>
                    <h3 class="text-base font-semibold">Deadlines USCIS</h3>
                    <p class="text-xs" style="color: hsl(var(--fg-muted));">Próximos 30 días</p>
                </div>
            </div>
            <span class="badge-soft badge-danger"><?= count($deadlines) ?></span>
        </div>
        <ul class="divide-y" style="--tw-divide-opacity: 1;">
            <?php
            $today_ts = strtotime(date('Y-m-d'));
            $kind_data = [
                'biometrics' => ['👆', 'Biometrics', 'badge-info'],
                'interview'  => ['🎙️', 'Entrevista', 'badge-warning'],
                'rfe'        => ['⚠️', 'Vence RFE',  'badge-danger'],
            ];
            foreach ($deadlines as $d):
                $days = round((strtotime($d['deadline_at']) - $today_ts) / 86400);
                [$icon, $label, $cls] = $kind_data[$d['kind']];
                $cls = $days < 0 ? 'badge-danger' : ($days < 7 ? 'badge-warning' : $cls);
                $label_days = $days < 0 ? abs($days) . 'd vencido' : ($days === 0 ? 'HOY' : "en {$days}d");
            ?>
            <li class="px-5 py-3 flex items-center gap-4 hover:bg-slate-50/50 transition" style="border-color: hsl(var(--border));">
                <div class="flex-shrink-0 text-center w-12">
                    <div class="kyros-eyebrow text-[10px]"><?= e(date('M', strtotime($d['deadline_at']))) ?></div>
                    <div class="kyros-number text-2xl font-bold leading-none mt-0.5 <?= $days < 7 ? 'text-red-600' : '' ?>" style="color: <?= $days < 7 ? '' : 'hsl(var(--fg))' ?>;"><?= e(date('d', strtotime($d['deadline_at']))) ?></div>
                </div>
                <div class="flex-1 min-w-0">
                    <a href="<?= e(tenant_url('cases/' . $d['id'])) ?>" class="block">
                        <div class="text-sm font-semibold truncate"><?= $icon ?> <?= e($label) ?></div>
                        <div class="text-xs font-mono mt-0.5 truncate" style="color: hsl(var(--fg-muted));"><?= e($d['case_number']) ?> · <?= e($d['first_name'] . ' ' . $d['last_name']) ?></div>
                    </a>
                </div>
                <span class="badge-soft <?= $cls ?> whitespace-nowrap"><?= e($label_days) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>

    <?php if (!empty($finance) && ((float)$finance['pending'] > 0 || (float)$finance['paid'] > 0)):
        $total = (float)$finance['paid'] + (float)$finance['pending'];
        $pct = $total > 0 ? round((float)$finance['paid'] / $total * 100) : 0;
    ?>
    <div class="bento-1 <?= !empty($deadlines) ? 'bento-lg-4' : 'bento-lg-12' ?> kyros-card p-5">
        <div class="flex items-center gap-3 mb-4">
            <div class="h-9 w-9 rounded-lg flex items-center justify-center" style="background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%); color: #047857;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </div>
            <div>
                <h3 class="text-base font-semibold">Balance financiero</h3>
                <p class="text-xs" style="color: hsl(var(--fg-muted));">Honorarios y filing fees</p>
            </div>
        </div>
        <div class="space-y-4">
            <div>
                <div class="flex justify-between text-xs" style="color: hsl(var(--fg-muted));">
                    <span>Cobrado</span>
                    <span class="kyros-number font-semibold text-emerald-600">$<?= e(number_format((float)$finance['paid'], 0)) ?></span>
                </div>
            </div>
            <div>
                <div class="flex justify-between text-xs" style="color: hsl(var(--fg-muted));">
                    <span>Pendiente</span>
                    <span class="kyros-number font-semibold text-amber-600">$<?= e(number_format((float)$finance['pending'], 0)) ?></span>
                </div>
            </div>
            <hr style="border-color: hsl(var(--border));">
            <div class="flex items-baseline justify-between">
                <span class="kyros-eyebrow">Total</span>
                <span class="kyros-number text-2xl font-bold">$<?= e(number_format($total, 0)) ?></span>
            </div>
            <div>
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span style="color: hsl(var(--fg-muted));">Tasa de cobro</span>
                    <span class="font-semibold"><?= $pct ?>%</span>
                </div>
                <div class="h-2 rounded-full overflow-hidden" style="background: hsl(var(--surface-2));">
                    <div class="h-full rounded-full transition-all duration-700" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #34d399, #10b981);"></div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- ============ MAIN CHARTS GRID (Bento asimétrico) ============ -->
<div class="bento mb-6">
    <div class="bento-1 bento-lg-8 kyros-card kyros-card-hover p-5 lg:p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="kyros-eyebrow">Tendencia</p>
                <h3 class="text-lg font-semibold mt-1">Casos creados</h3>
                <p class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">Últimos 30 días</p>
            </div>
            <span class="badge-soft badge-primary">Live</span>
        </div>
        <div class="chart-wrap"><canvas id="chartCreated" height="80"></canvas></div>
    </div>

    <div class="bento-1 bento-lg-4 kyros-card kyros-card-hover p-5 lg:p-6">
        <div class="mb-5">
            <p class="kyros-eyebrow">Distribución</p>
            <h3 class="text-lg font-semibold mt-1">Por tipo</h3>
            <p class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">Top 8 formularios</p>
        </div>
        <div class="chart-wrap"><canvas id="chartType" height="100"></canvas></div>
    </div>
</div>

<!-- ============ STATUS + ATTORNEYS ============ -->
<div class="bento mb-6">
    <div class="bento-1 bento-lg-8 kyros-card kyros-card-hover p-5 lg:p-6">
        <div class="flex items-center justify-between mb-5">
            <div>
                <p class="kyros-eyebrow">Pipeline</p>
                <h3 class="text-lg font-semibold mt-1">Casos por estado</h3>
            </div>
            <a href="<?= e(tenant_url('reports')) ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Ver reportes →</a>
        </div>
        <div class="chart-wrap"><canvas id="chartStatus" height="80"></canvas></div>
    </div>

    <div class="bento-1 bento-lg-4 kyros-card kyros-card-hover p-5 lg:p-6">
        <div class="mb-5">
            <p class="kyros-eyebrow">Equipo</p>
            <h3 class="text-lg font-semibold mt-1">Carga por abogado</h3>
        </div>
        <ul class="space-y-3.5">
            <?php
            $maxAtt = count($by_attorney) > 0 ? max(array_map(fn($x) => max(1, (int)$x['total']), $by_attorney)) : 1;
            foreach ($by_attorney as $a):
                $pct_att = round((int)$a['total'] / $maxAtt * 100);
            ?>
            <li>
                <div class="flex items-center justify-between text-sm mb-1.5">
                    <div class="flex items-center gap-2 min-w-0">
                        <div class="h-7 w-7 rounded-full flex items-center justify-center text-xs font-bold text-white flex-shrink-0"
                             style="background: linear-gradient(135deg, hsl(<?= ((int)$a['id'] * 70) % 360 ?> 70% 60%), hsl(<?= ((int)$a['id'] * 70 + 40) % 360 ?> 70% 50%));">
                            <?= e(strtoupper(mb_substr($a['name'], 0, 1))) ?>
                        </div>
                        <span class="font-medium truncate"><?= e($a['name']) ?></span>
                    </div>
                    <span class="text-xs flex-shrink-0 kyros-number" style="color: hsl(var(--fg-muted));">
                        <strong style="color: hsl(var(--fg));"><?= (int)$a['abiertos'] ?></strong>/<?= (int)$a['total'] ?>
                    </span>
                </div>
                <div class="h-1.5 rounded-full overflow-hidden" style="background: hsl(var(--surface-2));">
                    <div class="h-full rounded-full transition-all duration-700" style="width: <?= $pct_att ?>%; background: linear-gradient(90deg, hsl(<?= ((int)$a['id'] * 70) % 360 ?> 70% 60%), hsl(<?= ((int)$a['id'] * 70 + 40) % 360 ?> 70% 50%));"></div>
                </div>
            </li>
            <?php endforeach; ?>
            <?php if (empty($by_attorney)): ?>
            <li class="text-sm text-center py-6" style="color: hsl(var(--fg-muted));">Sin abogados activos.</li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<!-- ============ ACTIVITY + RECENT + TASKS ============ -->
<div class="bento">
    <!-- Recientes (más ancho) -->
    <div class="bento-1 bento-lg-7 kyros-card overflow-hidden">
        <div class="px-5 py-4 flex items-center justify-between" style="border-bottom: 1px solid hsl(var(--border));">
            <div>
                <p class="kyros-eyebrow">Actividad</p>
                <h3 class="text-base font-semibold mt-0.5">Casos recientes</h3>
            </div>
            <a href="<?= e(tenant_url('cases')) ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Ver todos →</a>
        </div>
        <ul>
            <?php foreach ($recent as $r):
                $colors = ['urgent' => 'danger','high' => 'warning','normal' => 'neutral','low' => 'neutral'];
                $st_colors = ['intake' => 'neutral','preparing' => 'info','filed' => 'primary','rfe' => 'warning',
                              'approved' => 'success','denied' => 'danger','withdrawn' => 'neutral','closed' => 'neutral'];
            ?>
            <li class="px-5 py-3.5 flex items-start justify-between gap-3 hover:bg-slate-50/50 transition" style="border-top: 1px solid hsl(var(--border));">
                <a href="<?= e(tenant_url('cases/' . $r['id'])) ?>" class="flex-1 min-w-0 block">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-xs font-mono font-semibold text-indigo-600"><?= e($r['case_number']) ?></span>
                        <?php if ($r['priority'] !== 'normal'): ?>
                        <span class="badge-soft badge-<?= $colors[$r['priority']] ?>"><?= e(CASE_PRIORITIES[$r['priority']]) ?></span>
                        <?php endif; ?>
                        <span class="badge-soft badge-<?= $st_colors[$r['status']] ?? 'neutral' ?>"><?= e(case_status_label($r['status'])) ?></span>
                    </div>
                    <div class="mt-1 text-sm font-medium truncate"><?= e($r['title']) ?></div>
                    <div class="mt-0.5 text-xs flex items-center gap-2" style="color: hsl(var(--fg-muted));">
                        <span><?= e($r['first_name'] . ' ' . $r['last_name']) ?></span>
                        <?php if ($r['attorney_name']): ?>
                        <span>·</span><span><?= e($r['attorney_name']) ?></span>
                        <?php endif; ?>
                        <span>·</span><span class="font-mono"><?= e($r['case_type']) ?></span>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
            <li class="empty-state">
                <div class="empty-state-icon">📂</div>
                <p class="text-sm font-medium">Aún no hay casos</p>
                <?php if (can('cases.create')): ?>
                <a href="<?= e(tenant_url('cases/new')) ?>" class="mt-3 inline-block btn btn-primary">Crear el primer caso</a>
                <?php endif; ?>
            </li>
            <?php endif; ?>
        </ul>
    </div>

    <!-- Sidebar: tareas + actividad apiladas -->
    <div class="bento-1 bento-lg-5 space-y-4 lg:space-y-5">
        <!-- Próximas tareas -->
        <div class="kyros-card overflow-hidden">
            <div class="px-5 py-4 flex items-center justify-between" style="border-bottom: 1px solid hsl(var(--border));">
                <div>
                    <p class="kyros-eyebrow">Próximas</p>
                    <h3 class="text-base font-semibold mt-0.5">Tareas</h3>
                </div>
                <a href="<?= e(tenant_url('tasks')) ?>" class="text-xs font-semibold text-indigo-600 hover:text-indigo-500">Ver →</a>
            </div>
            <ul>
                <?php foreach ($upcoming as $tk):
                    $overdue = $tk['due_date'] && $tk['due_date'] < date('Y-m-d');
                ?>
                <li class="px-5 py-3 flex items-start gap-2.5 hover:bg-slate-50/50 transition" style="border-top: 1px solid hsl(var(--border));">
                    <span class="mt-1 flex-shrink-0 inline-flex h-2 w-2 rounded-full <?= $overdue ? 'bg-red-500' : ($tk['priority'] === 'urgent' ? 'bg-orange-500' : 'bg-slate-300') ?>"></span>
                    <div class="min-w-0 flex-1">
                        <div class="text-sm font-medium truncate"><?= e($tk['title']) ?></div>
                        <div class="text-xs mt-0.5 flex items-center gap-1.5 flex-wrap" style="color: hsl(var(--fg-muted));">
                            <?= e($tk['assignee_name'] ?? 'Sin asignar') ?>
                            <?php if ($tk['due_date']): ?>
                            <span>·</span><span class="<?= $overdue ? 'text-red-600 font-semibold' : '' ?>"><?= e(date('d M', strtotime($tk['due_date']))) ?></span>
                            <?php endif; ?>
                            <?php if ($tk['case_number']): ?>
                            <span>·</span><span class="font-mono text-indigo-600"><?= e($tk['case_number']) ?></span>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($upcoming)): ?>
                <li class="px-5 py-8 text-center text-sm" style="color: hsl(var(--fg-muted));">
                    🎉 Sin tareas pendientes
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- Activity feed -->
        <div class="kyros-card overflow-hidden">
            <div class="px-5 py-4" style="border-bottom: 1px solid hsl(var(--border));">
                <p class="kyros-eyebrow">Audit</p>
                <h3 class="text-base font-semibold mt-0.5">Actividad reciente</h3>
            </div>
            <ul class="max-h-72 overflow-y-auto">
                <?php
                $eventLabels = [
                    'auth.login.success' => ['inició sesión', '#10b981'],
                    'auth.logout' => ['cerró sesión', '#94a3b8'],
                    'client.created' => ['creó cliente', '#6366f1'],
                    'client.updated' => ['actualizó cliente', '#0ea5e9'],
                    'case.created' => ['creó caso', '#8b5cf6'],
                    'case.updated' => ['actualizó caso', '#0ea5e9'],
                    'case.status_changed' => ['cambió estado', '#f59e0b'],
                    'document.uploaded' => ['subió documento', '#10b981'],
                    'document.deleted' => ['eliminó documento', '#ef4444'],
                    'user.created' => ['creó usuario', '#6366f1'],
                    'user.2fa_enabled' => ['activó 2FA', '#10b981'],
                    'export.csv' => ['exportó CSV', '#0ea5e9'],
                ];
                foreach ($activity as $a):
                    [$lbl, $color] = $eventLabels[$a['event']] ?? [$a['event'], '#94a3b8'];
                    $_actor = $a['actor_name'] ?? 'Sistema';
                ?>
                <li class="px-4 py-2.5 text-xs flex items-center gap-2.5" style="border-top: 1px solid hsl(var(--border));">
                    <?php if ($a['actor_name']): ?>
                        <?= avatar_render($_actor, 'sm') ?>
                    <?php else: ?>
                        <span class="inline-flex h-7 w-7 rounded-full items-center justify-center flex-shrink-0" style="background: hsl(var(--surface-2));">
                            <span class="h-2 w-2 rounded-full" style="background: <?= e($color) ?>"></span>
                        </span>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <div class="truncate">
                            <span class="font-semibold"><?= e($_actor) ?></span>
                            <span style="color: hsl(var(--fg-muted));"><?= e($lbl) ?></span>
                        </div>
                        <div class="text-[10px]" style="color: hsl(var(--fg-muted));" title="<?= e($a['created_at']) ?>"><?= e(time_ago($a['created_at'])) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($activity)): ?>
                <li class="p-6 text-center text-sm" style="color: hsl(var(--fg-muted));">Sin actividad reciente.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "Inter, system-ui, sans-serif";
    Chart.defaults.font.size = 11;
    Chart.defaults.color = '#64748b';
    Chart.defaults.borderColor = 'rgba(148, 163, 184, 0.12)';

    const sparkClients = <?= json_encode($spark_clients, JSON_UNESCAPED_UNICODE) ?>;
    const sparkCases   = <?= json_encode($spark_cases, JSON_UNESCAPED_UNICODE) ?>;
    const byStatus     = <?= json_encode($by_status, JSON_UNESCAPED_UNICODE) ?>;
    const byType       = <?= json_encode($by_type, JSON_UNESCAPED_UNICODE) ?>;
    const created      = <?= json_encode($created_series, JSON_UNESCAPED_UNICODE) ?>;
    const statusLabels = <?= json_encode(CASE_STATUSES, JSON_UNESCAPED_UNICODE) ?>;

    const sparkOpts = {
        responsive: true, maintainAspectRatio: false,
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        scales: { x: { display: false }, y: { display: false } },
        elements: { point: { radius: 0 } }
    };

    function gradient(ctx, color, alpha) {
        const g = ctx.createLinearGradient(0, 0, 0, 250);
        g.addColorStop(0, color + alpha);
        g.addColorStop(1, color + '00');
        return g;
    }

    new Chart(document.getElementById('sparkClients'), {
        type: 'line',
        data: { labels: sparkClients.map(r=>r.d), datasets: [{
            data: sparkClients.map(r=>r.c),
            borderColor: '#6366f1',
            backgroundColor: function(c) { return gradient(c.chart.ctx, '#6366f1', '40'); },
            tension: 0.45, fill: true, borderWidth: 2,
        }]},
        options: sparkOpts
    });
    new Chart(document.getElementById('sparkCases'), {
        type: 'line',
        data: { labels: sparkCases.map(r=>r.d), datasets: [{
            data: sparkCases.map(r=>r.c),
            borderColor: '#8b5cf6',
            backgroundColor: function(c) { return gradient(c.chart.ctx, '#8b5cf6', '40'); },
            tension: 0.45, fill: true, borderWidth: 2,
        }]},
        options: sparkOpts
    });

    const tooltipStyle = {
        backgroundColor: 'rgba(15, 23, 42, 0.95)', padding: 12, cornerRadius: 10,
        titleFont: { size: 11, weight: 'bold' }, bodyFont: { size: 12 },
        displayColors: false, borderWidth: 0,
    };

    new Chart(document.getElementById('chartCreated'), {
        type: 'line',
        data: {
            labels: created.map(r => new Date(r.d).toLocaleDateString('es', { day:'2-digit', month:'short' })),
            datasets: [{
                label: 'Casos',
                data: created.map(r => r.c),
                borderColor: '#6366f1',
                backgroundColor: function(c) { return gradient(c.chart.ctx, '#6366f1', '50'); },
                tension: 0.4, fill: true, borderWidth: 2.5,
                pointRadius: 0, pointHoverRadius: 6,
                pointBackgroundColor: '#6366f1', pointBorderColor: '#fff', pointBorderWidth: 2,
                pointHoverBorderWidth: 3,
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            interaction: { intersect: false, mode: 'index' },
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: {
                x: { grid: { display: false }, ticks: { font: { size: 10 }, maxRotation: 0 } },
                y: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { drawTicks: false, color: 'rgba(148,163,184,0.08)' }, border: { display: false } }
            }
        }
    });

    if (byType.length) {
        new Chart(document.getElementById('chartType'), {
            type: 'doughnut',
            data: {
                labels: byType.map(r => r.case_type),
                datasets: [{
                    data: byType.map(r => r.c),
                    backgroundColor: ['#6366f1','#8b5cf6','#ec4899','#f59e0b','#10b981','#0ea5e9','#ef4444','#14b8a6'],
                    borderWidth: 3, borderColor: 'transparent', hoverOffset: 8, spacing: 2,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '68%',
                plugins: {
                    legend: { position: 'bottom', labels: { font: { size: 10 }, boxWidth: 8, boxHeight: 8, padding: 8, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: tooltipStyle
                }
            }
        });
    }

    const statusColors = { intake: '#94a3b8', preparing: '#6366f1', filed: '#0ea5e9', rfe: '#f59e0b', approved: '#10b981', denied: '#ef4444', withdrawn: '#64748b', closed: '#475569' };
    new Chart(document.getElementById('chartStatus'), {
        type: 'bar',
        data: {
            labels: byStatus.map(r => statusLabels[r.status] || r.status),
            datasets: [{
                data: byStatus.map(r => r.c),
                backgroundColor: byStatus.map(r => statusColors[r.status] || '#94a3b8'),
                borderRadius: 8, borderSkipped: false, barThickness: 24,
            }]
        },
        options: {
            indexAxis: 'y', responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false }, tooltip: tooltipStyle },
            scales: {
                x: { beginAtZero: true, ticks: { precision: 0, font: { size: 10 } }, grid: { color: 'rgba(148,163,184,0.08)' }, border: { display: false } },
                y: { grid: { display: false }, ticks: { font: { size: 12, weight: '500' } }, border: { display: false } }
            }
        }
    });
});
</script>
