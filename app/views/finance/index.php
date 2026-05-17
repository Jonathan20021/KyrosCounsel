<div class="space-y-6">
    <!-- Header -->
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="kyros-eyebrow">CFO Dashboard</p>
            <h1 class="kyros-title mt-1">Finanzas</h1>
            <p class="kyros-subtitle mt-1">Análisis financiero del bufete</p>
        </div>
    </div>

    <!-- KPIs -->
    <div class="bento">
        <div class="bento-1 bento-md-6 bento-lg-3 kyros-stat">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="kyros-eyebrow">Cobrado</div>
                    <div class="mt-1 kyros-number text-3xl font-bold text-emerald-600">$<?= e(number_format((float)$stats['paid'], 0)) ?></div>
                </div>
                <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #d1fae5 0%, #6ee7b7 100%); color: #047857;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="20 6 9 17 4 12"/></svg>
                </div>
            </div>
        </div>
        <div class="bento-1 bento-md-6 bento-lg-3 kyros-stat">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="kyros-eyebrow">Pendiente (AR)</div>
                    <div class="mt-1 kyros-number text-3xl font-bold text-amber-600">$<?= e(number_format((float)$stats['pending'], 0)) ?></div>
                </div>
                <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%); color: #92400e;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                </div>
            </div>
        </div>
        <div class="bento-1 bento-md-6 bento-lg-3 kyros-stat">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="kyros-eyebrow">Cobrado este mes</div>
                    <div class="mt-1 kyros-number text-3xl font-bold">$<?= e(number_format((float)$stats['this_month'], 0)) ?></div>
                </div>
                <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #ddd6fe 0%, #a5b4fc 100%); color: #4338ca;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                </div>
            </div>
        </div>
        <div class="bento-1 bento-md-6 bento-lg-3 kyros-stat">
            <div class="flex items-start justify-between mb-3">
                <div>
                    <div class="kyros-eyebrow">Horas no facturadas</div>
                    <div class="mt-1 kyros-number text-3xl font-bold">$<?= e(number_format((float)$unbilled_time['total'], 0)) ?></div>
                    <div class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));"><?= e(round((int)$unbilled_time['minutes']/60, 1)) ?>h pendientes</div>
                </div>
                <div class="kyros-stat-icon" style="background: linear-gradient(135deg, #fed7aa 0%, #fb923c 100%); color: #c2410c;">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Ingresos por mes + Aging -->
    <div class="bento">
        <div class="bento-1 bento-lg-8 kyros-card p-5 lg:p-6">
            <div class="flex items-center justify-between mb-5">
                <div>
                    <p class="kyros-eyebrow">Tendencia</p>
                    <h3 class="text-lg font-semibold mt-1">Ingresos cobrados por mes</h3>
                    <p class="text-xs mt-0.5" style="color: hsl(var(--fg-muted));">Últimos 12 meses</p>
                </div>
            </div>
            <div class="chart-wrap"><canvas id="chartMonthly" height="80"></canvas></div>
        </div>

        <div class="bento-1 bento-lg-4 kyros-card p-5 lg:p-6">
            <p class="kyros-eyebrow">Accounts Receivable</p>
            <h3 class="text-lg font-semibold mt-1 mb-4">Aging</h3>
            <?php
            $buckets = [
                ['0-30 días',  $aging['bucket_30'],   '#10b981'],
                ['31-60 días', $aging['bucket_60'],   '#f59e0b'],
                ['61-90 días', $aging['bucket_90'],   '#f97316'],
                ['+90 días',   $aging['bucket_over'], '#ef4444'],
            ];
            $total_aging = array_sum(array_column($buckets, 1));
            ?>
            <ul class="space-y-3">
                <?php foreach ($buckets as [$label, $amt, $color]):
                    $pct = $total_aging > 0 ? round((float)$amt / $total_aging * 100) : 0;
                ?>
                <li>
                    <div class="flex items-center justify-between text-sm mb-1.5">
                        <span class="font-medium"><?= e($label) ?></span>
                        <span class="kyros-number font-semibold">$<?= e(number_format((float)$amt, 0)) ?></span>
                    </div>
                    <div class="h-1.5 rounded-full overflow-hidden" style="background: hsl(var(--surface-2));">
                        <div class="h-full rounded-full" style="width: <?= $pct ?>%; background: <?= $color ?>;"></div>
                    </div>
                </li>
                <?php endforeach; ?>
            </ul>
            <hr class="my-4" style="border-color: hsl(var(--border));">
            <div class="flex items-baseline justify-between">
                <span class="kyros-eyebrow">Total AR</span>
                <span class="kyros-number text-2xl font-bold">$<?= e(number_format($total_aging, 0)) ?></span>
            </div>
        </div>
    </div>

    <!-- Top deudores + Por categoria -->
    <div class="bento">
        <div class="bento-1 bento-lg-7 kyros-card overflow-hidden">
            <div class="px-5 py-4" style="border-bottom: 1px solid hsl(var(--border));">
                <p class="kyros-eyebrow">Cobranza</p>
                <h3 class="text-lg font-semibold mt-1">Top deudores</h3>
            </div>
            <ul>
                <?php foreach ($top_debtors as $d):
                    $days_old = round((time() - strtotime($d['oldest'])) / 86400);
                ?>
                <li class="px-5 py-3 flex items-center gap-3 hover:bg-slate-50/50 transition" style="border-top: 1px solid hsl(var(--border));">
                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 text-white flex items-center justify-center text-xs font-bold flex-shrink-0">
                        <?= e(strtoupper(mb_substr($d['first_name'], 0, 1) . mb_substr($d['last_name'], 0, 1))) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <a href="<?= e(tenant_url('clients/' . $d['id'])) ?>" class="font-medium text-sm hover:text-indigo-600"><?= e($d['last_name'] . ', ' . $d['first_name']) ?></a>
                        <div class="text-xs" style="color: hsl(var(--fg-muted));"><?= (int)$d['invoices'] ?> factura(s) · más antigua hace <?= $days_old ?>d</div>
                    </div>
                    <div class="text-right">
                        <div class="font-bold kyros-number text-amber-600">$<?= e(number_format((float)$d['pending'], 2)) ?></div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($top_debtors)): ?>
                <li class="empty-state"><div class="empty-state-icon">✓</div><p class="text-sm">Sin pagos pendientes 🎉</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <div class="bento-1 bento-lg-5 kyros-card p-5 lg:p-6">
            <p class="kyros-eyebrow">Mix de ingresos</p>
            <h3 class="text-lg font-semibold mt-1 mb-4">Por categoría</h3>
            <div class="chart-wrap"><canvas id="chartCat" height="120"></canvas></div>
            <ul class="mt-4 space-y-2">
                <?php
                $catLabels = ['attorney_fee' => 'Honorarios', 'filing_fee' => 'Filing fee', 'biometrics' => 'Biometrics', 'translation' => 'Traducción', 'other' => 'Otro'];
                foreach ($by_category as $bc):
                ?>
                <li class="flex items-center justify-between text-sm">
                    <span><?= e($catLabels[$bc['category']] ?? $bc['category']) ?></span>
                    <span class="font-mono">$<?= e(number_format((float)$bc['total'], 0)) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <!-- Pagos recientes -->
    <div class="kyros-card overflow-hidden">
        <div class="px-5 py-4" style="border-bottom: 1px solid hsl(var(--border));">
            <p class="kyros-eyebrow">Movimientos</p>
            <h3 class="text-lg font-semibold mt-1">Pagos recibidos recientes</h3>
        </div>
        <ul>
            <?php foreach ($recent as $r): ?>
            <li class="px-5 py-3 flex items-center gap-3" style="border-top: 1px solid hsl(var(--border));">
                <span class="badge-soft badge-success flex-shrink-0">✓ Pagado</span>
                <div class="flex-1 min-w-0">
                    <div class="text-sm font-medium"><?= e($r['concept']) ?></div>
                    <div class="text-xs" style="color: hsl(var(--fg-muted));">
                        <span class="font-mono"><?= e($r['case_number']) ?></span> · <?= e($r['first_name'] . ' ' . $r['last_name']) ?>
                        · <?= e($r['paid_at']) ?>
                    </div>
                </div>
                <div class="font-bold kyros-number text-emerald-600">$<?= e(number_format((float)$r['amount_usd'], 2)) ?></div>
            </li>
            <?php endforeach; ?>
            <?php if (empty($recent)): ?>
            <li class="empty-state"><div class="empty-state-icon">💰</div><p class="text-sm">Sin pagos cobrados aún.</p></li>
            <?php endif; ?>
        </ul>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function () {
    Chart.defaults.font.family = "Inter, system-ui, sans-serif";
    Chart.defaults.color = '#64748b';

    const monthly = <?= json_encode($monthly, JSON_UNESCAPED_UNICODE) ?>;
    const byCat = <?= json_encode($by_category, JSON_UNESCAPED_UNICODE) ?>;

    function gradient(ctx, color, alpha) {
        const g = ctx.createLinearGradient(0, 0, 0, 250);
        g.addColorStop(0, color + alpha);
        g.addColorStop(1, color + '00');
        return g;
    }

    if (monthly.length) {
        new Chart(document.getElementById('chartMonthly'), {
            type: 'bar',
            data: {
                labels: monthly.map(r => r.m),
                datasets: [{
                    data: monthly.map(r => parseFloat(r.total)),
                    backgroundColor: '#10b981', borderRadius: 8, borderSkipped: false,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false,
                plugins: { legend: { display: false }, tooltip: {
                    backgroundColor: '#1e293b', padding: 12, cornerRadius: 10, displayColors: false,
                    callbacks: { label: (ctx) => '$' + new Intl.NumberFormat('en-US').format(ctx.parsed.y) }
                }},
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: { beginAtZero: true, ticks: { callback: v => '$' + (v >= 1000 ? (v/1000) + 'k' : v) }, grid: { color: 'rgba(148,163,184,0.08)' }, border: { display: false } }
                }
            }
        });
    }

    if (byCat.length) {
        const catLabels = {attorney_fee: 'Honorarios', filing_fee: 'Filing fee', biometrics: 'Biometrics', translation: 'Traducción', other: 'Otro'};
        new Chart(document.getElementById('chartCat'), {
            type: 'doughnut',
            data: {
                labels: byCat.map(r => catLabels[r.category] || r.category),
                datasets: [{
                    data: byCat.map(r => parseFloat(r.total)),
                    backgroundColor: ['#6366f1','#10b981','#f59e0b','#8b5cf6','#0ea5e9'],
                    borderWidth: 3, borderColor: 'transparent', hoverOffset: 8, spacing: 2,
                }]
            },
            options: {
                responsive: true, maintainAspectRatio: false, cutout: '65%',
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b', padding: 12, cornerRadius: 10, displayColors: false,
                        callbacks: { label: (ctx) => ctx.label + ': $' + new Intl.NumberFormat('en-US').format(ctx.parsed) }
                    }
                }
            }
        });
    }
});
</script>
