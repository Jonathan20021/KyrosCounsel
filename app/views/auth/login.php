<div class="min-h-screen grid lg:grid-cols-2 bg-white">

    <!-- ========== LEFT: Brand panel con orbes ========== -->
    <div class="kn-hero hidden lg:flex relative overflow-hidden flex-col justify-between p-12 text-white"
         style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 45%, #4338ca 100%);">

        <!-- Orbes -->
        <div class="kn-orb kn-orb-1" style="opacity:0.45"></div>
        <div class="kn-orb kn-orb-2" style="opacity:0.40"></div>

        <!-- Grid sutil -->
        <div class="absolute inset-0 pointer-events-none"
             style="background-image: linear-gradient(rgba(255,255,255,0.05) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.05) 1px, transparent 1px); background-size: 48px 48px; mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 30%, transparent 80%); -webkit-mask-image: radial-gradient(ellipse 80% 60% at 50% 50%, black 30%, transparent 80%);"></div>

        <!-- Top: brand -->
        <a href="<?= e(url('/')) ?>" class="relative z-10 flex items-center gap-2.5 group w-max">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-white/10 backdrop-blur-xl border border-white/20 text-white font-bold shadow-lg group-hover:scale-105 transition">K</div>
            <span class="text-xl font-bold tracking-tight"><?= e(APP_NAME) ?></span>
        </a>

        <!-- Center: hero copy -->
        <div class="relative z-10 max-w-md">
            <span class="inline-flex items-center gap-2 rounded-full bg-white/10 backdrop-blur px-3 py-1 text-xs font-medium ring-1 ring-white/20">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                Plataforma operativa
            </span>
            <h2 class="mt-6 text-4xl xl:text-5xl font-bold leading-[1.1] tracking-tight">
                Tu bufete migratorio,
                <span class="bg-gradient-to-r from-indigo-300 via-violet-300 to-pink-300 bg-clip-text text-transparent">en un solo lugar.</span>
            </h2>
            <p class="mt-5 text-lg text-indigo-100/80 leading-relaxed">
                Cifrado AEAD, multi-tenant, workflows automáticos. Diseñado para escalar sin perder seguridad.
            </p>

            <!-- Features chips -->
            <div class="mt-8 grid grid-cols-3 gap-3">
                <div class="rounded-xl bg-white/5 backdrop-blur border border-white/10 p-4 hover:bg-white/10 transition">
                    <div class="text-3xl font-bold tracking-tight">256</div>
                    <div class="text-xs text-white/70 mt-0.5">bits AEAD</div>
                </div>
                <div class="rounded-xl bg-white/5 backdrop-blur border border-white/10 p-4 hover:bg-white/10 transition">
                    <div class="text-3xl font-bold tracking-tight">2FA</div>
                    <div class="text-xs text-white/70 mt-0.5">TOTP nativo</div>
                </div>
                <div class="rounded-xl bg-white/5 backdrop-blur border border-white/10 p-4 hover:bg-white/10 transition">
                    <div class="text-3xl font-bold tracking-tight">∞</div>
                    <div class="text-xs text-white/70 mt-0.5">Casos</div>
                </div>
            </div>

            <!-- Testimonial mini -->
            <div class="mt-10 flex items-start gap-3 rounded-2xl bg-white/5 backdrop-blur border border-white/10 p-4">
                <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-400 to-violet-500 flex items-center justify-center font-bold text-sm flex-shrink-0">CN</div>
                <div class="text-sm">
                    <p class="text-indigo-100/90 italic">"Pasamos de 8 sistemas a uno solo. Recuperamos 40% del tiempo del equipo."</p>
                    <p class="mt-2 text-xs text-white/60">Lic. Carolina Núñez · Núñez & Asociados</p>
                </div>
            </div>
        </div>

        <!-- Bottom: footer -->
        <div class="relative z-10 flex items-center justify-between text-xs text-white/50">
            <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?></span>
            <div class="flex items-center gap-3">
                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span> Sistemas operativos</span>
            </div>
        </div>
    </div>

    <!-- ========== RIGHT: Form ========== -->
    <div class="flex items-center justify-center px-4 py-8 sm:px-6 sm:py-12 lg:px-12 bg-white relative">

        <!-- Mobile brand -->
        <a href="<?= e(url('/')) ?>" class="lg:hidden absolute top-6 left-6 flex items-center gap-2.5">
            <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/30">K</div>
            <span class="text-lg font-bold tracking-tight"><?= e(APP_NAME) ?></span>
        </a>

        <!-- Back to home -->
        <a href="<?= e(url('/')) ?>" class="hidden lg:flex absolute top-6 right-6 items-center gap-1.5 text-xs font-medium text-slate-500 hover:text-slate-900 transition">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
            Volver al inicio
        </a>

        <div class="w-full max-w-sm">
            <div class="mb-8">
                <span class="kn-eyebrow">Iniciar sesión</span>
                <h1 class="mt-3 text-3xl font-bold tracking-tight">Bienvenido de vuelta</h1>
                <p class="mt-2 text-sm text-slate-600">Ingresa tus credenciales para continuar.</p>
            </div>

            <form class="space-y-4" method="POST" action="<?= e(url('login')) ?>" autocomplete="on">
                <?= csrf_field() ?>

                <div>
                    <label for="email" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide mb-1.5">Email</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input id="email" name="email" type="email" autocomplete="username" required
                               value="<?= e(old('email')) ?>"
                               placeholder="tu@bufete.com"
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-3 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>
                </div>

                <div>
                    <div class="flex items-center justify-between mb-1.5">
                        <label for="password" class="block text-xs font-semibold text-slate-700 uppercase tracking-wide">Contraseña</label>
                        <a href="#" class="text-xs font-medium text-indigo-600 hover:text-indigo-500">¿Olvidaste?</a>
                    </div>
                    <div class="relative" x-data="{ show: false }">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                        </span>
                        <input id="password" name="password" :type="show ? 'text' : 'password'" autocomplete="current-password" required
                               placeholder="••••••••"
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-10 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition" tabindex="-1">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                </div>

                <label class="flex items-center gap-2 text-sm text-slate-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                    Recordarme en este dispositivo
                </label>

                <button type="submit" class="kn-cta-shimmer relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 transition-all overflow-hidden">
                    <span class="relative z-10">Iniciar sesión</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </button>
            </form>

            <div class="mt-6 relative flex items-center">
                <div class="flex-1 h-px bg-slate-200"></div>
                <span class="px-3 text-xs uppercase tracking-wider text-slate-400 font-medium">o</span>
                <div class="flex-1 h-px bg-slate-200"></div>
            </div>

            <p class="mt-6 text-center text-sm text-slate-600">
                ¿Aún no tienes cuenta?
                <a href="<?= e(url('onboarding')) ?>" class="font-semibold text-indigo-600 hover:text-indigo-500">Crea tu bufete</a>
            </p>

            <div class="mt-8 flex items-center justify-center gap-3 text-[11px] text-slate-400 flex-wrap">
                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> TLS</span>
                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Argon2id</span>
                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> CSRF</span>
                <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Rate-limit</span>
            </div>
        </div>
    </div>
</div>
