<?php
$revJson = json_encode(array_map(fn($r)=>['d'=>$r['d'],'s'=>(float)$r['s']], $series));
?>

<div class="mb-6 flex items-baseline justify-between flex-wrap gap-3">
    <div>
        <h1 class="text-3xl font-bold tracking-tight">Facturación</h1>
        <p class="mt-1 text-sm text-slate-600">Ledger global de invoices, MRR y métricas</p>
    </div>
    <div class="flex flex-wrap gap-2">
        <form method="POST" action="<?= e(url('admin/billing/generate')) ?>" data-confirm="¿Generar facturas para el mes en curso? (idempotente)">
            <?= csrf_field() ?>
            <button class="btn btn-primary" style="background:#d97706;">Generar invoices del mes</button>
        </form>
        <a href="<?= e(url('admin/export/invoices.csv')) ?>" class="btn btn-secondary">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    <div class="kyros-card p-5"><div class="kyros-eyebrow">MRR actual</div><div class="text-3xl font-bold mt-1">$<?= number_format($mrr, 0) ?></div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">ARR proyectado</div><div class="text-3xl font-bold mt-1">$<?= number_format($arr, 0) ?></div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">ARPU</div><div class="text-3xl font-bold mt-1">$<?= number_format($arpu, 2) ?></div></div>
    <div class="kyros-card p-5"><div class="kyros-eyebrow">Pagado total</div><div class="text-3xl font-bold mt-1 text-emerald-600">$<?= number_format($totals['paid'], 0) ?></div></div>
</div>

<!-- Status pipeline -->
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 mb-6">
    <div class="kyros-card p-4 border-l-4 border-emerald-500"><div class="text-xs uppercase font-semibold text-slate-500">Pagadas</div><div class="text-2xl font-bold mt-1">$<?= number_format($totals['paid'], 2) ?></div></div>
    <div class="kyros-card p-4 border-l-4 border-amber-500"><div class="text-xs uppercase font-semibold text-slate-500">Pendientes</div><div class="text-2xl font-bold mt-1">$<?= number_format($totals['pending'], 2) ?></div></div>
    <div class="kyros-card p-4 border-l-4 border-rose-500"><div class="text-xs uppercase font-semibold text-slate-500">Fallidas</div><div class="text-2xl font-bold mt-1">$<?= number_format($totals['failed'], 2) ?></div></div>
    <div class="kyros-card p-4 border-l-4 border-slate-400"><div class="text-xs uppercase font-semibold text-slate-500">Reembolsadas</div><div class="text-2xl font-bold mt-1">$<?= number_format($totals['refunded'], 2) ?></div></div>
</div>

<!-- Chart -->
<div class="kyros-card p-5 mb-6">
    <h3 class="font-semibold mb-3">Ingresos pagados — 30 días</h3>
    <div class="chart-wrap"><canvas id="chBilling"></canvas></div>
</div>

<!-- Invoices table -->
<div class="kyros-card overflow-hidden">
    <div class="p-4 border-b border-slate-200 flex items-center justify-between">
        <h3 class="font-semibold">Facturas (<?= count($invoices) ?>)</h3>
    </div>
    <table class="kyros-table w-full">
        <thead class="bg-slate-50"><tr class="text-left text-xs uppercase text-slate-500">
            <th class="px-4 py-3">Número</th>
            <th class="px-4 py-3">Tenant</th>
            <th class="px-4 py-3">Plan</th>
            <th class="px-4 py-3 text-right">Monto</th>
            <th class="px-4 py-3">Estado</th>
            <th class="px-4 py-3">Pagada</th>
            <th class="px-4 py-3"></th>
        </tr></thead>
        <tbody class="divide-y divide-slate-100">
            <?php foreach ($invoices as $i): ?>
            <tr class="hover:bg-slate-50">
                <td class="px-4 py-3 text-xs font-mono"><?= e($i['invoice_number'] ?? '#' . $i['id']) ?></td>
                <td class="px-4 py-3 text-sm"><a href="<?= e(url('admin/tenants/' . $i['tenant_id'])) ?>" class="text-amber-700 hover:text-amber-600"><?= e($i['tenant_name']) ?></a></td>
                <td class="px-4 py-3 text-sm"><?= e($i['plan_name'] ?? '—') ?></td>
                <td class="px-4 py-3 text-right text-sm font-mono">$<?= number_format((float)$i['amount_usd'], 2) ?></td>
                <td class="px-4 py-3"><span class="badge-soft <?= ['paid'=>'badge-success','pending'=>'badge-warning','failed'=>'badge-danger','refunded'=>'badge-neutral'][$i['status']] ?>"><?= e($i['status']) ?></span></td>
                <td class="px-4 py-3 text-xs text-slate-500"><?= $i['paid_at'] ? e(date('Y-m-d', strtotime($i['paid_at']))) : '—' ?></td>
                <td class="px-4 py-3 text-right">
                    <form method="POST" action="<?= e(url('admin/billing/' . $i['id'] . '/mark')) ?>" class="inline-flex gap-1">
                        <?= csrf_field() ?>
                        <select name="status" class="text-xs rounded border border-slate-200 px-1.5 py-0.5">
                            <option value="paid">paid</option>
                            <option value="pending">pending</option>
                            <option value="failed">failed</option>
                            <option value="refunded">refunded</option>
                        </select>
                        <button class="text-xs font-semibold text-amber-700 hover:text-amber-600">Marcar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
            <?php if (empty($invoices)): ?>
            <tr><td colspan="7" class="px-4 py-12 text-center text-sm text-slate-500">Sin facturas. Se generan automáticamente al cobrar suscripciones.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
document.addEventListener('DOMContentLoaded', function() {
    if (!window.Chart) return;
    var data = <?= $revJson ?>;
    new Chart(document.getElementById('chBilling'), {
        type: 'line',
        data: {
            labels: data.map(r => r.d.slice(5)),
            datasets: [{
                data: data.map(r => r.s),
                fill: true, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.10)',
                tension: 0.3, borderWidth: 2.5, pointRadius: 0
            }]
        },
        options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true } } }
    });
});
</script>
