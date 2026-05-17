<?php /* Dark mode + Alpine store global + event delegation. Cargar en <head> antes de Alpine. */ ?>
<script nonce="<?= e(csp_nonce()) ?>">
(function() {
    // ===== DARK MODE INIT (sin FOUC) =====
    var t = localStorage.getItem('kyros-theme');
    if (!t) t = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    if (t === 'dark') document.documentElement.classList.add('dark');

    // ===== SIDEBAR COLLAPSED INIT (sin FOUC) =====
    // Se aplica antes de pintar para evitar saltos de layout.
    var sc = localStorage.getItem('kyros-sidebar-collapsed');
    if (sc === '1') document.documentElement.classList.add('sidebar-collapsed');
})();

// ===== ALPINE STORES =====
document.addEventListener('alpine:init', () => {
    // Solo un dropdown abierto a la vez
    Alpine.store('menu', {
        active: null,
        toggle(name) { this.active = this.active === name ? null : name; },
        open(name)   { this.active = name; },
        close()      { this.active = null; },
        is(name)     { return this.active === name; }
    });

    // Sidebar global persistente
    Alpine.store('sidebar', {
        collapsed: localStorage.getItem('kyros-sidebar-collapsed') === '1',
        toggle() {
            this.collapsed = !this.collapsed;
            localStorage.setItem('kyros-sidebar-collapsed', this.collapsed ? '1' : '0');
            document.documentElement.classList.toggle('sidebar-collapsed', this.collapsed);
            // Forzar resize para que Chart.js recalcule canvas
            window.dispatchEvent(new Event('resize'));
        }
    });
});

// ===== TOGGLE GLOBAL =====
window.kyrosToggleTheme = function() {
    var d = document.documentElement;
    d.classList.toggle('dark');
    localStorage.setItem('kyros-theme', d.classList.contains('dark') ? 'dark' : 'light');
    if (window.Chart && Chart.instances) {
        Object.values(Chart.instances).forEach(function(c){ try{c.update();}catch(e){} });
    }
};

// ===== EVENT DELEGATION (CSP-safe) =====
// Reemplaza onclick/onsubmit inline (bloqueados por CSP nonce-only).
document.addEventListener('DOMContentLoaded', function() {

    // [data-action="theme-toggle"]
    document.body.addEventListener('click', function(e) {
        var t = e.target.closest('[data-action]');
        if (!t) return;
        var action = t.dataset.action;

        if (action === 'theme-toggle') {
            e.preventDefault();
            window.kyrosToggleTheme();
        }
        else if (action === 'print') {
            e.preventDefault();
            window.print();
        }
        else if (action === 'dismiss-toast') {
            var toast = t.closest('[data-toast]');
            if (toast) toast.remove();
        }
    });

    // [data-copy="elementId"] - copia al clipboard
    document.body.addEventListener('click', function(e) {
        var t = e.target.closest('[data-copy]');
        if (!t) return;
        e.preventDefault();
        var src = document.getElementById(t.dataset.copy);
        if (!src) return;
        src.select();
        try {
            navigator.clipboard.writeText(src.value).then(function() {
                var orig = t.textContent;
                t.textContent = '✓ Copiado';
                setTimeout(function() { t.textContent = orig; }, 1500);
            });
        } catch (err) {
            document.execCommand('copy');
        }
    });

    // Confirmacion en submit: <form data-confirm="¿seguro?">
    document.body.addEventListener('submit', function(e) {
        var msg = e.target.getAttribute && e.target.getAttribute('data-confirm');
        if (msg && !window.confirm(msg)) {
            e.preventDefault();
        }
    });
});
</script>
