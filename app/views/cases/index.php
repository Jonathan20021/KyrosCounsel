<?php
$f = $filters;
$base = tenant_url('cases');
function _build_url_without($base, $f, $remove) {
    $kept = [];
    foreach ($f as $k => $v) { if ($k === $remove) continue; if ($v !== '' && $v !== 0) $kept[$k] = $v; }
    return $base . ($kept ? '?' . http_build_query($kept) : '');
}
$attorney_name = '';
if ($f['attorney']) {
    foreach ($attorneys as $a) if ((int)$a['id'] === (int)$f['attorney']) $attorney_name = $a['name'];
}
$any_filter = ($f['status'] || $f['priority'] || $f['type'] || $f['attorney'] || $f['country'] || $f['q'] || $f['from'] || $f['to']);
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Operativo</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight">Casos</h1>
                <?php
                $help_title = 'Casos migratorios';
                $help_body  = 'Cada caso agrupa cliente, beneficiarios, evidencia, pagos, citas, plazos y comunicación.';
                $help_tips  = [
                    'Cambia estado arrastrando en el kanban',
                    'Mover a "RFE" dispara el workflow automático (tarea + email)',
                    'Genera G-28, cover letter o invoice desde el detalle',
                ];
                $help_lesson = 4;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="mt-1 text-sm text-slate-600"><?= count($cases) ?> resultados</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= e(tenant_url('export/cases')) ?>" class="btn btn-secondary" title="Descargar CSV">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                CSV
            </a>
            <a href="<?= e(tenant_url('cases/board')) ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Kanban
            </a>
            <?php if (can('cases.create')): ?>
            <a href="<?= e(tenant_url('cases/new')) ?>" class="btn btn-primary">+ Nuevo caso</a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Saved views -->
    <?php if (!empty($saved_views) || $any_filter): ?>
    <div class="flex flex-wrap items-center gap-2">
        <span class="text-xs font-semibold uppercase tracking-wider" style="color: hsl(var(--fg-muted));">Vistas:</span>
        <?php foreach ($saved_views as $sv):
            $svFilters = json_decode($sv['filters_json'], true) ?: [];
            $svUrl = $base . (!empty($svFilters) ? '?' . http_build_query($svFilters) : '');
            $isMatch = (json_encode($svFilters) === json_encode(array_filter($f, fn($v) => $v !== '' && $v !== 0)));
        ?>
        <a href="<?= e($svUrl) ?>" class="chip <?= $isMatch ? 'chip-primary' : '' ?>">
            <?= $sv['icon'] ? e($sv['icon']) : '⭐' ?> <?= e($sv['name']) ?>
            <?php if ($sv['user_id'] === null): ?><span class="text-[9px] opacity-60">· compartida</span><?php endif; ?>
        </a>
        <?php endforeach; ?>

        <?php if ($any_filter): ?>
        <!-- Boton guardar vista actual -->
        <div x-data="{ open: false, name: '', shared: false }" class="relative">
            <button type="button" @click.stop="open = !open" class="chip" style="background: hsl(var(--primary) / 0.1); color: hsl(var(--primary));">
                ⭐ Guardar vista
            </button>
            <div x-show="open" x-cloak @click.outside="open = false"
                 x-transition class="kyros-dropdown w-80 absolute right-0 mt-2 z-30 p-4">
                <h4 class="text-sm font-semibold mb-2">Guardar filtros como vista</h4>
                <form method="POST" action="<?= e(tenant_url('views')) ?>" class="space-y-3">
                    <?= csrf_field() ?>
                    <input type="hidden" name="module" value="cases">
                    <input type="hidden" name="filters_json" value='<?= e(json_encode(array_filter($f, fn($v) => $v !== '' && $v !== 0))) ?>'>
                    <input name="name" required maxlength="80" placeholder="Nombre de la vista..." x-model="name"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    <input name="icon" placeholder="Icono (emoji opcional)" maxlength="10"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    <label class="flex items-center gap-2 text-xs">
                        <input type="checkbox" name="shared" value="1" x-model="shared" class="rounded">
                        Compartir con todo el bufete
                    </label>
                    <div class="flex justify-end gap-2">
                        <button type="button" @click="open = false" class="text-xs">Cancelar</button>
                        <button class="btn btn-primary text-xs">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Filtros avanzados con chips -->
    <div class="kyros-card p-4" x-data="{ open: false }">
        <form method="GET" action="<?= e($base) ?>" class="space-y-3">
            <div class="flex flex-wrap items-center gap-2">
                <div class="flex-1 min-w-[200px] relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                    </span>
                    <input name="q" value="<?= e($f['q']) ?>" placeholder="Buscar caso, cliente, numero..."
                           class="w-full pl-9 pr-3 py-2 bg-slate-50 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                </div>
                <button type="button" @click="open = !open" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/></svg>
                    Filtros
                    <span x-show="!open">▾</span><span x-show="open">▴</span>
                </button>
                <button class="btn btn-primary">Aplicar</button>
                <?php if ($any_filter): ?>
                <a href="<?= e($base) ?>" class="btn btn-ghost text-xs">Limpiar todo</a>
                <?php endif; ?>
            </div>

            <div x-show="open" x-cloak class="grid grid-cols-1 gap-3 md:grid-cols-3 pt-3 border-t border-slate-200">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Estado</label>
                    <select name="status" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Todos —</option>
                        <?php foreach (CASE_STATUSES as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['status'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Prioridad</label>
                    <select name="priority" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Cualquiera —</option>
                        <?php foreach (CASE_PRIORITIES as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['priority'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Tipo</label>
                    <select name="type" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Todos —</option>
                        <?php foreach ($available_types as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['type'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Abogado</label>
                    <select name="attorney" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="0">— Cualquiera —</option>
                        <?php foreach ($attorneys as $a): ?>
                        <option value="<?= (int)$a['id'] ?>" <?= (int)$f['attorney'] === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Pais</label>
                    <select name="country" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Todos —</option>
                        <?php foreach (COUNTRIES as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= $f['country'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Apertura desde</label>
                        <input name="from" type="date" value="<?= e($f['from']) ?>" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">hasta</label>
                        <input name="to" type="date" value="<?= e($f['to']) ?>" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                </div>
            </div>
        </form>

        <?php if ($any_filter): ?>
        <div class="mt-3 pt-3 border-t border-slate-200 flex flex-wrap items-center gap-2">
            <span class="text-xs text-slate-500">Filtros activos:</span>
            <?php if ($f['q']): ?>
            <span class="chip chip-primary">q: "<?= e($f['q']) ?>"<a href="<?= e(_build_url_without($base, $f, 'q')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['status']): ?>
            <span class="chip chip-primary">Estado: <?= e(CASE_STATUSES[$f['status']]) ?><a href="<?= e(_build_url_without($base, $f, 'status')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['priority']): ?>
            <span class="chip chip-primary">Prioridad: <?= e(CASE_PRIORITIES[$f['priority']]) ?><a href="<?= e(_build_url_without($base, $f, 'priority')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['type']): ?>
            <span class="chip chip-primary">Tipo: <?= e($f['type']) ?><a href="<?= e(_build_url_without($base, $f, 'type')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['attorney']): ?>
            <span class="chip chip-primary">Abogado: <?= e($attorney_name) ?><a href="<?= e(_build_url_without($base, $f, 'attorney')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['country']): ?>
            <span class="chip chip-primary">Pais: <?= e(country_name($f['country'])) ?><a href="<?= e(_build_url_without($base, $f, 'country')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['from']): ?>
            <span class="chip chip-primary">Desde: <?= e($f['from']) ?><a href="<?= e(_build_url_without($base, $f, 'from')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
            <?php if ($f['to']): ?>
            <span class="chip chip-primary">Hasta: <?= e($f['to']) ?><a href="<?= e(_build_url_without($base, $f, 'to')) ?>" class="chip-remove">×</a></span>
            <?php endif; ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="kyros-card overflow-hidden" x-data="{ selected: [], get count() { return this.selected.length; }, toggleAll(checked) { this.selected = checked ? <?= json_encode(array_column($cases, 'id')) ?> : []; } }">

        <!-- Bulk action bar -->
        <?php if (!empty($cases) && can('cases.update')): ?>
        <div x-show="count > 0" x-cloak style="display: none;"
             x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0"
             class="px-5 py-3 bg-indigo-50 border-b border-indigo-100 flex flex-wrap items-center gap-3">
            <span class="text-sm font-semibold text-indigo-900"><span x-text="count"></span> seleccionado(s)</span>

            <form method="POST" action="<?= e(tenant_url('cases/bulk')) ?>" class="flex flex-wrap items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="ids" :value="selected.join(',')">
                <input type="hidden" name="action" value="change_status">
                <select name="new_status" class="rounded-md border border-slate-300 py-1.5 px-2 text-xs">
                    <option value="">Cambiar estado a...</option>
                    <?php foreach (CASE_STATUSES as $k => $v): ?>
                    <option value="<?= e($k) ?>"><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary text-xs py-1.5">Aplicar</button>
            </form>

            <form method="POST" action="<?= e(tenant_url('cases/bulk')) ?>" class="flex flex-wrap items-center gap-2">
                <?= csrf_field() ?>
                <input type="hidden" name="ids" :value="selected.join(',')">
                <input type="hidden" name="action" value="change_priority">
                <select name="new_priority" class="rounded-md border border-slate-300 py-1.5 px-2 text-xs">
                    <option value="">Prioridad...</option>
                    <?php foreach (CASE_PRIORITIES as $k => $v): ?>
                    <option value="<?= e($k) ?>"><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-secondary text-xs py-1.5">Aplicar</button>
            </form>

            <button @click="selected = []" class="text-xs text-slate-600 hover:text-slate-900 ml-auto">Limpiar seleccion</button>
        </div>
        <?php endif; ?>

        <?php if (empty($cases)): ?>
        <?php if ($any_filter): ?>
        <div class="empty-state py-12">
            <div class="empty-state-icon">🔍</div>
            <h3 class="text-lg font-semibold mt-2">Sin resultados</h3>
            <p class="mt-1 text-sm text-slate-500">Ningún caso coincide con los filtros aplicados.</p>
            <a href="<?= e($base) ?>" class="mt-4 inline-block btn btn-secondary">Limpiar filtros</a>
        </div>
        <?php else: ?>
        <div class="px-6 py-12 lg:py-16">
            <div class="mx-auto max-w-lg text-center">
                <div class="relative mx-auto h-32 w-32 mb-6">
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 30% 30%, rgba(99,102,241,0.25), transparent 70%); filter: blur(20px);"></div>
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 70% 70%, rgba(236,72,153,0.20), transparent 70%); filter: blur(20px);"></div>
                    <div class="relative h-full flex items-center justify-center">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-blue-500 to-indigo-600 text-white text-4xl flex items-center justify-center shadow-xl shadow-blue-500/30">📁</div>
                    </div>
                </div>
                <h3 class="text-2xl font-bold tracking-tight">Aún no tienes casos</h3>
                <p class="mt-2 text-slate-600">El caso es el corazón del bufete. Agrupa cliente, beneficiarios, evidencia, pagos, citas, plazos y comunicación.</p>

                <?php if (can('cases.create')): ?>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="<?= e(tenant_url('cases/new')) ?>" class="btn btn-primary">+ Crear primer caso</a>
                    <a href="<?= e(url('training.html')) ?>?lesson=4" target="_blank" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Ver lección
                    </a>
                </div>
                <?php endif; ?>

                <div class="mt-8 flex flex-wrap justify-center gap-2 text-xs">
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">I-130</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">I-485</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">I-589</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">N-400</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">DACA</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">Asilo</span>
                    <span class="px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">+ más</span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        <?php else: ?>
        <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-slate-100 kyros-table kyros-responsive">
            <thead class="bg-slate-50/50">
                <tr>
                    <?php if (can('cases.update')): ?>
                    <th class="px-3 py-3 w-10">
                        <input type="checkbox" @change="toggleAll($event.target.checked)" :checked="count === <?= count($cases) ?> && count > 0"
                               class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                    </th>
                    <?php endif; ?>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Caso</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Cliente</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Prioridad</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Abogado</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Apertura</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($cases as $c): ?>
                <tr>
                    <?php if (can('cases.update')): ?>
                    <td class="px-3 py-3 w-10" data-label="Seleccionar">
                        <input type="checkbox" :value="<?= (int)$c['id'] ?>" x-model="selected"
                               class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                    </td>
                    <?php endif; ?>
                    <td class="px-4 py-3 text-sm" data-label="__primary">
                        <a href="<?= e(tenant_url('cases/' . $c['id'])) ?>" class="block">
                            <div class="font-mono text-xs text-indigo-600 font-medium"><?= e($c['case_number']) ?></div>
                            <div class="font-medium text-slate-900 truncate max-w-[300px]"><?= e($c['title']) ?></div>
                        </a>
                    </td>
                    <td class="px-4 py-3 text-sm" data-label="Cliente">
                        <a href="<?= e(tenant_url('clients/' . $c['client_id'])) ?>" class="hover:underline text-slate-700"><?= e($c['last_name'] . ', ' . $c['first_name']) ?></a>
                    </td>
                    <td class="px-4 py-3 text-sm font-mono text-xs text-slate-600" data-label="Tipo"><?= e($c['case_type']) ?></td>
                    <td class="px-4 py-3 text-sm" data-label="Estado">
                        <?php if (can('cases.update')): ?>
                        <select class="case-status-select rounded-md border border-slate-200 bg-white px-2 py-1 text-xs font-medium focus:ring-2 focus:ring-indigo-500 cursor-pointer"
                                data-case-id="<?= (int)$c['id'] ?>" data-original="<?= e($c['status']) ?>">
                            <?php foreach (CASE_STATUSES as $sk => $sv): ?>
                            <option value="<?= e($sk) ?>" <?= $sk === $c['status'] ? 'selected' : '' ?>><?= e($sv) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <?php else: ?>
                        <span class="badge-soft badge-neutral"><?= e(case_status_label($c['status'])) ?></span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm" data-label="Prioridad">
                        <?php $cls = ['urgent' => 'danger','high' => 'warning','normal' => 'neutral','low' => 'neutral'][$c['priority']]; ?>
                        <span class="badge-soft badge-<?= $cls ?>"><?= e(CASE_PRIORITIES[$c['priority']]) ?></span>
                    </td>
                    <td class="px-4 py-3 text-sm text-slate-700" data-label="Abogado"><?= e($c['attorney_name'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-500 whitespace-nowrap" data-label="Apertura"><?= e(date('d M Y', strtotime($c['opened_at']))) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>
</div>
<style nonce="<?= e(csp_nonce()) ?>">[x-cloak] { display: none !important; }</style>
<script nonce="<?= e(csp_nonce()) ?>">
(function() {
    const csrf = '<?= e(csrf_token()) ?>';
    document.querySelectorAll('.case-status-select').forEach(sel => {
        sel.addEventListener('change', async (e) => {
            const newStatus = sel.value;
            const id = sel.dataset.caseId;
            const original = sel.dataset.original;
            sel.disabled = true;
            try {
                const fd = new FormData();
                fd.append('_csrf', csrf);
                fd.append('status', newStatus);
                const r = await fetch('<?= e(tenant_url('cases')) ?>/' + id + '/move', { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                if (!j.ok) throw new Error('fail');
                sel.dataset.original = newStatus;
                showToast('Estado actualizado', 'success');
            } catch (err) {
                sel.value = original;
                showToast('Error al cambiar estado', 'error');
            } finally { sel.disabled = false; }
        });
    });
    function showToast(msg, type) {
        const t = document.createElement('div');
        t.className = 'fixed top-4 right-4 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg fade-in ' +
            (type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800');
        t.textContent = msg; document.body.appendChild(t);
        setTimeout(() => t.remove(), 2500);
    }
})();
</script>
