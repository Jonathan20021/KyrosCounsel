<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold"><?= e($client['first_name'] . ' ' . $client['last_name']) ?></h1>
            <p class="text-sm text-slate-500"><?= e($client['email'] ?? '') ?> · <?= e(country_name($client['nationality'] ?? '')) ?></p>
        </div>
        <div class="flex items-center gap-2">
            <?php if (can('clients.update')): ?>
            <a href="<?= e(tenant_url('clients/' . $client['id'] . '/edit')) ?>" class="rounded-md border border-slate-300 bg-white px-4 py-2 text-sm">Editar</a>
            <?php endif; ?>
            <?php if (can('cases.create')): ?>
            <a href="<?= e(tenant_url('cases/new?client_id=' . $client['id'])) ?>" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white hover:bg-indigo-500">+ Nuevo caso</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="lg:col-span-2 space-y-6">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold mb-4">Casos</h3>
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($cases as $cs): ?>
                    <li class="py-2 flex justify-between">
                        <a href="<?= e(tenant_url('cases/' . $cs['id'])) ?>" class="text-sm">
                            <span class="font-mono text-indigo-600"><?= e($cs['case_number']) ?></span> · <?= e($cs['title']) ?>
                        </a>
                        <span class="text-xs text-slate-500"><?= e(case_status_label($cs['status'])) ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($cases)): ?>
                    <li class="py-2 text-sm text-slate-500">Sin casos.</li>
                    <?php endif; ?>
                </ul>
            </div>

            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold mb-4">Documentos</h3>
                <ul class="divide-y divide-slate-100">
                    <?php foreach ($docs as $d): ?>
                    <li class="py-2 flex justify-between text-sm">
                        <a href="<?= e(tenant_url('documents/' . $d['id'] . '/download')) ?>" class="text-indigo-600 hover:underline"><?= e($d['name']) ?></a>
                        <span class="text-xs text-slate-500"><?= e(round((int)$d['size_bytes']/1024) . ' KB · ' . $d['created_at']) ?></span>
                    </li>
                    <?php endforeach; ?>
                    <?php if (empty($docs)): ?>
                    <li class="py-2 text-sm text-slate-500">Sin documentos.</li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>

        <div class="space-y-4">
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold mb-3">Datos sensibles</h3>
                <dl class="space-y-2 text-sm">
                    <div><dt class="text-slate-500">Pasaporte</dt><dd class="font-mono"><?= e($client['passport'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">A-Number</dt><dd class="font-mono"><?= e($client['alien_number'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">Fecha de nacimiento</dt><dd><?= e($client['date_of_birth'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">Telefono</dt><dd><?= e($client['phone'] ?: '—') ?></dd></div>
                    <div><dt class="text-slate-500">Direccion</dt><dd><?= e($client['address'] ?: '—') ?></dd></div>
                </dl>
            </div>
            <?php if (!empty($client['notes'])): ?>
            <div class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold mb-2">Notas</h3>
                <p class="text-sm text-slate-700 whitespace-pre-wrap"><?= e($client['notes']) ?></p>
            </div>
            <?php endif; ?>

            <!-- Portal del cliente -->
            <div id="portal" class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
                <h3 class="text-base font-semibold mb-1">🔗 Portal del cliente</h3>
                <p class="text-xs text-slate-500 mb-4">Genera un link seguro para que el cliente vea el estado de sus casos.</p>

                <?php if ($portal_token_once): $url = url('portal/' . $portal_token_once); ?>
                <div class="rounded-lg border-2 border-emerald-300 bg-emerald-50 p-3 mb-3">
                    <p class="text-xs font-semibold text-emerald-900">⚠️ Copia este link AHORA — no se mostrara de nuevo:</p>
                    <div class="mt-2 flex gap-2">
                        <input value="<?= e($url) ?>" readonly id="portalUrl" class="flex-1 rounded-md border border-emerald-200 bg-white px-2 py-1 text-xs font-mono">
                        <button type="button" data-copy="portalUrl" class="btn btn-primary text-xs">Copiar</button>
                    </div>
                </div>
                <?php endif; ?>

                <?php if (can('clients.update')): ?>
                <form method="POST" action="<?= e(tenant_url('clients/' . $client['id'] . '/portal')) ?>" class="flex items-center gap-2 mb-4">
                    <?= csrf_field() ?>
                    <select name="days" class="rounded-md border border-slate-300 py-1.5 px-2 text-xs">
                        <option value="7">7 días</option>
                        <option value="30" selected>30 días</option>
                        <option value="90">90 días</option>
                        <option value="180">180 días</option>
                    </select>
                    <button class="btn btn-primary text-xs">Generar link de acceso</button>
                </form>

                <!-- Login propio (email + password) -->
                <div class="mt-4 pt-4 border-t border-slate-200">
                    <h4 class="text-sm font-semibold mb-2">Login con email y contrasena</h4>
                    <p class="text-xs text-slate-500 mb-3">Permite al cliente acceder permanentemente con sus credenciales.</p>
                    <?php if ($portal_pass_once): ?>
                    <div class="rounded-lg border-2 border-emerald-300 bg-emerald-50 p-3 mb-3">
                        <p class="text-xs font-semibold text-emerald-900">⚠️ Comparte estas credenciales AHORA:</p>
                        <div class="mt-2 grid grid-cols-2 gap-2 text-xs">
                            <div>
                                <span class="block text-slate-500">URL</span>
                                <input value="<?= e(url('cliente/login')) ?>" readonly class="w-full rounded-md border border-emerald-200 bg-white px-2 py-1 text-[10px] font-mono">
                            </div>
                            <div>
                                <span class="block text-slate-500">Email</span>
                                <input value="<?= e($client['email']) ?>" readonly class="w-full rounded-md border border-emerald-200 bg-white px-2 py-1 text-[10px] font-mono">
                            </div>
                        </div>
                        <div class="mt-2">
                            <span class="block text-xs text-slate-500">Contrasena temporal</span>
                            <div class="flex gap-2 mt-1">
                                <input value="<?= e($portal_pass_once) ?>" readonly id="portalPass" class="flex-1 rounded-md border border-emerald-200 bg-white px-2 py-1 text-xs font-mono font-bold">
                                <button type="button" data-copy="portalPass" class="btn btn-primary text-xs">Copiar</button>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    <?php if (!$client['email']): ?>
                    <p class="text-xs text-amber-600">⚠️ Cliente debe tener email registrado para habilitar login.</p>
                    <?php else: ?>
                    <form method="POST" action="<?= e(tenant_url('clients/' . $client['id'] . '/portal/enable')) ?>"
                          data-confirm="<?= $client['portal_enabled'] ? 'Esto generara una nueva contrasena temporal. ¿Continuar?' : 'Habilitar login del cliente?' ?>">
                        <?= csrf_field() ?>
                        <button class="btn btn-secondary text-xs">
                            <?= $client['portal_enabled'] ? '🔄 Resetear contrasena' : '🔓 Habilitar login con email' ?>
                        </button>
                    </form>
                    <?php if ($client['portal_enabled']): ?>
                    <p class="mt-2 text-xs text-emerald-600">✓ Login activo</p>
                    <?php if ($client['portal_last_login_at']): ?>
                    <p class="text-xs text-slate-500">Último acceso: <?= e($client['portal_last_login_at']) ?></p>
                    <?php endif; ?>
                    <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <ul class="space-y-2">
                    <?php foreach ($portal_tokens as $pt):
                        $active = !$pt['revoked_at'] && strtotime($pt['expires_at']) > time();
                    ?>
                    <li class="flex items-center justify-between text-xs p-2 rounded-md <?= $active ? 'bg-emerald-50' : 'bg-slate-50' ?>">
                        <div>
                            <span class="font-mono"><?= e(substr($pt['token_hash'], 0, 12)) ?>...</span>
                            <?php if ($active): ?>
                            <span class="badge-soft badge-success">Activo</span>
                            <?php elseif ($pt['revoked_at']): ?>
                            <span class="badge-soft badge-danger">Revocado</span>
                            <?php else: ?>
                            <span class="badge-soft badge-neutral">Expirado</span>
                            <?php endif; ?>
                            <span class="text-slate-500">vence <?= e(date('d M Y', strtotime($pt['expires_at']))) ?></span>
                            <?php if ($pt['last_used_at']): ?>
                            <span class="text-slate-500">· último uso <?= e(date('d M', strtotime($pt['last_used_at']))) ?></span>
                            <?php endif; ?>
                        </div>
                        <?php if ($active && can('clients.update')): ?>
                        <form method="POST" action="<?= e(tenant_url('clients/' . $client['id'] . '/portal/' . $pt['id'] . '/revoke')) ?>" data-confirm="Revocar acceso?">
                            <?= csrf_field() ?>
                            <button class="text-red-600 hover:underline">Revocar</button>
                        </form>
                        <?php endif; ?>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <?php if (can('clients.delete')): ?>
            <form method="POST" action="<?= e(tenant_url('clients/' . $client['id'] . '/delete')) ?>"
                  data-confirm="¿Eliminar este cliente? Esto borrara todos sus casos y datos.">
                <?= csrf_field() ?>
                <button class="text-xs text-red-600 hover:underline">Eliminar cliente</button>
            </form>
            <?php endif; ?>
        </div>
    </div>
</div>
