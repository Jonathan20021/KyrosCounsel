<?php
/**
 * Renderiza un botón de ayuda (?) que abre un popover con info del módulo
 * y un link directo a la lección de la academia.
 *
 * Variables esperadas (set por quien lo incluye):
 *   $help_title    string  — título del módulo
 *   $help_body     string  — descripción 1-2 frases
 *   $help_lesson   int     — índice de la lección (0-15) en la academia
 *   $help_tips     array   — opcional, lista de tips cortos
 */
$help_id = 'help-' . substr(md5(($help_title ?? '') . microtime(true)), 0, 8);
?>
<div class="relative inline-block" x-data="{ open: false }">
    <button @click="open = !open" type="button"
            class="inline-flex items-center justify-center h-7 w-7 rounded-full border border-slate-200 bg-white hover:bg-indigo-50 hover:border-indigo-300 text-slate-500 hover:text-indigo-600 transition"
            :class="open ? 'bg-indigo-50 border-indigo-300 text-indigo-600' : ''"
            title="¿Cómo se usa este módulo?">
        <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    </button>
    <div x-show="open" x-cloak @click.outside="open = false"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 -translate-y-1"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="absolute right-0 top-full mt-2 z-30 w-80 rounded-xl bg-white border border-slate-200 shadow-2xl overflow-hidden">
        <div class="p-4 border-b border-slate-100 bg-gradient-to-br from-indigo-50/60 to-violet-50/60">
            <div class="text-[10px] uppercase tracking-wider font-bold text-indigo-700">Ayuda rápida</div>
            <h4 class="font-bold mt-0.5 text-sm"><?= e($help_title ?? '') ?></h4>
        </div>
        <div class="p-4">
            <p class="text-xs text-slate-700 leading-relaxed"><?= e($help_body ?? '') ?></p>
            <?php if (!empty($help_tips)): ?>
            <ul class="mt-3 space-y-1.5">
                <?php foreach ($help_tips as $tip): ?>
                <li class="flex gap-2 text-[11px] text-slate-600">
                    <span class="text-indigo-500 font-bold flex-shrink-0">→</span>
                    <span><?= e($tip) ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
        </div>
        <?php if (isset($help_lesson)): ?>
        <a href="<?= e(url('training.html')) ?>?lesson=<?= (int)$help_lesson ?>" target="_blank"
           class="block px-4 py-2.5 border-t border-slate-100 bg-slate-50 hover:bg-slate-100 text-xs font-semibold text-indigo-600 transition">
            <span class="flex items-center justify-between">
                <span>Lección <?= (int)$help_lesson + 1 ?> en la Academia</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="7" y1="17" x2="17" y2="7"/><polyline points="7 7 17 7 17 17"/></svg>
            </span>
        </a>
        <?php endif; ?>
    </div>
</div>
<?php
// Limpiar variables después de usar
unset($help_title, $help_body, $help_lesson, $help_tips);
?>
