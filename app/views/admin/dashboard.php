<?php
$signupsJson = json_encode(array_map(fn($r) => ['d'=>$r['d'], 'c'=>(int)$r['c']], $series_signups));
$revJson     = json_encode(array_map(fn($r) => ['d'=>$r['d'], 's'=>(float)$r['s']], $series_revenue));
?>

<!-- Hero KPIs -->
<div class="mb-6">
    <div class="flex items-baseline justify-between flex-wrap gap-3">
        <div>
            <h1 class="text-3xl font-bold tracking-tight">Dashboard SaaS</h1>
            <p class="mt-1 text-sm text-slate-600">Panorama general del negocio · datos en vivo</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= e(url('admin/tenants/new')) ?>" class="btn btn-primary" style="background:#d97706;">+ Nuevo tenant</a>
            <a href="<?= e(url('admin/plans/new')) ?>" class="btn btn-secondary">+ Plan</a>
            <a href="<?= e(url('admin/announcements')) ?>" class="btn btn-secondary">Anuncio</a>
        </div>
    </div>
</div>

<!-- KPIs principales SaaS -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="kyros-stat">
        <div class="flex items-center justify-between">
            <span class="kyros-stat-icon" style="background: linear-gradient(135deg,#fef3c7,#fde68a); color:#b45309;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
            </span>
            <span class="badge-soft badge-success">+<?= number_format(($growth['new30'] ?? 0)) ?> 30d</span>
        </div>
        <div class="kyros-eyebrow mt-3">MRR</div>
        <div class="text-3xl font-bold tracking-tight kyros-number">$<?= number_format($mrr, 0) ?></div>
        <div class="text-xs text-slate-500 mt-1">ARR proyectado · $<?= number_format($arr, 0) ?></div>
    </div>

    <div class="kyros-stat">
        <div class="flex items-center justify-between">
            <span class="kyros-stat-icon" style="background: linear-gradient(135deg,#dbeafe,#bfdbfe); color:#1e40af;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/></svg>
            </span>
            <span class="badge-soft <?= $stats['tenants_trial'] > 0 ? 'badge-info' : 'badge-neutral' ?>"><?= $stats['tenants_trial'] ?> trial</span>
        </div>
        <div class="kyros-eyebrow mt-3">Tenants activos</div>
        <div class="text-3xl font-bold tracking-tight kyros-number"><?= number_format($stats['tenants_active']) ?></div>
        <div class="text-xs text-slate-500 mt-1"><?= $stats['tenants_total'] ?> totales · <?= $stats['tenants_susp'] ?> suspendidos</div>
    </div>

    <div class="kyros-stat">
        <div class="flex items-center justify-between">
            <span class="kyros-stat-icon" style="background: linear-gradient(135deg,#dcfce7,#bbf7d0); color:#15803d;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            </span>
            <span class="badge-soft <?= $conversion_rate >= 30 ? 'badge-success' : 'badge-warning' ?>"><?= number_format($conversion_rate, 1) ?>%</span>
        </div>
        <div class="kyros-eyebrow mt-3">Conversión 90d</div>
        <div class="text-3xl font-bold tracking-tight kyros-number"><?= number_format($conversion_rate, 1) ?>%</div>
        <div class="text-xs text-slate-500 mt-1">Trial → Active</div>
    </div>

    <div class="kyros-stat">
        <div class="flex items-center justify-between">
            <span class="kyros-stat-icon" style="background: linear-gradient(135deg,#fee2e2,#fecaca); color:#b91c1c;">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="23 18 13.5 8.5 8.5 13.5 1 6"/><polyline points="17 18 23 18 23 12"/></svg>
            </span>
            <span class="badge-soft <?= $churn_rate < 5 ? 'badge-success' : ($churn_rate < 10 ? 'badge-warning' : 'badge-danger') ?>"><?= number_format($churn_rate, 1) ?>%</span>
        </div>
        <div class="kyros-eyebrow mt-3">Churn 30d</div>
        <div class="text-3xl font-bold tracking-tight kyros-number"><?= number_format($churn_rate, 1) ?>%</div>
        <div class="text-xs text-slate-500 mt-1"><?= number_format(($growth['cancel30'] ?? 0)) ?> cancelados / 30d</div>
    </div>
</div>

<!-- Charts -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="kyros-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h3 class="font-semibold">Ingresos pagados — 30 días</h3>
                <p class="text-xs text-slate-500">Suma diaria de invoices status = paid</p>
            </div>
            <div class="text-right">
                <div class="text-2xl font-bold">$<?= number_format($monthly_revenue, 0) ?></div>
                <div class="text-[10px] text-slate-500 uppercase tracking-wider">Mes en curso</div>
            </div>
        </div>
        <div class="chart-wrap"><canvas id="chRevenue"></canvas></div>
    </div>

    <div class="space-y-4">
        <div class="kyros-card p-5">
            <div class="kyros-eyebrow">ARPU</div>
            <div class="text-3xl font-bold mt-1">$<?= number_format($arpu, 2) ?></div>
            <div class="text-xs text-slate-500 mt-1">Ingreso medio por tenant</div>
        </div>
        <div class="kyros-card p-5">
            <div class="kyros-eyebrow">Salud del sistema</div>
            <ul class="mt-3 space-y-1.5 text-sm">
                <li class="flex justify-between"><span class="text-slate-600">Almacenamiento</span><strong><?= number_format($health['storage_mb']) ?> MB</strong></li>
                <li class="flex justify-between"><span class="text-slate-600">Audit hoy</span><strong><?= number_format($health['audit_today']) ?></strong></li>
                <li class="flex justify-between"><span class="text-slate-600">Logins fallidos 24h</span><strong class="<?= $health['failed_logins_24h'] > 50 ? 'text-rose-600' : '' ?>"><?= number_format($health['failed_logins_24h']) ?></strong></li>
                <li class="flex justify-between"><span class="text-slate-600">Workflows 24h</span><strong><?= number_format($health['workflows_runs_24h']) ?></strong></li>
                <?php if ($unpaid_invoices > 0): ?>
                <li class="flex justify-between text-amber-700"><span>Facturas pendientes</span><strong><?= number_format($unpaid_invoices) ?></strong></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</div>

<!-- Signups + Plans -->
<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-6">
    <div class="kyros-card p-5 lg:col-span-2">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold">Nuevos tenants — 30 días</h3>
            <span class="badge-soft badge-success">+<?= $growth['new30'] ?? 0 ?> nuevos</span>
        </div>
        <div class="chart-wrap"><canvas id="chSignups"></canvas></div>
    </div>
    <div class="kyros-card p-5">
        <h3 class="font-semibold mb-4">Por plan</h3>
        <ul class="space-y-2">
            <?php foreach ($by_plan as $p): ?>
            <li class="flex items-center justify-between text-sm">
                <span class="font-medium"><?= e($p['name']) ?></span>
                <div class="flex items-center gap-3">
                    <span class="text-slate-500 text-xs">$<?= number_format((float)$p['price_usd'], 0) ?>/mes</span>
                    <span class="font-semibold tabular-nums"><?= (int)$p['c'] ?></span>
                </div>
            </li>
            <?php endforeach; ?>
        </ul>
        <div class="mt-4 pt-4 border-t border-slate-200">
            <a href="<?= e(url('admin/plans')) ?>" class="text-sm font-semibold text-amber-700 hover:text-amber-600">Gestionar planes →</a>
        </div>
    </div>
</div>

<!-- Alerta de tenants over-limit -->
<?php if (!empty($over_limit)): ?>
<div class="kyros-card overflow-hidden mb-6 border-l-4 border-amber-500">
    <div class="p-5 border-b border-slate-200 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="text-amber-500" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h3 class="font-semibold">Tenants cerca de exceder su plan</h3>
            <span class="badge-soft badge-warning"><?= count($over_limit) ?></span>
        </div>
        <span class="text-xs text-slate-500">≥80% en algún límite</span>
    </div>
    <div class="divide-y divide-slate-100">
        <?php foreach ($over_limit as $t):
            $pct = (float)$t['pct'] * 100;
            $usersPct = $t['max_users'] ? min(100, ((int)$t['users_count'] / (int)$t['max_users']) * 100) : 0;
            $casesPct = $t['max_cases'] ? min(100, ((int)$t['cases_count'] / (int)$t['max_cases']) * 100) : 0;
            $storagePct = $t['max_storage_mb'] ? min(100, ((int)$t['storage_mb'] / (int)$t['max_storage_mb']) * 100) : 0;
        ?>
        <div class="p-4 hover:bg-slate-50">
            <div class="flex items-center gap-3 mb-2">
                <a href="<?= e(url('admin/tenants/' . $t['id'])) ?>" class="font-semibold flex-1 truncate hover:text-amber-700"><?= e($t['name']) ?></a>
                <span class="text-xs font-mono text-slate-500"><?= e($t['plan_name']) ?></span>
                <span class="text-xs font-bold tabular-nums <?= $pct >= 100 ? 'text-rose-600' : 'text-amber-600' ?>"><?= number_format($pct, 0) ?>%</span>
            </div>
            <div class="grid grid-cols-3 gap-2 text-[11px]">
                <div>
                    <div class="flex justify-between text-slate-500"><span>Usuarios</span><span class="font-mono"><?= (int)$t['users_count'] ?>/<?= (int)$t['max_users'] ?></span></div>
                    <div class="h-1 mt-1 bg-slate-100 rounded-full overflow-hidden"><div class="h-full <?= $usersPct >= 100 ? 'bg-rose-500' : ($usersPct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width:<?= $usersPct ?>%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-slate-500"><span>Casos</span><span class="font-mono"><?= number_format((int)$t['cases_count']) ?>/<?= number_format((int)$t['max_cases']) ?></span></div>
                    <div class="h-1 mt-1 bg-slate-100 rounded-full overflow-hidden"><div class="h-full <?= $casesPct >= 100 ? 'bg-rose-500' : ($casesPct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width:<?= $casesPct ?>%"></div></div>
                </div>
                <div>
                    <div class="flex justify-between text-slate-500"><span>Storage</span><span class="font-mono"><?= number_format((int)$t['storage_mb']) ?>/<?= number_format((int)$t['max_storage_mb']) ?> MB</span></div>
                    <div class="h-1 mt-1 bg-slate-100 rounded-full overflow-hidden"><div class="h-full <?= $storagePct >= 100 ? 'bg-rose-500' : ($storagePct >= 80 ? 'bg-amber-500' : 'bg-emerald-500') ?>" style="width:<?= $storagePct ?>%"></div></div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<!-- Tenants recientes + Top -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-6">
    <div class="kyros-card overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-200">
            <h3 class="font-semibold">Tenants recientes</h3>
            <a href="<?= e(url('admin/tenants')) ?>" class="text-xs font-semibold text-amber-700 hover:text-amber-600">Ver todos →</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php foreach ($latest_tenants as $t):
                $statusBadge = ['active'=>'badge-success','trial'=>'badge-info','suspended'=>'badge-warning','cancelled'=>'badge-danger','pending'=>'badge-neutral'][$t['status']] ?? 'badge-neutral';
            ?>
            <a href="<?= e(url('admin/tenants/' . $t['id'])) ?>" class="flex items-center gap-3 p-4 hover:bg-slate-50 transition">
                <div class="h-9 w-9 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center font-bold text-sm flex-shrink-0">
                    <?= e(strtoupper(mb_substr($t['name'], 0, 1))) ?>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate"><?= e($t['name']) ?></div>
                    <div class="text-xs text-slate-500 font-mono truncate"><?= e($t['slug']) ?> · <?= e($t['country']) ?> · <?= e($t['plan_name'] ?? '—') ?></div>
                </div>
                <span class="badge-soft <?= $statusBadge ?> flex-shrink-0"><?= e($t['status']) ?></span>
            </a>
            <?php endforeach; ?>
        </div>
    </div>

    <div class="kyros-card overflow-hidden">
        <div class="flex items-center justify-between p-5 border-b border-slate-200">
            <h3 class="font-semibold">Top tenants por casos</h3>
            <a href="<?= e(url('admin/tenants?sort=revenue')) ?>" class="text-xs font-semibold text-amber-700 hover:text-amber-600">Ver más →</a>
        </div>
        <div class="divide-y divide-slate-100">
            <?php foreach ($top_tenants as $i => $t): ?>
            <a href="<?= e(url('admin/tenants/' . $t['id'])) ?>" class="flex items-center gap-3 p-4 hover:bg-slate-50 transition">
                <span class="w-6 text-xs font-bold text-slate-400">#<?= $i + 1 ?></span>
                <div class="flex-1 min-w-0">
                    <div class="font-medium truncate"><?= e($t['name']) ?></div>
                    <div class="text-xs text-slate-500"><?= (int)$t['users_count'] ?> usuarios · <?= e($t['country']) ?></div>
                </div>
                <div class="text-right">
                    <div class="font-bold tabular-nums"><?= number_format((int)$t['cases_count']) ?></div>
                    <div class="text-[10px] uppercase text-slate-500">casos</div>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<!-- Audit reciente -->
<div class="kyros-card overflow-hidden">
    <div class="flex items-center justify-between p-5 border-b border-slate-200">
        <h3 class="font-semibold">Actividad global reciente</h3>
        <a href="<?= e(url('admin/audit')) ?>" class="text-xs font-semibold text-amber-700 hover:text-amber-600">Ver auditoría →</a>
    </div>
    <ul class="divide-y divide-slate-100 max-h-[420px] overflow-y-auto">
        <?php foreach ($recent_audit as $a):
            $isLogin  = strpos($a['event'], 'auth.') === 0;
            $isFailed = $a['event'] === 'auth.login.failed';
            $iconBg   = $isFailed ? 'bg-rose-100 text-rose-600' : ($isLogin ? 'bg-emerald-100 text-emerald-600' : 'bg-slate-100 text-slate-600');
        ?>
        <li class="flex items-start gap-3 p-3 text-sm">
            <span class="h-7 w-7 rounded-md <?= $iconBg ?> flex items-center justify-center flex-shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <div class="flex-1 min-w-0">
                <div class="font-mono text-[11px] text-slate-700 truncate"><?= e($a['event']) ?></div>
                <div class="text-xs text-slate-500 truncate">
                    <?php if ($a['actor_name']): ?><strong><?= e($a['actor_name']) ?></strong><?php endif; ?>
                    <?php if ($a['tenant_name']): ?> · <?= e($a['tenant_name']) ?><?php endif; ?>
                    <?php if ($a['ip']): ?> · <?= e($a['ip']) ?><?php endif; ?>
                </div>
            </div>
            <span class="text-[11px] text-slate-400 flex-shrink-0 font-mono"><?= e(date('m-d H:i', strtotime($a['created_at']))) ?></span>
        </li>
        <?php endforeach; ?>
    </ul>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function() {
    if (!window.Chart) return;

    var revData = <?= $revJson ?>;
    new Chart(document.getElementById('chRevenue'), {
        type: 'line',
        data: {
            labels: revData.map(r => r.d.slice(5)),
            datasets: [{
                label: '$', data: revData.map(r => r.s),
                fill: true, borderColor: '#d97706', backgroundColor: 'rgba(217,119,6,0.1)',
                tension: 0.3, borderWidth: 2.5, pointRadius: 0
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true }, x: { grid: { display: false } } }
        }
    });

    var sData = <?= $signupsJson ?>;
    new Chart(document.getElementById('chSignups'), {
        type: 'bar',
        data: {
            labels: sData.map(r => r.d.slice(5)),
            datasets: [{
                data: sData.map(r => r.c),
                backgroundColor: 'rgba(99,102,241,0.6)',
                borderRadius: 4
            }]
        },
        options: {
            responsive: true, maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } }, x: { grid: { display: false } } }
        }
    });
});
</script>
