<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold tracking-tight">Usuarios del bufete</h1>
        <?php if (can('users.create')): ?>
        <a href="<?= e(tenant_url('users/new')) ?>" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Nuevo usuario</a>
        <?php endif; ?>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Email</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Rol</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">2FA</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Ultimo login</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($users as $u): ?>
                <tr class="hover:bg-slate-50">
                    <td class="px-4 py-3 text-sm font-medium">
                        <div class="flex items-center gap-2.5">
                            <?= avatar_render($u['name'], 'md', 'user-' . (int)$u['id']) ?>
                            <div class="min-w-0">
                                <div class="font-semibold truncate"><?= e($u['name']) ?></div>
                                <?php if ((int)$u['id'] === (int)$_SESSION['user_id']): ?>
                                <div class="text-[10px] uppercase font-bold tracking-wider text-indigo-600">Tú</div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700"><?= e($u['email']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= e(ucfirst(str_replace('_', ' ', $u['role']))) ?></td>
                    <td class="px-4 py-3 text-sm"><?= $u['two_factor_enabled'] ? '✓' : '—' ?></td>
                    <td class="px-4 py-3 text-sm text-slate-500"><?= e($u['last_login_at'] ?? 'Nunca') ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php $colors = ['active' => 'emerald', 'suspended' => 'red', 'invited' => 'amber', 'disabled' => 'slate']; ?>
                        <span class="inline-flex items-center rounded-full bg-<?= $colors[$u['status']] ?>-100 px-2.5 py-0.5 text-xs"><?= e($u['status']) ?></span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <?php if (can('users.update') && (int)$u['id'] !== (int)$_SESSION['user_id']): ?>
                        <form method="POST" action="<?= e(tenant_url('users/' . $u['id'] . '/status')) ?>">
                            <?= csrf_field() ?>
                            <button class="text-xs text-indigo-600 hover:underline">
                                <?= $u['status'] === 'active' ? 'Suspender' : 'Activar' ?>
                            </button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
