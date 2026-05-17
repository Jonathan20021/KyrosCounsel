<?php $t = current_tenant(); ?>
<style>
@media screen { body { background: #f1f5f9; padding: 2rem; } .doc { max-width: 8.5in; margin: 0 auto; padding: 1in; background: white; box-shadow: 0 4px 12px rgba(0,0,0,.1); } }
@media print { body { background: white; padding: 0; } .doc { padding: 0; box-shadow: none; max-width: none; } .no-print { display: none; } @page { margin: 0.75in; } }
.doc { font-family: 'Times New Roman', Georgia, serif; color: #1e293b; line-height: 1.6; font-size: 12pt; }
.doc h1 { font-size: 18pt; font-weight: 700; text-align: center; margin: 0; }
.doc h2 { font-size: 14pt; font-weight: 700; margin: 1.5rem 0 0.75rem; border-bottom: 2px solid #1e293b; padding-bottom: 0.25rem; }
.doc .field { display: grid; grid-template-columns: 200px 1fr; gap: 0.5rem; margin: 0.4rem 0; }
.doc .field strong { font-weight: 700; }
.doc .field .value { border-bottom: 1px solid #94a3b8; padding-bottom: 2px; min-height: 1.5em; }
.doc .signature-row { display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-top: 3rem; }
.doc .signature-row .sig { border-top: 1px solid #1e293b; padding-top: 4px; font-size: 10pt; text-align: center; min-height: 60px; }
.checkbox { display: inline-block; width: 14px; height: 14px; border: 1.5px solid #1e293b; vertical-align: middle; margin-right: 0.4rem; }
.checkbox.checked::after { content: '✓'; display: block; text-align: center; line-height: 11px; font-weight: bold; }
</style>

<div class="no-print" style="text-align:center;padding:1rem;font-family:system-ui;">
    <button onclick="window.print()" style="padding:.6rem 1.2rem;background:#4f46e5;color:white;border:0;border-radius:8px;font-weight:600;cursor:pointer;">🖨️ Imprimir / Guardar PDF</button>
    <a href="<?= e(tenant_url('cases/' . $case['id'])) ?>" style="margin-left:1rem;color:#64748b;">← Volver al caso</a>
</div>

<div class="doc">
    <div style="text-align:right;font-size:9pt;color:#64748b;">
        <strong>Form G-28</strong><br>
        Notice of Entry of Appearance as Attorney<br>
        OMB No. 1615-0105 | Expires 02/28/2027
    </div>
    <h1>NOTICE OF ENTRY OF APPEARANCE<br>AS ATTORNEY OR ACCREDITED REPRESENTATIVE</h1>
    <p style="text-align:center;font-size:10pt;color:#475569;">Department of Homeland Security · U.S. Citizenship and Immigration Services</p>

    <h2>Part 1 · Information About Attorney or Representative</h2>
    <div class="field"><strong>Full Legal Name:</strong>           <span class="value"><?= e($case['attorney_name'] ?? '') ?></span></div>
    <div class="field"><strong>Firm / Organization:</strong>       <span class="value"><?= e($t['name']) ?></span></div>
    <div class="field"><strong>Email:</strong>                     <span class="value"><?= e($case['attorney_email'] ?? '') ?></span></div>
    <div class="field"><strong>Country / Jurisdiction:</strong>    <span class="value"><?= e(country_name($t['country'])) ?></span></div>
    <div class="field"><strong>State Bar Number:</strong>          <span class="value">________________________________</span></div>

    <h2>Part 2 · Information About Client</h2>
    <div class="field"><strong>Full Name:</strong>                 <span class="value"><?= e($case['first_name'] . ' ' . $case['last_name']) ?></span></div>
    <div class="field"><strong>A-Number:</strong>                  <span class="value">A-________________________________</span></div>
    <div class="field"><strong>Country of Citizenship:</strong>    <span class="value"><?= e(country_name($case['nationality'] ?? '')) ?></span></div>
    <div class="field"><strong>Email:</strong>                     <span class="value"><?= e($case['client_email'] ?? '') ?></span></div>
    <div class="field"><strong>Phone:</strong>                     <span class="value"><?= e($case['client_phone'] ?? '') ?></span></div>
    <div class="field"><strong>Address:</strong>                   <span class="value"><?= e($case['address'] ?? '') ?></span></div>

    <h2>Part 3 · Notice of Appearance</h2>
    <p>I, the attorney/representative named in Part 1, hereby enter my appearance as the legal representative of the client named in Part 2 in connection with the following matter:</p>
    <div class="field"><strong>Form / Matter:</strong>             <span class="value"><?= e($case['case_type']) ?> — <?= e($case['title']) ?></span></div>
    <div class="field"><strong>Receipt # (if any):</strong>        <span class="value font-mono"><?= e($case['uscis_receipt'] ?? '') ?></span></div>
    <div class="field"><strong>Filed on:</strong>                  <span class="value"><?= $case['filed_at'] ? e(date('m/d/Y', strtotime($case['filed_at']))) : '' ?></span></div>

    <h2>Part 4 · Eligibility (mark one)</h2>
    <p style="font-size:11pt;line-height:1.8;">
        <span class="checkbox checked"></span> I am an attorney eligible to practice law in, and a member in good standing of, the bar of the highest court(s) of the following jurisdiction(s).<br>
        <span class="checkbox"></span> I am an accredited representative of the qualified non-profit organization named in Part 1.<br>
        <span class="checkbox"></span> Other (explain): _________________________________________________
    </p>

    <h2>Part 5 · Signature of Attorney</h2>
    <div class="signature-row">
        <div class="sig">Signature of Attorney/Representative</div>
        <div class="sig">Date (mm/dd/yyyy): <?= e(date('m/d/Y')) ?></div>
    </div>

    <h2>Part 6 · Client's Consent and Signature</h2>
    <p style="font-size:11pt;">I, <strong><?= e($case['first_name'] . ' ' . $case['last_name']) ?></strong>, hereby consent to the representation by the attorney/representative listed above for the matter referenced in Part 3. I authorize the release of information about my case to my representative.</p>
    <div class="signature-row">
        <div class="sig">Signature of Client</div>
        <div class="sig">Date (mm/dd/yyyy)</div>
    </div>

    <p style="margin-top:3rem;font-size:9pt;color:#64748b;text-align:center;">
        Generated by <?= e(APP_NAME) ?> · <?= e($t['name']) ?> · <?= e(date('d M Y H:i')) ?><br>
        This document is a draft for review. Consult with attorney before signing.
    </p>
</div>
