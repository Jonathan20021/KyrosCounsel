<div class="mb-6">
    <h1 class="text-3xl font-bold tracking-tight">Sistema</h1>
    <p class="mt-1 text-sm text-slate-600">Configuración global del SaaS</p>
</div>

<!-- Mantenimiento -->
<div class="kyros-card p-5 mb-4 <?= $maintenance ? 'border-2 border-rose-300 bg-rose-50/40' : '' ?>">
    <div class="flex items-start justify-between gap-3 flex-wrap">
        <div>
            <h3 class="font-semibold flex items-center gap-2">
                <span class="h-2.5 w-2.5 rounded-full <?= $maintenance ? 'bg-rose-500 animate-pulse' : 'bg-emerald-500' ?>"></span>
                Modo mantenimiento
            </h3>
            <p class="mt-1 text-sm text-slate-600"><?= $maintenance ? 'ACTIVO — los tenants ven mensaje de mantenimiento.' : 'Inactivo — el sistema opera normalmente.' ?></p>
        </div>
        <form method="POST" action="<?= e(url('admin/system/maintenance')) ?>" data-confirm="<?= $maintenance ? '¿Desactivar modo mantenimiento?' : '⚠ ¿Activar modo mantenimiento? Los tenants no podrán acceder.' ?>">
            <?= csrf_field() ?>
            <button class="<?= $maintenance ? 'btn btn-danger' : 'btn btn-secondary' ?>"><?= $maintenance ? 'Desactivar mantenimiento' : 'Activar mantenimiento' ?></button>
        </form>
    </div>
</div>

<!-- Settings -->
<div class="kyros-card p-5 mb-4">
    <h3 class="font-semibold mb-4">Configuración global</h3>
    <form method="POST" action="<?= e(url('admin/system/settings')) ?>" class="space-y-3">
        <?= csrf_field() ?>
        <?php foreach ($settings as $s): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 items-start py-2 border-b border-slate-100 last:border-0">
            <div>
                <label class="block text-sm font-semibold text-slate-700"><?= e($s['key']) ?></label>
                <p class="text-xs text-slate-500 mt-0.5"><?= e($s['description'] ?? '') ?></p>
            </div>
            <div class="md:col-span-2">
                <?php if (in_array($s['key'], ['maintenance_message','global_announcement'], true)): ?>
                <textarea name="settings[<?= e($s['key']) ?>]" rows="2" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"><?= e($s['value_text'] ?? '') ?></textarea>
                <?php else: ?>
                <input name="settings[<?= e($s['key']) ?>]" value="<?= e($s['value_text'] ?? '') ?>" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <?php endif; ?>
                <p class="mt-1 text-[11px] text-slate-400 font-mono">Actualizado <?= e($s['updated_at']) ?></p>
            </div>
        </div>
        <?php endforeach; ?>
        <button class="btn btn-primary mt-3" style="background:#d97706;">Guardar configuración</button>
    </form>
</div>

<!-- Info del sistema -->
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-4">
    <div class="kyros-card p-5">
        <h3 class="font-semibold mb-3">Información técnica</h3>
        <ul class="space-y-2 text-sm">
            <li class="flex justify-between border-b border-slate-100 py-2"><span class="text-slate-600">PHP</span><strong class="font-mono"><?= e($php_version) ?></strong></li>
            <li class="flex justify-between border-b border-slate-100 py-2"><span class="text-slate-600">MySQL</span><strong class="font-mono"><?= e($mysql_version) ?></strong></li>
            <li class="flex justify-between border-b border-slate-100 py-2"><span class="text-slate-600">Almacenamiento usado</span><strong class="font-mono"><?= number_format($storage_used_mb) ?> MB</strong></li>
            <li class="flex justify-between border-b border-slate-100 py-2"><span class="text-slate-600">Zona horaria</span><strong class="font-mono"><?= e(date_default_timezone_get()) ?></strong></li>
            <li class="flex justify-between py-2"><span class="text-slate-600">Sodium disponible</span><strong><?= function_exists('sodium_crypto_secretbox') ? '<span class="text-emerald-600">✓ Sí</span>' : '<span class="text-amber-600">No (fallback OpenSSL)</span>' ?></strong></li>
        </ul>
    </div>

    <div class="kyros-card p-5">
        <h3 class="font-semibold mb-3">Mantenimiento</h3>
        <p class="text-sm text-slate-600 mb-4">Operaciones de housekeeping del sistema.</p>
        <div class="space-y-2">
            <form method="POST" action="<?= e(url('admin/system/cache/clear')) ?>" data-confirm="¿Limpiar caché y rate-limits viejos?">
                <?= csrf_field() ?>
                <button class="w-full btn btn-secondary text-left">
                    <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="3 6 5 6 21 6"/><path d="M19 6l-2 14a2 2 0 01-2 2H9a2 2 0 01-2-2L5 6"/></svg>
                    Limpiar caché y rate-limits
                </button>
            </form>
            <a href="<?= e(url('admin/audit')) ?>" class="block w-full btn btn-secondary text-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                Ver audit log completo
            </a>
            <a href="<?= e(url('admin/health.json')) ?>" target="_blank" class="block w-full btn btn-secondary text-left">
                <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Health check JSON ↗
            </a>
            <p class="text-[11px] text-slate-500 mt-1">URL pública para monitoreo: <code class="font-mono"><?= e(url('admin/health.json')) ?></code></p>
        </div>
    </div>
</div>
