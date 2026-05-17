<div class="kn-hero relative min-h-screen overflow-hidden flex items-center justify-center px-4 py-12">
    <!-- Backdrop decorativo -->
    <div class="kn-grid-bg"></div>
    <div class="kn-orb kn-orb-1" style="opacity:0.30"></div>
    <div class="kn-orb kn-orb-2" style="opacity:0.30"></div>

    <div class="relative w-full max-w-md">

        <!-- Brand -->
        <a href="<?= e(url('/')) ?>" class="flex items-center justify-center gap-2.5 mb-8">
            <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow-lg shadow-indigo-500/30">K</div>
            <span class="text-xl font-bold tracking-tight"><?= e(APP_NAME) ?></span>
        </a>

        <div class="rounded-3xl border border-slate-200 bg-white/80 backdrop-blur-xl p-8 shadow-xl shadow-indigo-500/5">

            <!-- Icon hero -->
            <div class="text-center">
                <div class="kn-lock mx-auto">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                </div>
                <h1 class="mt-6 text-2xl font-bold tracking-tight">Verificación en 2 pasos</h1>
                <p class="mt-2 text-sm text-slate-600">Ingresa el código de 6 dígitos de tu app autenticadora<br>(Google Authenticator, 1Password, Authy).</p>
            </div>

            <form class="mt-8 space-y-6" method="POST" action="<?= e(url('login/2fa')) ?>"
                  x-data="otpForm()">
                <?= csrf_field() ?>

                <!-- OTP boxes -->
                <div>
                    <label class="block text-xs font-semibold uppercase tracking-wide text-slate-700 text-center mb-3">Código de 6 dígitos</label>
                    <div class="flex justify-center gap-2" @paste="onPaste($event)">
                        <template x-for="i in 6" :key="i">
                            <input
                                type="text" inputmode="numeric" pattern="[0-9]*" maxlength="1" autocomplete="one-time-code"
                                x-ref="`d${i}`"
                                x-on:input="onInput(i, $event)"
                                x-on:keydown="onKey(i, $event)"
                                class="otp-box w-12 h-14 text-2xl text-center font-bold rounded-xl border-2 border-slate-200 bg-slate-50 focus:bg-white focus:outline-none focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 transition"
                                :autofocus="i === 1">
                        </template>
                    </div>
                    <input type="hidden" name="code" x-model="code">
                </div>

                <button type="submit" class="kn-cta-shimmer relative w-full inline-flex items-center justify-center gap-2 rounded-xl bg-slate-900 hover:bg-slate-800 px-4 py-3 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 transition-all overflow-hidden disabled:opacity-50"
                        :disabled="code.length !== 6">
                    <span class="relative z-10">Verificar</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"/></svg>
                </button>
            </form>

            <div class="mt-6 text-center">
                <p class="text-xs text-slate-500">¿Perdiste acceso a tu autenticador?</p>
                <a href="<?= e(url('login')) ?>" class="mt-1 inline-flex items-center gap-1 text-xs font-medium text-indigo-600 hover:text-indigo-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/></svg>
                    Volver a iniciar sesión
                </a>
            </div>
        </div>

        <div class="mt-6 flex items-center justify-center gap-3 text-[11px] text-slate-400">
            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> TOTP RFC 6238</span>
            <span class="flex items-center gap-1"><span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span> Rate-limit activo</span>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
function otpForm() {
    return {
        digits: ['','','','','',''],
        get code() { return this.digits.join(''); },
        set code(v) { /* hidden field reactivity */ },
        onInput(i, e) {
            var v = (e.target.value || '').replace(/\D/g, '').slice(-1);
            e.target.value = v;
            this.digits[i-1] = v;
            if (v && i < 6) this.$refs['d'+(i+1)].focus();
            // Auto-submit cuando todos llenos
            if (i === 6 && this.digits.join('').length === 6) {
                e.target.form.requestSubmit();
            }
        },
        onKey(i, e) {
            if (e.key === 'Backspace' && !e.target.value && i > 1) {
                this.$refs['d'+(i-1)].focus();
            }
            if (e.key === 'ArrowLeft' && i > 1)  this.$refs['d'+(i-1)].focus();
            if (e.key === 'ArrowRight' && i < 6) this.$refs['d'+(i+1)].focus();
        },
        onPaste(e) {
            var txt = (e.clipboardData || window.clipboardData).getData('text');
            var digits = (txt || '').replace(/\D/g, '').slice(0, 6).split('');
            if (digits.length === 6) {
                e.preventDefault();
                for (var i = 0; i < 6; i++) {
                    this.digits[i] = digits[i];
                    this.$refs['d'+(i+1)].value = digits[i];
                }
                this.$refs['d6'].focus();
                setTimeout(() => e.target.form.requestSubmit(), 100);
            }
        }
    }
}
</script>
