<div class="mb-6">
    <h1 class="text-3xl font-bold tracking-tight">Seguridad</h1>
    <p class="mt-1 text-sm text-slate-600">Monitoreo cross-tenant — eventos sospechosos, sesiones, 2FA</p>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="kyros-card p-5"><div class="kyros-eyebrow">2FA habilitado</div><div class="text-3xl font-bold mt-1 text-emerald-600"><?= number_format($users_with_2fa) ?></div><div class="text-xs text-slate-500 mt-1">usuarios protegidos</div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">Sin 2FA</div><div class="text-3xl font-bold mt-1 <?= $users_without_2fa > 0 ? 'text-amber-600' : '' ?>"><?= number_format($users_without_2fa) ?></div><div class="text-xs text-slate-500 mt-1">usuarios activos</div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">Logins fallidos 24h</div><div class="text-3xl font-bold mt-1 <?= count($failed_logins) > 0 ? 'text-rose-600' : '' ?>"><?= number_format(array_sum(array_column($failed_logins, 'c'))) ?></div><div class="text-xs text-slate-500 mt-1">desde <?= count($failed_logins) ?> IPs</div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">Impersonations 50</div><div class="text-3xl font-bold mt-1"><?= number_format(count($impersonations)) ?></div><div class="text-xs text-slate-500 mt-1">históricas</div></div>
</div>

<!-- Logins fallidos -->
<div class="kyros-card overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="font-semibold">IPs con logins fallidos (24h)</h3>
        <span class="text-xs text-slate-500">Top 30</span>
    </div>
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">IP</th>
            <th class="px-4 py-3 text-right">Intentos</th>
            <th class="px-4 py-3">Último intento</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($failed_logins as $f):
                $isHigh = (int)$f['c'] >= 10;
            ?>
            <tr class="<?= $isHigh ? 'bg-rose-50/30' : '' ?> hover:bg-slate-50">
                <td class="px-4 py-3 text-sm font-mono"><?= e($f['ip'] ?? '—') ?></td>
                <td class="px-4 py-3 text-right text-sm font-bold <?= $isHigh ? 'text-rose-600' : '' ?>"><?= number_format((int)$f['c']) ?></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= e(date('Y-m-d H:i:s', strtotime($f['last_at']))) ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($failed_logins)): ?>
            <tr><td colspan="3" class="px-4 py-8 text-center text-sm text-slate-500">Sin intentos fallidos en las últimas 24 horas. ✓</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<!-- Permission denied -->
<div class="kyros-card overflow-hidden mb-6">
    <div class="p-4 border-b border-slate-200">
        <h3 class="font-semibold">Permisos denegados (7 días)</h3>
    </div>
    <ul class="divide-y divide-slate-100 max-h-[300px] overflow-y-auto">
        <?php foreach ($permission_denied as $p): ?>
        <li class="p-3 flex items-start gap-3 text-sm">
            <svg class="text-rose-500 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="4.93" y1="4.93" x2="19.07" y2="19.07"/></svg>
            <div class="flex-1 min-w-0">
                <div class="font-medium text-sm"><?= e($p['actor_name'] ?? 'Desconocido') ?> · <span class="text-slate-500"><?= e($p['tenant_name'] ?? '—') ?></span></div>
                <div class="text-xs text-slate-500 font-mono"><?= e($p['ip'] ?? '—') ?> · <?= e(substr($p['context_json'] ?? '', 0, 80)) ?></div>
            </div>
            <span class="text-xs text-slate-400 font-mono whitespace-nowrap"><?= e(date('m-d H:i', strtotime($p['created_at']))) ?></span>
        </li>
        <?php endforeach; ?>
        <?php if (empty($permission_denied)): ?>
        <li class="p-8 text-center text-sm text-slate-500">Sin denegaciones en los últimos 7 días. ✓</li>
        <?php endif; ?>
    </ul>
</div>

<!-- Impersonations -->
<div class="kyros-card overflow-hidden">
    <div class="p-4 border-b border-slate-200">
        <h3 class="font-semibold">Historial de impersonations</h3>
    </div>
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">Super admin</th>
            <th class="px-4 py-3">Como</th>
            <th class="px-4 py-3">Tenant</th>
            <th class="px-4 py-3">Inicio</th>
            <th class="px-4 py-3">Fin</th>
            <th class="px-4 py-3">Razón</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($impersonations as $i):
                $active = empty($i['ended_at']);
            ?>
            <tr class="<?= $active ? 'bg-amber-50/40' : '' ?>">
                <td class="px-4 py-3 text-sm"><?= e($i['sa_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-sm"><?= e($i['target_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($i['target_tenant_id']): ?>
                    <a href="<?= e(url('admin/tenants/' . $i['target_tenant_id'])) ?>" class="text-amber-700 hover:text-amber-600"><?= e($i['tenant_name']) ?></a>
                    <?php else: ?><span class="text-slate-400">—</span><?php endif; ?>
                </td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= e(date('Y-m-d H:i', strtotime($i['started_at']))) ?></td>
                <td class="px-4 py-3 text-xs <?= $active ? 'text-amber-600 font-bold' : 'text-slate-500' ?>"><?= $active ? 'EN CURSO' : e(date('Y-m-d H:i', strtotime($i['ended_at']))) ?></td>
                <td class="px-4 py-3 text-xs text-slate-500 truncate max-w-[180px]"><?= e($i['reason'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($impersonations)): ?>
            <tr><td colspan="6" class="px-4 py-8 text-center text-sm text-slate-500">Sin registros de impersonation.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
