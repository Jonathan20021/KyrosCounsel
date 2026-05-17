<?php
$is_edit = !empty($client);
$action = $is_edit ? tenant_url('clients/' . $client['id']) : tenant_url('clients');
$c = $client ?: [];
$g = function ($k, $d = '') use ($c) { return $c[$k] ?? old($k, $d); };
?>
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold"><?= $is_edit ? 'Editar cliente' : 'Nuevo cliente' ?></h1>

    <form method="POST" action="<?= e($action) ?>" class="mt-6 space-y-6 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?= csrf_field() ?>

        <h3 class="text-sm font-semibold text-slate-700">Datos basicos</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Nombre *</label>
                <input name="first_name" required value="<?= e($g('first_name')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Apellido *</label>
                <input name="last_name" required value="<?= e($g('last_name')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Email</label>
                <input name="email" type="email" value="<?= e($g('email')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Telefono</label>
                <input name="phone" value="<?= e($g('phone')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 focus:ring-2 focus:ring-indigo-600 sm:text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium">Nacionalidad</label>
                <select name="nationality" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <option value="">— Seleccione —</option>
                    <?php foreach (COUNTRIES as $code => $name): ?>
                    <option value="<?= e($code) ?>" <?= ($g('nationality') === $code) ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Pais de residencia</label>
                <select name="country_residence" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <option value="">— Seleccione —</option>
                    <?php foreach (COUNTRIES as $code => $name): ?>
                    <option value="<?= e($code) ?>" <?= ($g('country_residence') === $code) ? 'selected' : '' ?>><?= e($name) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <hr class="border-slate-200">
        <h3 class="text-sm font-semibold text-slate-700">Datos sensibles (cifrados con XChaCha20-Poly1305)</h3>
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div>
                <label class="block text-sm font-medium">Pasaporte</label>
                <input name="passport" value="<?= e($g('passport')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium">A-Number</label>
                <input name="alien_number" value="<?= e($g('alien_number')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm font-mono">
            </div>
            <div>
                <label class="block text-sm font-medium">Fecha de nacimiento</label>
                <input name="date_of_birth" type="date" value="<?= e($g('date_of_birth')) ?>"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
            </div>
        </div>

        <hr class="border-slate-200">
        <div>
            <label class="block text-sm font-medium">Direccion</label>
            <input name="address" value="<?= e($g('address')) ?>"
                   class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium">Notas</label>
            <textarea name="notes" rows="3"
                      class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm"><?= e($g('notes')) ?></textarea>
        </div>
        <div>
            <label class="block text-sm font-medium">Estado</label>
            <select name="status" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                <?php foreach (['active' => 'Activo', 'inactive' => 'Inactivo', 'archived' => 'Archivado'] as $k => $v): ?>
                <option value="<?= e($k) ?>" <?= ($g('status', 'active') === $k) ? 'selected' : '' ?>><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="flex justify-end gap-2">
            <a href="<?= e(tenant_url('clients')) ?>" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Cancelar</a>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><?= $is_edit ? 'Guardar' : 'Crear' ?></button>
        </div>
    </form>
</div>
