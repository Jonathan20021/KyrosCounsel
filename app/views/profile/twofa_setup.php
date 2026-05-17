<div class="max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold tracking-tight">Activar 2FA</h1>
    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm space-y-4">
        <ol class="list-decimal list-inside text-sm text-slate-700 space-y-2">
            <li>Abre tu app autenticadora (Google Authenticator, Authy, 1Password, etc.)</li>
            <li>Escanea el QR de abajo, o ingresa el codigo manualmente.</li>
            <li>Ingresa el codigo de 6 digitos que aparece para confirmar.</li>
        </ol>

        <div class="text-center">
            <img src="<?= e($qr) ?>" alt="QR 2FA" class="mx-auto rounded-lg border border-slate-200" width="200" height="200">
        </div>
        <div class="text-center">
            <p class="text-xs text-slate-500">Codigo manual:</p>
            <code class="mt-1 inline-block bg-slate-100 px-3 py-1 rounded text-sm font-mono"><?= e(chunk_split($secret, 4, ' ')) ?></code>
        </div>

        <form method="POST" action="<?= e(tenant_url('profile/2fa/enable')) ?>" class="space-y-3">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium">Codigo de 6 digitos</label>
                <input name="code" required pattern="[0-9]{6}" maxlength="6" inputmode="numeric"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 text-center text-xl tracking-widest ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600">
            </div>
            <div class="flex justify-end gap-2">
                <a href="<?= e(tenant_url('profile')) ?>" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Cancelar</a>
                <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Activar</button>
            </div>
        </form>
    </div>
</div>
