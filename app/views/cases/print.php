<?php $t = current_tenant(); ?>
<style>
@media screen { body { background: #f1f5f9; padding: 2rem; } .sheet { max-width: 8.5in; margin: 0 auto; padding: 1in; background: white; box-shadow: 0 4px 12px rgba(0,0,0,.1); } }
@media print { body { background: white; padding: 0; } .sheet { padding: 0; box-shadow: none; max-width: none; } .no-print { display: none; } @page { margin: 0.6in; } }
.sheet { font-family: Inter, system-ui, sans-serif; color: #1e293b; line-height: 1.5; }
.sheet h1 { font-size: 28px; font-weight: 800; margin: 0 0 0.25rem; }
.sheet h2 { font-size: 16px; font-weight: 700; margin: 1.5rem 0 0.5rem; padding-bottom: 0.25rem; border-bottom: 2px solid #4f46e5; color: #4f46e5; }
.sheet table { width: 100%; border-collapse: collapse; margin: 0.5rem 0; font-size: 12px; }
.sheet table th, .sheet table td { border: 1px solid #e2e8f0; padding: 0.4rem 0.6rem; text-align: left; }
.sheet table th { background: #f8fafc; font-weight: 600; }
.kv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem 1rem; font-size: 13px; margin: 0.5rem 0; }
.kv-grid div strong { color: #475569; font-weight: 600; }
</style>
<div class="no-print" style="text-align:center;padding:1rem;">
    <button onclick="window.print()" style="padding:.6rem 1.2rem;background:#4f46e5;color:white;border:0;border-radius:8px;font-weight:600;cursor:pointer;">🖨️ Imprimir / Guardar PDF</button>
    <a href="<?= e(tenant_url('cases/' . $case['id'])) ?>" style="margin-left:1rem;color:#64748b;">← Volver</a>
</div>

<div class="sheet">
    <!-- Header -->
    <div style="display:flex;justify-content:space-between;align-items:flex-start;border-bottom:3px solid #1e293b;padding-bottom:0.75rem;">
        <div>
            <h1><?= e($t['name']) ?></h1>
            <div style="font-size:11px;color:#64748b;"><?= e(country_name($t['country'])) ?> · <?= e(APP_NAME) ?></div>
        </div>
        <div style="text-align:right;">
            <div style="font-size:11px;color:#64748b;text-transform:uppercase;letter-spacing:.05em;">Hoja del caso</div>
            <div style="font-family:'JetBrains Mono',monospace;font-size:18px;font-weight:700;"><?= e($case['case_number']) ?></div>
            <div style="font-size:11px;color:#64748b;">Generado: <?= e(date('d M Y H:i')) ?></div>
        </div>
    </div>

    <!-- Resumen -->
    <h2>📋 Resumen del caso</h2>
    <div class="kv-grid">
        <div><strong>Titulo:</strong> <?= e($case['title']) ?></div>
        <div><strong>Tipo:</strong> <?= e($case['case_type']) ?> · <?= e(country_name($case['country'])) ?></div>
        <div><strong>Estado:</strong> <?= e(case_status_label($case['status'])) ?></div>
        <div><strong>Prioridad:</strong> <?= e(CASE_PRIORITIES[$case['priority']]) ?></div>
        <div><strong>Apertura:</strong> <?= e($case['opened_at']) ?></div>
        <div><strong>Presentado:</strong> <?= e($case['filed_at'] ?? '—') ?></div>
        <div><strong>Decision:</strong> <?= e($case['decision_at'] ?? '—') ?></div>
        <div><strong>Abogado:</strong> <?= e($case['attorney_name'] ?? '—') ?></div>
    </div>

    <!-- Cliente -->
    <h2>👤 Cliente</h2>
    <div class="kv-grid">
        <div><strong>Nombre:</strong> <?= e($case['first_name'] . ' ' . $case['last_name']) ?></div>
        <div><strong>Nacionalidad:</strong> <?= e(country_name($case['nationality'] ?? '')) ?></div>
        <div><strong>Email:</strong> <?= e($case['client_email'] ?? '—') ?></div>
        <div><strong>Telefono:</strong> <?= e($case['client_phone'] ?? '—') ?></div>
    </div>

    <!-- USCIS -->
    <?php if ($case['uscis_receipt'] || $case['biometrics_at'] || $case['interview_at']): ?>
    <h2>🏛️ USCIS</h2>
    <div class="kv-grid">
        <?php if ($case['uscis_receipt']): ?><div><strong>Receipt:</strong> <span style="font-family:monospace;"><?= e($case['uscis_receipt']) ?></span></div><?php endif; ?>
        <?php if ($case['receipt_date']): ?><div><strong>Fecha recibo:</strong> <?= e($case['receipt_date']) ?></div><?php endif; ?>
        <?php if ($case['service_center']): ?><div><strong>Service Center:</strong> <?= e($case['service_center']) ?></div><?php endif; ?>
        <?php if ($case['biometrics_at']): ?><div><strong>Biometrics:</strong> <?= e($case['biometrics_at']) ?></div><?php endif; ?>
        <?php if ($case['interview_at']): ?><div><strong>Entrevista:</strong> <?= e($case['interview_at']) ?></div><?php endif; ?>
        <?php if ($case['rfe_due_at']): ?><div><strong>Vence RFE:</strong> <?= e($case['rfe_due_at']) ?></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <!-- Beneficiarios -->
    <?php if (!empty($beneficiaries)): ?>
    <h2>👨‍👩‍👧 Beneficiarios / dependientes</h2>
    <table>
        <thead><tr><th>Nombre</th><th>Relacion</th><th>Fecha nac.</th><th>Nacionalidad</th></tr></thead>
        <tbody>
            <?php foreach ($beneficiaries as $b): ?>
            <tr>
                <td><?= e($b['first_name'] . ' ' . $b['last_name']) ?></td>
                <td><?= e(ucfirst($b['relationship'])) ?></td>
                <td><?= e($b['date_of_birth'] ?? '—') ?></td>
                <td><?= e(country_name($b['nationality'] ?? '')) ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Evidencia -->
    <?php if (!empty($evidence)): ?>
    <h2>📋 Checklist de evidencia</h2>
    <table>
        <thead><tr><th style="width:50px;">Estado</th><th>Item</th><th>Categoria</th><th>Recibido</th></tr></thead>
        <tbody>
            <?php foreach ($evidence as $ev): ?>
            <tr>
                <td style="text-align:center;font-size:14px;"><?= $ev['is_received'] ? '✓' : ($ev['is_required'] ? '⚠' : '·') ?></td>
                <td><?= e($ev['name']) ?> <?= $ev['is_required'] ? '<span style="color:#dc2626;font-size:10px;">(requerido)</span>' : '' ?></td>
                <td style="font-size:11px;color:#64748b;"><?= e(DOCUMENT_CATEGORIES[$ev['category']] ?? '—') ?></td>
                <td style="font-size:11px;"><?= e($ev['received_at'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Pagos -->
    <?php if (!empty($payments)): ?>
    <h2>💰 Pagos y honorarios</h2>
    <table>
        <thead><tr><th>Concepto</th><th>Categoria</th><th>Estado</th><th style="text-align:right;">Monto</th></tr></thead>
        <tbody>
            <?php foreach ($payments as $p): ?>
            <tr>
                <td><?= e($p['concept']) ?></td>
                <td style="font-size:11px;"><?= e($p['category']) ?></td>
                <td><?= e($p['status']) ?> <?= $p['paid_at'] ? '· ' . e($p['paid_at']) : '' ?></td>
                <td style="text-align:right;font-family:monospace;">$<?= e(number_format((float)$p['amount_usd'], 2)) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr style="background:#f8fafc;font-weight:700;">
                <td colspan="3" style="text-align:right;">Total facturado:</td>
                <td style="text-align:right;font-family:monospace;">$<?= e(number_format($totals['total'], 2)) ?></td>
            </tr>
            <tr style="background:#f0fdf4;font-weight:700;color:#166534;">
                <td colspan="3" style="text-align:right;">Pagado:</td>
                <td style="text-align:right;font-family:monospace;">$<?= e(number_format($totals['paid'], 2)) ?></td>
            </tr>
            <tr style="background:#fef3c7;font-weight:700;color:#92400e;">
                <td colspan="3" style="text-align:right;">Balance pendiente:</td>
                <td style="text-align:right;font-family:monospace;">$<?= e(number_format($totals['total'] - $totals['paid'], 2)) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Citas -->
    <?php if (!empty($appointments)): ?>
    <h2>📅 Citas</h2>
    <table>
        <thead><tr><th>Fecha y hora</th><th>Tipo</th><th>Titulo</th><th>Lugar</th></tr></thead>
        <tbody>
            <?php foreach ($appointments as $apt): ?>
            <tr>
                <td><?= e(date('d M Y H:i', strtotime($apt['starts_at']))) ?></td>
                <td><?= e($apt['type']) ?></td>
                <td><?= e($apt['title']) ?></td>
                <td><?= e($apt['location'] ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <?php endif; ?>

    <!-- Historial -->
    <h2>📜 Historial de estados</h2>
    <table>
        <thead><tr><th>Fecha</th><th>Cambio</th><th>Por</th><th>Nota</th></tr></thead>
        <tbody>
            <?php foreach ($history as $h): ?>
            <tr>
                <td><?= e(date('d M Y H:i', strtotime($h['created_at']))) ?></td>
                <td><?= $h['from_status'] ? e(case_status_label($h['from_status'])) . ' → ' : '' ?><strong><?= e(case_status_label($h['to_status'])) ?></strong></td>
                <td><?= e($h['changed_by_name'] ?? 'Sistema') ?></td>
                <td><?= e($h['note'] ?? '') ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div style="margin-top:2rem;padding-top:1rem;border-top:1px solid #e2e8f0;font-size:10px;color:#94a3b8;text-align:center;">
        Documento generado por <?= e(APP_NAME) ?> · Confidencial · Para uso interno del bufete
    </div>
</div>
