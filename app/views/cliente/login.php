<div class="min-h-screen flex items-center justify-center px-4 py-12 bg-gradient-to-br from-slate-50 to-indigo-50">
    <div class="w-full max-w-md">
        <div class="text-center mb-8">
            <div class="mx-auto h-12 w-12 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center text-xl font-bold shadow-lg">K</div>
            <h1 class="mt-4 text-2xl font-bold text-slate-900">Portal del cliente</h1>
            <p class="mt-1 text-sm text-slate-600">Acceso a tu caso de inmigracion</p>
        </div>

        <form method="POST" action="<?= e(url('cliente/login')) ?>" class="rounded-2xl bg-white border border-slate-200 p-8 shadow-lg space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-900">Email</label>
                <input name="email" type="email" required value="<?= e(old('email')) ?>" autocomplete="username"
                       class="mt-2 block w-full rounded-lg border border-slate-200 py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-slate-900">Contrasena</label>
                <input name="password" type="password" required autocomplete="current-password"
                       class="mt-2 block w-full rounded-lg border border-slate-200 py-2.5 px-3 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
            </div>
            <button type="submit" class="w-full rounded-lg bg-gradient-to-br from-indigo-600 to-violet-600 hover:from-indigo-700 hover:to-violet-700 py-2.5 text-sm font-semibold text-white shadow-lg shadow-indigo-500/30">
                Iniciar sesion
            </button>
            <p class="text-center text-xs"><a href="<?= e(url('cliente/forgot')) ?>" class="text-indigo-600 hover:underline">¿Olvidaste tu contrasena?</a></p>
        </form>

        <p class="mt-6 text-center text-xs text-slate-500">
            ¿No tienes cuenta? Tu abogado debe habilitarla. <br>
            Tambien puedes acceder con <a href="<?= e(url('login')) ?>" class="text-indigo-600 hover:underline">link directo</a> si lo recibiste.
        </p>
    </div>
</div>
