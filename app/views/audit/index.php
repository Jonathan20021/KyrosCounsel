<?php
$last_page = (int)ceil($total / $per_page);
$labels = [
    'auth.login.success' => ['Inicio sesion', 'success'],
    'auth.login.failed' => ['Login fallido', 'danger'],
    'auth.logout' => ['Cierre sesion', 'neutral'],
    'client.created' => ['Cliente creado', 'success'],
    'client.updated' => ['Cliente actualizado', 'info'],
    'client.deleted' => ['Cliente eliminado', 'danger'],
    'case.created' => ['Caso creado', 'success'],
    'case.updated' => ['Caso actualizado', 'info'],
    'case.status_changed' => ['Estado cambiado', 'warning'],
    'document.uploaded' => ['Documento subido', 'success'],
    'document.downloaded' => ['Documento descargado', 'info'],
    'document.deleted' => ['Documento eliminado', 'danger'],
    'user.created' => ['Usuario creado', 'success'],
    'user.updated' => ['Usuario actualizado', 'info'],
    'user.password_changed' => ['Contrasena cambiada', 'info'],
    'user.2fa_enabled' => ['2FA activado', 'success'],
    'user.2fa_disabled' => ['2FA desactivado', 'warning'],
    'permission.denied' => ['Permiso denegado', 'danger'],
    'tenant.created' => ['Tenant creado', 'success'],
    'billing.plan_changed' => ['Plan cambiado', 'info'],
];
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Compliance</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Auditoria</h1>
            <p class="mt-1 text-sm text-slate-600">Registro append-only de eventos sensibles del bufete</p>
        </div>
        <a href="<?= e(tenant_url('export/audit')) ?>" class="btn btn-secondary" title="Descargar CSV">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Exportar CSV
        </a>
    </div>

    <div class="flex flex-wrap items-center gap-2">
        <a href="<?= e(tenant_url('audit')) ?>" class="badge-soft <?= $event === '' ? 'badge-primary' : 'badge-neutral' ?>">Todos</a>
        <?php foreach ($events as $ev):
            $lbl = $labels[$ev['event']][0] ?? $ev['event'];
        ?>
        <a href="<?= e(tenant_url('audit?event=' . urlencode($ev['event']))) ?>"
           class="badge-soft <?= $event === $ev['event'] ? 'badge-primary' : 'badge-neutral' ?>"><?= e($lbl) ?> · <?= (int)$ev['c'] ?></a>
        <?php endforeach; ?>
    </div>

    <div class="kyros-card overflow-hidden">
        <table class="min-w-full divide-y divide-slate-100 kyros-table">
            <thead class="bg-slate-50/50">
                <tr>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Cuando</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Actor</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Evento</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">IP</th>
                    <th class="px-4 py-3 text-left font-semibold uppercase tracking-wider text-slate-500">Detalle</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-100">
                <?php foreach ($rows as $r):
                    [$lbl, $color] = $labels[$r['event']] ?? [$r['event'], 'neutral'];
                ?>
                <tr>
                    <td class="px-4 py-3 text-sm font-mono text-xs text-slate-600 whitespace-nowrap"><?= e($r['created_at']) ?></td>
                    <td class="px-4 py-3 text-sm"><?= e($r['actor_name'] ?? 'Sistema') ?></td>
                    <td class="px-4 py-3 text-sm"><span class="badge-soft badge-<?= e($color) ?>"><?= e($lbl) ?></span></td>
                    <td class="px-4 py-3 text-sm font-mono text-xs text-slate-500"><?= e($r['ip'] ?? '—') ?></td>
                    <td class="px-4 py-3 text-sm text-slate-600 max-w-md truncate font-mono text-xs"><?= e($r['context_json'] ?? '') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($rows)): ?>
                <tr><td colspan="5" class="empty-state"><div class="empty-state-icon">📋</div><p class="text-sm text-slate-500">Sin eventos registrados.</p></td></tr>
                <?php endif; ?>
            </tbody>
        </table>
        <?php if ($last_page > 1): ?>
        <div class="p-4 border-t border-slate-100 flex items-center justify-between">
            <span class="text-xs text-slate-500"><?= e(($page - 1) * $per_page + 1) ?>-<?= e(min($page * $per_page, $total)) ?> de <?= (int)$total ?></span>
            <div class="flex items-center gap-2">
                <?php if ($page > 1): ?>
                <a href="?page=<?= e($page - 1) ?><?= $event ? '&event=' . urlencode($event) : '' ?>" class="btn btn-secondary text-xs">←</a>
                <?php endif; ?>
                <span class="text-xs text-slate-500">Pagina <?= (int)$page ?> de <?= (int)$last_page ?></span>
                <?php if ($page < $last_page): ?>
                <a href="?page=<?= e($page + 1) ?><?= $event ? '&event=' . urlencode($event) : '' ?>" class="btn btn-secondary text-xs">→</a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
