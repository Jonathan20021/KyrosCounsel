<?php
$isEdit = !empty($plan);
$features = $isEdit && !empty($plan['features_json']) ? json_decode($plan['features_json'], true) : [];
$action = $isEdit ? url('admin/plans/' . $plan['id']) : url('admin/plans');
?>
<div class="mb-6">
    <a href="<?= e(url('admin/plans')) ?>" class="text-sm text-slate-500 hover:text-slate-900">← Planes</a>
    <h1 class="mt-1 text-3xl font-bold tracking-tight"><?= $isEdit ? 'Editar plan' : 'Nuevo plan' ?></h1>
</div>

<form method="POST" action="<?= e($action) ?>" class="kyros-card p-6 space-y-5 max-w-3xl">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Nombre</label>
            <input name="name" required value="<?= e($plan['name'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Código</label>
            <input name="code" required pattern="[a-z0-9_-]+" value="<?= e($plan['code'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-mono">
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Descripción</label>
        <input name="description" value="<?= e($plan['description'] ?? '') ?>" placeholder="Resumen corto..." class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Precio USD</label>
            <input name="price_usd" type="number" step="0.01" min="0" required value="<?= e($plan['price_usd'] ?? 0) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Ciclo</label>
            <select name="billing_cycle" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <?php foreach (['monthly'=>'Mensual','yearly'=>'Anual','lifetime'=>'Lifetime'] as $k=>$v): ?>
                <option value="<?= $k ?>" <?= ($plan['billing_cycle'] ?? 'monthly') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Orden</label>
            <input name="sort_order" type="number" value="<?= e($plan['sort_order'] ?? 0) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
    </div>

    <hr class="border-slate-200">
    <h3 class="text-sm font-semibold">Límites</h3>
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Usuarios máx</label>
            <input name="max_users" type="number" min="1" value="<?= e($plan['max_users'] ?? 5) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Casos máx</label>
            <input name="max_cases" type="number" min="1" value="<?= e($plan['max_cases'] ?? 100) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Storage MB</label>
            <input name="max_storage_mb" type="number" min="0" value="<?= e($plan['max_storage_mb'] ?? 1024) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
    </div>

    <hr class="border-slate-200">
    <h3 class="text-sm font-semibold">Features incluidos</h3>
    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Emails/mes</label>
            <input name="feat_emails" type="number" min="0" value="<?= e($features['emails'] ?? 0) ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div class="space-y-2 pt-5">
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feat_workflows" <?= !empty($features['workflows']) ? 'checked' : '' ?>> Workflows automáticos</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feat_two_factor" <?= !empty($features['two_factor']) ? 'checked' : '' ?>> Autenticación 2FA</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feat_priority_support" <?= !empty($features['priority_support']) ? 'checked' : '' ?>> Soporte prioritario</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feat_api_access" <?= !empty($features['api_access']) ? 'checked' : '' ?>> API access</label>
            <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="feat_white_label" <?= !empty($features['white_label']) ? 'checked' : '' ?>> White-label</label>
        </div>
    </div>

    <hr class="border-slate-200">
    <div class="flex items-center gap-4">
        <label class="flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_active" <?= ($plan['is_active'] ?? 1) ? 'checked' : '' ?>> Plan activo</label>
        <label class="flex items-center gap-2 text-sm font-medium"><input type="checkbox" name="is_public" <?= ($plan['is_public'] ?? 1) ? 'checked' : '' ?>> Visible públicamente</label>
    </div>

    <div class="flex items-center gap-3">
        <button class="btn btn-primary" style="background:#d97706;"><?= $isEdit ? 'Guardar' : 'Crear plan' ?></button>
        <a href="<?= e(url('admin/plans')) ?>" class="btn btn-ghost text-slate-500">Cancelar</a>
    </div>
</form>
