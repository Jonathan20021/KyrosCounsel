<?php
$user = current_user();
?>
<div class="min-h-full">
    <nav class="border-b border-slate-200 bg-white">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-16 items-center justify-between">
                <div class="flex items-center gap-3">
                    <div class="flex h-8 w-8 items-center justify-center rounded-md bg-amber-500 text-white font-bold">K</div>
                    <span class="text-lg font-semibold tracking-tight"><?= e(APP_NAME) ?></span>
                    <span class="ml-2 rounded-full bg-amber-100 px-2.5 py-0.5 text-xs font-medium text-amber-800">
                        Super Admin
                    </span>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right text-sm">
                        <div class="font-medium"><?= e($user['name']) ?></div>
                        <div class="text-xs text-slate-500"><?= e($user['email']) ?></div>
                    </div>
                    <form method="POST" action="<?= e(url('logout')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit"
                                class="inline-flex h-9 items-center rounded-md border border-slate-300 bg-white px-3 text-sm font-medium text-slate-700 shadow-sm hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2">
                            Cerrar sesion
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <main class="mx-auto max-w-7xl px-4 py-8 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Panel Super Admin</h1>
                    <p class="mt-1 text-sm text-slate-600">Gestion global de tenants. (CRUD completo en Sprint 3.)</p>
                </div>
            </div>

            <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
                <table class="min-w-full divide-y divide-slate-200">
                    <thead class="bg-slate-50">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">ID</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Slug</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Pais</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Plan</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Creado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-200 bg-white">
                        <?php if (!empty($tenants)): foreach ($tenants as $t): ?>
                        <tr class="hover:bg-slate-50">
                            <td class="px-4 py-3 text-sm font-mono text-slate-700"><?= (int)$t['id'] ?></td>
                            <td class="px-4 py-3 text-sm font-mono text-indigo-600"><?= e($t['slug']) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-900"><?= e($t['name']) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-700"><?= e($t['country']) ?></td>
                            <td class="px-4 py-3 text-sm text-slate-700"><?= e($t['plan']) ?></td>
                            <td class="px-4 py-3 text-sm">
                                <?php if ($t['status'] === 'active'): ?>
                                    <span class="inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-0.5 text-xs font-medium text-emerald-800"><?= e($t['status']) ?></span>
                                <?php else: ?>
                                    <span class="inline-flex items-center rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700"><?= e($t['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="px-4 py-3 text-sm text-slate-500"><?= e($t['created_at']) ?></td>
                        </tr>
                        <?php endforeach; else: ?>
                        <tr><td colspan="7" class="px-4 py-8 text-center text-sm text-slate-500">No hay tenants registrados.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
