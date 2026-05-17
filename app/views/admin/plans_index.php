<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Planes y precios</h1>
        <p class="mt-1 text-sm text-slate-600"><?= count($plans) ?> planes registrados</p>
    </div>
    <a href="<?= e(url('admin/plans/new')) ?>" class="btn btn-primary" style="background:#d97706;">+ Nuevo plan</a>
</div>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
    <?php foreach ($plans as $p):
        $features = $p['features_json'] ? json_decode($p['features_json'], true) : [];
    ?>
    <div class="kyros-card p-6 relative <?= !$p['is_active'] ? 'opacity-60' : '' ?>">
        <?php if (!$p['is_active']): ?>
        <span class="absolute top-3 right-3 text-[10px] font-bold uppercase bg-rose-100 text-rose-700 px-2 py-0.5 rounded">Inactivo</span>
        <?php elseif (!$p['is_public']): ?>
        <span class="absolute top-3 right-3 text-[10px] font-bold uppercase bg-amber-100 text-amber-700 px-2 py-0.5 rounded">Privado</span>
        <?php endif; ?>
        <div class="flex items-baseline justify-between">
            <h3 class="text-lg font-bold"><?= e($p['name']) ?></h3>
            <span class="text-xs font-mono text-slate-500"><?= e($p['code']) ?></span>
        </div>
        <?php if (!empty($p['description'])): ?>
        <p class="mt-1 text-xs text-slate-500"><?= e($p['description']) ?></p>
        <?php endif; ?>
        <div class="mt-4">
            <span class="text-3xl font-bold tracking-tight">$<?= number_format((float)$p['price_usd'], 0) ?></span>
            <span class="text-xs text-slate-500">/<?= e($p['billing_cycle'] ?? 'mes') ?></span>
        </div>
        <ul class="mt-4 space-y-1 text-xs text-slate-600">
            <li>• <?= (int)$p['max_users'] ?> usuarios</li>
            <li>• <?= number_format((int)$p['max_cases']) ?> casos</li>
            <li>• <?= number_format((int)$p['max_storage_mb']) ?> MB</li>
            <?php if (!empty($features['workflows'])): ?><li>• Workflows</li><?php endif; ?>
            <?php if (!empty($features['two_factor'])): ?><li>• 2FA</li><?php endif; ?>
            <?php if (!empty($features['priority_support'])): ?><li>• Soporte prioritario</li><?php endif; ?>
            <?php if (!empty($features['api_access'])): ?><li>• API access</li><?php endif; ?>
            <?php if (!empty($features['white_label'])): ?><li>• White-label</li><?php endif; ?>
        </ul>
        <div class="mt-5 pt-4 border-t border-slate-200 flex items-center justify-between">
            <span class="text-xs text-slate-500"><?= (int)$p['tenants_count'] ?> tenants en este plan</span>
            <div class="flex gap-2">
                <a href="<?= e(url('admin/plans/' . $p['id'] . '/edit')) ?>" class="text-xs font-semibold text-amber-700 hover:text-amber-600">Editar</a>
                <form method="POST" action="<?= e(url('admin/plans/' . $p['id'] . '/toggle')) ?>" class="inline">
                    <?= csrf_field() ?>
                    <button class="text-xs font-semibold text-slate-500 hover:text-slate-900"><?= $p['is_active'] ? 'Desactivar' : 'Activar' ?></button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
