<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Tenants</h1>
        <p class="mt-1 text-sm text-slate-600"><?= count($tenants) ?> resultados</p>
    </div>
    <a href="<?= e(url('admin/tenants/new')) ?>" class="btn btn-primary" style="background:#d97706;">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Nuevo tenant
    </a>
</div>

<!-- Filtros -->
<form method="GET" class="kyros-card p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[200px]">
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Buscar</label>
        <input name="q" value="<?= e($filters['q']) ?>" placeholder="nombre, slug, email..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm focus:ring-2 focus:ring-amber-500/30 focus:border-amber-500">
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Estado</label>
        <select name="status" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">Todos</option>
            <?php foreach (['active','trial','suspended','cancelled','pending'] as $s): ?>
            <option value="<?= $s ?>" <?= $filters['status'] === $s ? 'selected' : '' ?>><?= e($s) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">País</label>
        <input name="country" value="<?= e($filters['country']) ?>" maxlength="2" placeholder="US" class="rounded-lg border border-slate-200 px-3 py-2 text-sm w-20 uppercase">
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan</label>
        <select name="plan_id" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">Todos</option>
            <?php foreach ($plans as $p): ?>
            <option value="<?= (int)$p['id'] ?>" <?= $filters['planId'] === (int)$p['id'] ? 'selected' : '' ?>><?= e($p['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Orden</label>
        <select name="sort" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="created_desc" <?= $filters['sort'] === 'created_desc' ? 'selected' : '' ?>>Más nuevos</option>
            <option value="created_asc"  <?= $filters['sort'] === 'created_asc'  ? 'selected' : '' ?>>Más viejos</option>
            <option value="name"         <?= $filters['sort'] === 'name'         ? 'selected' : '' ?>>Nombre A-Z</option>
            <option value="revenue"      <?= $filters['sort'] === 'revenue'      ? 'selected' : '' ?>>Mayor ingreso</option>
        </select>
    </div>
    <button class="btn btn-secondary">Aplicar</button>
    <a href="<?= e(url('admin/tenants')) ?>" class="btn btn-ghost text-slate-500">Limpiar</a>
</form>

<!-- Tabla con bulk actions -->
<form method="POST" action="<?= e(url('admin/tenants/bulk')) ?>" x-data="{ selected: [] }">
    <?= csrf_field() ?>

    <!-- Bulk action bar (sticky cuando hay selección) -->
    <div x-show="selected.length > 0" x-cloak
         class="mb-3 kyros-card p-3 flex flex-wrap items-center gap-3 border-2 border-amber-300 bg-amber-50/50">
        <span class="text-sm font-semibold text-amber-900"><span x-text="selected.length"></span> seleccionados</span>
        <select name="bulk_action" class="rounded-lg border border-slate-200 px-3 py-1.5 text-sm">
            <option value="">— Acción —</option>
            <option value="activate">Activar</option>
            <option value="suspend">Suspender</option>
            <option value="cancel">Cancelar</option>
            <option value="extend_trial">Extender trial</option>
            <option value="change_plan">Cambiar plan</option>
        </select>
        <select name="plan_id" class="rounded-lg border border-slate-200 px-2 py-1.5 text-sm" title="Para 'cambiar plan'">
            <option value="">Plan...</option>
            <?php foreach ($plans as $pp): ?>
            <option value="<?= (int)$pp['id'] ?>"><?= e($pp['name']) ?></option>
            <?php endforeach; ?>
        </select>
        <input type="number" name="days" value="14" min="1" max="365" class="w-20 rounded-lg border border-slate-200 px-2 py-1.5 text-sm" title="Días para 'extend_trial'">
        <button class="btn btn-primary" style="background:#d97706;" data-confirm="¿Aplicar a los seleccionados?">Aplicar</button>
        <button type="button" @click="selected = []" class="btn btn-ghost text-slate-500">Deseleccionar</button>
    </div>

<div class="kyros-card overflow-hidden">
    <div class="overflow-x-auto">
    <table class="kyros-table w-full">
        <thead class="bg-slate-50">
            <tr class="text-left text-xs font-semibold uppercase tracking-wider text-slate-500">
                <th class="px-3 py-3 w-10"><input type="checkbox" @change="selected = $event.target.checked ? Array.from(document.querySelectorAll('[data-tenant-id]')).map(el => el.dataset.tenantId) : []" class="rounded"></th>
                <th class="px-4 py-3">Tenant</th>
                <th class="px-4 py-3">Plan</th>
                <th class="px-4 py-3">Estado</th>
                <th class="px-4 py-3 text-right">MRR</th>
                <th class="px-4 py-3 text-right">Usuarios</th>
                <th class="px-4 py-3 text-right">Casos</th>
                <th class="px-4 py-3">Creado</th>
                <th class="px-4 py-3"></th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-100">
            <?php if (!empty($tenants)): foreach ($tenants as $t):
                $statusBadge = ['active'=>'badge-success','trial'=>'badge-info','suspended'=>'badge-warning','cancelled'=>'badge-danger','pending'=>'badge-neutral'][$t['status']] ?? 'badge-neutral';
            ?>
            <tr class="hover:bg-slate-50" :class="selected.includes('<?= (int)$t['id'] ?>') ? 'bg-amber-50/40' : ''">
                <td class="px-3 py-3">
                    <input type="checkbox" name="ids[]" value="<?= (int)$t['id'] ?>" data-tenant-id="<?= (int)$t['id'] ?>"
                           x-model="selected" class="rounded">
                </td>
                <td class="px-4 py-3">
                    <a href="<?= e(url('admin/tenants/' . $t['id'])) ?>" class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-lg bg-gradient-to-br from-amber-500 to-orange-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                            <?= e(strtoupper(mb_substr($t['name'], 0, 1))) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="font-semibold truncate"><?= e($t['name']) ?></div>
                            <div class="text-xs text-slate-500 font-mono truncate"><?= e($t['slug']) ?> · <?= e($t['country']) ?></div>
                        </div>
                    </a>
                </td>
                <td class="px-4 py-3 text-sm"><?= e($t['plan_name'] ?? '—') ?></td>
                <td class="px-4 py-3"><span class="badge-soft <?= $statusBadge ?>"><?= e($t['status']) ?></span></td>
                <td class="px-4 py-3 text-right text-sm font-mono">$<?= number_format((float)$t['monthly_revenue'], 0) ?></td>
                <td class="px-4 py-3 text-right text-sm"><?= number_format((int)$t['users_count']) ?></td>
                <td class="px-4 py-3 text-right text-sm"><?= number_format((int)$t['cases_count']) ?></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= e(date('Y-m-d', strtotime($t['created_at']))) ?></td>
                <td class="px-4 py-3 text-right">
                    <a href="<?= e(url('admin/tenants/' . $t['id'])) ?>" class="text-xs font-semibold text-amber-700 hover:text-amber-600">Ver →</a>
                </td>
            </tr>
            <?php endforeach; else: ?>
            <tr><td colspan="9" class="px-4 py-12 text-center text-sm text-slate-500">
                <svg class="mx-auto mb-3 text-slate-300" xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                Sin resultados con esos filtros.
            </td></tr>
            <?php endif; ?>
        </tbody>
    </table>
    </div>
</div>
</form>

<div class="mt-4 flex flex-wrap gap-2 items-center text-xs">
    <span class="text-slate-500">Exportar:</span>
    <a href="<?= e(url('admin/export/tenants.csv')) ?>" class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md border border-slate-200 hover:bg-slate-50 text-slate-700 font-medium">
        <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        CSV (todos)
    </a>
</div>
