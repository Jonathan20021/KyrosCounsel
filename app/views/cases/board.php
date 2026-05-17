<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Vista Kanban</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Tablero de casos</h1>
            <p class="mt-1 text-sm text-slate-600">Arrastra los casos entre columnas para cambiar su estado</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="<?= e(tenant_url('cases')) ?>" class="btn btn-secondary">Vista lista</a>
            <?php if (can('cases.create')): ?>
            <a href="<?= e(tenant_url('cases/new')) ?>" class="btn btn-primary">+ Nuevo caso</a>
            <?php endif; ?>
        </div>
    </div>

    <div class="kanban-board" id="kanban">
        <?php foreach (CASE_STATUSES as $code => $label):
            $cases = $by_status[$code] ?? [];
            $statusColors = [
                'intake' => '#94a3b8', 'preparing' => '#6366f1', 'filed' => '#0ea5e9',
                'rfe' => '#f59e0b', 'approved' => '#10b981', 'denied' => '#ef4444',
                'withdrawn' => '#64748b', 'closed' => '#475569',
            ];
        ?>
        <div class="kanban-col" data-status="<?= e($code) ?>">
            <div class="kanban-col-header">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full" style="background: <?= $statusColors[$code] ?? '#94a3b8' ?>"></span>
                    <span class="kanban-col-title"><?= e($label) ?></span>
                </div>
                <span class="kanban-col-count" data-count><?= count($cases) ?></span>
            </div>
            <div class="kanban-cards" data-dropzone="<?= e($code) ?>">
                <?php foreach ($cases as $c): ?>
                <div class="kanban-card" draggable="<?= can('cases.update') ? 'true' : 'false' ?>" data-case-id="<?= (int)$c['id'] ?>">
                    <div class="kanban-priority-bar kanban-priority-<?= e($c['priority']) ?>"></div>
                    <a href="<?= e(tenant_url('cases/' . $c['id'])) ?>" class="block">
                        <div class="font-mono text-[10px] text-indigo-600 font-medium"><?= e($c['case_number']) ?></div>
                        <div class="mt-1 text-sm font-medium leading-snug line-clamp-2"><?= e($c['title']) ?></div>
                        <div class="mt-2 text-xs text-slate-500">
                            <?= e($c['first_name'] . ' ' . $c['last_name']) ?>
                        </div>
                        <div class="mt-2 flex items-center justify-between">
                            <span class="badge-soft badge-neutral text-[10px] font-mono"><?= e($c['case_type']) ?></span>
                            <?php if ($c['attorney_name']): ?>
                            <span class="text-[10px] text-slate-500" title="<?= e($c['attorney_name']) ?>"><?= e(strtoupper(mb_substr($c['attorney_name'], 0, 2))) ?></span>
                            <?php endif; ?>
                        </div>
                    </a>
                </div>
                <?php endforeach; ?>
                <?php if (empty($cases)): ?>
                <div class="text-center text-xs text-slate-400 py-6 italic">Sin casos</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="text-xs text-slate-500 flex items-center gap-3 flex-wrap">
        <span>Prioridad:</span>
        <span class="flex items-center gap-1"><span class="inline-block h-1 w-6 rounded-full kanban-priority-urgent"></span> Urgente</span>
        <span class="flex items-center gap-1"><span class="inline-block h-1 w-6 rounded-full kanban-priority-high"></span> Alta</span>
        <span class="flex items-center gap-1"><span class="inline-block h-1 w-6 rounded-full kanban-priority-normal"></span> Normal</span>
        <span class="flex items-center gap-1"><span class="inline-block h-1 w-6 rounded-full kanban-priority-low"></span> Baja</span>
    </div>
</div>

<style nonce="<?= e(csp_nonce()) ?>">
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<script nonce="<?= e(csp_nonce()) ?>">
(function() {
    const csrfToken = '<?= e(csrf_token()) ?>';
    const moveUrl = (id) => '<?= e(tenant_url('cases')) ?>/' + id + '/move';
    const board = document.getElementById('kanban');
    let dragged = null;

    board.querySelectorAll('.kanban-card').forEach(card => {
        if (card.getAttribute('draggable') !== 'true') return;
        card.addEventListener('dragstart', (e) => {
            dragged = card;
            card.classList.add('dragging');
            e.dataTransfer.effectAllowed = 'move';
        });
        card.addEventListener('dragend', () => {
            card.classList.remove('dragging');
            board.querySelectorAll('.kanban-col').forEach(c => c.classList.remove('drop-target'));
        });
    });

    board.querySelectorAll('.kanban-col').forEach(col => {
        const dropzone = col.querySelector('[data-dropzone]');
        col.addEventListener('dragover', (e) => {
            if (!dragged) return;
            e.preventDefault();
            col.classList.add('drop-target');
            e.dataTransfer.dropEffect = 'move';
        });
        col.addEventListener('dragleave', (e) => {
            if (e.target === col) col.classList.remove('drop-target');
        });
        col.addEventListener('drop', async (e) => {
            e.preventDefault();
            col.classList.remove('drop-target');
            if (!dragged) return;

            const newStatus = col.dataset.status;
            const oldCol = dragged.parentElement.parentElement;
            const oldStatus = oldCol.dataset.status;
            if (oldStatus === newStatus) return;

            const caseId = dragged.dataset.caseId;
            // Optimistic UI
            const placeholder = dragged.cloneNode(true);
            dropzone.appendChild(dragged);
            // Update counts
            updateCount(oldCol);
            updateCount(col);
            // Remove "Sin casos" si existe
            const empty = dropzone.querySelector('.italic');
            if (empty) empty.remove();

            try {
                const fd = new FormData();
                fd.append('_csrf', csrfToken);
                fd.append('status', newStatus);
                const r = await fetch(moveUrl(caseId), { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                if (!j.ok) throw new Error(j.error || 'Error');
                // Toast
                showToast('Caso movido a ' + col.querySelector('.kanban-col-title').textContent, 'success');
            } catch (err) {
                // Revert
                oldCol.querySelector('[data-dropzone]').appendChild(dragged);
                updateCount(oldCol);
                updateCount(col);
                showToast('Error al mover el caso', 'error');
            }
        });
    });

    function updateCount(col) {
        const cards = col.querySelectorAll('.kanban-card').length;
        col.querySelector('[data-count]').textContent = cards;
        const dz = col.querySelector('[data-dropzone]');
        if (cards === 0 && !dz.querySelector('.italic')) {
            const e = document.createElement('div');
            e.className = 'text-center text-xs text-slate-400 py-6 italic';
            e.textContent = 'Sin casos';
            dz.appendChild(e);
        }
    }

    function showToast(msg, type) {
        const t = document.createElement('div');
        t.className = 'fixed top-4 right-4 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg fade-in ' +
            (type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800');
        t.textContent = msg;
        document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
})();
</script>
