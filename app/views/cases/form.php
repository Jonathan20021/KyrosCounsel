<?php
$is_edit = !empty($case);
$action = $is_edit ? tenant_url('cases/' . $case['id']) : tenant_url('cases');
$c = $case ?: [];
$g = function ($k, $d = '') use ($c) { return $c[$k] ?? old($k, $d); };
$country = $g('country', current_tenant()['country']);
?>
<div class="max-w-3xl">
    <h1 class="text-2xl font-bold"><?= $is_edit ? 'Editar caso' : 'Nuevo caso' ?></h1>

    <form method="POST" action="<?= e($action) ?>" class="mt-6 space-y-5 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
        <?= csrf_field() ?>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Cliente *</label>
                <select name="client_id" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <option value="">— Seleccione cliente —</option>
                    <?php foreach ($clients as $cl):
                        $sel = ((int)$g('client_id') === (int)$cl['id']) || ($preselect_client === (int)$cl['id']);
                    ?>
                    <option value="<?= (int)$cl['id'] ?>" <?= $sel ? 'selected' : '' ?>><?= e($cl['last_name'] . ', ' . $cl['first_name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">N° de caso (opcional)</label>
                <input name="case_number" value="<?= e($g('case_number')) ?>" placeholder="Auto-generado si lo dejas vacio"
                       class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm font-mono">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Titulo *</label>
            <input name="title" required value="<?= e($g('title')) ?>"
                   class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
            <div>
                <label class="block text-sm font-medium">Pais *</label>
                <select name="country" id="country" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <?php foreach (CASE_TYPES as $cc => $_): ?>
                    <option value="<?= e($cc) ?>" <?= $cc === $country ? 'selected' : '' ?>><?= e(country_name($cc)) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Tipo de caso *</label>
                <select name="case_type" id="case_type" required class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <?php foreach (case_types_for($country) as $code => $label): ?>
                    <option value="<?= e($code) ?>" <?= $code === $g('case_type') ? 'selected' : '' ?>><?= e($label) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
            <div>
                <label class="block text-sm font-medium">Estado</label>
                <select name="status" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <?php foreach (CASE_STATUSES as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $g('status', 'intake') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Prioridad</label>
                <select name="priority" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <?php foreach (CASE_PRIORITIES as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $g('priority', 'normal') === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium">Abogado asignado</label>
                <select name="attorney_id" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
                    <option value="0">— Sin asignar —</option>
                    <?php foreach ($attorneys as $a): ?>
                    <option value="<?= (int)$a['id'] ?>" <?= (int)$g('attorney_id') === (int)$a['id'] ? 'selected' : '' ?>><?= e($a['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium">Fecha de apertura</label>
            <input name="opened_at" type="date" value="<?= e($g('opened_at', date('Y-m-d'))) ?>"
                   class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm">
        </div>
        <div>
            <label class="block text-sm font-medium">Descripcion</label>
            <textarea name="description" rows="4" class="mt-1 block w-full rounded-md border-0 py-1.5 px-3 ring-1 ring-inset ring-slate-300 sm:text-sm"><?= e($g('description')) ?></textarea>
        </div>

        <?php if (!$is_edit): ?>
        <!-- Auto setup magic -->
        <div class="rounded-xl border-2 border-indigo-200 bg-gradient-to-br from-indigo-50 to-violet-50 p-4">
            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="auto_setup" value="1" checked class="mt-0.5 h-5 w-5 rounded border-slate-300 text-indigo-600 focus:ring-indigo-500">
                <span class="flex-1">
                    <span class="block font-semibold text-sm text-slate-900">⚡ Auto-popular caso (recomendado)</span>
                    <span class="block text-xs text-slate-700 mt-1">Crea automaticamente:</span>
                    <ul class="mt-2 grid grid-cols-1 sm:grid-cols-3 gap-2 text-xs text-slate-700">
                        <li class="flex items-center gap-1.5">📋 <span><strong>Checklist USCIS</strong> de evidencia</span></li>
                        <li class="flex items-center gap-1.5">✓ <span><strong>Tareas estandar</strong> con plazos</span></li>
                        <li class="flex items-center gap-1.5">💰 <span><strong>Filing fee</strong> como pago pendiente</span></li>
                    </ul>
                </span>
            </label>
        </div>
        <?php endif; ?>

        <div class="flex justify-end gap-2">
            <a href="<?= e(tenant_url('cases')) ?>" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Cancelar</a>
            <button class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500"><?= $is_edit ? 'Guardar' : 'Crear caso' ?></button>
        </div>
    </form>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
const TYPES_BY_COUNTRY = <?= json_encode(CASE_TYPES, JSON_UNESCAPED_UNICODE) ?>;
document.getElementById('country').addEventListener('change', function (e) {
    const types = TYPES_BY_COUNTRY[e.target.value] || TYPES_BY_COUNTRY['US'];
    const sel = document.getElementById('case_type');
    sel.innerHTML = '';
    Object.keys(types).forEach(k => {
        const opt = document.createElement('option');
        opt.value = k; opt.textContent = types[k];
        sel.appendChild(opt);
    });
});
</script>
