<?php $t = current_tenant(); ?>
<?php require __DIR__ . '/_letter_base.php'; ?>
<div class="doc">
    <div class="letterhead">
        <div class="firm-name">FORM I-864 SUPPORTING AFFIDAVIT</div>
        <div class="firm-meta">Affidavit of Support Under Section 213A of the INA · Cover Page</div>
    </div>

    <p style="text-align:center;font-size:14pt;font-weight:700;text-decoration:underline;margin:1.5rem 0;">
        AFFIDAVIT OF SUPPORT
    </p>

    <p>I, <strong>[SPONSOR NAME]</strong>, being duly sworn, depose and state under penalty of perjury under the
    laws of the United States that the following statements are true and correct to the best of my knowledge and belief:</p>

    <p class="section-h">1. SPONSOR IDENTIFICATION</p>
    <p>I am a citizen / lawful permanent resident of the United States, residing at [SPONSOR ADDRESS].
    My Social Security Number is [SSN]. I am submitting this affidavit in support of the application for
    permanent residence of the beneficiary identified below.</p>

    <p class="section-h">2. BENEFICIARY</p>
    <p>The beneficiary of this affidavit is <strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong>,
    a national of <?= e(country_name($case['nationality'] ?? '')) ?>, in connection with Form <?= e($case['case_type']) ?>
    (case <?= e($case['case_number']) ?>).</p>

    <p class="section-h">3. RELATIONSHIP TO BENEFICIARY</p>
    <p>My relationship to the beneficiary is: [SPECIFY: spouse, parent, child, sibling, employer, etc.]</p>

    <p class="section-h">4. FINANCIAL OBLIGATION</p>
    <p>I understand that under Section 213A of the Immigration and Nationality Act (INA), I am legally
    obligated to provide financial support to the beneficiary at an annual income of at least <strong>125%
    of the Federal Poverty Guidelines</strong> for my household size. This obligation continues until:</p>
    <ul>
        <li>The beneficiary becomes a U.S. citizen, OR</li>
        <li>The beneficiary has earned 40 quarters of work credit (approximately 10 years), OR</li>
        <li>The beneficiary permanently departs the United States, OR</li>
        <li>The beneficiary dies, OR</li>
        <li>I die.</li>
    </ul>

    <p class="section-h">5. INCOME AND ASSETS</p>
    <p>My most recent annual gross income is $[AMOUNT], as reflected in my federal income tax returns
    for the past three (3) years, copies of which are attached as supporting evidence.</p>

    <p>Household size for purposes of this affidavit: [NUMBER] persons.</p>

    <p class="section-h">6. SUPPORTING DOCUMENTATION ATTACHED</p>
    <ul>
        <li>Federal income tax returns (most recent 3 years)</li>
        <li>W-2s and/or 1099s</li>
        <li>Pay stubs (most recent 6 months)</li>
        <li>Letter from employer verifying employment and salary</li>
        <li>Bank statements (if relying on assets)</li>
        <li>Proof of U.S. citizenship or lawful permanent residence</li>
    </ul>

    <p style="margin-top:2rem;">I declare under penalty of perjury that the foregoing is true and correct.</p>

    <p style="margin-top:3rem;">Executed on this _____ day of _________________, <?= e(date('Y')) ?>, at ____________________________.</p>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:2rem;margin-top:2.5rem;">
        <div class="signature-line">Signature of Sponsor</div>
        <div class="signature-line">Printed Name of Sponsor</div>
    </div>

    <p style="margin-top:2rem;font-size:9pt;color:#666;text-align:center;">
        Prepared by <?= e($case['attorney_name'] ?? $t['name']) ?> · <?= e($t['name']) ?> · <?= e(date('d M Y')) ?>
    </p>
</div>
