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
                <div class="kn-lock mx-auto" style="background: linear-gradient(135deg, #059669, #10b981);">
                    <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                <h1 class="mt-5 text-2xl font-bold tracking-tight">Nueva contraseña</h1>
                <p class="mt-2 text-sm text-slate-600">Crea una contraseña segura de al menos 8 caracteres.</p>
            </div>

            <form method="POST" action="<?= e(url('cliente/reset/' . $token)) ?>" class="space-y-4" x-data="resetForm()">
                <?= csrf_field() ?>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Nueva contraseña</label>
                    <div class="relative">
                        <input name="password" :type="show ? 'text' : 'password'" required minlength="8" autofocus autocomplete="new-password"
                               x-model="password" @input="check()"
                               placeholder="Mínimo 8 caracteres"
                               class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 pr-10 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition">
                        <button type="button" @click="show = !show" class="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600" tabindex="-1">
                            <svg x-show="!show" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            <svg x-show="show" x-cloak xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19m-6.72-1.07a3 3 0 11-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                        </button>
                    </div>
                    <div class="mt-2 flex gap-1">
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 1 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 2 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 3 ? barColor : 'bg-slate-200'"></div>
                        <div class="h-1 flex-1 rounded-full transition" :class="score >= 4 ? barColor : 'bg-slate-200'"></div>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 mb-1.5">Confirmar contraseña</label>
                    <input name="password2" type="password" required minlength="8" autocomplete="new-password"
                           x-model="confirm"
                           placeholder="Repite la contraseña"
                           class="w-full rounded-xl border border-slate-200 px-3.5 py-2.5 text-sm bg-slate-50/50 focus:bg-white focus:outline-none focus:ring-2 focus:ring-indigo-500/30 focus:border-indigo-500 transition"
                           :class="confirm && password !== confirm ? 'border-rose-300 focus:border-rose-500 focus:ring-rose-500/20' : ''">
                    <p x-show="confirm && password !== confirm" x-cloak class="mt-1 text-[11px] text-rose-600">Las contraseñas no coinciden</p>
                </div>

                <button type="submit" class="kn-cta-shimmer relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 transition-all overflow-hidden disabled:opacity-50"
                        :disabled="!password || password.length < 8 || password !== confirm">
                    <span class="relative z-10">Cambiar contraseña</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
function resetForm() {
    return {
        password: '', confirm: '', show: false, score: 0,
        barColor: 'bg-slate-200',
        check() {
            var v = this.password || '', s = 0;
            if (v.length >= 8) s++;
            if (v.length >= 12) s++;
            if (/[A-Z]/.test(v) && /[a-z]/.test(v)) s++;
            if (/\d/.test(v) && /[^A-Za-z0-9]/.test(v)) s++;
            this.score = s;
            var colors = ['bg-rose-400','bg-orange-400','bg-amber-400','bg-emerald-400','bg-emerald-500'];
            this.barColor = colors[s] || colors[0];
        }
    }
}
</script>
