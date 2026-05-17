<div class="mb-6">
    <a href="<?= e(url('admin/tenants')) ?>" class="text-sm text-slate-500 hover:text-slate-900">← Tenants</a>
    <h1 class="mt-1 text-3xl font-bold tracking-tight">Nuevo tenant</h1>
    <p class="mt-1 text-sm text-slate-600">Aprovisiona un nuevo bufete con plan asignado.</p>
</div>

<form method="POST" action="<?= e(url('admin/tenants')) ?>" class="kyros-card p-6 space-y-5 max-w-3xl"
      x-data="{ name: '', slug: '', auto() { if (!this.slug || this.slug === this._prev) { this.slug = this.name.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g,'').replace(/[^a-z0-9\s-]/g,'').trim().replace(/\s+/g,'-'); this._prev = this.slug; } }, _prev: '' }">
    <?= csrf_field() ?>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Nombre del bufete</label>
            <input name="name" required x-model="name" @input="auto()" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Slug (URL)</label>
            <input name="slug" required pattern="[a-z0-9][a-z0-9-]+" x-model="slug" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-mono">
            <p class="mt-1 text-[11px] text-slate-500">a-z, 0-9 y guiones</p>
        </div>
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">País</label>
            <select name="country" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <?php foreach (COUNTRIES as $c => $n): ?>
                <option value="<?= e($c) ?>" <?= $c === 'US' ? 'selected' : '' ?>><?= e($n) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div>
            <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Plan</label>
            <select name="plan_id" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <?php foreach ($plans as $p): ?>
                <option value="<?= (int)$p['id'] ?>"><?= e($p['name']) ?> — $<?= (int)$p['price_usd'] ?>/<?= e($p['billing_cycle'] ?? 'mes') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div>
        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500 mb-1">Email de facturación (opcional)</label>
        <input name="billing_email" type="email" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
    </div>

    <div class="flex items-center gap-3 pt-3 border-t border-slate-200">
        <button class="btn btn-primary" style="background:#d97706;">Crear tenant</button>
        <a href="<?= e(url('admin/tenants')) ?>" class="btn btn-ghost text-slate-500">Cancelar</a>
    </div>
</form>
