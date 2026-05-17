<?php $selected = $selected_plan ?? 'pro'; ?>
<section class="kn-hero relative overflow-hidden pt-8 pb-20">
    <div class="kn-grid-bg"></div>
    <div class="kn-orb kn-orb-1" style="opacity:0.30"></div>
    <div class="kn-orb kn-orb-2" style="opacity:0.30"></div>

    <div class="mx-auto max-w-3xl px-6 lg:px-8 relative">

        <!-- Header -->
        <div class="text-center mb-10">
            <span class="kn-pill">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                14 días gratis · Sin tarjeta
            </span>
            <h1 class="mt-6 text-4xl lg:text-5xl font-bold tracking-tight">
                <span class="kn-headline">Crea tu bufete</span>
                <span class="kn-headline-grad">en 60 segundos.</span>
            </h1>
            <p class="mt-4 text-base text-slate-600">
                Tu tenant se aprovisiona automáticamente con clave de cifrado única.
            </p>
        </div>

        <!-- Form card -->
        <form method="POST" action="<?= e(url('onboarding')) ?>"
              class="relative rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-xl p-6 sm:p-10 shadow-xl shadow-indigo-500/5"
              x-data="onboardingForm()">
            <?= csrf_field() ?>

            <!-- ===== Sección 1: Tu bufete ===== -->
            <div class="space-y-6">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white items-center justify-center font-bold text-sm shadow-lg shadow-indigo-500/30">1</div>
                    <div>
                        <h2 class="text-lg font-bold leading-tight">Tu bufete</h2>
                        <p class="text-xs text-slate-500">Nombre, URL y país principal</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Nombre del bufete</label>
                        <input name="firm_name" required value="<?= e(old('firm_name')) ?>"
                               x-model="firm" @input="autoSlug()"
                               placeholder="Ej. Núñez & Asociados"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Slug (URL)</label>
                        <div class="relative">
                            <input name="slug" required value="<?= e(old('slug')) ?>" pattern="[a-z0-9][a-z0-9-]+"
                                   x-model="slug" @input="slugManual = true"
                                   placeholder="nunez-asociados"
                                   class="w-full rounded-xl border border-slate-200 pr-3 py-2.5 pl-[88px] text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition font-mono">
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-xs font-mono text-slate-400">/t/</span>
                        </div>
                        <p class="mt-1 text-[11px] text-slate-500">a-z, 0-9 y guiones</p>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">País principal</label>
                    <select name="country" class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                        <?php foreach (COUNTRIES as $code => $name): ?>
                        <option value="<?= e($code) ?>" <?= ($code === 'US') ? 'selected' : '' ?>><?= e($name) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>

            <hr class="my-8 border-slate-200">

            <!-- ===== Sección 2: Plan ===== -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white items-center justify-center font-bold text-sm shadow-lg shadow-indigo-500/30">2</div>
                    <div>
                        <h2 class="text-lg font-bold leading-tight">Elige tu plan</h2>
                        <p class="text-xs text-slate-500">14 días gratis en cualquiera. Cambia luego sin costo.</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <?php foreach ($plans as $i => $p):
                        $features = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                        $isFeatured = ($p['code'] === 'pro');
                    ?>
                    <label class="relative cursor-pointer">
                        <input type="radio" name="plan" value="<?= e($p['code']) ?>" class="peer sr-only" <?= ($selected === $p['code']) ? 'checked' : '' ?>>
                        <div class="relative h-full rounded-2xl border-2 border-slate-200 bg-white p-4 transition peer-checked:border-indigo-600 peer-checked:bg-indigo-50/40 peer-checked:shadow-lg peer-checked:shadow-indigo-500/10 hover:border-slate-300">
                            <?php if ($isFeatured): ?>
                            <span class="absolute -top-2 right-3 inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-400 to-pink-400 text-amber-950 text-[10px] font-bold px-2 py-0.5 shadow">
                                <svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                                Popular
                            </span>
                            <?php endif; ?>
                            <div class="flex items-center justify-between">
                                <h3 class="text-sm font-bold"><?= e($p['name']) ?></h3>
                                <div class="h-4 w-4 rounded-full border-2 border-slate-300 peer-checked:border-indigo-600 transition flex items-center justify-center">
                                    <div class="h-2 w-2 rounded-full bg-indigo-600 opacity-0 peer-checked:opacity-100 transition"></div>
                                </div>
                            </div>
                            <p class="mt-3 text-2xl font-bold tracking-tight">$<?= (int)$p['price_usd'] ?><span class="text-xs font-normal text-slate-500">/mes</span></p>
                            <ul class="mt-3 space-y-1 text-[11px] text-slate-600">
                                <li class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?= (int)$p['max_users'] ?> usuarios</li>
                                <li class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?= number_format((int)$p['max_cases']) ?> casos</li>
                                <li class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg><?= number_format((int)$p['max_storage_mb']) ?> MB</li>
                                <?php if (!empty($features['workflows'])): ?><li class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>Workflows</li><?php endif; ?>
                                <?php if (!empty($features['two_factor'])): ?><li class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#10b981" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>2FA</li><?php endif; ?>
                            </ul>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <hr class="my-8 border-slate-200">

            <!-- ===== Sección 3: Tu cuenta ===== -->
            <div class="space-y-4">
                <div class="flex items-center gap-3 mb-2">
                    <div class="flex h-9 w-9 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white items-center justify-center font-bold text-sm shadow-lg shadow-indigo-500/30">3</div>
                    <div>
                        <h2 class="text-lg font-bold leading-tight">Tu cuenta de administrador</h2>
                        <p class="text-xs text-slate-500">Tendrás acceso completo al tenant</p>
                    </div>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Tu nombre</label>
                        <input name="admin_name" required value="<?= e(old('admin_name')) ?>"
                               placeholder="Carolina Núñez"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Email</label>
                        <input name="admin_email" type="email" required value="<?= e(old('admin_email')) ?>"
                               placeholder="tu@bufete.com"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>
                </div>

                <div x-data="passwordStrength()">
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Contraseña</label>
                    <div class="relative">
                        <input name="admin_password" :type="show ? 'text' : 'password'" required minlength="8"
                               x-model="value" @input="check()"
                               placeholder="Mínimo 8 caracteres"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-10 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <!-- Strength meter -->
                    <div class="mt-2 flex gap-1">
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 1 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 2 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 3 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 4 ? barColor : 'bg-slate-200'"></div>
                    </div>
                    <p class="mt-1 text-[11px]" :class="textColor" x-text="label"></p>
                </div>
            </div>

            <!-- Submit -->
            <div class="mt-8">
                <button type="submit" class="kn-cta-shimmer relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 px-5 py-3.5 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 transition-all overflow-hidden">
                    <span class="relative z-10">Crear bufete y empezar</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
                <p class="mt-3 text-center text-[11px] text-slate-500">
                    Al crear cuenta aceptas los <a href="#" class="text-indigo-600 hover:underline">Términos</a> y la <a href="#" class="text-indigo-600 hover:underline">Privacidad</a>.
                </p>
            </div>

            <!-- Trust badges -->
            <div class="mt-6 pt-6 border-t border-slate-100 flex items-center justify-center gap-4 text-[11px] text-slate-400 flex-wrap">
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg> Cifrado AEAD</span>
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg> HIPAA-ready</span>
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg> Multi-tenant aislado</span>
            </div>
        </form>

        <p class="mt-6 text-center text-sm text-slate-600">
            ¿Ya tienes cuenta?
            <a href="<?= e(url('login')) ?>" class="font-semibold text-indigo-600 hover:text-indigo-500">Inicia sesión</a>
        </p>
    </div>
</section>

<script nonce="<?= e(csp_nonce()) ?>">
function onboardingForm() {
    return {
        firm: <?= json_encode(old('firm_name') ?? '') ?>,
        slug: <?= json_encode(old('slug') ?? '') ?>,
        slugManual: <?= !empty(old('slug')) ? 'true' : 'false' ?>,
        autoSlug() {
            if (this.slugManual) return;
            this.slug = (this.firm || '')
                .toLowerCase()
                .normalize('NFD').replace(/[̀-ͯ]/g, '')
                .replace(/[^a-z0-9\s-]/g, '')
                .trim().replace(/\s+/g, '-')
                .replace(/-+/g, '-');
        }
    }
}
function passwordStrength() {
    return {
        value: '', show: false, score: 0, label: '', barColor: 'bg-slate-200', textColor: 'text-slate-500',
        check() {
            var v = this.value || '';
            var s = 0;
            if (v.length >= 8) s++;
            if (v.length >= 12) s++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
            if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
            this.score = s;
            if (!v.length) { this.label = ''; return; }
            var labels = ['Muy débil', 'Débil', 'Aceptable', 'Fuerte', 'Excelente'];
            var colors = ['bg-rose-400', 'bg-orange-400', 'bg-amber-400', 'bg-emerald-400', 'bg-emerald-500'];
            var textColors = ['text-rose-600', 'text-orange-600', 'text-amber-600', 'text-emerald-600', 'text-emerald-600'];
            this.label = labels[s];
            this.barColor = colors[s] || colors[0];
            this.textColor = textColors[s] || textColors[0];
        }
    }
}
</script>
