<div class="max-w-2xl">
    <h1 class="text-2xl font-bold">Nuevo usuario</h1>
    <form method="POST" action="<?= e(tenant_url('users')) ?>" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?= csrf_field() ?>
        <div>
            <label class="block text-sm font-medium">Nombre completo</label>
            <input name="name" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium">Email</label>
            <input name="email" type="email" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium">Contrasena temporal (8+ caracteres)</label>
            <input name="password" type="password" required minlength="8" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium">Rol</label>
            <select name="role" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                <?php $roles = [
                    'tenant_admin' => 'Administrador del bufete',
                    'attorney'     => 'Abogado',
                    'paralegal'    => 'Paralegal',
                    'staff'        => 'Personal',
                ]; foreach ($roles as $k => $v): ?>
                <option value="<?= e($k) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex justify-end gap-2">
            <a href="<?= e(tenant_url('users')) ?>" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Cancelar</a>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">Crear</button>
        </div>
    </form>
</div>
