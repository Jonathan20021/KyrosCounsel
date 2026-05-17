<?php $brand = $link['brand_color'] ?: '#4f46e5'; $is_paid = $link['status'] === 'paid'; ?>
<style>
.pay-bg { background: linear-gradient(135deg, <?= e($brand) ?>0d 0%, <?= e($brand) ?>03 100%); min-height: 100vh; }
</style>

<div class="pay-bg flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md">
        <div class="text-center mb-6">
            <div class="mx-auto h-12 w-12 rounded-xl flex items-center justify-center text-white text-xl font-bold shadow-md" style="background: <?= e($brand) ?>;">
                <?= e(strtoupper(mb_substr($link['tenant_name'], 0, 1))) ?>
            </div>
            <p class="mt-3 text-sm text-slate-600"><?= e($link['tenant_name']) ?></p>
        </div>

        <div class="rounded-2xl bg-white border border-slate-200 p-8 shadow-xl">
            <p class="text-center text-xs uppercase tracking-wider text-slate-500 font-semibold">Pago seguro</p>
            <div class="mt-3 text-center">
                <div class="text-5xl font-bold text-slate-900">$<?= e(number_format((float)$link['amount_usd'], 2)) ?></div>
                <div class="text-xs text-slate-500 mt-1"><?= e($link['currency']) ?></div>
            </div>

            <hr class="my-5 border-slate-200">

            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt class="text-slate-500">Concepto</dt><dd class="font-medium text-right max-w-[60%]"><?= e($link['concept']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Caso</dt><dd class="font-mono text-xs"><?= e($link['case_number']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cliente</dt><dd><?= e($link['first_name'] . ' ' . $link['last_name']) ?></dd></div>
            </dl>

            <?php if ($is_paid): ?>
            <div class="mt-6 rounded-xl bg-emerald-50 border border-emerald-200 p-4 text-center">
                <div class="text-2xl">✓</div>
                <p class="mt-1 font-semibold text-emerald-800">Este pago ya fue confirmado</p>
                <p class="text-xs text-emerald-700 mt-1"><?= e($link['paid_at']) ?></p>
            </div>
            <?php else: ?>
            <form method="POST" action="<?= e(url('pay/' . $link['token'] . '/checkout')) ?>" class="mt-6">
                <button class="w-full rounded-xl py-3 text-sm font-semibold text-white shadow-lg transition hover:shadow-xl" style="background: <?= e($brand) ?>;">
                    🔒 Pagar con tarjeta
                </button>
            </form>
            <p class="mt-3 text-center text-[10px] text-slate-400">
                Procesado por <?= stripe_is_real() ? 'Stripe' : 'Stripe (modo demo)' ?> · Cifrado de extremo a extremo
            </p>
            <?php endif; ?>
        </div>

        <p class="mt-6 text-center text-xs text-slate-500">
            🔒 Este link es personal e intransferible · Vence el <?= e(date('d M Y', strtotime($link['expires_at']))) ?>
        </p>
    </div>
</div>
