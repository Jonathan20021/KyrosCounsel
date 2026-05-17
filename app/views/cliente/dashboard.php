<?php $brand = $session['brand_color'] ?: '#4f46e5'; ?>
<style>
.cliente-bg { background: linear-gradient(135deg, <?= e($brand) ?>0d 0%, <?= e($brand) ?>03 100%); min-height: 100vh; }
.cliente-accent { color: <?= e($brand) ?>; }
</style>

<div class="cliente-bg py-8 lg:py-12">
    <div class="mx-auto max-w-4xl px-4">
        <!-- Header -->
        <div class="flex flex-wrap items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-3">
                <div class="h-12 w-12 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-md" style="background: <?= e($brand) ?>;">
                    <?= e(strtoupper(mb_substr($session['tenant_name'], 0, 1))) ?>
                </div>
                <div>
                    <h1 class="text-xl lg:text-2xl font-bold text-slate-900"><?= e($session['tenant_name']) ?></h1>
                    <p class="text-sm text-slate-600">Hola, <?= e($session['first_name']) ?> 👋</p>
                </div>
            </div>
            <form method="POST" action="<?= e(url('cliente/logout')) ?>">
                <button class="rounded-lg border border-slate-300 bg-white px-4 py-2 text-sm hover:bg-slate-50">Cerrar sesion</button>
            </form>
        </div>

        <!-- Pagos pendientes -->
        <?php if (!empty($pending_payments)): ?>
        <div class="rounded-2xl bg-white border-2 border-amber-300 p-6 mb-6 shadow-sm">
            <h3 class="text-base font-semibold mb-4 flex items-center gap-2">
                💰 Pagos pendientes
                <span class="badge-soft badge-warning"><?= count($pending_payments) ?></span>
            </h3>
            <ul class="space-y-3">
                <?php foreach ($pending_payments as $p): ?>
                <li class="flex flex-wrap items-center gap-3 p-3 rounded-lg bg-amber-50">
                    <div class="flex-1 min-w-0">
                        <div class="font-medium"><?= e($p['concept']) ?></div>
                        <div class="text-xs text-slate-500 font-mono"><?= e($p['case_number']) ?></div>
                    </div>
                    <div class="font-bold text-amber-700">$<?= e(number_format((float)$p['amount_usd'], 2)) ?></div>
                    <?php if ($p['pay_token']): ?>
                    <a href="<?= e(url('pay/' . $p['pay_token'])) ?>" class="rounded-lg px-4 py-2 text-sm font-semibold text-white" style="background: <?= e($brand) ?>;">
                        Pagar ahora →
                    </a>
                    <?php else: ?>
                    <span class="text-xs text-slate-500 italic">Tu abogado generara el link</span>
                    <?php endif; ?>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>

        <!-- Casos -->
        <h3 class="text-sm font-semibold text-slate-700 uppercase tracking-wider mb-3">Mis casos</h3>
        <ul class="space-y-3">
            <?php foreach ($cases as $c):
                $st_colors = ['intake' => '#94a3b8','preparing' => '#6366f1','filed' => '#0ea5e9','rfe' => '#f59e0b',
                              'approved' => '#10b981','denied' => '#ef4444','withdrawn' => '#64748b','closed' => '#475569'];
                $st = $st_colors[$c['status']] ?? '#94a3b8';
            ?>
            <li class="rounded-2xl bg-white border border-slate-200 p-5 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3 mb-3">
                    <div>
                        <div class="text-xs font-mono font-semibold cliente-accent"><?= e($c['case_number']) ?></div>
                        <h4 class="mt-1 font-semibold text-slate-900"><?= e($c['title']) ?></h4>
                        <p class="text-xs text-slate-500"><?= e($c['case_type']) ?> · Abierto <?= e(date('d M Y', strtotime($c['opened_at']))) ?></p>
                    </div>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium" style="background: <?= $st ?>22; color: <?= $st ?>;">
                        <?= e(case_status_label($c['status'])) ?>
                    </span>
                </div>
                <?php if ($c['uscis_receipt'] || $c['biometrics_at'] || $c['interview_at']): ?>
                <div class="mt-3 pt-3 border-t border-slate-100 grid grid-cols-2 lg:grid-cols-3 gap-3 text-xs">
                    <?php if ($c['uscis_receipt']): ?>
                    <div><span class="block text-slate-500">USCIS Receipt</span><span class="font-mono"><?= e($c['uscis_receipt']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($c['biometrics_at']): ?>
                    <div><span class="block text-slate-500">Biometrics</span><span class="font-medium"><?= e($c['biometrics_at']) ?></span></div>
                    <?php endif; ?>
                    <?php if ($c['interview_at']): ?>
                    <div><span class="block text-slate-500">Entrevista</span><span class="font-medium"><?= e($c['interview_at']) ?></span></div>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </li>
            <?php endforeach; ?>
            <?php if (empty($cases)): ?>
            <li class="text-center py-12 text-slate-500">Aun no tienes casos asociados.</li>
            <?php endif; ?>
        </ul>

        <div class="mt-12 text-center text-xs text-slate-500">
            <p>🔒 Acceso seguro · Sesion cifrada</p>
        </div>
    </div>
</div>
