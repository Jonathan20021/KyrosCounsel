<?php
$t = current_tenant();
$is_i130 = $case['case_type'] === 'I-130';
$is_i485 = $case['case_type'] === 'I-485';
$is_n400 = $case['case_type'] === 'N-400';
$petitioner = $case['first_name'] . ' ' . $case['last_name'];
$beneficiary = $beneficiaries[0] ?? null;
?>
<style>
@media screen { body { background: #f1f5f9; padding: 2rem; } .doc { max-width: 8.5in; margin: 0 auto; padding: 1in; background: white; box-shadow: 0 4px 12px rgba(0,0,0,.1); } }
@media print { body { background: white; padding: 0; } .doc { padding: 0; box-shadow: none; max-width: none; } .no-print { display: none; } @page { margin: 0.75in; } }
.doc { font-family: 'Times New Roman', Georgia, serif; color: #000; line-height: 1.6; font-size: 12pt; }
.doc p { margin: 0 0 0.75em; text-align: justify; }
.doc .letterhead { text-align: center; padding-bottom: 0.75rem; border-bottom: 2px solid #000; margin-bottom: 1.5rem; }
.doc .letterhead .firm-name { font-size: 16pt; font-weight: 700; letter-spacing: 0.5px; }
.doc .letterhead .firm-meta { font-size: 10pt; color: #444; margin-top: 0.25rem; }
.doc .re-line { font-weight: 700; margin: 1.5rem 0; padding: 0.5rem 0.75rem; background: #f5f5f5; }
.doc ul { margin: 0.5rem 0 1rem 1.5rem; }
.doc li { margin-bottom: 0.25rem; }
.doc .section-h { font-weight: 700; text-decoration: underline; margin-top: 1rem; }
</style>

<div class="no-print" style="text-align:center;padding:1rem;font-family:system-ui;">
    <button onclick="window.print()" style="padding:.6rem 1.2rem;background:#4f46e5;color:white;border:0;border-radius:8px;font-weight:600;cursor:pointer;">🖨️ Imprimir / Guardar PDF</button>
    <a href="<?= e(tenant_url('cases/' . $case['id'])) ?>" style="margin-left:1rem;color:#64748b;">← Volver al caso</a>
</div>

<div class="doc">
    <!-- Letterhead -->
    <div class="letterhead">
        <div class="firm-name"><?= e(strtoupper($t['name'])) ?></div>
        <div class="firm-meta">Immigration Law Practice · <?= e(country_name($t['country'])) ?></div>
    </div>

    <!-- Date -->
    <p><?= e(date('F j, Y')) ?></p>

    <!-- Recipient -->
    <p>
        U.S. Citizenship and Immigration Services<br>
        <?php if ($case['service_center']): ?>
        <?= e(SERVICE_CENTERS[$case['service_center']] ?? $case['service_center']) ?><br>
        <?php else: ?>
        [Service Center Address]<br>
        <?php endif; ?>
    </p>

    <!-- Re line -->
    <div class="re-line">
        RE: <?= e($case['case_type']) ?> Petition for <?= e($petitioner) ?>
        <?php if ($case['uscis_receipt']): ?>
        <br>Receipt Number: <?= e($case['uscis_receipt']) ?>
        <?php endif; ?>
    </div>

    <p>Dear USCIS Officer:</p>

    <?php if ($is_i130): ?>
    <p>This office represents <strong><?= e($petitioner) ?></strong>, the petitioner in the above-referenced
    Form I-130, Petition for Alien Relative, filed on behalf of
    <?= $beneficiary ? '<strong>' . e($beneficiary['first_name'] . ' ' . $beneficiary['last_name']) . '</strong>' : '[Beneficiary Name]' ?>,
    the beneficiary. Enclosed please find the following documents in support of this petition:</p>

    <p class="section-h">SUPPORTING DOCUMENTS</p>
    <ul>
        <li>Form I-130, completed and signed</li>
        <li>Filing fee in the amount of $<?= e(number_format((float)$case['filing_fee_usd'] ?: 535, 0)) ?>.00</li>
        <li>Form G-28, Notice of Entry of Appearance as Attorney</li>
        <?php foreach ($evidence as $ev): ?>
        <li><?= e($ev['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <p>The petitioner and beneficiary share a bona fide relationship as evidenced by the supporting
    documentation. We respectfully request that you approve this petition based on the evidence submitted.</p>

    <?php elseif ($is_i485): ?>
    <p>This office represents <strong><?= e($petitioner) ?></strong>, the applicant in the above-referenced
    Form I-485, Application to Register Permanent Residence or Adjust Status. Enclosed please find the
    following documents in support of this application:</p>

    <p class="section-h">SUPPORTING DOCUMENTS</p>
    <ul>
        <li>Form I-485, completed and signed</li>
        <li>Filing fee in the amount of $<?= e(number_format((float)$case['filing_fee_usd'] ?: 1440, 0)) ?>.00</li>
        <li>Form G-28, Notice of Entry of Appearance as Attorney</li>
        <li>Form I-693, Medical Examination (in sealed envelope)</li>
        <li>Form I-864, Affidavit of Support, with supporting financial evidence</li>
        <?php foreach ($evidence as $ev): ?>
        <li><?= e($ev['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <p>The applicant is eligible to adjust status pursuant to INA §245(a). All required forms, fees,
    and supporting documentation are enclosed. We respectfully request that you approve this application.</p>

    <?php elseif ($is_n400): ?>
    <p>This office represents <strong><?= e($petitioner) ?></strong>, the applicant in the above-referenced
    Form N-400, Application for Naturalization. Enclosed please find:</p>

    <p class="section-h">SUPPORTING DOCUMENTS</p>
    <ul>
        <li>Form N-400, completed and signed</li>
        <li>Filing fee in the amount of $<?= e(number_format((float)$case['filing_fee_usd'] ?: 760, 0)) ?>.00</li>
        <li>Form G-28, Notice of Entry of Appearance as Attorney</li>
        <li>Copy of Permanent Resident Card (front and back)</li>
        <?php foreach ($evidence as $ev): ?>
        <li><?= e($ev['name']) ?></li>
        <?php endforeach; ?>
    </ul>

    <p>The applicant meets all eligibility requirements for naturalization pursuant to INA §316(a),
    including continuous residence, physical presence, good moral character, and attachment to the
    principles of the U.S. Constitution.</p>

    <?php else: ?>
    <p>This office represents <strong><?= e($petitioner) ?></strong> in the above-referenced
    <?= e($case['case_type']) ?> matter. Enclosed please find:</p>

    <p class="section-h">SUPPORTING DOCUMENTS</p>
    <ul>
        <li>Form <?= e($case['case_type']) ?>, completed and signed</li>
        <?php if ((float)$case['filing_fee_usd'] > 0): ?>
        <li>Filing fee in the amount of $<?= e(number_format((float)$case['filing_fee_usd'], 0)) ?>.00</li>
        <?php endif; ?>
        <li>Form G-28, Notice of Entry of Appearance as Attorney</li>
        <?php foreach ($evidence as $ev): ?>
        <li><?= e($ev['name']) ?></li>
        <?php endforeach; ?>
    </ul>
    <?php endif; ?>

    <p>If you require any additional information or documentation, please do not hesitate to contact
    this office at <?= e($case['attorney_email'] ?? '[email]') ?>. Thank you for your time and consideration.</p>

    <p style="margin-top:2rem;">Respectfully submitted,</p>
    <p style="margin-top:3rem;">
        ____________________________________<br>
        <strong><?= e($case['attorney_name'] ?? '[Attorney Name]') ?></strong><br>
        Attorney for Petitioner<br>
        <?= e($t['name']) ?><br>
        <?= e($case['attorney_email'] ?? '') ?>
    </p>

    <p style="margin-top:2rem;font-size:9pt;color:#666;">Enclosures as listed above.</p>
</div>
