<div class="kn-hero relative min-h-screen overflow-hidden flex items-center justify-center px-4 py-12">
    <div class="kn-grid-bg"></div>
    <div class="kn-orb kn-orb-1" style="opacity:0.30"></div>
    <div class="kn-orb kn-orb-2" style="opacity:0.30"></div>

    <div class="relative w-full max-w-md">
        <a href="<?= e(url('/')) ?>" class="flex items-center justify-center gap-2.5 mb-8">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/30">K</div>
            <span class="text-xl font-bold tracking-tight"><?= e(APP_NAME) ?></span>
        </a>

        <div class="rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-xl p-8 shadow-xl shadow-indigo-500/5">
            <div class="text-center mb-6">
                <div class="kn-lock mx-auto" style="background: linear-gradient(135deg, #4338ca, #7c3aed);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 2l-2 2m-7.61 7.61a5.5 5.5 0 11-7.778 7.778 5.5 5.5 0 017.777-7.777zm0 0L15.5 7.5m0 0l3 3L22 7l-3-3m-3.5 3.5L19 4"/></svg>
                </div>
                <h1 class="mt-5 text-2xl font-bold tracking-tight">Recuperar acceso</h1>
                <p class="mt-2 text-sm text-slate-600">Te enviaremos un enlace seguro a tu email para que puedas reestablecer tu contraseña.</p>
            </div>

            <form method="POST" action="<?= e(url('cliente/forgot')) ?>" class="space-y-4">
                <?= csrf_field() ?>
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Email registrado</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        </span>
                        <input name="email" type="email" required autofocus
                               placeholder="tu@email.com"
                               class="w-full rounded-xl border border-slate-200 pl-10 pr-3 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                    </div>
                </div>

                <button type="submit" class="kn-cta-shimmer relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 transition-all overflow-hidden">
                    <span class="relative z-10">Enviar enlace</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                </button>
            </form>

            <p class="mt-6 text-center">
                <a href="<?= e(url('cliente/login')) ?>" class="inline-flex items-center gap-1 text-xs font-medium text-slate-600 hover:text-indigo-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Volver al inicio de sesión
                </a>
            </p>
        </div>
    </div>
</div>
