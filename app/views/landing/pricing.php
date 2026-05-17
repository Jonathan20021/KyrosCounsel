<!-- ============================================================
     PRICING HERO
     ============================================================ -->
<section class="kn-hero relative overflow-hidden pt-12 pb-16 lg:pt-20 lg:pb-20">
    <div class="kn-grid-bg"></div>
    <div class="kn-orb kn-orb-1" style="opacity:0.35"></div>
    <div class="kn-orb kn-orb-2" style="opacity:0.35"></div>

    <div class="mx-auto max-w-7xl px-6 lg:px-8 relative">
        <div class="max-w-3xl mx-auto text-center" data-anim="fade-up">
            <span class="kn-eyebrow">Precios transparentes</span>
            <h1 class="mt-5 text-5xl lg:text-6xl font-bold tracking-tight">
                <span class="kn-headline">Empieza gratis.</span>
                <br><span class="kn-headline-grad">Crece sin sorpresas.</span>
            </h1>
            <p class="mt-6 text-lg text-slate-600">
                14 días de prueba en cualquier plan. Sin tarjeta de crédito.
                Cambia o cancela cuando quieras.
            </p>

            <div class="mt-10 inline-flex flex-col items-center">
                <div data-pricing-toggle class="kn-toggle">
                    <span class="kn-toggle-pill"></span>
                    <button data-period="monthly" class="active">Mensual</button>
                    <button data-period="yearly">Anual <span class="text-emerald-600 font-bold">−20%</span></button>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     PRICING CARDS
     ============================================================ -->
<section class="pb-12">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto" data-anim="stagger">
            <?php foreach ($plans as $p):
                $features = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                $featured = ($p['code'] === 'pro');
            ?>
            <div class="kn-price-card <?= $featured ? 'kn-price-featured' : '' ?>">
                <?php if ($featured): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-400 to-pink-400 text-amber-950 text-xs font-bold px-3 py-1 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Más popular
                </span>
                <?php endif; ?>

                <div class="relative">
                    <h3 class="text-lg font-bold"><?= e($p['name']) ?></h3>
                    <p class="text-sm <?= $featured ? 'text-slate-400' : 'text-slate-500' ?> mt-1">
                        <?= $p['code'] === 'starter' ? 'Para bufetes que arrancan' : ($p['code'] === 'pro' ? 'Para equipos en crecimiento' : 'Para firmas establecidas') ?>
                    </p>

                    <p class="mt-6 flex items-baseline gap-x-1">
                        <span class="text-5xl font-bold tracking-tight" data-price="<?= e($p['price_usd']) ?>">$<?= e(number_format((float)$p['price_usd'], 0)) ?></span>
                        <span class="text-sm <?= $featured ? 'text-slate-400' : 'text-slate-500' ?>" data-period-label>/mes</span>
                    </p>
                    <p data-savings class="hidden text-xs mt-1 <?= $featured ? 'text-emerald-300' : 'text-emerald-600' ?> font-semibold">Ahorras 20% pagando anual</p>

                    <a href="<?= e(url('onboarding?plan=' . $p['code'])) ?>"
                       class="mt-6 block w-full rounded-xl py-3 text-center text-sm font-semibold transition <?= $featured
                           ? 'bg-white text-indigo-700 hover:bg-slate-50 shadow-lg'
                           : 'bg-slate-900 text-white hover:bg-slate-800' ?>">
                        Empezar prueba gratis
                    </a>

                    <ul class="mt-8 space-y-3 text-sm <?= $featured ? 'text-slate-300' : 'text-slate-700' ?>">
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Hasta <strong><?= (int)$p['max_users'] ?> usuarios</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Hasta <strong><?= number_format((int)$p['max_cases']) ?> casos</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><strong><?= number_format((int)$p['max_storage_mb']) ?> MB</strong> almacenamiento</span>
                        </li>
                        <?php if (!empty($features['emails'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><strong><?= number_format((int)$features['emails']) ?> emails/mes</strong></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['workflows'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Workflows automáticos</span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['two_factor'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Autenticación 2FA</span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['priority_support'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Soporte prioritario</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     COMPARISON TABLE
     ============================================================ -->
<section class="py-20">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="text-center mb-12" data-anim="fade-up">
            <span class="kn-eyebrow">Tabla comparativa</span>
            <h2 class="mt-4 text-3xl lg:text-4xl font-bold tracking-tight">Compara todas las funciones</h2>
        </div>

        <div class="overflow-x-auto" data-anim="fade-up">
            <table class="kn-table w-full min-w-[640px]">
                <thead>
                    <tr>
                        <th>Función</th>
                        <?php foreach ($plans as $p): ?>
                        <th class="text-center"><?= e($p['name']) ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr><td class="font-medium">Multi-tenant aislado</td><?php foreach ($plans as $p): ?><td class="text-center"><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Cifrado AEAD por tenant</td><?php foreach ($plans as $p): ?><td class="text-center"><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Plantillas de casos migratorios</td><?php foreach ($plans as $p): ?><td class="text-center"><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Documentos seguros con SHA-256</td><?php foreach ($plans as $p): ?><td class="text-center"><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Tareas con plazos</td><?php foreach ($plans as $p): ?><td class="text-center"><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Workflows automáticos</td>
                        <?php foreach ($plans as $p):
                            $f = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                            $has = !empty($f['workflows']); ?>
                        <td class="text-center"><?php if ($has): ?><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?php else: ?><svg class="kn-cross inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php endif; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr><td class="font-medium">Autenticación 2FA (TOTP)</td>
                        <?php foreach ($plans as $p):
                            $f = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                            $has = !empty($f['two_factor']); ?>
                        <td class="text-center"><?php if ($has): ?><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?php else: ?><svg class="kn-cross inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php endif; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr><td class="font-medium">Soporte prioritario</td>
                        <?php foreach ($plans as $p):
                            $f = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                            $has = !empty($f['priority_support']); ?>
                        <td class="text-center"><?php if ($has): ?><svg class="kn-check inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?php else: ?><svg class="kn-cross inline" xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg><?php endif; ?></td>
                        <?php endforeach; ?>
                    </tr>
                    <tr><td class="font-medium">Usuarios incluidos</td><?php foreach ($plans as $p): ?><td class="text-center font-semibold"><?= (int)$p['max_users'] ?></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Casos máximos</td><?php foreach ($plans as $p): ?><td class="text-center font-semibold"><?= number_format((int)$p['max_cases']) ?></td><?php endforeach; ?></tr>
                    <tr><td class="font-medium">Almacenamiento</td><?php foreach ($plans as $p): ?><td class="text-center font-semibold"><?= number_format((int)$p['max_storage_mb']) ?> MB</td><?php endforeach; ?></tr>
                </tbody>
            </table>
        </div>
    </div>
</section>

<!-- ============================================================
     FAQ MINI
     ============================================================ -->
<section class="py-20">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
        <div class="text-center mb-10" data-anim="fade-up">
            <h2 class="text-3xl font-bold tracking-tight">Preguntas sobre precios</h2>
        </div>
        <div class="space-y-3" data-anim="stagger">
            <?php
            $faqs = [
                ['¿Puedo cambiar de plan?', 'Sí, puedes hacer upgrade o downgrade en cualquier momento desde tu panel. El cobro se prorratea automáticamente.'],
                ['¿Qué métodos de pago aceptan?', 'Tarjetas de crédito y débito (Visa, Mastercard, Amex) vía Stripe. Transferencia bancaria en planes Enterprise.'],
                ['¿Hay descuentos para anual?', 'Sí, pagando anual ahorras un 20% — equivalente a 2.4 meses gratis al año.'],
                ['¿Qué pasa después de los 14 días?', 'Si no eliges un plan al terminar la prueba, tu cuenta queda inactiva pero los datos se conservan 30 días.'],
            ];
            foreach ($faqs as [$q, $a]): ?>
            <details class="kn-faq-item">
                <summary class="kn-faq-trigger">
                    <span><?= e($q) ?></span>
                    <svg class="kn-faq-icon text-slate-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="kn-faq-body"><?= e($a) ?></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FINAL CTA
     ============================================================ -->
<section class="py-16">
    <div class="mx-auto max-w-7xl px-6 lg:px-8" data-anim="fade-up">
        <div class="kn-final-cta">
            <div class="relative z-10 max-w-2xl mx-auto">
                <h2 class="text-4xl lg:text-5xl font-bold tracking-tight">¿Aún con dudas?</h2>
                <p class="mt-5 text-lg text-indigo-100/80">
                    Empieza con la prueba gratis de 14 días. Sin tarjeta, sin compromiso.
                </p>
                <div class="mt-8">
                    <a href="<?= e(url('onboarding')) ?>" class="group inline-flex items-center gap-2 rounded-xl bg-white text-indigo-700 px-7 py-3.5 text-sm font-bold shadow-2xl hover:bg-slate-100 hover:scale-[1.02] transition-all">
                        Empezar ahora
                        <svg class="group-hover:translate-x-1 transition" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
