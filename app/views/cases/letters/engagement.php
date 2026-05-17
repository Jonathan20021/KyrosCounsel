<?php $t = current_tenant(); ?>
<?php require __DIR__ . '/_letter_base.php'; ?>
<div class="doc">
    <div class="letterhead">
        <div class="firm-name"><?= e(strtoupper($t['name'])) ?></div>
        <div class="firm-meta">Immigration Law Practice · <?= e(country_name($t['country'])) ?></div>
    </div>
    <p><?= e(date('F j, Y')) ?></p>
    <p>
        <?= e($case['first_name'] . ' ' . $case['last_name']) ?><br>
        <?= e($case['address'] ?? '[Client Address]') ?><br>
        <?php if ($case['client_email']): ?><?= e($case['client_email']) ?><br><?php endif; ?>
    </p>
    <div class="re-line">RE: Legal Representation Engagement Agreement — <?= e($case['case_type']) ?> Case</div>

    <p>Dear <?= e($case['first_name']) ?>,</p>

    <p>Thank you for choosing <strong><?= e($t['name']) ?></strong> to represent you in your immigration matter.
    This letter sets forth the terms of our engagement.</p>

    <p class="section-h">1. SCOPE OF REPRESENTATION</p>
    <p>This firm agrees to represent you in connection with the preparation and filing of Form
    <strong><?= e($case['case_type']) ?></strong> ("<?= e($case['title']) ?>"), case file <strong><?= e($case['case_number']) ?></strong>.
    Our representation includes:</p>
    <ul>
        <li>Initial consultation and case strategy</li>
        <li>Preparation and review of all required forms and supporting documents</li>
        <li>Filing the petition/application with USCIS or relevant agency</li>
        <li>Communication with USCIS on your behalf, including responses to RFEs</li>
        <li>Representation at any related interviews</li>
    </ul>

    <p class="section-h">2. FEES</p>
    <p>You agree to pay attorney fees in the amount of
    <strong>$<?= e(number_format((float)($case['attorney_fee_usd'] ?? 0), 2)) ?> USD</strong>
    for the services described above. This fee does not include:</p>
    <ul>
        <li>Government filing fees ($<?= e(number_format((float)($case['filing_fee_usd'] ?? 0), 0)) ?> for <?= e($case['case_type']) ?>)</li>
        <li>Translation costs</li>
        <li>Medical examination fees</li>
        <li>Postage, courier, and other out-of-pocket expenses</li>
    </ul>

    <p class="section-h">3. PAYMENT TERMS</p>
    <p>Payment may be made in installments according to the schedule we will agree upon separately.
    All filing fees and out-of-pocket expenses must be paid prior to filing.</p>

    <p class="section-h">4. CLIENT RESPONSIBILITIES</p>
    <p>You agree to:</p>
    <ul>
        <li>Provide accurate and complete information necessary for your case</li>
        <li>Respond promptly to requests for documents or information</li>
        <li>Keep this office informed of any changes in your address, telephone, or employment</li>
        <li>Notify this office immediately of any contact from immigration authorities</li>
    </ul>

    <p class="section-h">5. NO GUARANTEE OF OUTCOME</p>
    <p>While this office will use best efforts to achieve a favorable outcome, no specific result can be
    guaranteed. The decision rests entirely with USCIS and other governmental authorities.</p>

    <p class="section-h">6. TERMINATION</p>
    <p>Either party may terminate this engagement at any time with written notice. Upon termination, you
    will be billed for services rendered through the date of termination.</p>

    <p style="margin-top:2rem;">If the foregoing accurately reflects our agreement, please sign and return one copy to this office.</p>

    <p style="margin-top:2rem;">Sincerely,</p>
    <p style="margin-top:3rem;">
        ____________________________________<br>
        <strong><?= e($case['attorney_name'] ?? '[Attorney Name]') ?></strong><br>
        <?= e($t['name']) ?>
    </p>

    <p style="margin-top:2rem;font-weight:700;">AGREED AND ACCEPTED:</p>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:2rem;">
        <div class="signature-line">Client Signature</div>
        <div class="signature-line">Date</div>
    </div>
    <p style="margin-top:0.75rem;text-align:left;"><strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong></p>
</div>
