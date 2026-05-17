<?php $t = current_tenant(); ?>
<?php require __DIR__ . '/_letter_base.php'; ?>
<div class="doc">
    <div class="letterhead">
        <div class="firm-name"><?= e(strtoupper($t['name'])) ?></div>
        <div class="firm-meta">Immigration Law Practice · <?= e(country_name($t['country'])) ?></div>
    </div>
    <p><?= e(date('F j, Y')) ?></p>
    <p>
        U.S. Citizenship and Immigration Services<br>
        <?php if ($case['service_center']): ?>
        <?= e(SERVICE_CENTERS[$case['service_center']] ?? $case['service_center']) ?><br>
        <?php else: ?>
        [Service Center Address]<br>
        <?php endif; ?>
    </p>
    <div class="re-line">
        RE: Response to Request for Evidence (RFE)<br>
        Petitioner/Applicant: <?= e($case['first_name'] . ' ' . $case['last_name']) ?><br>
        Form: <?= e($case['case_type']) ?>
        <?php if ($case['uscis_receipt']): ?><br>Receipt Number: <?= e($case['uscis_receipt']) ?><?php endif; ?>
        <?php if ($case['rfe_due_at']): ?><br>RFE Response Due: <?= e(date('F j, Y', strtotime($case['rfe_due_at']))) ?><?php endif; ?>
    </div>

    <p>Dear USCIS Officer:</p>

    <p>This office represents <strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong>
    in the above-referenced matter. We are submitting this response to the Request for Evidence (RFE) issued
    by USCIS regarding the Form <?= e($case['case_type']) ?>.</p>

    <p>In response to the specific issues raised in the RFE, please find enclosed the following documentation
    and explanations:</p>

    <p class="section-h">RESPONSE TO ISSUE #1</p>
    <p>[Describe the first issue raised in the RFE and provide your response with supporting evidence.]</p>

    <p class="section-h">RESPONSE TO ISSUE #2</p>
    <p>[Describe the second issue and your response.]</p>

    <p class="section-h">SUPPORTING DOCUMENTS</p>
    <ul>
        <li>Original RFE notice (for reference)</li>
        <li>[Document 1]</li>
        <li>[Document 2]</li>
        <li>[Additional supporting evidence]</li>
    </ul>

    <p>The petitioner has provided substantial documentation to address the concerns raised in the RFE.
    We respectfully submit that the evidence enclosed satisfies all requirements and request that you proceed
    with the favorable adjudication of this petition.</p>

    <p>Should you require any additional clarification or documentation, please do not hesitate to contact
    this office at <?= e($case['attorney_email'] ?? '[email]') ?>.</p>

    <p style="margin-top:2rem;">Respectfully submitted,</p>
    <p style="margin-top:3rem;">
        ____________________________________<br>
        <strong><?= e($case['attorney_name'] ?? '[Attorney Name]') ?></strong><br>
        Attorney for Petitioner<br>
        <?= e($t['name']) ?><br>
        <?= e($case['attorney_email'] ?? '') ?>
    </p>
    <p style="font-size:9pt;color:#666;">cc: Client file</p>
</div>
