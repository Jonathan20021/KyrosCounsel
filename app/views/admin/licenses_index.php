<div class="mb-6">
    <h1 class="text-3xl font-bold tracking-tight">Licencias</h1>
    <p class="mt-1 text-sm text-slate-600">Generar y revocar claves de licencia globales</p>
</div>

<!-- Crear licencia rápida -->
<div class="kyros-card p-5 mb-4">
    <h3 class="font-semibold mb-3">Generar licencia</h3>
    <form method="POST" action="<?= e(url('admin/licenses')) ?>" class="grid grid-cols-1 md:grid-cols-5 gap-3 items-end">
        <?= csrf_field() ?>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Tenant</label>
            <select name="tenant_id" required class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">— Seleccionar —</option>
                <?php foreach ($tenants as $t): ?>
                <option value="<?= (int)$t['id'] ?>"><?= e($t['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan</label>
            <select name="plan_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="">Sin plan</option>
                <?php foreach ($plans as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Vigencia días</label>
            <input name="valid_days" type="number" min="0" placeholder="0 = no expira" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="md:col-span-1">
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Notas</label>
            <input name="notes" placeholder="opcional" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <button class="btn btn-primary" style="background:#d97706;">Generar</button>
    </form>
</div>

<!-- Lista -->
<div class="kyros-card overflow-hidden">
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">Clave</th>
            <th class="px-4 py-3">Tenant</th>
            <th class="px-4 py-3">Plan</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Emitida</th>
            <th class="px-4 py-3">Expira</th>
            <th class="px-4 py-3">Notas</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($licenses as $l): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-xs font-mono select-all"><?= e($l['license_key']) ?></td>
                <td class="px-4 py-3 text-sm">
                    <?php if ($l['tenant_id']): ?>
                    <a href="<?= e(url('admin/tenants/' . $l['tenant_id'])) ?>" class="text-amber-700 hover:text-amber-600"><?= e($l['tenant_name']) ?></a>
                    <?php else: ?><span class="text-slate-400">global</span><?php endif; ?>
                </td>
                <td class="px-4 py-3 text-sm"><?= e($l['plan_name'] ?? '—') ?></td>
                <td class="px-4 py-3"><span class="badge-soft <?= ['active'=>'badge-success','revoked'=>'badge-danger','expired'=>'badge-neutral'][$l['status']] ?>"><?= e($l['status']) ?></span></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= e(date('Y-m-d', strtotime($l['issued_at']))) ?></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= $l['expires_at'] ? e(date('Y-m-d', strtotime($l['expires_at']))) : 'No expira' ?></td>
                <td class="px-4 py-3 text-xs text-slate-500 truncate max-w-[180px]" title="<?= e($l['notes'] ?? '') ?>"><?= e($l['notes'] ?? '') ?></td>
                <td class="px-4 py-3 text-right">
                    <?php if ($l['status'] === 'active'): ?>
                    <form method="POST" action="<?= e(url('admin/licenses/' . $l['id'] . '/revoke')) ?>" class="inline" data-confirm="¿Revocar esta licencia?">
                        <?= csrf_field() ?>
                        <button class="text-xs font-semibold text-rose-600 hover:text-rose-500">Revocar</button>
                    </form>
                    <?php endif; ?>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($licenses)): ?>
            <tr><td colspan="8" class="px-4 py-12 text-center text-sm text-slate-500">Aún no hay licencias.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
