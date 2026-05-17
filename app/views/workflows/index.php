<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Automatización</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight">Workflows</h1>
                <?php
                $help_title = 'Workflows automáticos';
                $help_body  = 'Reglas cuando-entonces que ejecutan acciones automáticas: crear tareas, enviar emails, notificar al equipo.';
                $help_tips  = [
                    'Trigger + condición + acción (sin código)',
                    'Ejecuciones quedan en workflow_runs para auditoría',
                    'Ejemplo: caso pasa a "RFE" → crea tarea + email al cliente',
                ];
                $help_lesson = 8;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="mt-1 text-sm text-slate-600">Automatiza acciones cuando ocurren eventos en el sistema.</p>
        </div>
        <?php if (count($workflows) === 0 && count($runs) === 0): ?>
        <a href="<?= e(url('training.html')) ?>?lesson=8" target="_blank" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Ver lección
        </a>
        <?php endif; ?>
    </div>

    <?php if (empty($workflows)): ?>
    <div class="kyros-card px-6 py-10">
        <div class="mx-auto max-w-lg text-center">
            <div class="relative mx-auto h-28 w-28 mb-5">
                <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 30% 30%, rgba(168,85,247,0.25), transparent 70%); filter: blur(20px);"></div>
                <div class="relative h-full flex items-center justify-center">
                    <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-purple-500 to-violet-600 text-white text-4xl flex items-center justify-center shadow-xl shadow-purple-500/30">🔄</div>
                </div>
            </div>
            <h3 class="text-2xl font-bold tracking-tight">Activa tu primer workflow</h3>
            <p class="mt-2 text-slate-600">Aquí desbloqueas la magia: el sistema reacciona automáticamente cuando pasan cosas. Crea uno con el formulario de abajo.</p>
            <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs">
                <div class="rounded-xl border border-purple-200 bg-purple-50/40 p-3">
                    <div class="text-2xl mb-1">🎯</div>
                    <div class="font-bold">Trigger</div>
                    <div class="text-slate-500">Evento que dispara</div>
                </div>
                <div class="rounded-xl border border-amber-200 bg-amber-50/40 p-3">
                    <div class="text-2xl mb-1">🔍</div>
                    <div class="font-bold">Condición</div>
                    <div class="text-slate-500">Filtros opcionales</div>
                </div>
                <div class="rounded-xl border border-emerald-200 bg-emerald-50/40 p-3">
                    <div class="text-2xl mb-1">⚡</div>
                    <div class="font-bold">Acción</div>
                    <div class="text-slate-500">Lo que hace</div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <?php if (can('workflows.create')): ?>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm" x-data="{ at: 'create_task' }">
        <h3 class="text-base font-semibold mb-3">Crear workflow</h3>
        <form method="POST" action="<?= e(tenant_url('workflows')) ?>" class="space-y-3">
            <?= csrf_field() ?>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <div>
                    <label class="block text-sm font-medium">Nombre</label>
                    <input name="name" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium">Cuando ocurra</label>
                    <select name="trigger_event" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                        <option value="client.created">Se crea un cliente</option>
                        <option value="case.created">Se crea un caso</option>
                        <option value="case.status_changed">Cambia el estado de un caso</option>
                        <option value="task.completed">Se completa una tarea</option>
                        <option value="document.uploaded">Se sube un documento</option>
                    </select>
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium">Accion</label>
                <select name="action_type" x-model="at" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                    <option value="create_task">Crear tarea</option>
                    <option value="send_email">Enviar email al cliente</option>
                </select>
            </div>
            <div x-show="at === 'create_task'" class="grid grid-cols-1 gap-3 sm:grid-cols-3">
                <div>
                    <label class="block text-xs font-medium text-slate-500">Titulo</label>
                    <input name="action_title" placeholder="Tarea automatica" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Vence en (dias)</label>
                    <input name="action_due_days" type="number" value="7" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Prioridad</label>
                    <select name="action_priority" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                        <?php foreach (CASE_PRIORITIES as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div x-show="at === 'send_email'" class="space-y-3">
                <div>
                    <label class="block text-xs font-medium text-slate-500">Asunto</label>
                    <input name="action_subject" placeholder="Tu caso ha sido actualizado" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500">Cuerpo (puedes usar {{first_name}}, {{case_id}}, etc.)</label>
                    <textarea name="action_body" rows="3" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 text-sm">Hola {{first_name}}, hubo una actualizacion en tu caso.</textarea>
                </div>
            </div>
            <div class="flex justify-end">
                <button class="rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-semibold text-white">Crear workflow</button>
            </div>
        </form>
    </div>
    <?php endif; ?>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <h3 class="px-6 py-4 text-base font-semibold border-b border-slate-200">Workflows definidos</h3>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Nombre</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Trigger</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($workflows as $w): ?>
                <tr>
                    <td class="px-4 py-3 text-sm"><?= e($w['name']) ?></td>
                    <td class="px-4 py-3 text-sm font-mono text-xs"><?= e($w['trigger_event']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= $w['is_active'] ? '✓' : '—' ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php if (can('workflows.update')): ?>
                        <form method="POST" action="<?= e(tenant_url('workflows/' . $w['id'] . '/toggle')) ?>">
                            <?= csrf_field() ?>
                            <button class="text-xs text-indigo-600 hover:underline"><?= $w['is_active'] ? 'Desactivar' : 'Activar' ?></button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($workflows)): ?>
                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Sin workflows.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <h3 class="px-6 py-4 text-base font-semibold border-b border-slate-200">Ejecuciones recientes</h3>
        <table class="min-w-full divide-y divide-slate-200">
            <thead class="bg-slate-50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Cuando</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Workflow</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Evento</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-slate-500">Estado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($runs as $r): ?>
                <tr>
                    <td class="px-4 py-3 text-sm text-slate-500"><?= e($r['created_at']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= e($r['workflow_name']) ?></td>
                    <td class="px-4 py-3 text-sm font-mono text-xs"><?= e($r['event']) ?></td>
                    <td class="px-4 py-3 text-sm">
                        <?php $colors = ['success' => 'emerald', 'failed' => 'red', 'skipped' => 'slate']; ?>
                        <span class="inline-flex items-center rounded-full bg-<?= $colors[$r['status']] ?>-100 px-2.5 py-0.5 text-xs"><?= e($r['status']) ?></span>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($runs)): ?>
                <tr><td colspan="4" class="px-4 py-8 text-center text-sm text-slate-500">Aun no hay ejecuciones.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>
