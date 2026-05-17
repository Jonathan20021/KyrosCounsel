<?php
$statusBadge = ['active'=>'badge-success','trial'=>'badge-info','suspended'=>'badge-warning','cancelled'=>'badge-danger','pending'=>'badge-neutral'][$tenant['status']] ?? 'badge-neutral';
$byDayJson = json_encode(array_map(fn($r)=>['d'=>$r['d'],'c'=>(int)$r['c']], $by_day));
?>

<!-- Header -->
<div class="kyros-hero p-6 mb-6">
    <div class="flex flex-wrap items-start justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="h-14 w-14 rounded-xl bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center text-2xl font-bold shadow-lg shadow-amber-500/30 flex-shrink-0">
                <?= e(strtoupper(mb_substr($tenant['name'], 0, 1))) ?>
            </div>
            <div>
                <div class="flex items-center gap-2 flex-wrap">
                    <h1 class="text-2xl font-bold tracking-tight"><?= e($tenant['name']) ?></h1>
                    <span class="badge-soft <?= $statusBadge ?>"><?= e($tenant['status']) ?></span>
                    <?php if ($tenant['suspended_reason']): ?>
                    <span class="text-xs text-rose-600" title="<?= e($tenant['suspended_reason']) ?>">⚠ Suspendido</span>
                    <?php endif; ?>
                </div>
                <div class="text-sm text-slate-600 font-mono mt-1">
                    /t/<?= e($tenant['slug']) ?> · <?= e($tenant['country']) ?> · plan: <?= e($tenant['plan_name'] ?? '—') ?>
                </div>
                <?php if (!empty($tenant['billing_email'])): ?>
                <div class="text-xs text-slate-500 mt-0.5"><?= e($tenant['billing_email']) ?></div>
                <?php endif; ?>
            </div>
        </div>
        <div class="flex flex-wrap gap-2">
            <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/impersonate')) ?>" data-confirm="¿Iniciar sesión como tenant_admin de este bufete?">
                <?= csrf_field() ?>
                <input type="hidden" name="reason" value="Soporte / inspección">
                <button class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 00-4-4H6a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><polyline points="17 11 19 13 23 9"/></svg>
                    Impersonar
                </button>
            </form>
            <a href="<?= e(url('t/' . $tenant['slug'] . '/dashboard')) ?>" target="_blank" class="btn btn-secondary">Abrir tenant ↗</a>
        </div>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-6 gap-3 mb-6">
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Usuarios</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['users']) ?></div><div class="text-[11px] text-slate-500">de <?= (int)$tenant['max_users'] ?></div></div>
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Casos</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['cases']) ?></div><div class="text-[11px] text-slate-500">de <?= number_format((int)$tenant['max_cases']) ?></div></div>
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Clientes</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['clients']) ?></div></div>
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Documentos</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['docs']) ?></div></div>
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Almacén</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['storage_mb']) ?> MB</div><div class="text-[11px] text-slate-500">de <?= number_format((int)$tenant['max_storage_mb']) ?></div></div>
    <div class="kyros-card p-4"><div class="text-[10px] uppercase text-slate-500 font-semibold tracking-wider">Tareas</div><div class="text-2xl font-bold mt-1"><?= number_format($stats['tasks_pending']) ?></div><div class="text-[11px] text-slate-500">pendientes</div></div>
</div>

<!-- Tabs -->
<div x-data="{ tab: 'overview' }" class="space-y-4">
    <nav class="kyros-card p-1.5 flex gap-1 overflow-x-auto">
        <?php
        $tabs = [
            'overview'   => 'Resumen',
            'users'      => 'Usuarios',
            'billing'    => 'Facturación',
            'licenses'   => 'Licencias',
            'audit'      => 'Auditoría',
            'settings'   => 'Configuración',
            'danger'     => '⚠ Zona peligrosa',
        ];
        foreach ($tabs as $key => $label): ?>
        <button type="button" @click="tab = '<?= $key ?>'" :class="tab === '<?= $key ?>' ? 'bg-amber-500 text-white shadow' : 'text-slate-600 hover:bg-slate-100'" class="kn-tab whitespace-nowrap"><?= e($label) ?></button>
        <?php endforeach; ?>
    </nav>

    <!-- TAB: Overview -->
    <div x-show="tab === 'overview'" class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        <div class="kyros-card p-5 lg:col-span-2">
            <div class="flex items-center justify-between mb-4">
                <h3 class="font-semibold">Crecimiento de clientes — 30 días</h3>
            </div>
            <div class="chart-wrap"><canvas id="chTenantClients"></canvas></div>
        </div>
        <div class="kyros-card p-5">
            <h3 class="font-semibold mb-3">Casos por estado</h3>
            <ul class="space-y-1.5 text-sm">
                <?php foreach ($by_status as $s): ?>
                <li class="flex items-center justify-between">
                    <span class="capitalize"><?= e($s['status']) ?></span>
                    <span class="font-bold tabular-nums"><?= (int)$s['c'] ?></span>
                </li>
                <?php endforeach; ?>
                <?php if (empty($by_status)): ?>
                <li class="text-slate-500 italic">Sin casos aún.</li>
                <?php endif; ?>
            </ul>
        </div>
    </div>

    <!-- TAB: Usuarios -->
    <div x-show="tab === 'users'" x-cloak class="kyros-card overflow-hidden">
        <div class="p-4 border-b border-slate-200 flex items-center justify-between">
            <h3 class="font-semibold">Usuarios del tenant (<?= count($users) ?>)</h3>
            <span class="text-xs text-slate-500">Límite: <?= (int)$tenant['max_users'] ?></span>
        </div>
        <div class="overflow-x-auto">
        <table class="kyros-table w-full">
            <thead class="bg-slate-50"><tr class="text-left text-xs uppercase tracking-wider text-slate-500">
                <th class="px-4 py-3">Nombre</th><th class="px-4 py-3">Email</th><th class="px-4 py-3">Rol</th>
                <th class="px-4 py-3">2FA</th><th class="px-4 py-3">Último login</th><th class="px-4 py-3"></th>
            </tr></thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-sm font-medium"><?= e($u['name']) ?></td>
                    <td class="px-4 py-3 text-xs font-mono text-slate-600"><?= e($u['email']) ?></td>
                    <td class="px-4 py-3 text-xs"><?= e(str_replace('_', ' ', $u['role'])) ?></td>
                    <td class="px-4 py-3"><?= !empty($u['two_factor_enabled']) ? '<span class="badge-soft badge-success">ON</span>' : '<span class="badge-soft badge-neutral">OFF</span>' ?></td>
                    <td class="px-4 py-3 text-xs text-slate-500"><?= $u['last_login_at'] ? e(date('Y-m-d H:i', strtotime($u['last_login_at']))) : '—' ?></td>
                    <td class="px-4 py-3 text-right">
                        <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/reset-password')) ?>" class="inline" data-confirm="¿Generar contraseña temporal para <?= e($u['email']) ?>?">
                            <?= csrf_field() ?>
                            <input type="hidden" name="user_id" value="<?= (int)$u['id'] ?>">
                            <button class="text-xs font-semibold text-amber-700 hover:text-amber-600">Reset password</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
    </div>

    <!-- TAB: Facturación -->
    <div x-show="tab === 'billing'" x-cloak class="space-y-4">
        <div class="kyros-card p-5">
            <h3 class="font-semibold mb-4">Plan y facturación</h3>
            <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/change-plan')) ?>" class="flex flex-wrap items-end gap-3">
                <?= csrf_field() ?>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan</label>
                    <select name="plan_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <?php foreach ($plans as $p): ?>
                        <option value="<?= (int)$p['id'] ?>" <?= (int)$tenant['plan_id'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?> — $<?= number_format((float)$p['price_usd'],0) ?>/<?= e($p['billing_cycle'] ?? 'mes') ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button class="btn btn-primary" style="background:#d97706;">Cambiar plan</button>
            </form>

            <hr class="my-5 border-slate-200">

            <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/extend-trial')) ?>" class="flex flex-wrap items-end gap-3">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Extender trial</label>
                    <select name="days" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="7">+7 días</option>
                        <option value="14" selected>+14 días</option>
                        <option value="30">+30 días</option>
                        <option value="60">+60 días</option>
                        <option value="90">+90 días</option>
                    </select>
                </div>
                <button class="btn btn-secondary">Extender</button>
                <span class="text-xs text-slate-500 ml-auto">Trial actual: <?= e($tenant['trial_ends_at'] ?? 'no definido') ?></span>
            </form>
        </div>

        <div class="kyros-card overflow-hidden">
            <div class="p-4 border-b border-slate-200"><h3 class="font-semibold">Facturas (<?= count($invoices) ?>)</h3></div>
            <table class="kyros-table w-full">
                <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
                    <th class="px-4 py-3">Número</th><th class="px-4 py-3">Período</th><th class="px-4 py-3 text-right">Monto</th>
                    <th class="px-4 py-3">Estado</th><th class="px-4 py-3">Pagado</th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($invoices): foreach ($invoices as $i): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-xs font-mono"><?= e($i['invoice_number'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-xs"><?= e($i['period_start'] ?? '—') ?> → <?= e($i['period_end'] ?? '—') ?></td>
                        <td class="px-4 py-3 text-right text-sm font-mono">$<?= number_format((float)$i['amount_usd'], 2) ?></td>
                        <td class="px-4 py-3"><span class="badge-soft <?= ['paid'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger','refunded'=>'badge-neutral'][$i['status']] ?>"><?= e($i['status']) ?></span></td>
                        <td class="px-4 py-3 text-xs text-slate-500"><?= $i['paid_at'] ? e(date('Y-m-d', strtotime($i['paid_at']))) : '—' ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" class="px-4 py-8 text-center text-sm text-slate-500">Sin facturas registradas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Licencias -->
    <div x-show="tab === 'licenses'" x-cloak class="space-y-4">
        <div class="kyros-card p-5">
            <h3 class="font-semibold mb-4">Generar nueva licencia</h3>
            <form method="POST" action="<?= e(url('admin/licenses')) ?>" class="grid grid-cols-1 md:grid-cols-4 gap-3 items-end">
                <?= csrf_field() ?>
                <input type="hidden" name="tenant_id" value="<?= (int)$tenant['id'] ?>">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan asociado</label>
                    <select name="plan_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                        <option value="">Sin plan específico</option>
                        <?php foreach ($plans as $p): ?>
                        <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Vigencia (días)</label>
                    <input name="valid_days" type="number" min="0" placeholder="0 = sin expiración" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div class="md:col-span-2">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Notas internas</label>
                    <input name="notes" placeholder="Ej. licencia educativa, partner..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <button class="btn btn-primary md:col-span-4" style="background:#d97706;">Generar licencia</button>
            </form>
        </div>

        <div class="kyros-card overflow-hidden">
            <div class="p-4 border-b border-slate-200"><h3 class="font-semibold">Licencias emitidas (<?= count($licenses) ?>)</h3></div>
            <table class="kyros-table w-full">
                <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
                    <th class="px-4 py-3">Clave</th><th class="px-4 py-3">Plan</th><th class="px-4 py-3">Estado</th>
                    <th class="px-4 py-3">Emitida</th><th class="px-4 py-3">Expira</th><th class="px-4 py-3"></th>
                </tr></thead>
                <tbody class="divide-y divide-slate-100">
                    <?php if ($licenses): foreach ($licenses as $l): ?>
                    <tr class="hover:bg-slate-50">
                        <td class="px-4 py-3 text-xs font-mono"><?= e($l['license_key']) ?></td>
                        <td class="px-4 py-3 text-sm"><?= e($l['plan_name'] ?? '—') ?></td>
                        <td class="px-4 py-3"><span class="badge-soft <?= ['active'=>'badge-success','revoked'=>'badge-danger','expired'=>'badge-neutral'][$l['status']] ?>"><?= e($l['status']) ?></span></td>
                        <td class="px-4 py-3 text-xs text-slate-500"><?= e(date('Y-m-d', strtotime($l['issued_at']))) ?></td>
                        <td class="px-4 py-3 text-xs text-slate-500"><?= $l['expires_at'] ? e(date('Y-m-d', strtotime($l['expires_at']))) : 'No expira' ?></td>
                        <td class="px-4 py-3 text-right">
                            <?php if ($l['status'] === 'active'): ?>
                            <form method="POST" action="<?= e(url('admin/licenses/' . $l['id'] . '/revoke')) ?>" class="inline" data-confirm="¿Revocar esta licencia?">
                                <?= csrf_field() ?>
                                <button class="text-xs font-semibold text-rose-600 hover:text-rose-500">Revocar</button>
                            </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Sin licencias emitidas.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- TAB: Auditoría -->
    <div x-show="tab === 'audit'" x-cloak class="kyros-card overflow-hidden">
        <div class="p-4 border-b border-slate-200"><h3 class="font-semibold">Audit log del tenant (últimos 30)</h3></div>
        <ul class="divide-y divide-slate-100 max-h-[600px] overflow-y-auto">
            <?php foreach ($audit as $a): ?>
            <li class="p-3 flex items-start gap-3 text-sm">
                <div class="flex-1 min-w-0">
                    <div class="font-mono text-xs text-slate-700"><?= e($a['event']) ?></div>
                    <div class="text-xs text-slate-500"><?= e($a['actor_name'] ?? '—') ?> · <?= e($a['ip'] ?? '—') ?></div>
                </div>
                <span class="text-xs text-slate-400 font-mono whitespace-nowrap"><?= e(date('m-d H:i', strtotime($a['created_at']))) ?></span>
            </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <!-- TAB: Configuración -->
    <div x-show="tab === 'settings'" x-cloak class="kyros-card p-5">
        <h3 class="font-semibold mb-4">Información del tenant</h3>
        <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'])) ?>" class="space-y-4">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Nombre</label>
                    <input name="name" value="<?= e($tenant['name']) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">País</label>
                    <input name="country" value="<?= e($tenant['country']) ?>" maxlength="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm uppercase">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Email facturación</label>
                    <input name="billing_email" value="<?= e($tenant['billing_email'] ?? '') ?>" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Teléfono facturación</label>
                    <input name="billing_phone" value="<?= e($tenant['billing_phone'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan</label>
                <select name="plan_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <?php foreach ($plans as $p): ?>
                    <option value="<?= (int)$p['id'] ?>" <?= (int)$tenant['plan_id'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Notas internas</label>
                <textarea name="notes" rows="3" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><?= e($tenant['notes'] ?? '') ?></textarea>
            </div>
            <button class="btn btn-primary" style="background:#d97706;">Guardar cambios</button>
        </form>
    </div>

    <!-- TAB: Danger zone -->
    <div x-show="tab === 'danger'" x-cloak class="kyros-card p-5 border-2" style="border-color:#fecaca;">
        <div class="flex items-center gap-2 mb-4">
            <svg class="text-rose-500" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            <h3 class="text-lg font-bold text-rose-700">Zona peligrosa</h3>
        </div>

        <!-- Cambiar estado -->
        <div class="border border-slate-200 rounded-lg p-4 mb-3">
            <h4 class="font-semibold text-sm mb-2">Cambiar estado del tenant</h4>
            <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/status')) ?>" class="flex flex-wrap items-end gap-3" data-confirm="¿Confirmas el cambio de estado?">
                <?= csrf_field() ?>
                <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                    <option value="active"     <?= $tenant['status'] === 'active'     ? 'selected' : '' ?>>Activo</option>
                    <option value="trial"      <?= $tenant['status'] === 'trial'      ? 'selected' : '' ?>>Trial</option>
                    <option value="suspended"  <?= $tenant['status'] === 'suspended'  ? 'selected' : '' ?>>Suspendido</option>
                    <option value="cancelled"  <?= $tenant['status'] === 'cancelled'  ? 'selected' : '' ?>>Cancelado</option>
                    <option value="pending"    <?= $tenant['status'] === 'pending'    ? 'selected' : '' ?>>Pendiente</option>
                </select>
                <input name="reason" placeholder="Motivo (opcional)" class="rounded-lg border border-slate-200 px-3 py-2 text-sm flex-1 min-w-[200px]">
                <button class="btn btn-secondary">Aplicar</button>
            </form>
        </div>

        <!-- Eliminar -->
        <div class="border-2 border-rose-200 bg-rose-50/40 rounded-lg p-4">
            <h4 class="font-semibold text-sm mb-2 text-rose-700">Eliminar tenant permanentemente</h4>
            <p class="text-xs text-rose-700/80 mb-3">Esto borra todo: usuarios, casos, clientes, documentos, audit. <strong>No se puede deshacer.</strong></p>
            <form method="POST" action="<?= e(url('admin/tenants/' . $tenant['id'] . '/delete')) ?>" class="flex flex-wrap items-end gap-3" data-confirm="ÚLTIMA CONFIRMACIÓN: ¿Eliminar tenant '<?= e($tenant['slug']) ?>' y todos sus datos?">
                <?= csrf_field() ?>
                <input name="confirm_slug" placeholder="Escribe el slug exacto: <?= e($tenant['slug']) ?>" class="rounded-lg border border-rose-300 px-3 py-2 text-sm flex-1 min-w-[260px] font-mono">
                <button class="btn btn-danger">Eliminar definitivamente</button>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function() {
    if (!window.Chart) return;
    var data = <?= $byDayJson ?>;
    new Chart(document.getElementById('chTenantClients'), {
        type: 'line',
        data: {
            labels: data.map(r => r.d.slice(5)),
            datasets: [{
                data: data.map(r => r.c),
                fill: true,
                borderColor: '#d97706',
                backgroundColor: 'rgba(217,119,6,0.10)',
                tension: 0.3, borderWidth: 2.5, pointRadius: 0
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
