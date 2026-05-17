<?php $brand = $link['brand_color'] ?: '#4f46e5'; ?>
<style>.pay-bg { background: linear-gradient(135deg, <?= e($brand) ?>0d 0%, <?= e($brand) ?>03 100%); min-height: 100vh; }</style>
<div class="pay-bg flex items-center justify-center px-4 py-12">
    <div class="w-full max-w-md text-center">
        <div class="mx-auto h-20 w-20 rounded-full bg-gradient-to-br from-emerald-400 to-emerald-600 text-white flex items-center justify-center text-4xl shadow-xl mb-6">✓</div>
        <h1 class="text-3xl font-bold text-slate-900">¡Pago confirmado!</h1>
        <p class="mt-3 text-slate-600">Recibimos tu pago de <strong>$<?= e(number_format((float)$link['amount_usd'], 2)) ?></strong> para el caso <span class="font-mono"><?= e($link['case_number']) ?></span>.</p>
        <div class="mt-8 rounded-2xl bg-white border border-slate-200 p-6 text-left text-sm">
            <h3 class="text-xs uppercase tracking-wider font-semibold text-slate-500 mb-3">Detalle</h3>
            <dl class="space-y-2">
                <div class="flex justify-between"><dt class="text-slate-500">Bufete</dt><dd><?= e($link['tenant_name']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Concepto</dt><dd class="text-right max-w-[60%]"><?= e($link['concept']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Cliente</dt><dd><?= e($link['first_name'] . ' ' . $link['last_name']) ?></dd></div>
                <div class="flex justify-between"><dt class="text-slate-500">Monto</dt><dd class="font-bold">$<?= e(number_format((float)$link['amount_usd'], 2)) ?></dd></div>
            </dl>
        </div>
        <p class="mt-8 text-xs text-slate-500">Recibirás un email de confirmación. Si tienes preguntas, contacta a tu abogado.</p>
    </div>
</div>
