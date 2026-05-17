<div class="space-y-6">
    <h1 class="text-2xl font-bold tracking-tight">Facturacion</h1>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold mb-3">Plan actual</h3>
            <div class="text-3xl font-bold"><?= e($tenant['plan_name'] ?? 'Sin plan') ?></div>
            <div class="text-sm text-slate-500 mt-1">$<?= e(number_format((float)($tenant['price_usd'] ?? 0), 2)) ?>/mes</div>
            <div class="mt-4 text-sm">
                <div>Estado del bufete: <strong><?= e($tenant['status']) ?></strong></div>
                <?php if ($tenant['trial_ends_at']): ?>
                <div>Trial termina: <strong><?= e($tenant['trial_ends_at']) ?></strong></div>
                <?php endif; ?>
                <?php if ($sub && $sub['next_billing_at']): ?>
                <div>Proxima facturacion: <strong><?= e($sub['next_billing_at']) ?></strong></div>
                <?php endif; ?>
            </div>
        </div>

        <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <h3 class="text-base font-semibold mb-3">Uso vs limites</h3>
            <?php
            $items = [
                ['Usuarios', $usage['users'], (int)($tenant['max_users'] ?? 0)],
                ['Casos',    $usage['cases'], (int)($tenant['max_cases'] ?? 0)],
                ['Storage (MB)', $usage['storage_mb'], (int)($tenant['max_storage_mb'] ?? 0)],
            ];
            foreach ($items as [$label, $u, $m]):
                $pct = $m > 0 ? min(100, round($u / $m * 100)) : 0;
                $color = $pct >= 90 ? 'red' : ($pct >= 70 ? 'amber' : 'indigo');
            ?>
            <div class="mb-4">
                <div class="flex justify-between text-sm">
                    <span><?= e($label) ?></span>
                    <span class="text-slate-500"><?= e($u) ?> / <?= e($m) ?></span>
                </div>
                <div class="mt-1 h-2 rounded-full bg-slate-200 overflow-hidden">
                    <div class="h-full bg-<?= $color ?>-500" style="width: <?= $pct ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <?php if (can('billing.manage')): ?>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold mb-4">Cambiar plan</h3>
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <?php foreach ($plans as $p): $current = (int)$p['id'] === (int)$tenant['plan_id']; ?>
            <div class="rounded-xl border <?= $current ? 'border-indigo-500 ring-2 ring-indigo-200' : 'border-slate-200' ?> p-5">
                <div class="text-lg font-semibold"><?= e($p['name']) ?></div>
                <div class="text-2xl font-bold mt-1">$<?= (int)$p['price_usd'] ?><span class="text-sm text-slate-500">/mes</span></div>
                <ul class="mt-3 text-xs text-slate-600 space-y-1">
                    <li><?= (int)$p['max_users'] ?> usuarios</li>
                    <li><?= (int)$p['max_cases'] ?> casos</li>
                    <li><?= (int)$p['max_storage_mb'] ?> MB</li>
                </ul>
                <?php if (!$current): ?>
                <form method="POST" action="<?= e(tenant_url('billing/change-plan')) ?>" class="mt-4">
                    <?= csrf_field() ?>
                    <input type="hidden" name="plan_id" value="<?= (int)$p['id'] ?>">
                    <button class="w-full rounded-md bg-indigo-600 py-1.5 text-sm font-semibold text-white hover:bg-indigo-500">Cambiar a <?= e($p['name']) ?></button>
                </form>
                <?php else: ?>
                <div class="mt-4 text-center text-sm text-emerald-600 font-medium">Plan actual</div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
        <p class="mt-4 text-xs text-slate-500">Sprint actual: cambio de plan inmediato sin pago real. Integracion Stripe/PayPal en fase posterior.</p>
    </div>
    <?php endif; ?>
</div>
