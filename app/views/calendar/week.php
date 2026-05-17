<?php
$days_es = ['Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado', 'Domingo'];
$today = date('Y-m-d');
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="kyros-eyebrow">Vista semanal</p>
            <h1 class="kyros-title mt-1"><?= e(date('d M', strtotime($monday_str))) ?> — <?= e(date('d M Y', strtotime($sunday_str))) ?></h1>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="<?= e(tenant_url('calendar')) ?>" class="btn btn-secondary text-xs">Vista mes</a>
            <a href="<?= e(tenant_url('calendar/week?w=' . $prev)) ?>" class="btn btn-secondary">←</a>
            <a href="<?= e(tenant_url('calendar/week')) ?>" class="btn btn-secondary text-xs">Hoy</a>
            <a href="<?= e(tenant_url('calendar/week?w=' . $next)) ?>" class="btn btn-secondary">→</a>
        </div>
    </div>

    <!-- Filtro abogado -->
    <form method="GET" action="<?= e(tenant_url('calendar/week')) ?>" class="flex gap-2 items-center">
        <input type="hidden" name="w" value="<?= e($monday_str) ?>">
        <label class="text-sm text-slate-600">Abogado:</label>
        <select name="attorney" onchange="this.form.submit()" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
            <option value="0">Todos</option>
            <?php foreach ($attorneys as $a): ?>
            <option value="<?= (int)$a['id'] ?>" <?= $filter_attorney === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
            <?php endforeach; ?>
        </select>
    </form>

    <!-- Grid 7 días -->
    <div class="kyros-card overflow-x-auto">
        <div class="grid grid-cols-7 min-w-[900px]">
            <?php $i = 0; foreach ($by_date as $date => $items):
                $is_today = $date === $today;
                $is_weekend = $i >= 5;
                $tasks_count = count($items['tasks']);
                $appts_count = count($items['appts']);
            ?>
            <div class="border-r border-slate-100 last:border-r-0 <?= $is_weekend ? 'bg-slate-50/40' : '' ?>" style="border-color: hsl(var(--border));">
                <!-- Header del día -->
                <div class="px-3 py-3 text-center <?= $is_today ? 'bg-indigo-50' : '' ?>" style="border-bottom: 1px solid hsl(var(--border));">
                    <div class="text-xs uppercase tracking-wider <?= $is_today ? 'text-indigo-700 font-bold' : 'text-slate-500' ?>"><?= e($days_es[$i]) ?></div>
                    <div class="text-2xl font-bold mt-0.5 <?= $is_today ? 'text-indigo-600' : 'text-slate-900' ?>"><?= e(date('d', strtotime($date))) ?></div>
                    <?php if ($tasks_count + $appts_count > 0): ?>
                    <div class="text-[10px] text-slate-500 mt-0.5"><?= $tasks_count + $appts_count ?> items</div>
                    <?php endif; ?>
                </div>

                <!-- Items del día -->
                <div class="p-2 space-y-1.5 min-h-[420px]">
                    <?php foreach ($items['appts'] as $apt):
                        $type_colors = ['interview' => 'amber', 'biometrics' => 'emerald', 'consultation' => 'indigo', 'court' => 'red', 'other' => 'slate'];
                        $color = $type_colors[$apt['type']] ?? 'slate';
                    ?>
                    <a href="<?= e(tenant_url('cases/' . $apt['case_id'])) ?>"
                       class="block rounded-md border-l-[3px] border-<?= $color ?>-500 bg-<?= $color ?>-50 px-2 py-1.5 text-xs hover:shadow-sm transition">
                        <div class="font-mono text-[10px] text-<?= $color ?>-700"><?= e(date('H:i', strtotime($apt['starts_at']))) ?></div>
                        <div class="font-semibold truncate text-slate-900"><?= e($apt['title']) ?></div>
                        <?php if ($apt['case_number']): ?>
                        <div class="text-[10px] text-slate-500 truncate font-mono"><?= e($apt['case_number']) ?></div>
                        <?php endif; ?>
                    </a>
                    <?php endforeach; ?>

                    <?php foreach ($items['tasks'] as $tk):
                        $overdue = $date < $today && $tk['status'] === 'pending';
                        $prio_colors = ['urgent' => 'red', 'high' => 'amber', 'normal' => 'indigo', 'low' => 'slate'];
                        $color = $overdue ? 'red' : ($prio_colors[$tk['priority']] ?? 'indigo');
                        $done = $tk['status'] === 'done';
                    ?>
                    <a href="<?= e($tk['case_id'] ? tenant_url('cases/' . $tk['case_id']) : tenant_url('tasks')) ?>"
                       class="block rounded-md bg-<?= $color ?>-100/60 border border-<?= $color ?>-200 px-2 py-1 text-xs hover:bg-<?= $color ?>-100 transition <?= $done ? 'opacity-50' : '' ?>">
                        <div class="flex items-start gap-1.5">
                            <span class="mt-0.5 flex-shrink-0 inline-block h-3 w-3 rounded-sm border <?= $done ? 'bg-emerald-500 border-emerald-500' : 'border-' . $color . '-400' ?>"></span>
                            <div class="min-w-0 flex-1">
                                <div class="font-medium truncate <?= $done ? 'line-through' : '' ?>"><?= e($tk['title']) ?></div>
                                <?php if ($tk['assignee_name']): ?>
                                <div class="text-[10px] text-slate-500 truncate"><?= e($tk['assignee_name']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </a>
                    <?php endforeach; ?>

                    <?php if (empty($items['tasks']) && empty($items['appts'])): ?>
                    <div class="text-center text-[10px] text-slate-300 py-8 italic">Libre</div>
                    <?php endif; ?>
                </div>
            </div>
            <?php $i++; endforeach; ?>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap gap-3 text-xs text-slate-500">
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-amber-100 border-l-[3px] border-amber-500"></span> Entrevista</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-emerald-100 border-l-[3px] border-emerald-500"></span> Biometrics</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-indigo-100 border-l-[3px] border-indigo-500"></span> Consulta</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-red-100 border-l-[3px] border-red-500"></span> Audiencia / Vencido</span>
    </div>
</div>
