<?php $t = current_tenant(); ?>
<?php require __DIR__ . '/_letter_base.php'; ?>
<div class="doc">
    <div class="letterhead">
        <div class="firm-name"><?= e(strtoupper($t['name'])) ?></div>
        <div class="firm-meta">Immigration Law Practice · <?= e(country_name($t['country'])) ?></div>
    </div>
    <p><?= e(date('F j, Y')) ?></p>
    <p>VIA CERTIFIED MAIL — RETURN RECEIPT REQUESTED<br>and EMAIL</p>
    <p>
        <strong>[RECIPIENT NAME]</strong><br>
        [Recipient Address]<br>
    </p>
    <div class="re-line">
        RE: DEMAND FOR PAYMENT — <?= e($case['case_number']) ?><br>
        Client: <?= e($case['first_name'] . ' ' . $case['last_name']) ?>
    </div>

    <p>Dear [Recipient Name]:</p>

    <p>This office represents <strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong>.
    We write to formally demand payment of outstanding amounts due in connection with the above-referenced matter.</p>

    <p class="section-h">SUMMARY OF DEMAND</p>
    <p>Despite repeated requests, the following amounts remain unpaid and overdue:</p>
    <ul>
        <li>Principal owed: <strong>$[AMOUNT]</strong></li>
        <li>Date of original obligation: [DATE]</li>
        <li>Days past due: [NUMBER]</li>
    </ul>

    <p class="section-h">DEMAND</p>
    <p>You are hereby formally demanded to remit full payment of the amount owed within
    <strong>fourteen (14) days</strong> from the date of this letter. Payment may be made via certified
    check, money order, or wire transfer to:</p>
    <p style="margin-left:2rem;">
        <?= e($t['name']) ?><br>
        [Bank Account Information]
    </p>

    <p class="section-h">CONSEQUENCES OF NON-PAYMENT</p>
    <p>Should we fail to receive full payment within the timeframe specified, this firm is prepared
    to pursue all available legal remedies, including but not limited to:</p>
    <ul>
        <li>Filing a civil action for breach of contract and recovery of the principal amount, plus interest, attorney fees, and court costs;</li>
        <li>Reporting the debt to credit reporting agencies;</li>
        <li>Pursuing collection through a third-party agency.</li>
    </ul>

    <p>This letter is sent as a good-faith effort to resolve this matter without resorting to
    litigation. We strongly encourage you to take this demand seriously and respond promptly.</p>

    <p style="margin-top:2rem;">Govern yourself accordingly.</p>

    <p style="margin-top:2rem;">Sincerely,</p>
    <p style="margin-top:3rem;">
        ____________________________________<br>
        <strong><?= e($case['attorney_name'] ?? '[Attorney Name]') ?></strong><br>
        <?= e($t['name']) ?><br>
        <?= e($case['attorney_email'] ?? '') ?>
    </p>
</div>
