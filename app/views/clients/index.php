<?php
$f = $filters;
$base = tenant_url('clients');
function _build_url_without_c($base, $f, $remove) {
    $kept = [];
    foreach ($f as $k => $v) { if ($k === $remove) continue; if ($v !== '' && $v !== 0) $kept[$k] = $v; }
    return $base . ($kept ? '?' . http_build_query($kept) : '');
}
$any_filter = ($f['q'] || $f['status'] || $f['nationality'] || $f['residence']);
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Cartera</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight">Clientes</h1>
                <?php
                $help_title = 'Clientes (CRM)';
                $help_body  = 'Aquí registras a las personas que representas. El pasaporte y A-number se cifran automáticamente al guardar.';
                $help_tips  = [
                    'Buscar por pasaporte funciona aunque esté cifrado (blind index)',
                    'Selecciona varios y aplica acciones bulk',
                    'Genera enlace de portal para que tu cliente vea su caso sin login',
                ];
                $help_lesson = 3;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="mt-1 text-sm text-slate-600"><?= count($clients) ?> registros · datos sensibles cifrados</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= e(tenant_url('export/clients')) ?>" class="btn btn-secondary" title="Descargar CSV">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                CSV
            </a>
            <?php if (can('clients.create')): ?>
            <a href="<?= e(tenant_url('clients/new')) ?>" class="btn btn-primary">+ Nuevo cliente</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kyros-card p-4" x-data="{ open: false }">
        <form method="GET" action="<?= e($base) ?>" class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[200px] relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input name="q" value="<?= e($f['q']) ?>" placeholder="Nombre, email, pasaporte, A-number..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" @click="open = !open" class="btn btn-secondary">Filtros <span x-show="!open">▾</span><span x-show="open">▴</span></button>
                <button class="btn btn-primary">Aplicar</button>
                <?php if ($any_filter): ?>
                <a href="<?= e($base) ?>" class="btn btn-ghost text-xs">Limpiar todo</a>
                <?php endif; ?>
            </div>

            <div x-show="open" x-cloak class="grid grid-cols-1 gap-3 md:grid-cols-3 pt-3 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
                    <select name="status" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Cualquiera —</option>
                        <?php foreach (['active' => 'Activo', 'inactive' => 'Inactivo', 'archived' => 'Archivado'] as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Nacionalidad</label>
                    <select name="nationality" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Cualquiera —</option>
                        <?php foreach (COUNTRIES as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['nationality'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Pais de residencia</label>
                    <select name="residence" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Cualquiera —</option>
                        <?php foreach (COUNTRIES as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['residence'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
        </form>

        <?php if ($any_filter): ?>
        <div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500">Filtros activos:</span>
            <?php if ($f['q']): ?>
            <span class="chip chip-primary">q: "<?= e($f['q']) ?>"<a href="<?= e(_build_url_without_c($base, $f, 'q')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['status']): ?>
            <span class="chip chip-primary">Estado: <?= e($f['status']) ?><a href="<?= e(_build_url_without_c($base, $f, 'status')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['nationality']): ?>
            <span class="chip chip-primary">Nacionalidad: <?= e(country_name($f['nationality'])) ?><a href="<?= e(_build_url_without_c($base, $f, 'nationality')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['residence']): ?>
            <span class="chip chip-primary">Reside en: <?= e(country_name($f['residence'])) ?><a href="<?= e(_build_url_without_c($base, $f, 'residence')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="kyros-card overflow-hidden kyros-responsive-wrap">
        <?php if (empty($clients)): ?>
        <?php if ($any_filter): ?>
        <div class="empty-state py-12">
            <div class="empty-state-icon">🔍</div>
            <h3 class="text-lg font-semibold mt-2">Sin resultados</h3>
            <p class="mt-1 text-sm text-slate-500">Ningún cliente coincide con los filtros aplicados.</p>
            <a href="<?= e($base) ?>" class="mt-4 inline-block btn btn-secondary">Limpiar filtros</a>
        </div>
        <?php else: ?>
        <div class="px-6 py-12 lg:py-16">
            <div class="mx-auto max-w-lg text-center">
                <!-- Hero ilustración con orbes -->
                <div class="relative mx-auto h-32 w-32 mb-6">
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 30% 30%, rgba(99,102,241,0.25), transparent 70%); filter: blur(20px);"></div>
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 70% 70%, rgba(139,92,246,0.20), transparent 70%); filter: blur(20px);"></div>
                    <div class="relative h-full flex items-center justify-center">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-4xl flex items-center justify-center shadow-xl shadow-indigo-500/30">👤</div>
                    </div>
                </div>
                <h3 class="text-2xl font-bold tracking-tight">Aún no tienes clientes</h3>
                <p class="mt-2 text-slate-600">Empieza creando tu primer cliente. Su pasaporte, A-number y fecha de nacimiento se cifran automáticamente.</p>

                <?php if (can('clients.create')): ?>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="<?= e(tenant_url('clients/new')) ?>" class="btn btn-primary">+ Crear primer cliente</a>
                    <a href="<?= e(url('training.html')) ?>?lesson=3" target="_blank" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Ver lección
                    </a>
                </div>
                <?php endif; ?>

                <div class="mt-8 grid grid-cols-3 gap-3 text-xs">
                    <div class="rounded-xl border border-slate-200 p-3 text-center">
                        <div class="text-2xl mb-1">🔒</div>
                        <div class="font-semibold">PII cifrado</div>
                        <div class="text-slate-500 mt-0.5">XChaCha20</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-3 text-center">
                        <div class="text-2xl mb-1">🔍</div>
                        <div class="font-semibold">Búsqueda exacta</div>
                        <div class="text-slate-500 mt-0.5">por pasaporte</div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-3 text-center">
                        <div class="text-2xl mb-1">🔗</div>
                        <div class="font-semibold">Portal cliente</div>
                        <div class="text-slate-500 mt-0.5">acceso seguro</div>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 kyros-table kyros-responsive">
            <thead class="bg-slate-50/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Contacto</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Nacionalidad</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($clients as $c):
                    $_clientName = trim($c['first_name'] . ' ' . $c['last_name']);
                ?>
                <tr>
                    <td class="px-4 py-3 text-sm" data-label="__primary">
                        <a href="<?= e(tenant_url('clients/' . $c['id'])) ?>" class="flex items-center gap-2.5">
                            <?= avatar_render($_clientName, 'md', 'client-' . (int)$c['id']) ?>
                            <div class="min-w-0">
                                <div class="font-medium text-slate-900 hover:text-indigo-600 truncate"><?= e($c['last_name'] . ', ' . $c['first_name']) ?></div>
                                <div class="text-xs text-slate-500">Cliente #<?= (int)$c['id'] ?></div>
                            </div>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm" data-label="Contacto">
                        <div class="text-right sm:text-left">
                            <div class="text-slate-700 truncate max-w-[200px]"><?= e($c['email'] ?? '—') ?></div>
                            <div class="text-xs text-slate-500"><?= e($c['phone'] ?? '') ?></div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm" data-label="Nacionalidad">
                        <?php if ($c['nationality']): ?>
                        <span class="badge-soft badge-neutral"><?= e(country_name($c['nationality'])) ?></span>
                        <?php else: ?>
                        <span class="text-slate-400">—</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm" data-label="Estado">
                        <?php $cls = ['active' => 'success', 'inactive' => 'neutral', 'archived' => 'neutral'][$c['status']]; ?>
                        <span class="badge-soft badge-<?= $cls ?>"><?= e($c['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<style nonce="<?= e(csp_nonce()) ?>">[x-cloak] { display: none !important; }</style>
