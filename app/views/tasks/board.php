<?php
$cols = [
    'pending'     => ['Pendientes',    '#94a3b8'],
    'in_progress' => ['En progreso',   '#6366f1'],
    'done'        => ['Hechas',        '#10b981'],
    'cancelled'   => ['Canceladas',    '#ef4444'],
];
?>
<div class="space-y-6">
    <div class="flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-xs uppercase tracking-wider text-slate-500 font-medium">Vista Kanban</p>
            <h1 class="mt-1 text-3xl font-bold tracking-tight">Tablero de tareas</h1>
            <p class="mt-1 text-sm text-slate-600">Arrastra tareas para cambiar su estado</p>
        </div>
        <a href="<?= e(tenant_url('tasks')) ?>" class="btn btn-secondary">Vista lista</a>
    </div>

    <div class="grid gap-4" style="grid-template-columns: repeat(4, minmax(240px, 1fr));" id="tasks-kanban">
        <?php foreach ($cols as $code => [$label, $color]):
            $items = $by_status[$code] ?? [];
        ?>
        <div class="kanban-col" data-status="<?= e($code) ?>">
            <div class="kanban-col-header">
                <div class="flex items-center gap-2">
                    <span class="inline-block h-2 w-2 rounded-full" style="background: <?= $color ?>"></span>
                    <span class="kanban-col-title"><?= e($label) ?></span>
                </div>
                <span class="kanban-col-count" data-count><?= count($items) ?></span>
            </div>
            <div class="kanban-cards" data-dropzone="<?= e($code) ?>">
                <?php foreach ($items as $t):
                    $overdue = $t['due_date'] && $t['due_date'] < date('Y-m-d') && $t['status'] === 'pending';
                ?>
                <div class="kanban-card" draggable="<?= can('tasks.update') ? 'true' : 'false' ?>" data-task-id="<?= (int)$t['id'] ?>">
                    <div class="kanban-priority-bar kanban-priority-<?= e($t['priority']) ?>"></div>
                    <div class="text-sm font-medium leading-snug line-clamp-2"><?= e($t['title']) ?></div>
                    <div class="mt-2 flex items-center justify-between text-xs text-slate-500">
                        <?php if ($t['case_number']): ?>
                        <span class="font-mono text-indigo-600"><?= e($t['case_number']) ?></span>
                        <?php else: ?>
                        <span></span>
                        <?php endif; ?>
                        <?php if ($t['due_date']): ?>
                        <span class="<?= $overdue ? 'text-red-600 font-semibold' : '' ?>"><?= e(date('d M', strtotime($t['due_date']))) ?></span>
                        <?php endif; ?>
                    </div>
                    <?php if ($t['assignee_name']): ?>
                    <div class="mt-1.5 text-xs text-slate-500"><?= e($t['assignee_name']) ?></div>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
                <?php if (empty($items)): ?>
                <div class="text-center text-xs text-slate-400 py-6 italic">Sin tareas</div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<style nonce="<?= e(csp_nonce()) ?>">
.line-clamp-2 { display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
</style>

<script nonce="<?= e(csp_nonce()) ?>">
(function() {
    const csrfToken = '<?= e(csrf_token()) ?>';
    const moveUrl = (id) => '<?= e(tenant_url('tasks')) ?>/' + id + '/move';
    const board = document.getElementById('tasks-kanban');
    let dragged = null;

    board.querySelectorAll('.kanban-card').forEach(card => {
        if (card.getAttribute('draggable') !== 'true') return;
        card.addEventListener('dragstart', (e) => {
            dragged = card; card.classList.add('dragging');
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
            if (!dragged) return; e.preventDefault();
            col.classList.add('drop-target');
            e.dataTransfer.dropEffect = 'move';
        });
        col.addEventListener('dragleave', (e) => { if (e.target === col) col.classList.remove('drop-target'); });
        col.addEventListener('drop', async (e) => {
            e.preventDefault(); col.classList.remove('drop-target');
            if (!dragged) return;
            const newStatus = col.dataset.status;
            const oldCol = dragged.parentElement.parentElement;
            if (oldCol.dataset.status === newStatus) return;
            const taskId = dragged.dataset.taskId;
            dropzone.appendChild(dragged);
            updateCount(oldCol); updateCount(col);
            const empty = dropzone.querySelector('.italic'); if (empty) empty.remove();
            try {
                const fd = new FormData();
                fd.append('_csrf', csrfToken);
                fd.append('status', newStatus);
                const r = await fetch(moveUrl(taskId), { method: 'POST', body: fd, credentials: 'same-origin' });
                const j = await r.json();
                if (!j.ok) throw new Error('Error');
                showToast('Tarea movida a "' + col.querySelector('.kanban-col-title').textContent + '"', 'success');
            } catch (err) {
                oldCol.querySelector('[data-dropzone]').appendChild(dragged);
                updateCount(oldCol); updateCount(col);
                showToast('Error al mover la tarea', 'error');
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
            e.textContent = 'Sin tareas'; dz.appendChild(e);
        }
    }
    function showToast(msg, type) {
        const t = document.createElement('div');
        t.className = 'fixed top-4 right-4 z-50 max-w-sm rounded-xl border px-4 py-3 text-sm shadow-lg fade-in ' +
            (type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800');
        t.textContent = msg; document.body.appendChild(t);
        setTimeout(() => t.remove(), 3000);
    }
})();
</script>
