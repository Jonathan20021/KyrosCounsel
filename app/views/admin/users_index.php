<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Usuarios</h1>
        <p class="mt-1 text-sm text-slate-600"><?= count($users) ?> usuarios (todos los tenants)</p>
    </div>
</div>

<form method="GET" class="kyros-card p-4 mb-4 flex flex-wrap gap-3 items-end">
    <div class="flex-1 min-w-[240px]">
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Buscar</label>
        <input name="q" value="<?= e($filters['q']) ?>" placeholder="nombre, email..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>
    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Rol</label>
        <select name="role" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
            <option value="">Todos</option>
            <?php foreach (['super_admin','tenant_admin','attorney','paralegal','staff','client'] as $r): ?>
            <option value="<?= $r ?>" <?= $filters['role'] === $r ? 'selected' : '' ?>><?= e($r) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <button class="btn btn-secondary">Filtrar</button>
</form>

<div class="kyros-card overflow-hidden">
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">Usuario</th>
            <th class="px-4 py-3">Tenant</th>
            <th class="px-4 py-3">Rol</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">2FA</th>
            <th class="px-4 py-3">Último login</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($users as $u): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3">
                    <div class="flex items-center gap-2.5">
                        <div class="h-8 w-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 text-white flex items-center justify-center font-bold text-xs flex-shrink-0">
                            <?= e(strtoupper(mb_substr($u['name'], 0, 1))) ?>
                        </div>
                        <div class="min-w-0">
                            <div class="font-medium text-sm truncate"><?= e($u['name']) ?></div>
                            <div class="text-xs text-slate-500 font-mono truncate"><?= e($u['email']) ?></div>
                        </div>
                    </div>
                </td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($u['tenant_id']): ?>
                    <a href="<?= e(url('admin/tenants/' . $u['tenant_id'])) ?>" class="text-amber-700 hover:text-amber-600"><?= e($u['tenant_name']) ?></a>
                    <?php else: ?>
                    <span class="text-slate-400">—</span>
                    <?php endif; ?>
                </td>
                <td class="px-4 py-3 text-xs"><?= e(str_replace('_', ' ', $u['role'])) ?></td>
                <td class="px-4 py-3"><span class="badge-soft <?= ['active'=>'badge-success','disabled'=>'badge-danger','suspended'=>'badge-warning','invited'=>'badge-info'][$u['status']] ?? 'badge-neutral' ?>"><?= e($u['status']) ?></span></td>
                <td class="px-4 py-3"><?= !empty($u['two_factor_enabled']) ? '<span class="badge-soft badge-success">ON</span>' : '<span class="badge-soft badge-neutral">OFF</span>' ?></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= $u['last_login_at'] ? e(date('Y-m-d H:i', strtotime($u['last_login_at']))) : '—' ?></td>
                <td class="px-4 py-3 text-right">
                    <?php if ($u['role'] !== 'super_admin'): ?>
                    <?php if ($u['status'] === 'active'): ?>
                    <form method="POST" action="<?= e(url('admin/users/' . $u['id'] . '/disable')) ?>" class="inline" data-confirm="¿Deshabilitar a <?= e($u['email']) ?>?">
                        <?= csrf_field() ?>
                        <button class="text-xs font-semibold text-rose-600 hover:text-rose-500">Deshabilitar</button>
                    </form>
                    <?php else: ?>
                    <form method="POST" action="<?= e(url('admin/users/' . $u['id'] . '/enable')) ?>" class="inline">
                        <?= csrf_field() ?>
                        <button class="text-xs font-semibold text-emerald-600 hover:text-emerald-500">Activar</button>
                    </form>
                    <?php endif; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
