<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Analytics</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Reportes</h1>
            <p class="mt-1 text-sm text-slate-600">Tendencias y metricas de rendimiento del bufete</p>
        </div>
        <div class="flex gap-2 no-print">
            <button type="button" data-action="print" class="btn btn-secondary">
                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                Exportar PDF
            </button>
        </div>
    </div>

    <div class="print-only mb-6">
        <div class="text-center border-b border-slate-300 pb-4">
            <h1 class="text-2xl font-bold"><?= e(APP_NAME) ?> · Reporte ejecutivo</h1>
            <p class="text-sm mt-1"><?= e(current_tenant()['name']) ?> · <?= e(country_name(current_tenant()['country'])) ?></p>
            <p class="text-xs text-slate-600 mt-1">Generado el <?= e(date('d M Y H:i')) ?> · por <?= e(current_user()['name']) ?></p>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="kyros-card p-6">
            <h3 class="text-base font-semibold mb-4">Casos creados por mes</h3>
            <div class="chart-wrap"><canvas id="cMonth" height="100"></canvas></div>
        </div>

        <div class="kyros-card p-6">
            <h3 class="text-base font-semibold mb-4">Aprobacion vs Denegacion</h3>
            <div class="chart-wrap"><canvas id="cApproval" height="100"></canvas></div>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="kyros-card p-6">
            <h3 class="text-base font-semibold mb-4">Tiempo promedio de resolucion</h3>
            <div class="chart-wrap"><canvas id="cAvg" height="100"></canvas></div>
            <p class="mt-3 text-xs text-slate-500">Dias promedio entre apertura y decision (solo casos cerrados con decision).</p>
        </div>

        <div class="kyros-card p-6">
            <h3 class="text-base font-semibold mb-4">Productividad de tareas</h3>
            <div class="chart-wrap"><canvas id="cTasks" height="100"></canvas></div>
        </div>
    </div>

    <div class="kyros-card overflow-hidden">
        <div class="p-5 border-b border-slate-100">
            <h3 class="text-base font-semibold">Matriz tipo × estado</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-100 kyros-table">
                <thead class="bg-slate-50/50">
                    <tr>
                        <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Tipo</th>
                        <?php foreach (CASE_STATUSES as $code => $label): ?>
                        <th class="px-2 py-3 text-center font-semibold uppercase tracking-wider text-slate-500"><?= e(mb_substr($label, 0, 8)) ?></th>
                        <?php endforeach; ?>
                        <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-slate-500">Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    <?php
                    $matrix = [];
                    foreach ($type_status as $r) { $matrix[$r['case_type']][$r['status']] = (int)$r['c']; }
                    foreach ($matrix as $type => $rows):
                        $total = array_sum($rows);
                    ?>
                    <tr>
                        <td class="px-4 py-2 text-sm font-mono"><?= e($type) ?></td>
                        <?php foreach (CASE_STATUSES as $code => $_):
                            $val = $rows[$code] ?? 0;
                            $opacity = $val > 0 ? min(1, 0.15 + ($val / max(1, $total)) * 0.85) : 0;
                        ?>
                        <td class="px-2 py-2 text-center text-sm">
                            <?php if ($val > 0): ?>
                            <span class="inline-flex h-7 w-7 items-center justify-center rounded-md font-medium" style="background: rgba(99,102,241,<?= $opacity ?>); color: <?= $opacity > 0.5 ? 'white' : '#1e293b' ?>"><?= $val ?></span>
                            <?php else: ?>
                            <span class="text-slate-300">·</span>
                            <?php endif; ?>
                        </td>
                        <?php endforeach; ?>
                        <td class="px-4 py-2 text-right text-sm font-semibold"><?= $total ?></td>
                    </tr>
                    <?php endforeach; ?>
                    <?php if (empty($matrix)): ?>
                    <tr><td colspan="<?= count(CASE_STATUSES) + 2 ?>" class="empty-state"><p class="text-sm text-slate-500">Sin datos.</p></td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "Inter, system-ui, sans-serif";
    Chart.defaults.color = '#475569';

    const byMonth = <?= json_encode($by_month, JSON_UNESCAPED_UNICODE) ?>;
    const approval = <?= json_encode($approval, JSON_UNESCAPED_UNICODE) ?>;
    const avg = <?= json_encode($avg_days, JSON_UNESCAPED_UNICODE) ?>;
    const tasksWeek = <?= json_encode($tasks_week, JSON_UNESCAPED_UNICODE) ?>;

    const tooltipStyle = { backgroundColor: '#1e293b', padding: 10, cornerRadius: 8 };

    new Chart(document.getElementById('cMonth'), {
        type: 'bar',
        data: { labels: byMonth.map(r => r.m), datasets: [{
            data: byMonth.map(r => r.c), backgroundColor: '#6366f1', borderRadius: 6
        }]},
        options: { plugins: { legend: { display: false }, tooltip: tooltipStyle }, responsive: true, maintainAspectRatio: false,
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });

    new Chart(document.getElementById('cApproval'), {
        type: 'bar',
        data: { labels: approval.map(r => r.m), datasets: [
            { label: 'Aprobados', data: approval.map(r => r.approved), backgroundColor: '#10b981', borderRadius: 6 },
            { label: 'Denegados', data: approval.map(r => r.denied),   backgroundColor: '#ef4444', borderRadius: 6 },
        ]},
        options: { plugins: { legend: { position: 'bottom' }, tooltip: tooltipStyle }, responsive: true, maintainAspectRatio: false,
            scales: { x: { stacked: true, grid: { display: false } }, y: { stacked: true, beginAtZero: true, ticks: { precision: 0 } } } }
    });

    new Chart(document.getElementById('cAvg'), {
        type: 'bar',
        data: { labels: avg.map(r => r.case_type), datasets: [{
            data: avg.map(r => Math.round(parseFloat(r.avg_days || 0))),
            backgroundColor: '#f59e0b', borderRadius: 6
        }]},
        options: { indexAxis: 'y', plugins: { legend: { display: false }, tooltip: tooltipStyle }, responsive: true, maintainAspectRatio: false,
            scales: { x: { beginAtZero: true } } }
    });

    new Chart(document.getElementById('cTasks'), {
        type: 'line',
        data: { labels: tasksWeek.map(r => 'Sem ' + r.wk), datasets: [
            { label: 'Creadas', data: tasksWeek.map(r => r.total), borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.1)', tension: 0.35, fill: true },
            { label: 'Completadas', data: tasksWeek.map(r => r.done), borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.1)', tension: 0.35, fill: true },
        ]},
        options: { plugins: { legend: { position: 'bottom' }, tooltip: tooltipStyle }, responsive: true, maintainAspectRatio: false,
            scales: { x: { grid: { display: false } }, y: { beginAtZero: true, ticks: { precision: 0 } } } }
    });
});
</script>
