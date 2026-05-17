<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Vault</p>
            <div class="flex items-center gap-2 mt-1">
                <h1 class="text-3xl font-bold tracking-tight">Documentos</h1>
                <?php
                $help_title = 'Documentos cifrados';
                $help_body  = 'Pasaportes, I-94, actas, fotos. Todo se cifra en disco. Al descargar, el sistema verifica SHA-256.';
                $help_tips  = [
                    'Categoriza tus archivos para verlos agrupados',
                    'Tipos: PDF, JPG, PNG, DOCX (máx 50MB)',
                    'Vinculados a un caso aparecen en su detalle',
                ];
                $help_lesson = 5;
                require VIEWS_PATH . '/common/module_help.php';
                ?>
            </div>
            <p class="mt-1 text-sm text-slate-600">Archivos cifrados con AEAD · Verificacion SHA-256</p>
        </div>
    </div>

    <?php if (can('documents.create')): ?>
    <form method="POST" action="<?= e(tenant_url('documents/upload')) ?>" enctype="multipart/form-data"
          class="kyros-card p-5 flex flex-wrap items-end gap-3">
        <?= csrf_field() ?>
        <div class="flex items-center gap-3 flex-1 min-w-[260px]">
            <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center">
                <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="17 8 12 3 7 8"/><line x1="12" y1="3" x2="12" y2="15"/></svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold">Subir documento</h3>
                <p class="text-xs text-slate-500">Max 50MB · cifrado al guardar</p>
            </div>
        </div>
        <div>
            <label class="block text-xs font-medium text-slate-500 mb-1">Categoria</label>
            <select name="category" class="rounded-lg border border-slate-200 py-2 px-3 text-sm">
                <?php foreach (DOCUMENT_CATEGORIES as $k => $v): ?>
                <option value="<?= e($k) ?>"><?= e($v) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="flex-1 min-w-[200px]">
            <label class="block text-xs font-medium text-slate-500 mb-1">Archivo</label>
            <input type="file" name="file" required class="block w-full text-sm file:mr-3 file:py-2 file:px-3 file:rounded-lg file:border-0 file:bg-slate-100 file:text-slate-700 hover:file:bg-slate-200">
        </div>
        <button class="btn btn-primary">Subir y cifrar</button>
    </form>
    <?php endif; ?>

    <div class="kyros-card overflow-hidden">
        <?php if (empty($docs)): ?>
        <div class="empty-state">
            <div class="empty-state-icon">📁</div>
            <h3 class="text-lg font-semibold">Aun no hay documentos</h3>
            <p class="mt-1 text-sm text-slate-500">Sube el primero arriba.</p>
        </div>
        <?php else: ?>
        <table class="min-w-full divide-y divide-slate-100 kyros-table">
            <thead class="bg-slate-50/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Archivo</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Categoria</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Caso/Cliente</th>
                    <th class="px-4 py-3 text-right font-semibold uppercase tracking-wider text-slate-500">Tamano</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Subido</th>
                    <th></th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($docs as $d):
                    $ext = strtolower(pathinfo($d['name'], PATHINFO_EXTENSION));
                    $iconBg = ['pdf' => 'bg-red-100 text-red-600', 'doc' => 'bg-blue-100 text-blue-600', 'docx' => 'bg-blue-100 text-blue-600',
                               'jpg' => 'bg-amber-100 text-amber-600', 'jpeg' => 'bg-amber-100 text-amber-600', 'png' => 'bg-amber-100 text-amber-600'][$ext] ?? 'bg-slate-100 text-slate-600';
                ?>
                <tr>
                    <td class="px-4 py-3 text-sm">
                        <div class="flex items-center gap-2.5">
                            <div class="h-9 w-9 rounded-lg <?= $iconBg ?> flex items-center justify-center font-mono text-[10px] font-bold uppercase"><?= e($ext ?: '?') ?></div>
                            <a href="<?= e(tenant_url('documents/' . $d['id'] . '/download')) ?>" class="font-medium text-indigo-600 hover:underline truncate max-w-[300px]"><?= e($d['name']) ?></a>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm"><span class="badge-soft badge-neutral"><?= e(DOCUMENT_CATEGORIES[$d['category']] ?? '—') ?></span></td>
                    <td class="px-4 py-3 text-sm">
                        <?php if ($d['case_number']): ?><span class="font-mono text-xs text-indigo-600"><?= e($d['case_number']) ?></span><?php endif; ?>
                        <?php if ($d['first_name']): ?><div class="text-xs text-slate-500"><?= e($d['last_name'] . ', ' . $d['first_name']) ?></div><?php endif; ?>
                        <?php if (!$d['case_number'] && !$d['first_name']): ?>—<?php endif; ?>
                    </td>
                    <td class="px-4 py-3 text-sm text-right text-slate-500 font-mono text-xs"><?= e(round((int)$d['size_bytes']/1024) . ' KB') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-500 whitespace-nowrap"><?= e(date('d M Y H:i', strtotime($d['created_at']))) ?></td>
                    <td class="px-4 py-3 text-right">
                        <?php if (can('documents.delete')): ?>
                        <form method="POST" action="<?= e(tenant_url('documents/' . $d['id'] . '/delete')) ?>" data-confirm="¿Eliminar documento?">
                            <?= csrf_field() ?>
                            <button class="text-xs text-red-600 hover:underline">Eliminar</button>
                        </form>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
