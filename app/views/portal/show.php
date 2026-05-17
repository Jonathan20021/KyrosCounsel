<?php $brand = $portal['brand_color'] ?: '#4f46e5'; ?>
<style>
.portal-bg { background: linear-gradient(135deg, <?= e($brand) ?>0d 0%, <?= e($brand) ?>03 100%); min-height: 100vh; }
.portal-accent { color: <?= e($brand) ?>; }
.portal-btn { background: <?= e($brand) ?>; color: white; }
</style>

<div class="portal-bg py-8 lg:py-16">
    <div class="mx-auto max-w-3xl px-4">
        <!-- Header del bufete -->
        <div class="text-center mb-8">
            <div class="mx-auto h-14 w-14 rounded-2xl flex items-center justify-center text-white text-2xl font-bold shadow-lg" style="background: <?= e($brand) ?>;">
                <?= e(strtoupper(mb_substr($portal['tenant_name'], 0, 1))) ?>
            </div>
            <h1 class="mt-4 text-2xl lg:text-3xl font-bold tracking-tight text-slate-900"><?= e($portal['tenant_name']) ?></h1>
            <p class="mt-1 text-sm text-slate-600">Portal del cliente</p>
        </div>

        <!-- Saludo -->
        <div class="rounded-2xl bg-white border border-slate-200 p-6 lg:p-8 shadow-sm">
            <p class="text-xs uppercase tracking-wider font-semibold portal-accent">Hola</p>
            <h2 class="mt-1 text-2xl font-bold text-slate-900"><?= e($portal['first_name'] . ' ' . $portal['last_name']) ?></h2>
            <p class="mt-2 text-sm text-slate-600">Aqui puedes ver el estado de tus casos en tiempo real.</p>
        </div>

        <!-- Lista de casos -->
        <h3 class="mt-8 mb-3 text-sm font-semibold text-slate-700 uppercase tracking-wider">Mis casos (<?= count($cases) ?>)</h3>

        <ul class="space-y-3">
            <?php foreach ($cases as $c):
                $st_colors = ['intake' => '#94a3b8','preparing' => '#6366f1','filed' => '#0ea5e9','rfe' => '#f59e0b',
                              'approved' => '#10b981','denied' => '#ef4444','withdrawn' => '#64748b','closed' => '#475569'];
                $st = case_status_label($c['status']);
            ?>
            <li>
                <a href="<?= e(url('portal/' . $token . '/case/' . $c['id'])) ?>"
                   class="block rounded-xl bg-white border border-slate-200 p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between gap-3">
                        <div class="min-w-0 flex-1">
                            <div class="flex items-center gap-2 flex-wrap">
                                <span class="text-xs font-mono font-semibold portal-accent"><?= e($c['case_number']) ?></span>
                                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium" style="background: <?= $st_colors[$c['status']] ?? '#94a3b8' ?>22; color: <?= $st_colors[$c['status']] ?? '#94a3b8' ?>;">
                                    <?= e($st) ?>
                                </span>
                            </div>
                            <h4 class="mt-1 font-semibold text-slate-900"><?= e($c['title']) ?></h4>
                            <p class="text-xs text-slate-500 mt-1"><?= e($c['case_type']) ?> · Abierto <?= e(date('d M Y', strtotime($c['opened_at']))) ?></p>
                        </div>
                        <span class="text-slate-400">→</span>
                    </div>
                </a>
            </li>
            <?php endforeach; ?>
            <?php if (empty($cases)): ?>
            <li class="text-center py-12 text-slate-500">No hay casos asociados aún.</li>
            <?php endif; ?>
        </ul>

        <div class="mt-12 text-center text-xs text-slate-500">
            <p>🔒 Acceso seguro · Tu link es personal e intransferible</p>
            <p class="mt-2">© <?= date('Y') ?> <?= e($portal['tenant_name']) ?></p>
        </div>
    </div>
</div>
