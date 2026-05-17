<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Productividad</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight">Tareas</h1>
                <?php
                $help_title = 'Tareas y plazos';
                $help_body  = 'Pendientes con fecha y prioridad. Asígnalas a ti o a tu equipo.';
                $help_tips  = [
                    'Vincula una tarea a un caso para verla en ambos lugares',
                    'Las tareas vencidas aparecen en rojo en el dashboard',
                    'Usa el kanban para arrastrar entre estados',
                ];
                $help_lesson = 6;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="mt-1 text-sm text-slate-600"><?= count($tasks) ?> resultados</p>
        </div>
        <div class="flex gap-2">
            <a href="<?= e(tenant_url('export/tasks')) ?>" class="btn btn-secondary" title="Descargar CSV">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                CSV
            </a>
            <a href="<?= e(tenant_url('tasks/board')) ?>" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>
                Kanban
            </a>
        </div>
    </div>

    <div class="kyros-card p-3 flex flex-wrap gap-2">
        <?php $filters = ['open' => 'Abiertas', 'mine' => 'Mias', 'done' => 'Hechas', 'all' => 'Todas']; ?>
        <?php foreach ($filters as $k => $v): ?>
        <a href="<?= e(tenant_url('tasks?filter=' . $k)) ?>" class="badge-soft <?= $filter === $k ? 'badge-primary' : 'badge-neutral' ?>"><?= e($v) ?></a>
        <?php endforeach; ?>
    </div>

    <?php if (can('tasks.create')): ?>
    <form method="POST" action="<?= e(tenant_url('tasks')) ?>" class="kyros-card p-5 space-y-3">
        <?= csrf_field() ?>
        <h3 class="text-sm font-semibold">Crear tarea</h3>
        <div class="grid grid-cols-1 gap-3 sm:grid-cols-5">
            <input name="title" required placeholder="Titulo de la tarea" class="sm:col-span-2 rounded-lg border border-slate-200 py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500">
            <select name="case_id" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                <option value="0">— Sin caso —</option>
                <?php foreach ($cases as $c): ?>
                <option value="<?= (int)$c['id'] ?>"><?= e($c['case_number'] . ' · ' . mb_substr($c['title'], 0, 30)) ?></option>
                <?php endforeach; ?>
            </select>
            <select name="assignee_id" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                <option value="0">— Sin asignar —</option>
                <?php foreach ($attorneys as $a): ?>
                <option value="<?= (int)$a['id'] ?>"><?= e($a['name']) ?></option>
                <?php endforeach; ?>
            </select>
            <input name="due_date" type="date" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
        </div>
        <div class="flex items-center gap-3">
            <select name="priority" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                <?php foreach (CASE_PRIORITIES as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= $k === 'normal' ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
            <button class="btn btn-primary">Crear tarea</button>
        </div>
    </form>
    <?php endif; ?>

    <div class="kyros-card overflow-hidden">
        <?php if (empty($tasks)): ?>
        <div class="px-6 py-12 lg:py-16">
            <div class="mx-auto max-w-lg text-center">
                <div class="relative mx-auto h-32 w-32 mb-6">
                    <div class="absolute inset-0 rounded-full" style="background: radial-gradient(circle at 30% 30%, rgba(16,185,129,0.25), transparent 70%); filter: blur(20px);"></div>
                    <div class="relative h-full flex items-center justify-center">
                        <div class="h-20 w-20 rounded-2xl bg-gradient-to-br from-emerald-500 to-teal-600 text-white text-4xl flex items-center justify-center shadow-xl shadow-emerald-500/30">✓</div>
                    </div>
                </div>
                <h3 class="text-2xl font-bold tracking-tight"><?= $filter === 'open' ? 'Todo al día' : 'Sin tareas con este filtro' ?></h3>
                <p class="mt-2 text-slate-600">
                    <?= $filter === 'open'
                        ? '¡Buen trabajo! No tienes tareas pendientes.'
                        : 'Crea tu primera tarea para empezar a organizar tus pendientes.' ?>
                </p>
                <?php if ($filter !== 'open' && can('tasks.create')): ?>
                <div class="mt-6 flex flex-wrap items-center justify-center gap-3">
                    <a href="<?= e(url('training.html')) ?>?lesson=6" target="_blank" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                        Ver lección
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php else: ?>
        <ul class="divide-y divide-slate-100">
            <?php foreach ($tasks as $t):
                $overdue = $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'done';
                $done = $t['status'] === 'done';
            ?>
            <li class="p-4 hover:bg-slate-50 transition flex items-start gap-3">
                <?php if (can('tasks.update')): ?>
                <form method="POST" action="<?= e(tenant_url('tasks/' . $t['id'] . '/toggle')) ?>" class="flex-shrink-0 mt-0.5">
                    <?= csrf_field() ?>
                    <button type="submit" class="h-5 w-5 rounded border-2 transition <?= $done ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-indigo-500' ?>">
                        <?= $done ? '✓' : '' ?>
                    </button>
                </form>
                <?php endif; ?>
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 flex-wrap">
                        <span class="text-sm font-medium <?= $done ? 'line-through text-slate-400' : 'text-slate-900' ?>"><?= e($t['title']) ?></span>
                        <?php if ($t['case_number']): ?><span class="badge-soft badge-info font-mono text-[10px]"><?= e($t['case_number']) ?></span><?php endif; ?>
                        <?php $cls = ['urgent' => 'danger','high' => 'warning','normal' => 'neutral','low' => 'neutral'][$t['priority']]; ?>
                        <?php if ($t['priority'] !== 'normal'): ?><span class="badge-soft badge-<?= $cls ?>"><?= e(CASE_PRIORITIES[$t['priority']]) ?></span><?php endif; ?>
                        <?php if ($overdue): ?><span class="badge-soft badge-danger">Vencida</span><?php endif; ?>
                    </div>
                    <div class="mt-0.5 text-xs text-slate-500">
                        <?= e($t['assignee_name'] ?? 'Sin asignar') ?>
                        <?php if ($t['due_date']): ?>
                        · vence <span class="<?= $overdue ? 'text-red-600 font-medium' : '' ?>"><?= e(date('d M Y', strtotime($t['due_date']))) ?></span>
                        <?php endif; ?>
                    </div>
                </div>
                <?php if (can('tasks.update')): ?>
                <form method="POST" action="<?= e(tenant_url('tasks/' . $t['id'] . '/delete')) ?>" data-confirm="¿Eliminar tarea?" class="flex-shrink-0">
                    <?= csrf_field() ?>
                    <button class="text-xs text-slate-400 hover:text-red-600">×</button>
                </form>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>
    </div>
</div>
