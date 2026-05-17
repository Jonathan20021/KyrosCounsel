<?php
$brand = $portal['brand_color'] ?: '#4f46e5';
$st_colors = ['intake' => '#94a3b8','preparing' => '#6366f1','filed' => '#0ea5e9','rfe' => '#f59e0b',
              'approved' => '#10b981','denied' => '#ef4444','withdrawn' => '#64748b','closed' => '#475569'];
$st = $st_colors[$case['status']] ?? '#94a3b8';
$evReq = array_filter($evidence, fn($e) => (int)$e['is_required'] === 1);
$evRecv = array_filter($evReq, fn($e) => (int)$e['is_received'] === 1);
$pct = count($evReq) > 0 ? round(count($evRecv) / count($evReq) * 100) : 0;
?>
<style>
.portal-bg { background: linear-gradient(135deg, <?= e($brand) ?>0d 0%, <?= e($brand) ?>03 100%); min-height: 100vh; }
.portal-accent { color: <?= e($brand) ?>; }
</style>

<div class="portal-bg py-8 lg:py-12">
    <div class="mx-auto max-w-3xl px-4">
        <a href="<?= e(url('portal/' . $token)) ?>" class="text-sm portal-accent hover:underline">← Volver a mis casos</a>

        <!-- Hero -->
        <div class="mt-4 rounded-2xl bg-white border border-slate-200 p-6 lg:p-8 shadow-sm">
            <div class="flex items-start justify-between gap-3 flex-wrap">
                <div>
                    <p class="text-xs font-mono font-semibold portal-accent"><?= e($case['case_number']) ?></p>
                    <h1 class="mt-1 text-2xl lg:text-3xl font-bold tracking-tight"><?= e($case['title']) ?></h1>
                    <p class="text-sm text-slate-600 mt-1"><?= e($case['case_type']) ?></p>
                </div>
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium" style="background: <?= $st ?>22; color: <?= $st ?>;">
                    <?= e(case_status_label($case['status'])) ?>
                </span>
            </div>

            <hr class="my-5 border-slate-100">

            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Apertura</div>
                    <div class="mt-0.5 font-medium"><?= e(date('d M Y', strtotime($case['opened_at']))) ?></div>
                </div>
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Presentado</div>
                    <div class="mt-0.5 font-medium"><?= $case['filed_at'] ? e(date('d M Y', strtotime($case['filed_at']))) : '—' ?></div>
                </div>
                <?php if ($case['uscis_receipt']): ?>
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">USCIS Receipt</div>
                    <div class="mt-0.5 font-mono text-xs"><?= e($case['uscis_receipt']) ?></div>
                </div>
                <?php endif; ?>
                <?php if ($case['decision_at']): ?>
                <div>
                    <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Decisión</div>
                    <div class="mt-0.5 font-medium"><?= e(date('d M Y', strtotime($case['decision_at']))) ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Próximas fechas -->
        <?php if ($case['biometrics_at'] || $case['interview_at']): ?>
        <div class="mt-6 rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-semibold mb-3">📅 Próximas fechas</h3>
            <div class="space-y-3">
                <?php if ($case['biometrics_at']): ?>
                <div class="flex items-center gap-3">
                    <div class="text-2xl">👆</div>
                    <div>
                        <div class="font-medium">Biometrics</div>
                        <div class="text-sm text-slate-500"><?= e(date('l, d \d\e F Y', strtotime($case['biometrics_at']))) ?></div>
                    </div>
                </div>
                <?php endif; ?>
                <?php if ($case['interview_at']): ?>
                <div class="flex items-center gap-3">
                    <div class="text-2xl">🎙️</div>
                    <div>
                        <div class="font-medium">Entrevista USCIS</div>
                        <div class="text-sm text-slate-500"><?= e(date('l, d \d\e F Y', strtotime($case['interview_at']))) ?></div>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Progreso evidencia -->
        <?php if (!empty($evidence)): ?>
        <div class="mt-6 rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-base font-semibold">📋 Documentos</h3>
                <span class="text-sm font-semibold"><?= count($evRecv) ?>/<?= count($evReq) ?></span>
            </div>
            <div class="h-2 rounded-full bg-slate-100 overflow-hidden mb-4">
                <div class="h-full rounded-full transition-all" style="width: <?= $pct ?>%; background: linear-gradient(90deg, #34d399, <?= $brand ?>);"></div>
            </div>
            <ul class="space-y-2">
                <?php foreach ($evidence as $ev): ?>
                <li class="flex items-center gap-3 text-sm">
                    <span class="h-5 w-5 rounded border-2 flex items-center justify-center flex-shrink-0 <?= $ev['is_received'] ? '' : 'border-slate-300' ?>"
                          style="<?= $ev['is_received'] ? 'background: #10b981; border-color: #10b981; color: white;' : '' ?>">
                        <?= $ev['is_received'] ? '✓' : '' ?>
                    </span>
                    <span class="<?= $ev['is_received'] ? 'line-through text-slate-400' : '' ?>"><?= e($ev['name']) ?></span>
                    <?php if ($ev['is_required'] && !$ev['is_received']): ?>
                    <span class="text-[10px] font-semibold text-red-600 ml-auto">Pendiente</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Mensajes -->
        <div id="messages" class="mt-6 rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-semibold mb-4">💬 Conversación con tu abogado</h3>
            <div class="rounded-lg border border-slate-200 p-3 max-h-80 overflow-y-auto bg-slate-50 space-y-3 mb-4">
                <?php foreach ($messages as $m):
                    $is_staff = $m['sender_type'] === 'staff';
                ?>
                <div class="flex <?= $is_staff ? 'justify-start' : 'justify-end' ?>">
                    <div class="max-w-[80%] rounded-2xl px-4 py-2.5 <?= $is_staff ? 'bg-white border border-slate-200 rounded-bl-sm' : 'text-white rounded-br-sm' ?>"
                         <?= $is_staff ? '' : 'style="background: ' . e($brand) . ';"' ?>>
                        <div class="text-xs font-semibold mb-1 <?= $is_staff ? 'text-slate-500' : 'opacity-80' ?>">
                            <?= $is_staff ? e($m['sender_name'] ?? 'Tu abogado') : 'Tú' ?>
                            · <?= e(date('d M H:i', strtotime($m['created_at']))) ?>
                        </div>
                        <p class="text-sm whitespace-pre-wrap"><?= e($m['body']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($messages)): ?>
                <div class="text-center py-6 text-slate-500 text-sm">Aún no hay mensajes. ¡Escribe el primero!</div>
                <?php endif; ?>
            </div>

            <form method="POST" action="<?= e(url('portal/' . $token . '/case/' . $case['id'] . '/message')) ?>" class="space-y-2">
                <textarea name="body" rows="2" placeholder="Escribe un mensaje a tu abogado..." required maxlength="5000"
                          class="block w-full rounded-lg border border-slate-300 py-2 px-3 text-sm focus:ring-2 focus:ring-indigo-500"></textarea>
                <div class="flex justify-end">
                    <button class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: <?= e($brand) ?>;">Enviar mensaje</button>
                </div>
            </form>
        </div>

        <!-- Timeline -->
        <div class="mt-6 rounded-2xl bg-white border border-slate-200 p-6 shadow-sm">
            <h3 class="text-base font-semibold mb-4">📜 Historial</h3>
            <ol class="relative border-l-2 border-slate-200 ml-2 space-y-5 pt-1">
                <?php foreach ($history as $h): ?>
                <li class="ml-4">
                    <div class="absolute -left-[7px] mt-1.5 h-3 w-3 rounded-full border-2 border-white" style="background: <?= $brand ?>"></div>
                    <div class="text-xs text-slate-500"><?= e(date('d M Y', strtotime($h['created_at']))) ?></div>
                    <div class="text-sm font-medium mt-0.5">
                        <?= $h['from_status'] ? e(case_status_label($h['from_status'])) . ' → ' : 'Caso creado · ' ?>
                        <strong><?= e(case_status_label($h['to_status'])) ?></strong>
                    </div>
                    <?php if ($h['note']): ?><div class="text-xs text-slate-600 mt-1 italic"><?= e($h['note']) ?></div><?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ol>
        </div>

        <div class="mt-12 text-center text-xs text-slate-500">
            <p>🔒 Información confidencial · Solo para <?= e($portal['first_name']) ?></p>
        </div>
    </div>
</div>
