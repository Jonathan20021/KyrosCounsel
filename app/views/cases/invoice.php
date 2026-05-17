<?php
$t = current_tenant();
$brand = $t['brand_color'] ?? '#4f46e5';
$invoice_num = 'INV-' . date('Y') . '-' . str_pad((string)$case['id'], 5, '0', STR_PAD_LEFT);
// Totales
$total_payments = 0; $paid = 0;
foreach ($payments as $p) { $total_payments += (float)$p['amount_usd']; if ($p['status'] === 'paid') $paid += (float)$p['amount_usd']; }
$total_time_value = 0;
$total_minutes = 0;
foreach ($time_entries as $te) {
    $val = ((int)$te['minutes'] / 60) * (float)$te['hourly_rate_usd'];
    $total_time_value += $val;
    $total_minutes += (int)$te['minutes'];
}
$grand_total = $total_payments + $total_time_value;
$balance_due = $grand_total - $paid;
?>
<style>
@media screen { body { background: #f1f5f9; padding: 2rem; } .doc { max-width: 8.5in; margin: 0 auto; padding: 1in; background: white; box-shadow: 0 4px 12px rgba(0,0,0,.1); } }
@media print { body { background: white; padding: 0; } .doc { padding: 0; box-shadow: none; max-width: none; } .no-print { display: none; } @page { margin: 0.6in; } }
.doc { font-family: Inter, system-ui, sans-serif; color: #1e293b; line-height: 1.5; font-size: 11pt; }
.doc h1 { font-size: 32pt; font-weight: 800; margin: 0; letter-spacing: -1px; color: <?= e($brand) ?>; }
.doc h2 { font-size: 12pt; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: <?= e($brand) ?>; margin: 2rem 0 0.5rem; }
.doc table { width: 100%; border-collapse: collapse; margin: 0.75rem 0; }
.doc table th, .doc table td { padding: 0.5rem 0.6rem; text-align: left; border-bottom: 1px solid #e2e8f0; }
.doc table th { background: #f8fafc; font-weight: 700; font-size: 10pt; color: #475569; text-transform: uppercase; letter-spacing: 0.04em; }
.doc table td.num { text-align: right; font-variant-numeric: tabular-nums; }
.doc .totals-row td { font-weight: 700; border-top: 2px solid #1e293b; font-size: 12pt; }
.kv { display: grid; grid-template-columns: auto 1fr; gap: 0.4rem 1rem; font-size: 10pt; }
.kv strong { font-weight: 600; color: #475569; }
</style>

<div class="no-print" style="text-align:center;padding:1rem;font-family:system-ui;">
    <button onclick="window.print()" style="padding:.6rem 1.2rem;background:<?= e($brand) ?>;color:white;border:0;border-radius:8px;font-weight:600;cursor:pointer;">🖨️ Imprimir / Guardar PDF</button>
    <a href="<?= e(tenant_url('cases/' . $case['id'])) ?>" style="margin-left:1rem;color:#64748b;">← Volver al caso</a>
</div>

<div class="doc">
    <!-- Header con identidad del bufete -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid <?= e($brand) ?>;padding-bottom:1rem;">
        <div>
            <div style="display:flex;align-items:center;gap:0.6rem;margin-bottom:0.25rem;">
                <div style="height:42px;width:42px;border-radius:10px;background:<?= e($brand) ?>;color:white;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:18pt;">
                    <?= e(strtoupper(mb_substr($t['name'], 0, 1))) ?>
                </div>
                <div>
                    <div style="font-size:14pt;font-weight:700;color:#1e293b;"><?= e($t['name']) ?></div>
                    <div style="font-size:9pt;color:#64748b;"><?= e(country_name($t['country'])) ?></div>
                </div>
            </div>
        </div>
        <div style="text-align:right;">
            <h1>FACTURA</h1>
            <div style="font-family:'JetBrains Mono',monospace;font-size:11pt;color:#64748b;margin-top:0.25rem;"><?= e($invoice_num) ?></div>
            <div style="font-size:9pt;color:#94a3b8;margin-top:0.25rem;">Emitida: <?= e(date('d M Y')) ?></div>
        </div>
    </div>

    <!-- Bill to -->
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:1.5rem;">
        <div>
            <div style="font-size:9pt;text-transform:uppercase;color:#64748b;letter-spacing:0.04em;font-weight:600;margin-bottom:0.25rem;">Facturar a</div>
            <div style="font-weight:700;font-size:12pt;"><?= e($case['first_name'] . ' ' . $case['last_name']) ?></div>
            <?php if ($case['client_email']): ?><div style="font-size:10pt;color:#64748b;"><?= e($case['client_email']) ?></div><?php endif; ?>
            <?php if ($case['address']): ?><div style="font-size:10pt;color:#64748b;"><?= e($case['address']) ?></div><?php endif; ?>
        </div>
        <div>
            <div style="font-size:9pt;text-transform:uppercase;color:#64748b;letter-spacing:0.04em;font-weight:600;margin-bottom:0.25rem;">Caso</div>
            <div class="kv">
                <strong>Número:</strong><span><?= e($case['case_number']) ?></span>
                <strong>Tipo:</strong><span><?= e($case['case_type']) ?></span>
                <strong>Título:</strong><span><?= e($case['title']) ?></span>
                <?php if ($case['attorney_name']): ?>
                <strong>Abogado:</strong><span><?= e($case['attorney_name']) ?></span>
                <?php endif; ?>
                <?php if ($case['uscis_receipt']): ?>
                <strong>USCIS:</strong><span style="font-family:monospace;"><?= e($case['uscis_receipt']) ?></span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Honorarios y fees -->
    <?php if (!empty($payments)): ?>
    <h2>Honorarios y costos</h2>
    <table>
        <thead>
            <tr><th>Concepto</th><th>Categoría</th><th>Estado</th><th class="num">Monto USD</th></tr>
        </thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= e($p['concept']) ?></td>
                <td style="font-size:10pt;color:#64748b;">
                    <?= e(['attorney_fee' => 'Honorarios', 'filing_fee' => 'Filing fee', 'biometrics' => 'Biometrics', 'translation' => 'Traducción', 'other' => 'Otro'][$p['category']] ?? $p['category']) ?>
                </td>
                <td style="font-size:10pt;">
                    <?php if ($p['status'] === 'paid'): ?>
                    <span style="color:#059669;font-weight:600;">✓ Pagado</span>
                    <?= $p['paid_at'] ? '<br><span style="font-size:9pt;color:#94a3b8;">' . e($p['paid_at']) . '</span>' : '' ?>
                    <?php else: ?>
                    <span style="color:#d97706;font-weight:600;">Pendiente</span>
                    <?php endif; ?>
                </td>
                <td class="num">$<?= e(number_format((float)$p['amount_usd'], 2)) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Time entries billables -->
    <?php if (!empty($time_entries)): ?>
    <h2>Horas de trabajo facturable</h2>
    <table>
        <thead>
            <tr><th>Fecha</th><th>Descripción</th><th>Profesional</th><th class="num">Tiempo</th><th class="num">Tarifa</th><th class="num">Monto</th></tr>
        </thead>
        <tbody>
            <?php foreach ($time_entries as $te):
                $hours = intdiv((int)$te['minutes'], 60);
                $mins = (int)$te['minutes'] % 60;
                $val = ((int)$te['minutes'] / 60) * (float)$te['hourly_rate_usd'];
            ?>
            <tr>
                <td style="font-size:10pt;"><?= e($te['entry_date']) ?></td>
                <td><?= e($te['description']) ?></td>
                <td style="font-size:10pt;color:#64748b;"><?= e($te['user_name'] ?? '—') ?></td>
                <td class="num" style="font-size:10pt;"><?= $hours ?>h <?= $mins ?>m</td>
                <td class="num" style="font-size:10pt;color:#64748b;">$<?= e(number_format((float)$te['hourly_rate_usd'], 0)) ?>/hr</td>
                <td class="num">$<?= e(number_format($val, 2)) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr>
                <td colspan="3" style="text-align:right;color:#64748b;font-style:italic;">Subtotal horas (<?= intdiv($total_minutes, 60) ?>h <?= $total_minutes % 60 ?>m):</td>
                <td colspan="3" class="num">$<?= e(number_format($total_time_value, 2)) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Totales -->
    <table style="margin-top:1.5rem;">
        <tbody>
            <tr>
                <td style="text-align:right;color:#64748b;">Subtotal:</td>
                <td class="num" style="width:140px;">$<?= e(number_format($grand_total, 2)) ?></td>
            </tr>
            <tr>
                <td style="text-align:right;color:#059669;">Pagado:</td>
                <td class="num" style="color:#059669;">−$<?= e(number_format($paid, 2)) ?></td>
            </tr>
            <tr class="totals-row">
                <td style="text-align:right;">SALDO PENDIENTE</td>
                <td class="num" style="color:<?= $balance_due > 0 ? '#dc2626' : '#059669' ?>;">$<?= e(number_format($balance_due, 2)) ?></td>
            </tr>
        </tbody>
    </table>

    <!-- Pago / nota -->
    <div style="margin-top:2rem;padding:1rem;background:#f8fafc;border-left:3px solid <?= e($brand) ?>;border-radius:6px;font-size:10pt;color:#475569;">
        <strong style="color:#1e293b;">Términos de pago:</strong> Pago dentro de 30 días desde la fecha de emisión. Para preguntas sobre esta factura, contacte a <?= e($case['attorney_name'] ?? $t['name']) ?>.
    </div>

    <div style="margin-top:3rem;padding-top:1rem;border-top:1px solid #e2e8f0;font-size:8pt;color:#94a3b8;text-align:center;">
        Factura generada por <?= e(APP_NAME) ?> · <?= e(date('d M Y H:i')) ?>
    </div>
</div>
