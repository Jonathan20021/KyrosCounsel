<?php /* Estilos compartidos para todas las cartas legales. Incluir con require. */ ?>
<style>
@media screen { body { background: #f1f5f9; padding: 2rem; } .doc { max-width: 8.5in; margin: 0 auto; padding: 1in; background: white; box-shadow: 0 4px 12px rgba(0,0,0,.1); } }
@media print { body { background: white; padding: 0; } .doc { padding: 0; box-shadow: none; max-width: none; } .no-print { display: none; } @page { margin: 0.75in; } }
.doc { font-family: 'Times New Roman', Georgia, serif; color: #000; line-height: 1.6; font-size: 12pt; }
.doc p { margin: 0 0 0.75em; text-align: justify; }
.doc .letterhead { text-align: center; padding-bottom: 0.75rem; border-bottom: 2px solid #000; margin-bottom: 1.5rem; }
.doc .letterhead .firm-name { font-size: 16pt; font-weight: 700; letter-spacing: 0.5px; }
.doc .letterhead .firm-meta { font-size: 10pt; color: #444; margin-top: 0.25rem; }
.doc .re-line { font-weight: 700; margin: 1.5rem 0; padding: 0.5rem 0.75rem; background: #f5f5f5; }
.doc ul, .doc ol { margin: 0.5rem 0 1rem 1.5rem; }
.doc li { margin-bottom: 0.25rem; }
.doc .section-h { font-weight: 700; text-decoration: underline; margin-top: 1rem; }
.doc .signature-line { border-top: 1px solid #000; padding-top: 4px; font-size: 10pt; min-height: 50px; }
</style>
<div class="no-print" style="text-align:center;padding:1rem;font-family:system-ui;">
    <button onclick="window.print()" style="padding:.6rem 1.2rem;background:#4f46e5;color:white;border:0;border-radius:8px;font-weight:600;cursor:pointer;">🖨️ Imprimir / Guardar PDF</button>
    <a href="<?= e(tenant_url('cases/' . $case['id'])) ?>" style="margin-left:1rem;color:#64748b;">← Volver al caso</a>
</div>
