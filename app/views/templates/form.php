<?php
$is_edit = !empty($template);
$action = $is_edit ? tenant_url('templates/' . $template['id']) : tenant_url('templates');
$tpl = $template ?: ['name' => '', 'slug' => '', 'category' => '', 'subject' => '', 'body_html' => '', 'is_active' => 1];
?>
<div class="max-w-4xl">
    <h1 class="kyros-title"><?= $is_edit ? 'Editar plantilla' : 'Nueva plantilla' ?></h1>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
        <form method="POST" action="<?= e($action) ?>" class="lg:col-span-2 space-y-4 kyros-card p-6">
            <?= csrf_field() ?>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium mb-1">Nombre</label>
                    <input name="name" required value="<?= e($tpl['name']) ?>" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                </div>
                <div>
                    <label class="block text-sm font-medium mb-1">Slug</label>
                    <input name="slug" required value="<?= e($tpl['slug']) ?>" pattern="[a-z0-9_-]+" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm font-mono">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Categoría</label>
                <select name="category" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
                    <?php foreach (['' => '— Sin categoría —', 'welcome' => 'Bienvenida', 'status_update' => 'Actualización de estado', 'document_request' => 'Solicitud de documentos', 'reminder' => 'Recordatorio', 'general' => 'General'] as $k => $v): ?>
                    <option value="<?= e($k) ?>" <?= $tpl['category'] === $k ? 'selected' : '' ?>><?= e($v) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Asunto</label>
                <input name="subject" required value="<?= e($tpl['subject']) ?>" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm">
            </div>
            <div>
                <label class="block text-sm font-medium mb-1">Cuerpo HTML</label>
                <textarea name="body_html" required rows="14" class="block w-full rounded-lg border border-slate-200 py-2 px-3 text-sm font-mono"><?= e($tpl['body_html']) ?></textarea>
            </div>
            <label class="flex items-center gap-2 text-sm">
                <input type="checkbox" name="is_active" value="1" <?= !empty($tpl['is_active']) ? 'checked' : '' ?> class="h-4 w-4 rounded">
                Plantilla activa
            </label>
            <div class="flex justify-end gap-2">
                <a href="<?= e(tenant_url('templates')) ?>" class="btn btn-secondary">Cancelar</a>
                <button class="btn btn-primary"><?= $is_edit ? 'Guardar' : 'Crear' ?></button>
            </div>
        </form>

        <aside class="kyros-card p-5">
            <h3 class="text-sm font-semibold mb-3">Variables disponibles</h3>
            <p class="text-xs text-slate-500 mb-3">Usa <code class="bg-slate-100 px-1 rounded">{{variable}}</code> en el asunto o cuerpo.</p>
            <ul class="space-y-1.5 text-xs max-h-96 overflow-y-auto">
                <?php foreach (MERGE_VARS_DOC as $var => $desc): ?>
                <li class="flex flex-col">
                    <code class="text-indigo-600 font-mono">{{<?= e($var) ?>}}</code>
                    <span class="text-slate-500"><?= e($desc) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </aside>
    </div>
</div>
