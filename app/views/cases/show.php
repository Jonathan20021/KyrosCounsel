<?php
$prio_colors = ['urgent' => 'danger','high' => 'warning','normal' => 'neutral','low' => 'neutral'];
$status_color = ['intake' => 'neutral','preparing' => 'info','filed' => 'primary','rfe' => 'warning',
                 'approved' => 'success','denied' => 'danger','withdrawn' => 'neutral','closed' => 'neutral'];
// Plantillas de email activas para el dropdown
$_email_tpls = [];
try { $_email_tpls = tenant_select('email_templates', ['*'], ['is_active' => 1]); } catch (Throwable $e) {}

// Deadlines proximos
$today = strtotime(date('Y-m-d'));
$deadlines = [];
foreach (['filed_at' => 'Presentado', 'biometrics_at' => 'Biometrics', 'interview_at' => 'Entrevista', 'rfe_due_at' => 'Vence RFE'] as $field => $label) {
    if (!empty($case[$field])) {
        $days = round((strtotime($case[$field]) - $today) / 86400);
        $deadlines[] = ['label' => $label, 'date' => $case[$field], 'days' => $days];
    }
}
?>
<div class="space-y-6" x-data="{ tab: window.location.hash ? window.location.hash.slice(1) : 'overview' }" x-init="$watch('tab', t => history.replaceState(null, '', '#' + t))">

    <!-- ============ HEADER ============ -->
    <div class="kyros-card p-5 lg:p-6">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div class="flex-1 min-w-0">
                <div class="flex flex-wrap items-center gap-2">
                    <h1 class="text-2xl lg:text-3xl font-bold tracking-tight font-mono"><?= e($case['case_number']) ?></h1>
                    <span class="badge-soft badge-<?= $status_color[$case['status']] ?? 'neutral' ?>"><?= e(case_status_label($case['status'])) ?></span>
                    <span class="badge-soft badge-<?= $prio_colors[$case['priority']] ?>"><?= e(CASE_PRIORITIES[$case['priority']]) ?></span>
                    <?php if (!empty($case['uscis_receipt'])): ?>
                    <span class="badge-soft badge-info" title="USCIS Receipt"><?= e($case['uscis_receipt']) ?></span>
                    <?php endif; ?>
                </div>
                <h2 class="mt-2 text-lg text-slate-700"><?= e($case['title']) ?></h2>
                <div class="mt-2 flex flex-wrap items-center gap-x-3 gap-y-1 text-sm text-slate-500">
                    <a href="<?= e(tenant_url('clients/' . $case['client_id'])) ?>" class="hover:underline font-medium text-slate-700"><?= e($case['first_name'] . ' ' . $case['last_name']) ?></a>
                    <span>·</span>
                    <span><?= e($case['case_type']) ?></span>
                    <span>·</span>
                    <span><?= e(country_name($case['country'])) ?></span>
                    <?php if ($case['attorney_name']): ?>
                    <span>·</span>
                    <span><?= e($case['attorney_name']) ?></span>
                    <?php endif; ?>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="<?= e(tenant_url('cases/' . $case['id'] . '/invoice')) ?>" target="_blank" class="btn btn-secondary" title="Generar factura PDF">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 000 7h5a3.5 3.5 0 010 7H6"/></svg>
                    Factura
                </a>
                <div x-data="{ openLetters: false }" class="relative">
                    <button type="button" @click.stop="openLetters = !openLetters" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        Cartas <span class="text-xs opacity-60">▾</span>
                    </button>
                    <div x-show="openLetters" x-cloak @click.outside="openLetters = false" style="display:none;"
                         x-transition class="kyros-dropdown w-72 right-0 mt-2 z-30">
                        <div class="kyros-dropdown-header">
                            <h3 class="text-sm font-semibold">Generar carta legal</h3>
                            <p class="text-xs" style="color: hsl(var(--fg-muted));">Plantillas auto-llenadas</p>
                        </div>
                        <div class="p-2">
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/cover-letter')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #c7d2fe, #a5b4fc); color: #4338ca;">📨</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">Cover Letter</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">Para presentar a USCIS</span></span>
                            </a>
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/letter/rfe_response')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #fef3c7, #fde68a); color: #92400e;">⚠️</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">RFE Response</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">Respuesta a RFE</span></span>
                            </a>
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/letter/affidavit_support')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #d1fae5, #6ee7b7); color: #047857;">💰</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">Affidavit of Support</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">I-864 cover</span></span>
                            </a>
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/letter/asylum_cover')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #fce7f3, #fbcfe8); color: #be185d;">🛡️</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">Asylum Cover (I-589)</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">Para Asylum Office</span></span>
                            </a>
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/letter/engagement')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #ddd6fe, #c4b5fd); color: #6d28d9;">✍️</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">Engagement Letter</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">Contrato cliente-bufete</span></span>
                            </a>
                            <a href="<?= e(tenant_url('cases/' . $case['id'] . '/letter/demand_letter')) ?>" target="_blank" class="kyros-action-item">
                                <span class="kyros-action-icon" style="background: linear-gradient(135deg, #fee2e2, #fca5a5); color: #b91c1c;">⚖️</span>
                                <span class="flex-1 min-w-0"><span class="block text-sm font-semibold">Demand Letter</span><span class="block text-xs truncate" style="color: hsl(var(--fg-muted));">Carta de cobro</span></span>
                            </a>
                        </div>
                    </div>
                </div>
                <?php if (!empty($_email_tpls) && $case['client_email'] && can('cases.update')): ?>
                <div x-data="{ open: false }" class="relative">
                    <button type="button" @click.stop="open = !open" class="btn btn-secondary">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        Enviar email
                    </button>
                    <div x-show="open" x-cloak @click.outside="open = false" style="display:none;"
                         x-transition class="kyros-dropdown w-72 right-0 mt-2 z-30">
                        <div class="kyros-dropdown-header">
                            <h3 class="text-sm font-semibold">Plantilla a enviar</h3>
                            <p class="text-xs" style="color: hsl(var(--fg-muted));">A: <?= e($case['client_email']) ?></p>
                        </div>
                        <div class="p-2 space-y-1 max-h-72 overflow-y-auto">
                            <?php foreach ($_email_tpls as $tpl): ?>
                            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/send-email')) ?>" data-confirm="Enviar '<?= e(addslashes($tpl['name'])) ?>' al cliente?">
                                <?= csrf_field() ?>
                                <input type="hidden" name="template_id" value="<?= (int)$tpl['id'] ?>">
                                <button class="w-full text-left rounded-lg px-3 py-2 hover:bg-slate-50 transition">
                                    <div class="text-sm font-semibold"><?= e($tpl['name']) ?></div>
                                    <div class="text-xs truncate" style="color: hsl(var(--fg-muted));"><?= e($tpl['subject']) ?></div>
                                </button>
                            </form>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <a href="<?= e(tenant_url('cases/' . $case['id'] . '/g28')) ?>" target="_blank" class="btn btn-secondary" title="Generar Form G-28">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="13" y2="17"/></svg>
                    G-28
                </a>
                <a href="<?= e(tenant_url('cases/' . $case['id'] . '/print')) ?>" target="_blank" class="btn btn-secondary">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    Imprimir
                </a>
                <?php if (can('cases.update')): ?>
                <a href="<?= e(tenant_url('cases/' . $case['id'] . '/edit')) ?>" class="btn btn-secondary">Editar</a>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick KPIs row -->
        <div class="mt-5 grid grid-cols-2 lg:grid-cols-5 gap-3 pt-5 border-t border-slate-100">
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Apertura</div>
                <div class="font-semibold text-sm mt-0.5"><?= e(date('d M Y', strtotime($case['opened_at']))) ?></div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Presentado</div>
                <div class="font-semibold text-sm mt-0.5"><?= $case['filed_at'] ? e(date('d M Y', strtotime($case['filed_at']))) : '—' ?></div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Honorarios</div>
                <div class="font-semibold text-sm mt-0.5">$<?= e(number_format((float)($case['attorney_fee_usd'] ?? 0), 0)) ?></div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Filing fee</div>
                <div class="font-semibold text-sm mt-0.5">$<?= e(number_format((float)($case['filing_fee_usd'] ?? 0), 0)) ?></div>
            </div>
            <div>
                <div class="text-xs uppercase tracking-wider text-slate-500">Evidencia</div>
                <div class="flex items-center gap-2 mt-0.5">
                    <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-emerald-400 to-emerald-600" style="width: <?= (int)$evidence_progress ?>%"></div>
                    </div>
                    <span class="text-xs font-semibold"><?= (int)$evidence_progress ?>%</span>
                </div>
            </div>
        </div>

        <?php if (!empty($deadlines)): ?>
        <!-- Deadlines countdown -->
        <div class="mt-4 flex flex-wrap gap-2 pt-4 border-t border-slate-100">
            <?php foreach ($deadlines as $d):
                $cls = $d['days'] < 0 ? 'badge-danger' : ($d['days'] < 7 ? 'badge-warning' : 'badge-info');
                $label_days = $d['days'] < 0 ? abs($d['days']) . 'd vencido' : ($d['days'] === 0 ? 'hoy' : 'en ' . $d['days'] . 'd');
            ?>
            <span class="badge-soft <?= $cls ?>"><?= e($d['label']) ?>: <?= e(date('d M', strtotime($d['date']))) ?> · <?= e($label_days) ?></span>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

    <!-- ============ TABS ============ -->
    <div class="kyros-card overflow-hidden">
        <div class="border-b border-slate-200 overflow-x-auto">
            <nav class="flex gap-1 px-2 py-1 min-w-max">
                <?php
                $tabs = [
                    'overview'      => ['Resumen', count($notes ?? [])],
                    'uscis'         => ['USCIS', $case['uscis_receipt'] ? '✓' : ''],
                    'evidence'      => ['Evidencia', count($evidence)],
                    'beneficiaries' => ['Beneficiarios', count($beneficiaries)],
                    'payments'      => ['Pagos', count($payments)],
                    'appointments'  => ['Citas', count($appointments)],
                    'documents'     => ['Documentos', count($docs)],
                    'tasks'         => ['Tareas', count($tasks)],
                    'messages'      => ['Mensajes', count($messages)],
                    'time'          => ['Tiempo', count($time_entries)],
                    'history'       => ['Historial', count($history)],
                ];
                foreach ($tabs as $key => [$label, $count]):
                ?>
                <button type="button" @click="tab = '<?= e($key) ?>'"
                        :class="tab === '<?= e($key) ?>' ? 'bg-indigo-50 text-indigo-700' : 'text-slate-600 hover:bg-slate-50'"
                        class="px-3 py-2 text-sm font-medium rounded-md transition flex items-center gap-1.5 whitespace-nowrap">
                    <?= e($label) ?>
                    <?php if ($count !== '' && $count !== 0): ?>
                    <span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-200 text-slate-700"><?= e($count) ?></span>
                    <?php endif; ?>
                </button>
                <?php endforeach; ?>
            </nav>
        </div>

        <!-- ============ OVERVIEW TAB ============ -->
        <div x-show="tab === 'overview'" class="p-5 lg:p-6 space-y-5">
            <?php if ($case['description']): ?>
            <div>
                <h3 class="text-sm font-semibold mb-2">Descripcion</h3>
                <p class="text-sm whitespace-pre-wrap text-slate-700"><?= e($case['description']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Cambio de estado rapido -->
            <?php if (can('cases.update')): ?>
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="rounded-xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold mb-3">Cambiar estado</h3>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/status')) ?>" class="space-y-2">
                        <?= csrf_field() ?>
                        <select name="status" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                            <?php foreach (CASE_STATUSES as $k => $v): ?>
                            <option value="<?= e($k) ?>" <?= $k === $case['status'] ? 'selected' : '' ?>><?= e($v) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <textarea name="note" rows="2" placeholder="Nota del cambio (opcional)" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm"></textarea>
                        <button class="btn btn-primary w-full">Aplicar cambio</button>
                    </form>
                </div>

                <div class="rounded-xl border border-slate-200 p-4">
                    <h3 class="text-sm font-semibold mb-3">Cliente</h3>
                    <div class="space-y-1 text-sm">
                        <div><a href="<?= e(tenant_url('clients/' . $case['client_id'])) ?>" class="font-medium text-indigo-600 hover:underline"><?= e($case['first_name'] . ' ' . $case['last_name']) ?></a></div>
                        <?php if ($case['client_email']): ?><div class="text-slate-600">📧 <?= e($case['client_email']) ?></div><?php endif; ?>
                        <?php if ($case['client_phone']): ?><div class="text-slate-600">📞 <?= e($case['client_phone']) ?></div><?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Notas -->
            <div>
                <h3 class="text-sm font-semibold mb-3">Notas (<?= count($notes) ?>)</h3>
                <?php if (can('notes.create')): ?>
                <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/notes')) ?>" class="mb-4">
                    <?= csrf_field() ?>
                    <textarea name="body" rows="2" placeholder="Agregar nota..." required
                              class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm"></textarea>
                    <div class="mt-2 flex justify-end">
                        <button class="btn btn-primary">Agregar nota</button>
                    </div>
                </form>
                <?php endif; ?>
                <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-slate-50/40">
                    <?php foreach ($notes as $n): ?>
                    <li class="p-3">
                        <div class="text-xs text-slate-500"><?= e($n['author_name'] ?? 'Sistema') ?> · <?= e(date('d M Y H:i', strtotime($n['created_at']))) ?></div>
                        <p class="text-sm text-slate-800 mt-1 whitespace-pre-wrap"><?= e($n['body']) ?></p>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($notes)): ?>
                    <li class="p-4 text-sm text-slate-500 text-center italic">Sin notas.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <!-- ============ USCIS TAB ============ -->
        <div x-show="tab === 'uscis'" x-cloak class="p-5 lg:p-6 space-y-5">
            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/uscis')) ?>" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?= csrf_field() ?>
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium mb-1">USCIS Receipt Number</label>
                    <input name="uscis_receipt" value="<?= e($case['uscis_receipt'] ?? '') ?>" placeholder="EAC2412345678"
                           pattern="[A-Za-z]{3}\d{10}" maxlength="13"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm font-mono uppercase">
                    <p class="mt-1 text-xs text-slate-500">3 letras + 10 digitos. Ej: EAC, WAC, LIN, SRC, MSC, IOE, YSC</p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Fecha del recibo</label>
                    <input name="receipt_date" type="date" value="<?= e($case['receipt_date'] ?? '') ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Service Center</label>
                    <select name="service_center" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <option value="">— Seleccione —</option>
                        <?php foreach (SERVICE_CENTERS as $k => $v): ?>
                        <option value="<?= e($k) ?>" <?= ($case['service_center'] ?? '') === $k ? 'selected' : '' ?>><?= e($k) ?> — <?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Biometrics</label>
                    <input name="biometrics_at" type="date" value="<?= e($case['biometrics_at'] ?? '') ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Entrevista</label>
                    <input name="interview_at" type="date" value="<?= e($case['interview_at'] ?? '') ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Vence respuesta a RFE</label>
                    <input name="rfe_due_at" type="date" value="<?= e($case['rfe_due_at'] ?? '') ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Filing fee USD</label>
                    <input name="filing_fee_usd" type="number" step="0.01" min="0" value="<?= e($case['filing_fee_usd'] ?? filing_fee($case['case_type'])) ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    <p class="mt-1 text-xs text-slate-500">Sugerido para <?= e($case['case_type']) ?>: $<?= e(filing_fee($case['case_type'])) ?></p>
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Honorarios USD</label>
                    <input name="attorney_fee_usd" type="number" step="0.01" min="0" value="<?= e($case['attorney_fee_usd'] ?? 0) ?>"
                           class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div class="md:col-span-2 flex justify-end">
                    <button class="btn btn-primary">Guardar datos USCIS</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <!-- ============ EVIDENCE TAB ============ -->
        <div x-show="tab === 'evidence'" x-cloak class="p-5 lg:p-6 space-y-5">
            <div class="flex flex-wrap items-center justify-between gap-3">
                <div>
                    <h3 class="text-sm font-semibold">Checklist de evidencia</h3>
                    <p class="text-xs text-slate-500">Progreso: <?= count($evReq ?? array_filter($evidence, fn($e) => (int)$e['is_required'] === 1)) ?> requeridos</p>
                </div>
                <?php if (empty($evidence) && $has_template && can('cases.update')): ?>
                <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/evidence/seed')) ?>">
                    <?= csrf_field() ?>
                    <button class="btn btn-primary">📋 Cargar plantilla USCIS para <?= e($case['case_type']) ?></button>
                </form>
                <?php endif; ?>
            </div>

            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/evidence')) ?>" class="flex flex-wrap items-end gap-2 pb-4 border-b border-slate-200">
                <?= csrf_field() ?>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Item de evidencia</label>
                    <input name="name" required placeholder="Ej: Acta de matrimonio apostillada" class="block w-full rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Categoria</label>
                    <select name="category" class="block rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <?php foreach (DOCUMENT_CATEGORIES as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_required" checked class="rounded"> Requerido</label>
                <button class="btn btn-secondary">+ Agregar</button>
            </form>
            <?php endif; ?>

            <ul class="space-y-2">
                <?php foreach ($evidence as $ev): ?>
                <li class="flex items-center gap-3 rounded-xl border border-slate-200 p-3 hover:bg-slate-50/50">
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/evidence/' . $ev['id'] . '/toggle')) ?>" class="flex-shrink-0">
                        <?= csrf_field() ?>
                        <button class="h-5 w-5 rounded border-2 transition flex items-center justify-center <?= $ev['is_received'] ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-emerald-500' ?>">
                            <?= $ev['is_received'] ? '✓' : '' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium <?= $ev['is_received'] ? 'line-through text-slate-400' : '' ?>"><?= e($ev['name']) ?></div>
                        <div class="text-xs text-slate-500">
                            <?php if ($ev['is_required']): ?><span class="badge-soft badge-danger text-[10px]">Requerido</span><?php endif; ?>
                            <?php if ($ev['category']): ?> · <?= e(DOCUMENT_CATEGORIES[$ev['category']] ?? $ev['category']) ?><?php endif; ?>
                            <?php if ($ev['received_at']): ?> · recibido <?= e($ev['received_at']) ?><?php endif; ?>
                        </div>
                    </div>
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/evidence/' . $ev['id'] . '/delete')) ?>" data-confirm="Eliminar item?">
                        <?= csrf_field() ?>
                        <button class="text-xs text-slate-400 hover:text-red-600 px-2">×</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($evidence)): ?>
                <li class="empty-state">
                    <div class="empty-state-icon">📋</div>
                    <p class="text-sm text-slate-500">Sin items de evidencia.</p>
                    <?php if ($has_template): ?>
                    <p class="text-xs text-slate-400 mt-1">Carga la plantilla de USCIS para <?= e($case['case_type']) ?>.</p>
                    <?php endif; ?>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ BENEFICIARIES TAB ============ -->
        <div x-show="tab === 'beneficiaries'" x-cloak class="p-5 lg:p-6 space-y-5">
            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/beneficiaries')) ?>" class="rounded-xl border border-slate-200 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-sm font-semibold">Agregar beneficiario / dependiente</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input name="first_name" required placeholder="Nombre" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <input name="last_name" required placeholder="Apellido" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <select name="relationship" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="spouse">Conyuge</option>
                        <option value="child">Hijo/a</option>
                        <option value="parent">Padre/Madre</option>
                        <option value="sibling">Hermano/a</option>
                        <option value="derivative">Derivado</option>
                        <option value="principal">Principal</option>
                    </select>
                    <input name="date_of_birth" type="date" placeholder="Fecha nac." class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <select name="nationality" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="">Nacionalidad...</option>
                        <?php foreach (COUNTRIES as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input name="passport" placeholder="Pasaporte (cifrado)" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm font-mono">
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">+ Agregar</button></div>
            </form>
            <?php endif; ?>

            <ul class="grid grid-cols-1 md:grid-cols-2 gap-3">
                <?php foreach ($beneficiaries as $b): ?>
                <li class="rounded-xl border border-slate-200 p-4 flex items-start gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-purple-500 text-white flex items-center justify-center font-semibold">
                        <?= e(strtoupper(mb_substr($b['first_name'], 0, 1) . mb_substr($b['last_name'], 0, 1))) ?>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="font-medium text-sm"><?= e($b['first_name'] . ' ' . $b['last_name']) ?></div>
                        <div class="text-xs text-slate-500"><?= e(ucfirst($b['relationship'])) ?>
                            <?php if ($b['date_of_birth']): ?> · DOB <?= e($b['date_of_birth']) ?><?php endif; ?>
                            <?php if ($b['nationality']): ?> · <?= e(country_name($b['nationality'])) ?><?php endif; ?>
                        </div>
                    </div>
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/beneficiaries/' . $b['id'] . '/delete')) ?>" data-confirm="Eliminar?">
                        <?= csrf_field() ?>
                        <button class="text-slate-400 hover:text-red-600 text-sm">×</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($beneficiaries)): ?>
                <li class="md:col-span-2 empty-state">
                    <div class="empty-state-icon">👨‍👩‍👧</div>
                    <p class="text-sm text-slate-500">Sin beneficiarios o dependientes registrados.</p>
                </li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ PAYMENTS TAB ============ -->
        <div x-show="tab === 'payments'" x-cloak class="p-5 lg:p-6 space-y-5">
            <div class="grid grid-cols-3 gap-3">
                <div class="kyros-stat">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Total facturado</div>
                    <div class="mt-1 text-2xl font-bold">$<?= e(number_format($totals['total'], 2)) ?></div>
                </div>
                <div class="kyros-stat">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Pagado</div>
                    <div class="mt-1 text-2xl font-bold text-emerald-600">$<?= e(number_format($totals['paid'], 2)) ?></div>
                </div>
                <div class="kyros-stat">
                    <div class="text-xs uppercase tracking-wider text-slate-500">Balance</div>
                    <div class="mt-1 text-2xl font-bold text-amber-600">$<?= e(number_format($totals['pending'], 2)) ?></div>
                </div>
            </div>

            <?php if (can('cases.update')): ?>
            <!-- Plan de pagos en cuotas -->
            <details class="rounded-xl border border-indigo-200 bg-indigo-50/50 p-4">
                <summary class="cursor-pointer font-semibold text-sm text-indigo-900">📅 Crear plan de pagos en cuotas</summary>
                <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/plans')) ?>" class="mt-3 space-y-3">
                    <?= csrf_field() ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                        <input name="name" required placeholder="Nombre del plan (ej. Honorarios I-130)" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        <input name="concept" placeholder="Concepto base (ej. Honorarios)" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Total USD</label>
                            <input name="total_usd" type="number" step="0.01" min="1" required class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">N° cuotas</label>
                            <input name="installments_count" type="number" min="2" max="36" value="3" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Inicio</label>
                            <input name="start_date" type="date" value="<?= e(date('Y-m-d')) ?>" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-500 mb-1">Cada (dias)</label>
                            <input name="interval_days" type="number" min="7" max="180" value="30" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                        </div>
                    </div>
                    <div class="flex justify-end">
                        <button class="btn btn-primary text-xs">Crear plan + cuotas</button>
                    </div>
                </form>
            </details>

            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/payments')) ?>" class="rounded-xl border border-slate-200 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-sm font-semibold">Registrar pago / fee individual</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                    <input name="concept" required placeholder="Concepto" class="md:col-span-2 rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <input name="amount_usd" type="number" step="0.01" min="0" required placeholder="Monto USD" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <select name="category" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="attorney_fee">Honorarios abogado</option>
                        <option value="filing_fee">Filing fee USCIS</option>
                        <option value="biometrics">Biometrics</option>
                        <option value="translation">Traduccion</option>
                        <option value="other">Otro</option>
                    </select>
                    <select name="status" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="pending">Pendiente</option>
                        <option value="paid">Pagado</option>
                    </select>
                    <select name="method" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="">Metodo...</option>
                        <option value="cash">Efectivo</option>
                        <option value="transfer">Transferencia</option>
                        <option value="card">Tarjeta</option>
                        <option value="check">Cheque</option>
                    </select>
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">+ Registrar</button></div>
            </form>
            <?php endif; ?>

            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                <?php foreach ($payments as $p): ?>
                <li class="p-3 flex items-start gap-3 flex-wrap">
                    <span class="badge-soft <?= $p['status'] === 'paid' ? 'badge-success' : 'badge-warning' ?> flex-shrink-0 mt-0.5"><?= e($p['status']) ?></span>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium"><?= e($p['concept']) ?></div>
                        <div class="text-xs text-slate-500"><?= e(['attorney_fee' => 'Honorarios','filing_fee' => 'Filing fee','biometrics' => 'Biometrics','translation' => 'Traduccion','other' => 'Otro'][$p['category']] ?? $p['category']) ?>
                            <?php if ($p['paid_at']): ?> · <?= e($p['paid_at']) ?><?php endif; ?>
                            <?php if ($p['method']): ?> · <?= e($p['method']) ?><?php endif; ?>
                        </div>
                        <?php if ($p['status'] === 'pending'): ?>
                            <?php if (!empty($p['pay_token'])): ?>
                            <div class="mt-1.5 text-xs flex items-center gap-1.5 flex-wrap">
                                <a href="<?= e(url('pay/' . $p['pay_token'])) ?>" target="_blank" class="text-indigo-600 hover:underline">🔗 Ver link de pago</a>
                                <?php if (!empty($pay_link_once) && (int)$pay_link_once['pid'] === (int)$p['id']): ?>
                                <input id="payurl<?= $p['id'] ?>" value="<?= e(url('pay/' . $pay_link_once['token'])) ?>" readonly class="text-[10px] font-mono px-2 py-0.5 rounded border border-emerald-200 bg-emerald-50 max-w-xs">
                                <button type="button" data-copy="payurl<?= $p['id'] ?>" class="text-xs text-indigo-600 hover:underline">Copiar URL</button>
                                <?php endif; ?>
                            </div>
                            <?php elseif (can('cases.update')): ?>
                            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/payments/' . $p['id'] . '/link')) ?>" class="mt-1.5">
                                <?= csrf_field() ?>
                                <button class="text-xs text-indigo-600 hover:underline">💳 Generar link de pago online</button>
                            </form>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <div class="text-right">
                        <div class="font-semibold">$<?= e(number_format((float)$p['amount_usd'], 2)) ?></div>
                    </div>
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/payments/' . $p['id'] . '/toggle')) ?>">
                        <?= csrf_field() ?>
                        <button class="text-xs text-indigo-600 hover:underline whitespace-nowrap"><?= $p['status'] === 'paid' ? 'Marcar pendiente' : 'Marcar pagado' ?></button>
                    </form>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/payments/' . $p['id'] . '/delete')) ?>" data-confirm="Eliminar?">
                        <?= csrf_field() ?>
                        <button class="text-slate-400 hover:text-red-600 text-sm">×</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($payments)): ?>
                <li class="empty-state"><div class="empty-state-icon">💰</div><p class="text-sm text-slate-500">Sin pagos registrados.</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ APPOINTMENTS TAB ============ -->
        <div x-show="tab === 'appointments'" x-cloak class="p-5 lg:p-6 space-y-5">
            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/appointments')) ?>" class="rounded-xl border border-slate-200 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-sm font-semibold">Agendar cita</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <input name="title" required placeholder="Titulo de la cita" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <select name="type" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <option value="interview">Entrevista USCIS</option>
                        <option value="biometrics">Biometrics</option>
                        <option value="consultation">Consulta</option>
                        <option value="court">Audiencia</option>
                        <option value="other">Otro</option>
                    </select>
                    <input name="date" type="date" required class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <input name="time" type="time" value="09:00" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                    <input name="location" placeholder="Lugar" class="md:col-span-2 rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">+ Agendar</button></div>
            </form>
            <?php endif; ?>

            <ul class="space-y-3">
                <?php foreach ($appointments as $apt):
                    $when = strtotime($apt['starts_at']);
                    $is_past = $when < time();
                    $type_icons = ['interview' => '🎙️', 'biometrics' => '👆', 'consultation' => '💼', 'court' => '⚖️', 'other' => '📅'];
                ?>
                <li class="flex items-start gap-4 rounded-xl border border-slate-200 p-4 <?= $is_past ? 'opacity-60' : '' ?>">
                    <div class="flex-shrink-0 w-16 text-center">
                        <div class="text-xs uppercase tracking-wider text-slate-500"><?= e(date('M', $when)) ?></div>
                        <div class="text-2xl font-bold"><?= e(date('d', $when)) ?></div>
                        <div class="text-xs text-slate-500"><?= e(date('H:i', $when)) ?></div>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-semibold"><?= $type_icons[$apt['type']] ?? '📅' ?> <?= e($apt['title']) ?></div>
                        <div class="text-xs text-slate-500 mt-0.5"><?= e(['interview' => 'Entrevista','biometrics' => 'Biometrics','consultation' => 'Consulta','court' => 'Audiencia','other' => 'Otro'][$apt['type']]) ?>
                            <?php if ($apt['location']): ?> · 📍 <?= e($apt['location']) ?><?php endif; ?>
                        </div>
                        <?php if ($apt['notes']): ?><div class="text-xs text-slate-600 mt-1"><?= e($apt['notes']) ?></div><?php endif; ?>
                    </div>
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/appointments/' . $apt['id'] . '/delete')) ?>" data-confirm="Eliminar?">
                        <?= csrf_field() ?>
                        <button class="text-slate-400 hover:text-red-600 text-sm">×</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($appointments)): ?>
                <li class="empty-state"><div class="empty-state-icon">📅</div><p class="text-sm text-slate-500">Sin citas agendadas.</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ DOCUMENTS TAB ============ -->
        <div x-show="tab === 'documents'" x-cloak class="p-5 lg:p-6 space-y-4">
            <?php if (can('documents.create')): ?>
            <form method="POST" action="<?= e(tenant_url('documents/upload')) ?>" enctype="multipart/form-data" class="rounded-xl border border-slate-200 p-4 flex flex-wrap items-end gap-3">
                <?= csrf_field() ?>
                <input type="hidden" name="case_id" value="<?= (int)$case['id'] ?>">
                <input type="hidden" name="client_id" value="<?= (int)$case['client_id'] ?>">
                <div>
                    <label class="block text-xs font-medium text-slate-500 mb-1">Categoria</label>
                    <select name="category" class="rounded-lg border border-slate-200 py-1.5 px-3 text-sm">
                        <?php foreach (DOCUMENT_CATEGORIES as $k => $v): ?>
                        <option value="<?= e($k) ?>"><?= e($v) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="block text-xs font-medium text-slate-500 mb-1">Archivo</label>
                    <input type="file" name="file" required class="block w-full text-sm">
                </div>
                <button class="btn btn-primary">Subir</button>
            </form>
            <?php endif; ?>

            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                <?php foreach ($docs as $d):
                    $ext = strtolower(pathinfo($d['name'], PATHINFO_EXTENSION));
                ?>
                <li class="p-3 flex items-center gap-3">
                    <div class="h-9 w-9 rounded-lg bg-slate-100 text-slate-600 flex items-center justify-center font-mono text-[10px] font-bold uppercase"><?= e($ext ?: '?') ?></div>
                    <div class="flex-1 min-w-0">
                        <a href="<?= e(tenant_url('documents/' . $d['id'] . '/download')) ?>" class="text-sm font-medium text-indigo-600 hover:underline truncate block"><?= e($d['name']) ?></a>
                        <div class="text-xs text-slate-500"><?= e(DOCUMENT_CATEGORIES[$d['category']] ?? '') ?> · <?= e(round((int)$d['size_bytes']/1024) . ' KB') ?></div>
                    </div>
                    <span class="text-xs text-slate-500 whitespace-nowrap"><?= e(date('d M', strtotime($d['created_at']))) ?></span>
                </li>
                <?php endforeach; ?>
                <?php if (empty($docs)): ?>
                <li class="empty-state"><div class="empty-state-icon">📁</div><p class="text-sm text-slate-500">Sin documentos.</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ TASKS TAB ============ -->
        <div x-show="tab === 'tasks'" x-cloak class="p-5 lg:p-6 space-y-3">
            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                <?php foreach ($tasks as $t):
                    $overdue = $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] !== 'done';
                ?>
                <li class="p-3 flex items-center gap-3">
                    <?php if (can('tasks.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('tasks/' . $t['id'] . '/toggle')) ?>" class="flex-shrink-0">
                        <?= csrf_field() ?>
                        <button class="h-5 w-5 rounded border-2 transition flex items-center justify-center <?= $t['status']==='done' ? 'bg-emerald-500 border-emerald-500 text-white' : 'border-slate-300 hover:border-emerald-500' ?>">
                            <?= $t['status']==='done' ? '✓' : '' ?>
                        </button>
                    </form>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium <?= $t['status']==='done' ? 'line-through text-slate-400' : '' ?>"><?= e($t['title']) ?></div>
                        <div class="text-xs text-slate-500">
                            <?= e($t['assignee_name'] ?? 'Sin asignar') ?>
                            <?php if ($t['due_date']): ?> · <span class="<?= $overdue ? 'text-red-600 font-medium' : '' ?>">vence <?= e($t['due_date']) ?></span><?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endforeach; ?>
                <?php if (empty($tasks)): ?>
                <li class="empty-state"><div class="empty-state-icon">✓</div><p class="text-sm text-slate-500">Sin tareas asignadas.</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ MESSAGES TAB ============ -->
        <div x-show="tab === 'messages'" x-cloak class="p-5 lg:p-6 space-y-4">
            <div class="rounded-xl border border-slate-200 p-3 max-h-96 overflow-y-auto bg-slate-50/40 space-y-3">
                <?php foreach ($messages as $m):
                    $is_staff = $m['sender_type'] === 'staff';
                ?>
                <div class="flex <?= $is_staff ? 'justify-end' : 'justify-start' ?>">
                    <div class="max-w-[75%] rounded-2xl px-4 py-2.5 <?= $is_staff ? 'bg-indigo-600 text-white rounded-br-sm' : 'bg-white border border-slate-200 rounded-bl-sm' ?>">
                        <div class="text-xs font-semibold mb-1 <?= $is_staff ? 'text-indigo-200' : 'text-slate-500' ?>">
                            <?= $is_staff ? e($m['sender_name'] ?? 'Sistema') : e($case['first_name'] . ' ' . $case['last_name']) ?>
                            · <?= e(date('d M H:i', strtotime($m['created_at']))) ?>
                        </div>
                        <p class="text-sm whitespace-pre-wrap"><?= e($m['body']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <div class="empty-state"><div class="empty-state-icon">💬</div><p class="text-sm text-slate-500">Inicia la conversación con tu cliente</p></div>
                <?php endif; ?>
            </div>

            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/messages')) ?>" class="space-y-2">
                <?= csrf_field() ?>
                <textarea name="body" rows="2" placeholder="Escribe un mensaje al cliente..." required maxlength="5000"
                          class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm"></textarea>
                <div class="flex justify-end">
                    <button class="btn btn-primary">Enviar al cliente</button>
                </div>
            </form>
            <?php endif; ?>
        </div>

        <!-- ============ TIME TRACKING TAB ============ -->
        <div x-show="tab === 'time'" x-cloak class="p-5 lg:p-6 space-y-5">
            <!-- Stats -->
            <div class="grid grid-cols-3 gap-3">
                <div class="kyros-stat">
                    <div class="kyros-eyebrow">Total horas</div>
                    <div class="mt-1 kyros-number text-2xl font-bold"><?= e(intdiv($time_totals['minutes'], 60)) ?>h <?= e($time_totals['minutes'] % 60) ?>m</div>
                </div>
                <div class="kyros-stat">
                    <div class="kyros-eyebrow">Horas billables</div>
                    <div class="mt-1 kyros-number text-2xl font-bold text-emerald-600"><?= e(intdiv($time_totals['billable_minutes'], 60)) ?>h <?= e($time_totals['billable_minutes'] % 60) ?>m</div>
                </div>
                <div class="kyros-stat">
                    <div class="kyros-eyebrow">Valor billable</div>
                    <div class="mt-1 kyros-number text-2xl font-bold">$<?= e(number_format($time_totals['billable_amount'], 2)) ?></div>
                </div>
            </div>

            <?php if (can('cases.update')): ?>
            <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/time')) ?>" class="rounded-xl border border-slate-200 p-4 space-y-3">
                <?= csrf_field() ?>
                <h3 class="text-sm font-semibold">Registrar tiempo</h3>
                <input name="description" required placeholder="Descripcion del trabajo realizado..." class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Horas</label>
                        <input name="hours" type="number" min="0" max="24" value="0" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Minutos</label>
                        <input name="minutes" type="number" min="0" max="59" value="30" step="5" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-slate-500 mb-1">Fecha</label>
                        <input name="entry_date" type="date" value="<?= e(date('Y-m-d')) ?>" class="w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    </div>
                    <div class="flex items-end">
                        <label class="flex items-center gap-2 text-sm cursor-pointer">
                            <input type="checkbox" name="is_billable" value="1" checked class="h-4 w-4 rounded border-slate-300 text-indigo-600">
                            Billable
                        </label>
                    </div>
                </div>
                <div class="flex justify-end"><button class="btn btn-primary">+ Registrar tiempo</button></div>
            </form>
            <?php endif; ?>

            <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200">
                <?php foreach ($time_entries as $te):
                    $hours = intdiv((int)$te['minutes'], 60);
                    $mins = (int)$te['minutes'] % 60;
                    $value = ((int)$te['minutes'] / 60) * (float)$te['hourly_rate_usd'];
                ?>
                <li class="p-3 flex items-start gap-3">
                    <?php if ($te['is_billable']): ?>
                    <span class="badge-soft badge-success flex-shrink-0">$$$</span>
                    <?php else: ?>
                    <span class="badge-soft badge-neutral flex-shrink-0">no-bill</span>
                    <?php endif; ?>
                    <div class="flex-1 min-w-0">
                        <div class="text-sm font-medium"><?= e($te['description']) ?></div>
                        <div class="text-xs text-slate-500 mt-0.5"><?= e($te['user_name'] ?? 'Sistema') ?> · <?= e($te['entry_date']) ?> · ${<?= e(number_format((float)$te['hourly_rate_usd'], 0)) ?>}/hr</div>
                    </div>
                    <div class="text-right">
                        <div class="text-sm font-bold kyros-number"><?= $hours ?>h <?= $mins ?>m</div>
                        <?php if ($te['is_billable']): ?>
                        <div class="text-xs text-emerald-600 font-mono">$<?= e(number_format($value, 2)) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php if (can('cases.update')): ?>
                    <form method="POST" action="<?= e(tenant_url('cases/' . $case['id'] . '/time/' . $te['id'] . '/delete')) ?>" data-confirm="Eliminar registro?">
                        <?= csrf_field() ?>
                        <button class="text-slate-400 hover:text-red-600 text-sm">×</button>
                    </form>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
                <?php if (empty($time_entries)): ?>
                <li class="empty-state"><div class="empty-state-icon">⏱️</div><p class="text-sm text-slate-500">Sin registros de tiempo.</p></li>
                <?php endif; ?>
            </ul>
        </div>

        <!-- ============ HISTORY TAB ============ -->
        <div x-show="tab === 'history'" x-cloak class="p-5 lg:p-6">
            <ol class="relative border-l-2 border-slate-200 ml-2 space-y-6 pt-2">
                <?php foreach ($history as $h): ?>
                <li class="ml-4">
                    <div class="absolute -left-[7px] mt-1.5 h-3 w-3 rounded-full bg-indigo-500 border-2 border-white"></div>
                    <div class="text-xs text-slate-500"><?= e(date('d M Y · H:i', strtotime($h['created_at']))) ?></div>
                    <div class="text-sm font-medium mt-0.5">
                        <?= $h['from_status'] ? e(case_status_label($h['from_status'])) . ' → ' : 'Caso creado · ' ?>
                        <strong><?= e(case_status_label($h['to_status'])) ?></strong>
                    </div>
                    <div class="text-xs text-slate-500"><?= e($h['changed_by_name'] ?? 'Sistema') ?></div>
                    <?php if ($h['note']): ?><div class="text-sm text-slate-700 mt-1 italic"><?= e($h['note']) ?></div><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>
    </div>
</div>
