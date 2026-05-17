<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Anuncios globales</h1>
        <p class="mt-1 text-sm text-slate-600">Banners mostrados a tenants. Útil para releases, mantenimientos, ofertas.</p>
    </div>
</div>

<!-- Form crear -->
<div class="kyros-card p-6 mb-6" x-data="{ open: false }">
    <button @click="open = !open" class="w-full flex items-center justify-between font-semibold text-left">
        <span>+ Crear nuevo anuncio</span>
        <svg :class="open ? 'rotate-180' : ''" class="transition" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 12 15 18 9"/></svg>
    </button>
    <form x-show="open" x-cloak method="POST" action="<?= e(url('admin/announcements')) ?>" class="mt-4 space-y-3">
        <?= csrf_field() ?>
        <input name="title" required placeholder="Título" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm">
        <textarea name="body" required rows="3" placeholder="Mensaje (puede incluir HTML básico)" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm"></textarea>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <select name="severity" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="info">Info</option>
                <option value="success">Éxito</option>
                <option value="warning">Advertencia</option>
                <option value="danger">Crítico</option>
            </select>
            <select name="audience" class="rounded-lg border border-slate-200 px-3 py-2 text-sm">
                <option value="all">Todos</option>
                <option value="trial">Solo trial</option>
                <option value="active">Solo activos</option>
                <option value="suspended">Suspendidos</option>
            </select>
            <input name="starts_at" type="datetime-local" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Inicio">
            <input name="ends_at" type="datetime-local" class="rounded-lg border border-slate-200 px-3 py-2 text-sm" placeholder="Fin">
        </div>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" checked> Activar inmediatamente</label>
        <button class="btn btn-primary" style="background:#d97706;">Publicar anuncio</button>
    </form>
</div>

<!-- Lista -->
<div class="space-y-3">
    <?php foreach ($announcements as $a):
        $sev = $a['severity'];
        $sevColors = [
            'info'    => 'border-blue-200 bg-blue-50/50',
            'success' => 'border-emerald-200 bg-emerald-50/50',
            'warning' => 'border-amber-200 bg-amber-50/50',
            'danger'  => 'border-rose-200 bg-rose-50/50',
        ];
        $cls = $sevColors[$sev] ?? '';
    ?>
    <div class="kyros-card p-5 border-l-4 <?= $cls ?> <?= !$a['is_active'] ? 'opacity-50' : '' ?>">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1">
                <div class="flex items-center gap-2 mb-1 flex-wrap">
                    <span class="badge-soft badge-<?= $sev === 'danger' ? 'danger' : ($sev === 'warning' ? 'warning' : ($sev === 'success' ? 'success' : 'info')) ?>"><?= e($sev) ?></span>
                    <span class="text-[10px] uppercase font-semibold text-slate-500">→ <?= e($a['audience']) ?></span>
                    <?php if (!$a['is_active']): ?><span class="text-[10px] uppercase font-semibold text-slate-400">Inactivo</span><?php endif; ?>
                </div>
                <h3 class="font-semibold"><?= e($a['title']) ?></h3>
                <p class="mt-1 text-sm text-slate-600"><?= e($a['body']) ?></p>
                <div class="mt-2 text-xs text-slate-500">
                    <?php if ($a['starts_at']): ?>Desde <?= e(date('Y-m-d H:i', strtotime($a['starts_at']))) ?> · <?php endif; ?>
                    <?php if ($a['ends_at']): ?>Hasta <?= e(date('Y-m-d H:i', strtotime($a['ends_at']))) ?> · <?php endif; ?>
                    Creado <?= e(date('Y-m-d', strtotime($a['created_at']))) ?>
                </div>
            </div>
            <div class="flex flex-col gap-1">
                <form method="POST" action="<?= e(url('admin/announcements/' . $a['id'] . '/toggle')) ?>" class="inline">
                    <?= csrf_field() ?>
                    <button class="text-xs font-semibold text-amber-700 hover:text-amber-600"><?= $a['is_active'] ? 'Desactivar' : 'Activar' ?></button>
                </form>
                <form method="POST" action="<?= e(url('admin/announcements/' . $a['id'] . '/delete')) ?>" class="inline" data-confirm="¿Eliminar anuncio?">
                    <?= csrf_field() ?>
                    <button class="text-xs font-semibold text-rose-600 hover:text-rose-500">Eliminar</button>
                </form>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
    <?php if (empty($announcements)): ?>
    <div class="kyros-card p-8 text-center text-sm text-slate-500">No hay anuncios. Crea uno arriba ↑</div>
    <?php endif; ?>
</div>
