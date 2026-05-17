<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="kyros-eyebrow">Comunicación</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="kyros-title">Plantillas de email</h1>
                <?php
                $help_title = 'Plantillas de email';
                $help_body  = 'Mensajes pre-armados con variables (mail merge) que envías a clientes desde el caso.';
                $help_tips  = [
                    'Usa {{client.first_name}}, {{case.number}}, {{case.status}} y más',
                    'Carga las pre-armadas con "Sembrar"',
                    'Combínalas con workflows para enviar automáticamente',
                ];
                $help_lesson = 9;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="kyros-subtitle mt-1">Reutilizables con merge fields tipo <code class="font-mono text-xs bg-slate-100 px-1.5 py-0.5 rounded">{{client.first_name}}</code></p>
        </div>
        <div class="flex gap-2">
            <?php if (empty($templates)): ?>
            <form method="POST" action="<?= e(tenant_url('templates/seed')) ?>">
                <?= csrf_field() ?>
                <button class="btn btn-secondary">📋 Cargar 4 plantillas estándar</button>
            </form>
            <?php endif; ?>
            <a href="<?= e(tenant_url('templates/new')) ?>" class="btn btn-primary">+ Nueva plantilla</a>
        </div>
    </div>

    <div class="kyros-card overflow-hidden">
        <?php if (empty($templates)): ?>
        <div class="px-6 py-12 lg:py-16">
            <div class="mx-auto max-w-lg text-center">
                <div class="relative mx-auto h-32 w-32 mb-6">
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 30% 30%, rgba(236,72,153,0.20), transparent 70%); filter: blur(20px);"></div>
                    <div class="relative h-full flex items-center justify-center">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-pink-500 to-rose-600 text-white text-4xl flex items-center justify-center shadow-xl shadow-pink-500/30">✉️</div>
                    </div>
                </div>
                <h3 class="text-2xl font-bold tracking-tight">Aún no tienes plantillas</h3>
                <p class="mt-2 text-slate-600">Las plantillas te ahorran tiempo: redactas una vez, las usas siempre. Soportan variables como <code class="font-mono text-xs bg-slate-100 px-1 rounded">{{client.first_name}}</code>.</p>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <form method="POST" action="<?= e(tenant_url('templates/seed')) ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-primary">📋 Cargar 4 plantillas estándar</button>
                    </form>
                    <a href="<?= e(tenant_url('templates/new')) ?>" class="btn btn-secondary">+ Crear desde cero</a>
                    <a href="<?= e(url('training.html')) ?>?lesson=9" target="_blank" class="btn btn-ghost text-slate-600">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Ver lección
                    </a>
                </div>
                <div class="mt-8 grid grid-cols-2 gap-2 text-xs">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-left"><strong class="text-sm">Engagement letter</strong><p class="text-slate-500 mt-0.5">Contrato cliente-abogado.</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-left"><strong class="text-sm">RFE recibida</strong><p class="text-slate-500 mt-0.5">Notifica al cliente.</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-left"><strong class="text-sm">Decisión aprobada</strong><p class="text-slate-500 mt-0.5">Felicitación post-aprobación.</p></div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-3 text-left"><strong class="text-sm">Recordatorio cita</strong><p class="text-slate-500 mt-0.5">24h antes de audiencia.</p></div>
                </div>
            </div>
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-slate-100 kyros-table">
            <thead class="bg-slate-50/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Slug</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Categoría</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Asunto</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($templates as $t): ?>
                <tr>
                    <td class="px-4 py-3 text-sm font-medium"><?= e($t['name']) ?></td>
                    <td class="px-4 py-3 text-sm font-mono text-xs text-indigo-600"><?= e($t['slug']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= e($t['category'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-600 max-w-xs truncate"><?= e($t['subject']) ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($t['is_active']): ?>
                        <span class="badge-soft badge-success">Activa</span>
                        <?php else: ?>
                        <span class="badge-soft badge-neutral">Inactiva</span>
                        <?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <a href="<?= e(tenant_url('templates/' . $t['id'] . '/edit')) ?>" class="text-xs text-indigo-600 hover:underline mr-3">Editar</a>
                        <form method="POST" action="<?= e(tenant_url('templates/' . $t['id'] . '/delete')) ?>" data-confirm="Eliminar plantilla?" class="inline">
                            <?= csrf_field() ?>
                            <button class="text-xs text-red-600 hover:underline">Eliminar</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
