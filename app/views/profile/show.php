<div class="max-w-2xl space-y-6">
    <h1 class="text-2xl font-bold tracking-tight">Mi perfil</h1>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold mb-3">Datos</h3>
        <dl class="space-y-2 text-sm">
            <div><dt class="text-slate-500">Nombre</dt><dd class="font-medium"><?= e($me['name']) ?></dd></div>
            <div><dt class="text-slate-500">Email</dt><dd><?= e($me['email']) ?></dd></div>
            <div><dt class="text-slate-500">Rol</dt><dd><?= e(ucfirst(str_replace('_', ' ', $me['role']))) ?></dd></div>
            <div><dt class="text-slate-500">2FA</dt><dd><?= $me['two_factor_enabled'] ? '✓ Activado' : 'No activado' ?></dd></div>
        </dl>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold mb-3">Cambiar contrasena</h3>
        <form method="POST" action="<?= e(tenant_url('profile/password')) ?>" class="space-y-3">
            <?= csrf_field() ?>
            <div>
                <label class="block text-sm font-medium">Contrasena actual</label>
                <input name="current_password" type="password" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Nueva contrasena (8+)</label>
                <input name="new_password" type="password" required minlength="8" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Repetir</label>
                <input name="new_password2" type="password" required minlength="8" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
            </div>
            <div class="flex justify-end">
                <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Cambiar</button>
            </div>
        </form>
    </div>

    <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
        <h3 class="text-base font-semibold mb-3">Autenticacion en 2 pasos (2FA)</h3>
        <?php if ($me['two_factor_enabled']): ?>
            <p class="text-sm text-emerald-700">2FA esta activado. Si pierdes acceso a tu app autenticadora, contacta al administrador.</p>
            <form method="POST" action="<?= e(tenant_url('profile/2fa/disable')) ?>" class="mt-4" data-confirm="¿Desactivar 2FA?">
                <?= csrf_field() ?>
                <button class="text-sm text-red-600 hover:underline">Desactivar 2FA</button>
            </form>
        <?php else: ?>
            <p class="text-sm text-slate-600">Aumenta la seguridad activando 2FA con Google Authenticator, Authy, 1Password o cualquier app TOTP.</p>
            <a href="<?= e(tenant_url('profile/2fa')) ?>" class="mt-4 inline-block rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Activar 2FA</a>
        <?php endif; ?>
    </div>
</div>
