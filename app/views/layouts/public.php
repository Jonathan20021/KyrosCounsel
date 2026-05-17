<!DOCTYPE html>
<html lang="es" class="bg-white scroll-smooth">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Software de última generación para abogados de inmigración. Multi-tenant, cifrado AEAD, multi-país. Diseñado para bufetes en EE.UU., RD y más.">
    <meta property="og:title" content="<?= e(APP_NAME) ?> · Software para abogados de inmigración">
    <meta property="og:description" content="Multi-tenant blindado · Cifrado AEAD · Workflows automáticos · Multi-país.">
    <meta property="og:type" content="website">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap">
    <title><?= e($title ?? APP_NAME) ?> · Software para abogados de inmigración</title>
    <?php require VIEWS_PATH . '/common/theme_init.php'; ?>
    <script nonce="<?= e(csp_nonce()) ?>">
      window.tailwind = window.tailwind || {};
      window.tailwind.config = {
        darkMode: 'class',
        theme: {
          extend: {
            fontFamily: { sans: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] },
            colors: {
              brand: { 50:'#eef2ff',100:'#e0e7ff',200:'#c7d2fe',300:'#a5b4fc',400:'#818cf8',500:'#6366f1',600:'#4f46e5',700:'#4338ca',800:'#3730a3',900:'#312e81',950:'#1e1b4b' }
            },
            keyframes: {
              shine: { '0%':{backgroundPosition:'200% 0'}, '100%':{backgroundPosition:'-200% 0'} },
              float: { '0%,100%':{transform:'translateY(0)'}, '50%':{transform:'translateY(-12px)'} }
            },
            animation: {
              shine: 'shine 6s linear infinite',
              float: 'float 6s ease-in-out infinite'
            }
          }
        }
      };
    </script>
    <script src="https://cdn.tailwindcss.com" nonce="<?= e(csp_nonce()) ?>"></script>
    <link rel="stylesheet" href="<?= e(url('assets/app.css?v=' . filemtime(BASE_PATH . '/assets/app.css'))) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/landing.css?v=' . filemtime(BASE_PATH . '/assets/landing.css'))) ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
    <script defer src="https://unpkg.com/gsap@3.12.5/dist/gsap.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
    <script defer src="https://unpkg.com/gsap@3.12.5/dist/ScrollTrigger.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
</head>
<body class="text-slate-900 antialiased font-sans bg-white selection:bg-indigo-200 selection:text-indigo-900">
<?php $err = flash_get('error'); $ok = flash_get('success'); ?>
<?php if ($err): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg"><?= e($err) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg"><?= e($ok) ?></div>
<?php endif; ?>

<!-- ===================== NAVBAR ===================== -->
<header id="kn-nav" class="fixed top-0 inset-x-0 z-50 transition-all duration-300" x-data="{ open: false }">
    <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
        <div class="kn-nav-inner mt-3 flex h-14 items-center justify-between rounded-2xl border border-slate-200/60 bg-white/70 px-4 backdrop-blur-xl shadow-[0_1px_2px_rgba(0,0,0,0.04),0_8px_24px_-8px_rgba(15,23,42,0.08)]">
            <a href="<?= e(url('/')) ?>" class="flex items-center gap-2.5 group">
                <div class="relative flex h-8 w-8 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white text-sm font-bold shadow-lg shadow-indigo-500/30 transition group-hover:scale-105">
                    K
                    <span class="absolute -inset-1 rounded-xl bg-gradient-to-br from-indigo-500 to-violet-600 opacity-0 group-hover:opacity-30 blur transition"></span>
                </div>
                <span class="text-base font-bold tracking-tight"><?= e(APP_NAME) ?></span>
            </a>
            <nav class="hidden md:flex items-center gap-1 text-sm font-medium text-slate-600">
                <a href="#features" class="px-3 py-1.5 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition">Producto</a>
                <a href="#workflow" class="px-3 py-1.5 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition">Cómo funciona</a>
                <a href="<?= e(url('pricing')) ?>" class="px-3 py-1.5 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition">Precios</a>
                <a href="#faq" class="px-3 py-1.5 rounded-lg hover:text-slate-900 hover:bg-slate-100/80 transition">FAQ</a>
            </nav>
            <div class="flex items-center gap-2">
                <a href="<?= e(url('login')) ?>" class="hidden sm:inline-flex px-3 py-1.5 text-sm font-medium text-slate-700 hover:text-slate-900 rounded-lg hover:bg-slate-100/80 transition">Iniciar sesión</a>
                <a href="<?= e(url('onboarding')) ?>" class="kn-cta-shimmer relative inline-flex items-center gap-1.5 rounded-lg bg-slate-900 px-3.5 py-1.5 text-sm font-semibold text-white shadow-md hover:shadow-lg hover:shadow-indigo-500/20 transition overflow-hidden">
                    <span class="relative z-10">Empezar gratis</span>
                    <svg class="relative z-10" xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                </a>
                <button @click="open = !open" class="md:hidden p-2 rounded-lg hover:bg-slate-100" aria-label="Menu">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
            </div>
        </div>
        <!-- Mobile menu -->
        <div x-show="open" x-cloak x-transition class="md:hidden mt-2 rounded-2xl border border-slate-200 bg-white/95 backdrop-blur-xl p-3 shadow-lg">
            <a href="#features" @click="open=false" class="block px-3 py-2 rounded-lg hover:bg-slate-100 text-sm">Producto</a>
            <a href="#workflow" @click="open=false" class="block px-3 py-2 rounded-lg hover:bg-slate-100 text-sm">Cómo funciona</a>
            <a href="<?= e(url('pricing')) ?>" class="block px-3 py-2 rounded-lg hover:bg-slate-100 text-sm">Precios</a>
            <a href="#faq" @click="open=false" class="block px-3 py-2 rounded-lg hover:bg-slate-100 text-sm">FAQ</a>
            <a href="<?= e(url('login')) ?>" class="block px-3 py-2 rounded-lg hover:bg-slate-100 text-sm">Iniciar sesión</a>
        </div>
    </div>
</header>

<main class="pt-20"><?= $content ?? '' ?></main>

<!-- ===================== FOOTER ===================== -->
<footer class="relative mt-32 border-t border-slate-200 bg-slate-50/60">
    <div class="mx-auto max-w-7xl px-6 lg:px-8 pt-16 pb-10">
        <div class="grid grid-cols-2 md:grid-cols-5 gap-10">
            <div class="col-span-2">
                <div class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-gradient-to-br from-indigo-500 to-violet-600 text-white font-bold shadow-md shadow-indigo-500/30">K</div>
                    <span class="text-lg font-bold tracking-tight"><?= e(APP_NAME) ?></span>
                </div>
                <p class="mt-4 text-sm text-slate-600 max-w-xs">El sistema operativo de tu bufete migratorio. Multi-tenant, cifrado y diseñado para escalar.</p>
                <div class="mt-5 flex items-center gap-2">
                    <span class="inline-flex items-center gap-1.5 rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-medium text-emerald-700 ring-1 ring-emerald-700/10">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span> Todos los sistemas operativos
                    </span>
                </div>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Producto</h4>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-700">
                    <li><a href="#features" class="hover:text-indigo-600 transition">Funciones</a></li>
                    <li><a href="<?= e(url('pricing')) ?>" class="hover:text-indigo-600 transition">Precios</a></li>
                    <li><a href="#workflow" class="hover:text-indigo-600 transition">Cómo funciona</a></li>
                    <li><a href="#faq" class="hover:text-indigo-600 transition">FAQ</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Empresa</h4>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-700">
                    <li><a href="#" class="hover:text-indigo-600 transition">Sobre nosotros</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition">Contacto</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition">Soporte</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-xs font-semibold uppercase tracking-wider text-slate-500">Legal</h4>
                <ul class="mt-4 space-y-2.5 text-sm text-slate-700">
                    <li><a href="#" class="hover:text-indigo-600 transition">Privacidad</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition">Términos</a></li>
                    <li><a href="#" class="hover:text-indigo-600 transition">Seguridad</a></li>
                </ul>
            </div>
        </div>
        <div class="mt-12 pt-6 border-t border-slate-200 flex flex-wrap justify-between items-center gap-3 text-sm text-slate-500">
            <span>&copy; <?= date('Y') ?> <?= e(APP_NAME) ?>. Todos los derechos reservados.</span>
            <span class="font-mono text-xs">EE.UU. · República Dominicana · México</span>
        </div>
    </div>
</footer>

<script defer nonce="<?= e(csp_nonce()) ?>" src="<?= e(url('assets/landing.js?v=' . filemtime(BASE_PATH . '/assets/landing.js'))) ?>"></script>
</body>
</html>
