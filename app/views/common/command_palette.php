<?php /* Command palette Cmd+K con search LIVE en clientes/casos/notas/tareas */ ?>
<div id="kyros-cmdk" class="hidden fixed inset-0 z-[100]">
    <div class="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" data-cmdk-close></div>
    <div class="relative mx-auto mt-20 max-w-2xl px-4">
        <div class="kyros-dropdown overflow-hidden">
            <div class="flex items-center px-4" style="border-bottom: 1px solid hsl(var(--border));">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-slate-400 mr-2"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                <input type="text" id="kyros-cmdk-input" placeholder="Buscar clientes, casos, notas, tareas... o escribir un comando" autocomplete="off"
                       class="flex-1 py-3 bg-transparent border-0 outline-none text-sm" style="color: hsl(var(--fg));">
                <span id="kyros-cmdk-loading" class="hidden text-xs text-slate-400 mr-2">⏳</span>
                <kbd class="ml-2 px-1.5 py-0.5 text-[10px] font-mono rounded border border-slate-200 text-slate-500" style="border-color: hsl(var(--border));">ESC</kbd>
            </div>
            <ul id="kyros-cmdk-results" class="max-h-[400px] overflow-y-auto p-2 text-sm"></ul>
            <div class="px-4 py-2 text-[11px] text-slate-500 flex items-center gap-3" style="border-top: 1px solid hsl(var(--border)); background: hsl(var(--surface-2));">
                <span><kbd class="px-1 py-0.5 rounded border" style="border-color: hsl(var(--border));">↑↓</kbd> navegar</span>
                <span><kbd class="px-1 py-0.5 rounded border" style="border-color: hsl(var(--border));">⏎</kbd> abrir</span>
                <span class="ml-auto"><kbd class="px-1 py-0.5 rounded border" style="border-color: hsl(var(--border));">Ctrl+K</kbd></span>
            </div>
        </div>
    </div>
</div>

<script nonce="<?= e(csp_nonce()) ?>">
(function() {
    const tBase = '<?= e(tenant_url('')) ?>';
    const searchUrl = '<?= e(tenant_url('search')) ?>';

    const COMMANDS = [
        { icon: '🏠', label: 'Dashboard', kw: 'inicio home', url: tBase + 'dashboard' },
        { icon: '👥', label: 'Clientes', kw: 'clients lista', url: tBase + 'clients' },
        { icon: '➕', label: 'Nuevo cliente', kw: 'new client crear', url: tBase + 'clients/new' },
        { icon: '📂', label: 'Casos', kw: 'cases lista', url: tBase + 'cases' },
        { icon: '➕', label: 'Nuevo caso', kw: 'new case crear', url: tBase + 'cases/new' },
        { icon: '📋', label: 'Tablero Kanban (casos)', kw: 'board kanban', url: tBase + 'cases/board' },
        { icon: '📁', label: 'Documentos', kw: 'docs files', url: tBase + 'documents' },
        { icon: '✓', label: 'Tareas', kw: 'tasks todo', url: tBase + 'tasks' },
        { icon: '📅', label: 'Calendario mensual', kw: 'calendar', url: tBase + 'calendar' },
        { icon: '🗓️', label: 'Calendario semanal', kw: 'week calendar', url: tBase + 'calendar/week' },
        { icon: '📊', label: 'Reportes', kw: 'reports analytics', url: tBase + 'reports' },
        { icon: '💼', label: 'Finanzas', kw: 'finance ar money', url: tBase + 'finance' },
        { icon: '📧', label: 'Plantillas email', kw: 'templates email', url: tBase + 'templates' },
        { icon: '⚙️', label: 'Workflows', kw: 'automations', url: tBase + 'workflows' },
        { icon: '🛡️', label: 'Auditoria', kw: 'audit log', url: tBase + 'audit' },
        { icon: '👤', label: 'Usuarios', kw: 'users team', url: tBase + 'users' },
        { icon: '💳', label: 'Facturacion', kw: 'billing plan', url: tBase + 'billing' },
        { icon: '🔐', label: 'Mi perfil', kw: 'profile 2fa', url: tBase + 'profile' },
        { icon: '🌙', label: 'Cambiar tema (claro/oscuro)', kw: 'theme dark light', action: 'theme-toggle' },
    ];

    const root = document.getElementById('kyros-cmdk');
    const input = document.getElementById('kyros-cmdk-input');
    const list = document.getElementById('kyros-cmdk-results');
    const loading = document.getElementById('kyros-cmdk-loading');
    let activeIndex = 0;
    let visible = COMMANDS.slice();
    let searchAbort = null;
    let searchTimer = null;

    function open() { root.classList.remove('hidden'); input.value = ''; render([{ section: 'Comandos' }, ...COMMANDS.map(c => ({...c, kind: 'cmd'}))]); setTimeout(() => input.focus(), 30); }
    function close() { root.classList.add('hidden'); }

    function render(items) {
        // Filtra entradas tipo seccion de visible (no son seleccionables)
        visible = items.filter(i => !i.section);
        activeIndex = 0;
        if (items.length === 0) {
            list.innerHTML = '<li class="px-3 py-8 text-center text-sm" style="color: hsl(var(--fg-muted));">Sin resultados</li>';
            return;
        }
        let html = '';
        let idx = 0;
        items.forEach(c => {
            if (c.section) {
                html += `<li class="px-3 pt-3 pb-1 text-[10px] uppercase tracking-wider font-semibold" style="color: hsl(var(--fg-muted));">${c.section}</li>`;
                return;
            }
            html += `
                <li>
                    <button type="button" data-idx="${idx}" class="kyros-cmdk-item w-full flex items-center gap-3 px-3 py-2 rounded-lg text-left ${idx === 0 ? 'bg-indigo-50 text-indigo-900' : 'hover:bg-slate-50'}">
                        <span class="text-lg flex-shrink-0">${c.icon || '·'}</span>
                        <div class="flex-1 min-w-0">
                            <div class="font-medium truncate">${escapeHtml(c.title || c.label)}</div>
                            ${c.subtitle ? `<div class="text-xs truncate" style="color: hsl(var(--fg-muted));">${escapeHtml(c.subtitle)}</div>` : ''}
                        </div>
                        ${c.kind && c.kind !== 'cmd' ? `<span class="text-[10px] px-1.5 py-0.5 rounded-full bg-slate-100" style="color: hsl(var(--fg-muted));">${c.kind}</span>` : ''}
                    </button>
                </li>`;
            idx++;
        });
        list.innerHTML = html;
        Array.from(list.querySelectorAll('.kyros-cmdk-item')).forEach(b => {
            b.addEventListener('click', () => execute(visible[parseInt(b.dataset.idx)]));
            b.addEventListener('mouseenter', () => setActive(parseInt(b.dataset.idx)));
        });
    }

    function setActive(i) {
        activeIndex = Math.max(0, Math.min(visible.length - 1, i));
        Array.from(list.querySelectorAll('.kyros-cmdk-item')).forEach((b, idx) => {
            const isActive = idx === activeIndex;
            b.classList.toggle('bg-indigo-50', isActive);
            b.classList.toggle('text-indigo-900', isActive);
        });
        const el = list.querySelectorAll('.kyros-cmdk-item')[activeIndex];
        if (el) el.scrollIntoView({ block: 'nearest' });
    }

    function execute(cmd) {
        if (!cmd) return;
        close();
        if (cmd.action === 'theme-toggle' && window.kyrosToggleTheme) { window.kyrosToggleTheme(); return; }
        if (cmd.url) window.location.href = cmd.url;
    }

    function filterCommands(q) {
        q = q.toLowerCase().trim();
        if (!q) return COMMANDS.slice();
        return COMMANDS.filter(c =>
            c.label.toLowerCase().includes(q) || (c.kw && c.kw.toLowerCase().includes(q))
        );
    }

    async function liveSearch(q) {
        if (searchAbort) searchAbort.abort();
        searchAbort = new AbortController();
        loading.classList.remove('hidden');
        try {
            const r = await fetch(searchUrl + '?q=' + encodeURIComponent(q), {
                signal: searchAbort.signal, credentials: 'same-origin'
            });
            const j = await r.json();
            const cmds = filterCommands(q).map(c => ({...c, kind: 'cmd'}));
            const results = (j.results || []);
            const items = [];
            if (cmds.length) { items.push({ section: 'Comandos' }); cmds.forEach(c => items.push(c)); }
            if (results.length) { items.push({ section: 'Resultados' }); results.forEach(r => items.push(r)); }
            render(items);
        } catch (e) {
            if (e.name !== 'AbortError') console.error(e);
        } finally {
            loading.classList.add('hidden');
        }
    }

    function escapeHtml(s) {
        return String(s).replace(/[&<>"']/g, c => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'})[c]);
    }

    document.addEventListener('keydown', (e) => {
        if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
            e.preventDefault();
            root.classList.contains('hidden') ? open() : close();
            return;
        }
        if (root.classList.contains('hidden')) return;
        if (e.key === 'Escape') { e.preventDefault(); close(); }
        else if (e.key === 'ArrowDown') { e.preventDefault(); setActive(activeIndex + 1); }
        else if (e.key === 'ArrowUp')   { e.preventDefault(); setActive(activeIndex - 1); }
        else if (e.key === 'Enter')     { e.preventDefault(); execute(visible[activeIndex]); }
    });

    input.addEventListener('input', (e) => {
        const q = e.target.value.trim();
        clearTimeout(searchTimer);
        if (q.length < 2) {
            const cmds = filterCommands(q).map(c => ({...c, kind: 'cmd'}));
            render([{ section: 'Comandos' }, ...cmds]);
            return;
        }
        searchTimer = setTimeout(() => liveSearch(q), 200);
    });

    document.body.addEventListener('click', (e) => {
        const t = e.target.closest('[data-action]');
        if (t && t.dataset.action === 'open-cmdk') { e.preventDefault(); open(); }
        if (e.target.closest('[data-cmdk-close]')) close();
    });
})();
</script>
