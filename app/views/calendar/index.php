<?php
$first_dow = (int)date('N', strtotime($first));   // 1=lun .. 7=dom
$days_in_month = (int)date('t', strtotime($first));
$prev_ym = date('Y-m', strtotime($first . ' -1 month'));
$next_ym = date('Y-m', strtotime($first . ' +1 month'));
$today = date('Y-m-d');
$month_label = ucfirst(strftime('%B %Y', strtotime($first)) ?: date('F Y', strtotime($first)));
$month_label_es = (function () use ($first) {
    $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio',
             '07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
    return $meses[date('m', strtotime($first))] . ' ' . date('Y', strtotime($first));
})();
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Calendario</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight"><?= e($month_label_es) ?></h1>
                <?php
                $help_title = 'Calendario operativo';
                $help_body  = 'Vista mensual o semanal con tareas, vencimientos de casos, citas y presentaciones.';
                $help_tips  = [
                    'Color rojo = vencimiento, violeta = audiencia, azul = tarea',
                    'Click en evento abre el caso/tarea relacionada',
                    'Cambia entre Mes y Semana según el detalle que necesites',
                ];
                $help_lesson = 7;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(tenant_url('calendar/week')) ?>" class="btn btn-secondary text-xs">Vista semana</a>
            <a href="<?= e(tenant_url('calendar?ym=' . $prev_ym)) ?>" class="btn btn-secondary">←</a>
            <a href="<?= e(tenant_url('calendar')) ?>" class="btn btn-secondary text-xs">Hoy</a>
            <a href="<?= e(tenant_url('calendar?ym=' . $next_ym)) ?>" class="btn btn-secondary">→</a>
        </div>
    </div>

    <div class="kyros-card overflow-hidden">
        <div class="grid grid-cols-7 border-b border-slate-200 bg-slate-50/50">
            <?php foreach (['Lun','Mar','Mie','Jue','Vie','Sab','Dom'] as $d): ?>
            <div class="px-3 py-2 text-xs font-semibold uppercase tracking-wider text-slate-500 text-center"><?= e($d) ?></div>
            <?php endforeach; ?>
        </div>
        <div class="grid grid-cols-7">
            <?php
            for ($i = 1; $i < $first_dow; $i++): ?>
            <div class="bg-slate-50/30 min-h-[120px] border-b border-r border-slate-100"></div>
            <?php endfor;
            for ($d = 1; $d <= $days_in_month; $d++):
                $date = sprintf('%s-%02d', $ym, $d);
                $items = $by_date[$date] ?? [];
                $is_today = $date === $today;
                $is_weekend = in_array(date('N', strtotime($date)), [6, 7]);
            ?>
            <div class="calendar-cell min-h-[120px] border-b border-r border-slate-100 p-2 <?= $is_weekend ? 'bg-slate-50/30' : '' ?> <?= $is_today ? 'ring-2 ring-indigo-500 ring-inset' : '' ?>">
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-semibold <?= $is_today ? 'text-indigo-600' : 'text-slate-700' ?>"><?= $d ?></span>
                    <?php if (!empty($items['tasks']) || !empty($items['cases'])): ?>
                    <span class="text-[10px] text-slate-400"><?= count(($items['tasks'] ?? [])) + count(($items['cases'] ?? [])) ?></span>
                    <?php endif; ?>
                </div>
                <div class="space-y-1 max-h-[80px] overflow-y-auto">
                    <?php foreach ($items['tasks'] ?? [] as $t):
                        $overdue = $t['due_date'] < $today && $t['status'] === 'pending';
                    ?>
                    <a href="<?= e(tenant_url('tasks')) ?>" class="block px-1.5 py-0.5 rounded text-[10px] truncate
                              <?= $overdue ? 'bg-red-100 text-red-800' : ($t['priority'] === 'urgent' ? 'bg-orange-100 text-orange-800' : 'bg-indigo-100 text-indigo-800') ?>"
                       title="<?= e($t['title']) ?>">
                        ◷ <?= e($t['title']) ?>
                    </a>
                    <?php endforeach; ?>
                    <?php foreach ($items['cases'] ?? [] as $c): ?>
                    <a href="<?= e(tenant_url('cases/' . $c['id'])) ?>" class="block px-1.5 py-0.5 rounded text-[10px] truncate
                              <?= $c['marker'] === 'decision' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800' ?>"
                       title="<?= e($c['title']) ?>">
                        <?= $c['marker'] === 'decision' ? '✓' : '◇' ?> <?= e($c['case_number']) ?>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endfor; ?>
            <?php
            $cells_used = ($first_dow - 1) + $days_in_month;
            $remaining = (7 - ($cells_used % 7)) % 7;
            for ($i = 0; $i < $remaining; $i++): ?>
            <div class="bg-slate-50/30 min-h-[120px] border-b border-r border-slate-100"></div>
            <?php endfor; ?>
        </div>
    </div>

    <div class="flex flex-wrap gap-3 text-xs">
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-indigo-200"></span> Tarea</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-orange-200"></span> Tarea urgente</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-red-200"></span> Tarea vencida</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-amber-200"></span> Caso presentado</span>
        <span class="flex items-center gap-1.5"><span class="inline-block h-3 w-3 rounded bg-emerald-200"></span> Decision</span>
    </div>
</div>
