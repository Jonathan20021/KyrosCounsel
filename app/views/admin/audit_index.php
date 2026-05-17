<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Auditoría global</h1>
        <p class="mt-1 text-sm text-slate-600">Eventos cross-tenant — últimos 300</p>
    </div>
    <a href="<?= e(url('admin/export/audit.csv')) ?>" class="btn btn-secondary">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Exportar CSV (10k)
    </a>
</div>

<form method="GET" class="kyros-card p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[240px]">
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Evento</label>
        <input name="event" value="<?= e($filters['event']) ?>" placeholder="auth.login.failed, tenant.*, ..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-mono">
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Tenant</label>
        <select name="tenant_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">Todos</option>
            <?php foreach ($tenants as $t): ?>
            <option value="<?= (int)$t['id'] ?>" <?= $filters['tenantId'] === (int)$t['id'] ? 'selected' : '' ?>><?= e($t['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary">Filtrar</button>
    <a href="<?= e(url('admin/audit')) ?>" class="btn btn-ghost text-slate-500">Limpiar</a>
</form>

<div class="kyros-card overflow-hidden">
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">Cuándo</th>
            <th class="px-4 py-3">Evento</th>
            <th class="px-4 py-3">Actor</th>
            <th class="px-4 py-3">Tenant</th>
            <th class="px-4 py-3">IP</th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100 max-h-[700px]">
            <?php foreach ($events as $e):
                $isFailed = $e['event'] === 'auth.login.failed';
                $isImpersonate = strpos($e['event'], 'admin.impersonate') === 0;
                $rowClass = $isFailed ? 'bg-rose-50/30' : ($isImpersonate ? 'bg-amber-50/40' : '');
            ?>
            <tr class="<?= $rowClass ?> hover:bg-slate-50">
                <td class="px-4 py-3 text-xs text-slate-500 font-mono whitespace-nowrap"><?= e(date('Y-m-d H:i:s', strtotime($e['created_at']))) ?></td>
                <td class="px-4 py-3"><span class="text-xs font-mono <?= $isFailed ? 'text-rose-700 font-semibold' : '' ?>"><?= e($e['event']) ?></span></td>
                <td class="px-4 py-3 text-xs"><?= $e['actor_name'] ? e($e['actor_name']) : '<span class="text-slate-400">—</span>' ?><?php if ($e['actor_email']): ?><div class="text-[10px] text-slate-500 font-mono"><?= e($e['actor_email']) ?></div><?php endif; ?></td>
                <td class="px-4 py-3 text-xs">
                    <?php if ($e['tenant_id']): ?>
                    <a href="<?= e(url('admin/tenants/' . $e['tenant_id'])) ?>" class="text-amber-700 hover:text-amber-600"><?= e($e['tenant_name'] ?? $e['tenant_slug']) ?></a>
                    <?php else: ?>
                    <span class="text-slate-400">global</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-xs font-mono text-slate-500"><?= e($e['ip'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($events)): ?>
            <tr><td colspan="5" class="px-4 py-12 text-center text-sm text-slate-500">Sin eventos.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
