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
        Asylum Office<br>
        [Asylum Office Address]<br>
    </p>
    <div class="re-line">
        RE: I-589 Application for Asylum and for Withholding of Removal<br>
        Applicant: <?= e($case['first_name'] . ' ' . $case['last_name']) ?><br>
        Country of Persecution: <?= e(country_name($case['nationality'] ?? '')) ?>
        <?php if ($case['uscis_receipt']): ?><br>Receipt: <?= e($case['uscis_receipt']) ?><?php endif; ?>
    </div>

    <p>Dear Asylum Officer:</p>

    <p>This office represents <strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong>,
    a national of <?= e(country_name($case['nationality'] ?? '')) ?>, in the above-referenced asylum application
    pursuant to INA §§ 208 and 241(b)(3).</p>

    <p class="section-h">BASIS FOR ASYLUM</p>
    <p>The applicant has suffered past persecution and has a well-founded fear of future persecution
    on account of [PROTECTED GROUND: race / religion / nationality / political opinion / membership
    in a particular social group] in <?= e(country_name($case['nationality'] ?? '')) ?>.</p>

    <p class="section-h">SUPPORTING EVIDENCE ENCLOSED</p>
    <ol>
        <li>Form I-589, completed and signed</li>
        <li>Form G-28, Notice of Entry of Appearance</li>
        <li>Applicant's detailed personal declaration</li>
        <li>Identity documents (passport, national ID)</li>
        <li>Country conditions reports:
            <ul>
                <li>U.S. State Department Country Reports on Human Rights Practices</li>
                <li>Reports from credible NGOs (Human Rights Watch, Amnesty International)</li>
                <li>Recent news articles documenting conditions</li>
            </ul>
        </li>
        <li>Witness affidavits supporting the applicant's account</li>
        <li>Medical/psychological evaluations (if applicable)</li>
        <li>Police reports or threats received (if available)</li>
        <li>Photographs documenting persecution or its effects</li>
    </ol>

    <p class="section-h">ELIGIBILITY ANALYSIS</p>
    <p>The applicant satisfies the elements of asylum:</p>
    <ol>
        <li><strong>Past persecution and/or well-founded fear:</strong> Documented in personal declaration
        and corroborating evidence;</li>
        <li><strong>Protected ground:</strong> [SPECIFY] — established by personal narrative and country
        conditions evidence;</li>
        <li><strong>Nexus:</strong> The persecution was/will be on account of the protected ground;</li>
        <li><strong>Government inability or unwillingness to control:</strong> Country conditions evidence
        demonstrates that the government of <?= e(country_name($case['nationality'] ?? '')) ?> is unable or
        unwilling to protect the applicant.</li>
    </ol>

    <p class="section-h">ONE-YEAR FILING REQUIREMENT</p>
    <p>This application is timely filed within one year of the applicant's arrival in the United States,
    pursuant to INA § 208(a)(2)(B). [OR: Applicant qualifies for an exception under INA § 208(a)(2)(D)
    based on changed/extraordinary circumstances.]</p>

    <p>The applicant is prepared to testify regarding the events and conditions described in the
    declaration. We respectfully request that the application be granted.</p>

    <p style="margin-top:2rem;">Respectfully submitted,</p>
    <p style="margin-top:3rem;">
        ____________________________________<br>
        <strong><?= e($case['attorney_name'] ?? '[Attorney Name]') ?></strong><br>
        Counsel for Applicant<br>
        <?= e($t['name']) ?><br>
        <?= e($case['attorney_email'] ?? '') ?>
    </p>
</div>
