<!-- ============================================================
     HERO
     ============================================================ -->
<section class="kn-hero relative overflow-hidden pt-12 pb-32 lg:pt-20 lg:pb-40">
    <div class="kn-grid-bg"></div>
    <div class="kn-orb kn-orb-1"></div>
    <div class="kn-orb kn-orb-2"></div>
    <div class="kn-orb kn-orb-3"></div>

    <div class="mx-auto max-w-7xl px-6 lg:px-8 relative">
        <div class="mx-auto max-w-4xl text-center">
            <span class="kn-pill kn-reveal">
                <span class="h-1.5 w-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                Nuevo · Workflows automáticos con IA
                <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
            </span>

            <h1 class="kn-reveal mt-8 text-5xl sm:text-6xl lg:text-7xl font-bold tracking-tight leading-[1.05]" style="transition-delay:80ms">
                <span class="kn-headline">El sistema operativo</span>
                <br>
                <span class="kn-headline-grad">de tu bufete migratorio.</span>
            </h1>

            <p class="kn-reveal mt-7 text-lg lg:text-xl leading-relaxed text-slate-600 max-w-2xl mx-auto" style="transition-delay:160ms">
                Gestiona clientes, casos, documentos y plazos con la plataforma más segura del mercado.
                Diseñado para bufetes en EE.UU., República Dominicana y México.
            </p>

            <div class="mt-10 flex items-center justify-center gap-3 flex-wrap kn-reveal" style="transition-delay:240ms">
                <a href="<?= e(url('onboarding')) ?>" class="group relative inline-flex items-center gap-2 rounded-xl bg-slate-900 px-6 py-3.5 text-sm font-semibold text-white shadow-xl shadow-indigo-500/20 hover:shadow-2xl hover:shadow-indigo-500/30 hover:scale-[1.02] transition-all overflow-hidden">
                    <span class="relative z-10">Empezar prueba de 14 días</span>
                    <svg class="relative z-10 group-hover:translate-x-1 transition" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    <span class="absolute inset-0 bg-gradient-to-r from-indigo-600 via-violet-600 to-pink-600 opacity-0 group-hover:opacity-100 transition-opacity"></span>
                </a>
                <a href="#demo" class="inline-flex items-center gap-2 rounded-xl border border-slate-300 bg-white/70 backdrop-blur px-6 py-3.5 text-sm font-semibold text-slate-900 hover:bg-white transition">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Ver demo
                </a>
            </div>

            <p class="kn-reveal mt-5 text-xs text-slate-500 font-medium flex items-center justify-center gap-3 flex-wrap" style="transition-delay:320ms">
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg> Sin tarjeta de crédito</span>
                <span class="text-slate-300">·</span>
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg> Setup en 5 minutos</span>
                <span class="text-slate-300">·</span>
                <span class="flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" class="text-emerald-500"><polyline points="20 6 9 17 4 12"/></svg> Cancela cuando quieras</span>
            </p>
        </div>

        <!-- Mockup hero -->
        <div data-hero-mockup class="kn-mockup-wrap mt-20 mx-auto max-w-5xl kn-reveal" style="transition-delay:400ms">
            <div class="kn-mockup-glow"></div>
            <div class="kn-mockup">
                <!-- Mockup browser bar -->
                <div class="kn-mockup-bar">
                    <div class="kn-mockup-dot bg-rose-400"></div>
                    <div class="kn-mockup-dot bg-amber-400"></div>
                    <div class="kn-mockup-dot bg-emerald-400"></div>
                    <div class="ml-3 flex-1 h-6 rounded-md bg-white/80 border border-slate-200 flex items-center justify-center text-[11px] text-slate-500 font-mono">app.<?= strtolower(e(APP_NAME)) ?>.com/dashboard</div>
                </div>
                <!-- Mockup content (dashboard simulado) -->
                <div class="grid grid-cols-12 min-h-[440px]">
                    <div class="col-span-3 bg-slate-900 text-slate-100 p-4 space-y-1">
                        <div class="flex items-center gap-2 mb-4 px-2">
                            <div class="h-7 w-7 rounded-md bg-gradient-to-br from-indigo-500 to-violet-600 text-xs font-bold flex items-center justify-center">K</div>
                            <span class="text-sm font-semibold">KyrosCounsel</span>
                        </div>
                        <div class="px-3 py-2 rounded-md bg-slate-800 text-sm flex items-center gap-2">
                            <div class="h-1.5 w-1.5 rounded-full bg-indigo-400"></div> Dashboard
                        </div>
                        <div class="px-3 py-2 rounded-md text-sm text-slate-400 flex items-center gap-2 hover:bg-slate-800/50">Clientes <span class="ml-auto text-[10px] bg-slate-800 px-1.5 rounded">142</span></div>
                        <div class="px-3 py-2 rounded-md text-sm text-slate-400 flex items-center gap-2 hover:bg-slate-800/50">Casos <span class="ml-auto text-[10px] bg-slate-800 px-1.5 rounded">38</span></div>
                        <div class="px-3 py-2 rounded-md text-sm text-slate-400">Documentos</div>
                        <div class="px-3 py-2 rounded-md text-sm text-slate-400">Tareas</div>
                        <div class="px-3 py-2 rounded-md text-sm text-slate-400">Calendario</div>
                        <div class="pt-3 mt-2 border-t border-slate-800">
                            <div class="px-3 py-2 rounded-md text-sm text-slate-400">Reportes</div>
                            <div class="px-3 py-2 rounded-md text-sm text-slate-400">Workflows</div>
                        </div>
                    </div>
                    <div class="col-span-9 p-6 bg-white">
                        <div class="flex items-baseline justify-between mb-5">
                            <div>
                                <div class="text-xs uppercase tracking-wider text-slate-500 font-semibold">Buenos días</div>
                                <div class="text-2xl font-bold mt-1">Resumen operativo</div>
                            </div>
                            <button class="rounded-lg bg-indigo-600 text-white text-xs font-semibold px-3 py-2">+ Nuevo caso</button>
                        </div>
                        <div class="grid grid-cols-4 gap-3 mb-5">
                            <div class="rounded-xl border border-slate-200 p-3"><div class="text-[10px] uppercase text-slate-500 font-semibold">Casos activos</div><div class="text-2xl font-bold mt-1">142</div><div class="text-[10px] text-emerald-600 font-semibold mt-1">↑ 12% vs mes</div></div>
                            <div class="rounded-xl border border-slate-200 p-3"><div class="text-[10px] uppercase text-slate-500 font-semibold">Por vencer</div><div class="text-2xl font-bold mt-1 text-amber-600">7</div><div class="text-[10px] text-slate-500 mt-1">próx. 3 días</div></div>
                            <div class="rounded-xl border border-slate-200 p-3"><div class="text-[10px] uppercase text-slate-500 font-semibold">Aprobados</div><div class="text-2xl font-bold mt-1 text-emerald-600">89%</div><div class="text-[10px] text-slate-500 mt-1">tasa éxito</div></div>
                            <div class="rounded-xl border border-slate-200 p-3"><div class="text-[10px] uppercase text-slate-500 font-semibold">Ingresos MTD</div><div class="text-2xl font-bold mt-1">$42.8k</div><div class="text-[10px] text-emerald-600 font-semibold mt-1">↑ 8.4%</div></div>
                        </div>
                        <div class="rounded-xl border border-slate-200 p-4">
                            <div class="text-xs font-semibold text-slate-700 mb-3">Casos por estado</div>
                            <svg viewBox="0 0 400 120" class="w-full h-32">
                                <defs><linearGradient id="hg" x1="0" x2="0" y1="0" y2="1"><stop offset="0%" stop-color="#6366f1" stop-opacity="0.4"/><stop offset="100%" stop-color="#6366f1" stop-opacity="0"/></linearGradient></defs>
                                <path d="M 0 90 L 40 70 L 80 80 L 120 50 L 160 60 L 200 30 L 240 40 L 280 20 L 320 35 L 360 15 L 400 25 L 400 120 L 0 120 Z" fill="url(#hg)"/>
                                <path d="M 0 90 L 40 70 L 80 80 L 120 50 L 160 60 L 200 30 L 240 40 L 280 20 L 320 35 L 360 15 L 400 25" fill="none" stroke="#6366f1" stroke-width="2.5" stroke-linecap="round"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     LOGO MARQUEE
     ============================================================ -->
<section class="py-12 border-y border-slate-200/60 bg-slate-50/50">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-widest text-slate-500 mb-8">Confianza de bufetes en 12+ países</p>
        <div class="kn-marquee">
            <div class="kn-marquee-track">
                <?php
                $logos = ['Núñez & Asoc.', 'Velázquez Law', 'Pérez Migration', 'Rodríguez Legal', 'García International', 'Martínez Counsel', 'Reyes & Partners', 'Castro Immigration', 'Hidalgo Law Group', 'Torres Legal'];
                foreach (array_merge($logos, $logos) as $name):
                ?>
                <div class="text-2xl font-bold tracking-tight text-slate-400 hover:text-slate-700 transition whitespace-nowrap"><?= e($name) ?></div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     FEATURES — BENTO GRID
     ============================================================ -->
<section id="features" class="py-24 lg:py-32 bg-white">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="max-w-2xl mb-16" data-anim="fade-up">
            <span class="kn-eyebrow">Todo en un sistema</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight leading-tight">
                Una plataforma. <br><span class="kn-headline-grad">Todo el bufete.</span>
            </h2>
            <p class="mt-5 text-lg text-slate-600">
                Reemplaza Excel, carpetas compartidas y servicios sueltos con una plataforma diseñada específicamente para inmigración.
            </p>
        </div>

        <div class="kn-bento" data-anim="stagger">

            <!-- Card grande: Multi-tenant -->
            <div class="kn-bento-card kn-bento-large p-8">
                <div class="flex items-start justify-between mb-4">
                    <div class="kn-lock">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="11" width="18" height="11" rx="2" ry="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg>
                    </div>
                    <span class="text-xs font-mono text-slate-400 bg-slate-100 px-2 py-1 rounded">XChaCha20-Poly1305</span>
                </div>
                <h3 class="mt-6 text-2xl font-bold">Cifrado AEAD por tenant</h3>
                <p class="mt-3 text-slate-600 leading-relaxed">
                    Pasaportes, A-numbers y SSN cifrados con clave derivada por bufete (HKDF-SHA256).
                    Imposible cruzar tenants ni siquiera por bug. <span class="font-semibold text-slate-900">Cumple con HIPAA y GDPR</span>.
                </p>
                <div class="mt-6 grid grid-cols-3 gap-3 text-center">
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-2xl font-bold">256</div>
                        <div class="text-[10px] uppercase tracking-wide text-slate-500">bits AEAD</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-2xl font-bold">SHA-256</div>
                        <div class="text-[10px] uppercase tracking-wide text-slate-500">integridad</div>
                    </div>
                    <div class="rounded-xl bg-slate-50 p-3">
                        <div class="text-2xl font-bold">TOTP</div>
                        <div class="text-[10px] uppercase tracking-wide text-slate-500">2FA opcional</div>
                    </div>
                </div>
            </div>

            <!-- Card pequeño: Multi-país -->
            <div class="kn-bento-card kn-bento-small p-6">
                <div class="flex flex-wrap gap-2 mb-4">
                    <span class="text-2xl">🇺🇸</span>
                    <span class="text-2xl">🇩🇴</span>
                    <span class="text-2xl">🇲🇽</span>
                </div>
                <h3 class="text-lg font-bold">Multi-país nativo</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Catálogos por jurisdicción. I-130, I-485, asilo, N-400, residencia DR, mexicana...
                </p>
            </div>

            <!-- Card pequeño: Workflows -->
            <div class="kn-bento-card kn-bento-small p-6">
                <div class="flex items-center justify-center mb-4">
                    <svg xmlns="http://www.w3.org/2000/svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="url(#gw)" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><defs><linearGradient id="gw" x1="0" x2="1" y1="0" y2="1"><stop offset="0%" stop-color="#6366f1"/><stop offset="100%" stop-color="#ec4899"/></linearGradient></defs><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                </div>
                <h3 class="text-lg font-bold">Workflows automáticos</h3>
                <p class="mt-2 text-sm text-slate-600">
                    Crea tareas y envía emails cuando ocurren eventos. Sin código.
                </p>
            </div>

            <!-- Card medio: Documentos -->
            <div class="kn-bento-card kn-bento-medium p-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    Documentos seguros
                </h3>
                <p class="mt-2 text-sm text-slate-600">Archivos cifrados en disco con verificación SHA-256 al descargar. Versiones, metadata, auditoría.</p>
                <div class="mt-4 space-y-2">
                    <div class="flex items-center gap-3 text-xs"><div class="w-7 h-7 rounded-md bg-rose-50 text-rose-600 flex items-center justify-center font-semibold">PDF</div><div class="flex-1 truncate">Pasaporte_Rodriguez.pdf</div><span class="text-emerald-600">✓</span></div>
                    <div class="flex items-center gap-3 text-xs"><div class="w-7 h-7 rounded-md bg-blue-50 text-blue-600 flex items-center justify-center font-semibold">JPG</div><div class="flex-1 truncate">I94_Garcia_2024.jpg</div><span class="text-emerald-600">✓</span></div>
                    <div class="flex items-center gap-3 text-xs"><div class="w-7 h-7 rounded-md bg-slate-100 text-slate-700 flex items-center justify-center font-semibold">DOC</div><div class="flex-1 truncate">Carta_Apoyo_Lopez.docx</div><span class="text-emerald-600">✓</span></div>
                </div>
            </div>

            <!-- Card medio: Reportes -->
            <div class="kn-bento-card kn-bento-medium p-6">
                <h3 class="text-lg font-bold flex items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/></svg>
                    Dashboards en vivo
                </h3>
                <p class="mt-2 text-sm text-slate-600">KPIs animados, sparklines, distribución por abogado y actividad reciente. Filtros guardados.</p>
                <div class="mt-4 flex items-end gap-1 h-20">
                    <?php
                    $heights = [40, 65, 50, 80, 45, 70, 55, 90, 60, 75, 50, 85];
                    foreach ($heights as $h):
                    ?>
                    <div class="flex-1 rounded-t-md bg-gradient-to-t from-indigo-500 to-violet-400" style="height: <?= $h ?>%"></div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Card pequeño: 2FA -->
            <div class="kn-bento-card kn-bento-small p-6">
                <div class="flex items-center justify-center mb-4">
                    <div class="grid grid-cols-3 gap-1">
                        <?php for ($i = 0; $i < 9; $i++): ?>
                        <div class="w-3.5 h-3.5 rounded-sm <?= ($i % 3 === 0 || $i === 4) ? 'bg-slate-900' : 'bg-slate-200' ?>"></div>
                        <?php endfor; ?>
                    </div>
                </div>
                <h3 class="text-lg font-bold">2FA + Auditoría</h3>
                <p class="mt-2 text-sm text-slate-600">TOTP en cualquier autenticador + log inmutable de acciones críticas.</p>
            </div>

            <!-- Card pequeño: Calendario -->
            <div class="kn-bento-card kn-bento-small p-6">
                <div class="text-center mb-4">
                    <div class="text-[10px] uppercase tracking-wider text-slate-500 font-semibold">Octubre</div>
                    <div class="text-3xl font-bold">12</div>
                </div>
                <h3 class="text-lg font-bold">Calendario operativo</h3>
                <p class="mt-2 text-sm text-slate-600">Tareas, vencimientos, presentaciones y decisiones. Una sola vista.</p>
            </div>

            <!-- Card pequeño: Plantillas -->
            <div class="kn-bento-card kn-bento-small p-6">
                <div class="space-y-1.5 mb-4">
                    <div class="h-2 bg-slate-200 rounded-full"></div>
                    <div class="h-2 bg-slate-200 rounded-full w-4/5"></div>
                    <div class="h-2 bg-slate-200 rounded-full w-3/5"></div>
                    <div class="h-2 bg-indigo-400 rounded-full w-2/3"></div>
                </div>
                <h3 class="text-lg font-bold">Plantillas inteligentes</h3>
                <p class="mt-2 text-sm text-slate-600">Cartas de apoyo, RFEs, engagement letters. Mail merge con datos del caso.</p>
            </div>

            <!-- Card grande inferior: Ahorro -->
            <div class="kn-bento-card kn-bento-large p-8 bg-gradient-to-br from-slate-900 to-indigo-950 text-white border-slate-800">
                <div class="flex items-center gap-2 mb-4">
                    <span class="kn-eyebrow !bg-indigo-500/20 !text-indigo-200 !border-indigo-500/30">ROI medido</span>
                </div>
                <h3 class="text-2xl font-bold">Tus abogados ganan <span class="kn-headline-grad">~12 horas a la semana</span></h3>
                <p class="mt-3 text-slate-300 max-w-md">
                    Eliminas Excel, Word genérico y carpetas compartidas. Todo lo importante está conectado: cliente → caso → tareas → documentos → cobro.
                </p>
                <div class="mt-6 grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-3xl font-bold kn-ticker" data-counter="12" data-suffix="h">0h</div>
                        <div class="text-xs text-slate-400 mt-1">por abogado / sem</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold kn-ticker" data-counter="3" data-suffix="x">0x</div>
                        <div class="text-xs text-slate-400 mt-1">más casos por mes</div>
                    </div>
                    <div>
                        <div class="text-3xl font-bold kn-ticker" data-counter="100" data-prefix="$" data-suffix="k+">0</div>
                        <div class="text-xs text-slate-400 mt-1">año extra/abogado</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     STATS COUNTER
     ============================================================ -->
<section class="py-20 bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center" data-anim="fade-up">
            <div>
                <div class="text-5xl lg:text-6xl font-bold kn-ticker tracking-tight" data-counter="142" data-suffix="+">0</div>
                <div class="mt-2 text-sm text-slate-600 font-medium">Bufetes activos</div>
            </div>
            <div>
                <div class="text-5xl lg:text-6xl font-bold kn-ticker tracking-tight" data-counter="38500" data-suffix="+">0</div>
                <div class="mt-2 text-sm text-slate-600 font-medium">Casos gestionados</div>
            </div>
            <div>
                <div class="text-5xl lg:text-6xl font-bold kn-ticker tracking-tight" data-counter="99.9" data-suffix="%">0</div>
                <div class="mt-2 text-sm text-slate-600 font-medium">Uptime SLA</div>
            </div>
            <div>
                <div class="text-5xl lg:text-6xl font-bold kn-ticker tracking-tight" data-counter="12">0</div>
                <div class="mt-2 text-sm text-slate-600 font-medium">Países</div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     WORKFLOW (Cómo funciona)
     ============================================================ -->
<section id="workflow" class="py-24 lg:py-32 bg-white" data-workflow>
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-20" data-anim="fade-up">
            <span class="kn-eyebrow">Empieza en minutos</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight">De cero a productivo en <span class="kn-headline-grad">3 pasos</span></h2>
        </div>

        <div class="relative max-w-3xl mx-auto">
            <!-- Línea vertical central -->
            <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-gradient-to-b from-indigo-500 via-violet-500 to-pink-500 origin-top" data-workflow-line></div>

            <div class="space-y-12">
                <div class="flex gap-6 relative" data-anim="fade-up">
                    <div class="kn-step-num relative z-10">1</div>
                    <div class="flex-1 pt-1.5">
                        <h3 class="text-xl font-bold">Crea tu tenant</h3>
                        <p class="mt-2 text-slate-600">Onboarding de 5 minutos. Eliges nombre, slug y país. Tu base de datos privada se aprovisiona automáticamente con clave de cifrado única.</p>
                        <div class="mt-3 inline-flex items-center gap-2 text-xs font-mono text-slate-500 bg-slate-50 border border-slate-200 rounded-md px-3 py-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                            tenant.kyroscounsel.com listo en ~30s
                        </div>
                    </div>
                </div>

                <div class="flex gap-6 relative" data-anim="fade-up">
                    <div class="kn-step-num relative z-10">2</div>
                    <div class="flex-1 pt-1.5">
                        <h3 class="text-xl font-bold">Importa tus clientes</h3>
                        <p class="mt-2 text-slate-600">Sube un CSV o conecta tu antigua herramienta. Los PII se cifran automáticamente al ingresar. Mapeo guiado de tipos de caso.</p>
                        <div class="mt-3 flex flex-wrap gap-2">
                            <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">CSV</span>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">Excel</span>
                            <span class="text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-700 font-medium">API REST</span>
                        </div>
                    </div>
                </div>

                <div class="flex gap-6 relative" data-anim="fade-up">
                    <div class="kn-step-num relative z-10">3</div>
                    <div class="flex-1 pt-1.5">
                        <h3 class="text-xl font-bold">Configura workflows</h3>
                        <p class="mt-2 text-slate-600">Reglas tipo "si caso pasa a RFE → crear tarea + email al cliente". Plantillas pre-armadas para los flujos más comunes.</p>
                        <div class="mt-3 inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-gradient-to-r from-indigo-50 to-violet-50 border border-indigo-200 text-xs font-medium text-indigo-700">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                            8 plantillas listas para usar
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     DEMO INTERACTIVO (Tabs)
     ============================================================ -->
<section id="demo" class="py-24 lg:py-32 bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12" data-anim="fade-up">
            <span class="kn-eyebrow">Demo en vivo</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight">Ve KyrosCounsel <span class="kn-headline-grad">en acción</span></h2>
        </div>

        <div data-anim="fade-up" data-tabs class="max-w-5xl mx-auto">
            <div class="inline-flex items-center gap-1 p-1 rounded-xl bg-slate-200/70 mx-auto block w-fit mb-6">
                <button data-tab="casos" class="kn-tab active">Casos</button>
                <button data-tab="docs" class="kn-tab">Documentos</button>
                <button data-tab="tareas" class="kn-tab">Tareas</button>
                <button data-tab="reportes" class="kn-tab">Reportes</button>
            </div>

            <div class="kn-mockup">
                <div class="kn-mockup-bar">
                    <div class="kn-mockup-dot bg-rose-400"></div>
                    <div class="kn-mockup-dot bg-amber-400"></div>
                    <div class="kn-mockup-dot bg-emerald-400"></div>
                </div>

                <!-- Panel: Casos -->
                <div data-panel="casos" class="p-6 bg-white">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-lg font-bold">Casos activos</h3>
                        <button class="text-xs bg-indigo-600 text-white rounded-lg px-3 py-1.5 font-semibold">+ Nuevo</button>
                    </div>
                    <table class="w-full text-sm">
                        <thead class="text-left text-xs uppercase text-slate-500 border-b border-slate-200">
                            <tr><th class="pb-2">Caso</th><th>Cliente</th><th>Tipo</th><th>Estado</th><th>Plazo</th></tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr><td class="py-3 font-mono text-xs">#KYR-2401</td><td>María Rodríguez</td><td>I-485</td><td><span class="text-xs bg-amber-50 text-amber-700 px-2 py-0.5 rounded-full">RFE</span></td><td class="text-amber-600 font-medium">3 días</td></tr>
                            <tr><td class="py-3 font-mono text-xs">#KYR-2402</td><td>Juan García</td><td>N-400</td><td><span class="text-xs bg-blue-50 text-blue-700 px-2 py-0.5 rounded-full">Filed</span></td><td>14 días</td></tr>
                            <tr><td class="py-3 font-mono text-xs">#KYR-2403</td><td>Ana López</td><td>Asilo</td><td><span class="text-xs bg-violet-50 text-violet-700 px-2 py-0.5 rounded-full">Hearing</span></td><td>22 días</td></tr>
                            <tr><td class="py-3 font-mono text-xs">#KYR-2404</td><td>Carlos Méndez</td><td>I-130</td><td><span class="text-xs bg-emerald-50 text-emerald-700 px-2 py-0.5 rounded-full">Aprobado</span></td><td>—</td></tr>
                        </tbody>
                    </table>
                </div>

                <!-- Panel: Docs -->
                <div data-panel="docs" class="p-6 bg-white hidden">
                    <h3 class="text-lg font-bold mb-4">Documentos cifrados</h3>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
                        <?php
                        $docs = [
                            ['Pasaporte_Maria.pdf', 'PDF', 'rose'],
                            ['I94_Juan_2024.jpg', 'JPG', 'blue'],
                            ['Acta_Nacimiento.pdf', 'PDF', 'rose'],
                            ['Carta_Apoyo_Ana.docx', 'DOC', 'slate'],
                            ['Foto_Pasaporte.jpg', 'JPG', 'blue'],
                            ['I797_Carlos.pdf', 'PDF', 'rose'],
                            ['Tax_Returns.pdf', 'PDF', 'rose'],
                            ['Bank_Statements.pdf', 'PDF', 'rose'],
                        ];
                        foreach ($docs as [$n, $t, $c]):
                        ?>
                        <div class="rounded-lg border border-slate-200 p-3 hover:border-indigo-300 transition cursor-pointer">
                            <div class="w-10 h-10 rounded-md bg-<?= $c ?>-50 text-<?= $c ?>-600 flex items-center justify-center font-bold text-xs mb-2"><?= $t ?></div>
                            <div class="text-xs font-medium truncate"><?= e($n) ?></div>
                            <div class="text-[10px] text-emerald-600 mt-1 flex items-center gap-1"><svg xmlns="http://www.w3.org/2000/svg" width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg> Cifrado</div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Panel: Tareas -->
                <div data-panel="tareas" class="p-6 bg-white hidden">
                    <h3 class="text-lg font-bold mb-4">Mis tareas</h3>
                    <div class="space-y-2">
                        <?php
                        $tasks = [
                            ['Responder RFE - Caso #KYR-2401', 'Hoy', 'urgente'],
                            ['Subir affidavit de María Rodríguez', 'Mañana', 'alta'],
                            ['Llamar a Juan García - confirmar entrevista', '2 días', 'normal'],
                            ['Revisar I-130 de Carlos Méndez', '5 días', 'normal'],
                            ['Preparar engagement letter - nuevo cliente', '1 semana', 'baja'],
                        ];
                        foreach ($tasks as [$t, $d, $p]):
                            $colors = ['urgente'=>'rose','alta'=>'amber','normal'=>'slate','baja'=>'slate'];
                        ?>
                        <div class="flex items-center gap-3 p-3 rounded-lg border border-slate-200 hover:bg-slate-50 transition">
                            <input type="checkbox" class="rounded">
                            <div class="flex-1 text-sm"><?= e($t) ?></div>
                            <span class="text-xs px-2 py-0.5 rounded-full bg-<?= $colors[$p] ?>-50 text-<?= $colors[$p] ?>-700"><?= e($p) ?></span>
                            <span class="text-xs text-slate-500 font-medium w-20 text-right"><?= e($d) ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- Panel: Reportes -->
                <div data-panel="reportes" class="p-6 bg-white hidden">
                    <h3 class="text-lg font-bold mb-4">Reporte mensual</h3>
                    <div class="grid grid-cols-3 gap-4 mb-5">
                        <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500 font-semibold">Casos abiertos</div><div class="text-3xl font-bold mt-1">127</div></div>
                        <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500 font-semibold">Tasa éxito</div><div class="text-3xl font-bold mt-1 text-emerald-600">89%</div></div>
                        <div class="rounded-xl border border-slate-200 p-4"><div class="text-xs uppercase text-slate-500 font-semibold">Ingresos</div><div class="text-3xl font-bold mt-1">$42.8k</div></div>
                    </div>
                    <div class="rounded-xl border border-slate-200 p-4">
                        <div class="text-xs font-semibold text-slate-700 mb-2">Casos por tipo</div>
                        <div class="flex items-end gap-2 h-32">
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:80%"></div><div class="text-[10px] mt-1">I-485</div></div>
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:60%"></div><div class="text-[10px] mt-1">I-130</div></div>
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:45%"></div><div class="text-[10px] mt-1">N-400</div></div>
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:35%"></div><div class="text-[10px] mt-1">Asilo</div></div>
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:25%"></div><div class="text-[10px] mt-1">DACA</div></div>
                            <div class="flex-1 flex flex-col items-center"><div class="w-full bg-indigo-500 rounded-t" style="height:50%"></div><div class="text-[10px] mt-1">Otros</div></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- ============================================================
     TESTIMONIALS
     ============================================================ -->
<section class="py-24 lg:py-32 bg-white">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-16" data-anim="fade-up">
            <span class="kn-eyebrow">Testimonios</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight">Bufetes que ya operan <span class="kn-headline-grad">con KyrosCounsel</span></h2>
        </div>

        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6" data-anim="stagger">
            <?php
            $testimonials = [
                [
                    'q' => '"Pasamos de 8 sistemas distintos a uno solo. Mis abogados están menos estresados y producen un 40% más."',
                    'n' => 'Lic. Carolina Núñez',
                    'r' => 'Núñez & Asociados · Santo Domingo',
                    'a' => 'CN'
                ],
                [
                    'q' => '"El cifrado AEAD nos permitió pasar la auditoría HIPAA sin un solo hallazgo. Tranquilidad total."',
                    'n' => 'Atty. James Velázquez',
                    'r' => 'Velázquez Law · Miami, FL',
                    'a' => 'JV'
                ],
                [
                    'q' => '"Los workflows automáticos eliminaron tareas repetitivas. Recuperé 12 horas a la semana de mi tiempo."',
                    'n' => 'Lic. Roberto Pérez',
                    'r' => 'Pérez Migration · CDMX',
                    'a' => 'RP'
                ],
            ];
            foreach ($testimonials as $t):
            ?>
            <div class="kn-testimonial">
                <div class="flex items-center gap-1 mb-4 text-amber-400">
                    <?php for ($i = 0; $i < 5; $i++): ?>
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <?php endfor; ?>
                </div>
                <p class="text-slate-700 leading-relaxed"><?= e($t['q']) ?></p>
                <div class="mt-5 flex items-center gap-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-violet-600 text-white flex items-center justify-center font-bold text-sm"><?= e($t['a']) ?></div>
                    <div>
                        <div class="font-semibold text-sm"><?= e($t['n']) ?></div>
                        <div class="text-xs text-slate-500"><?= e($t['r']) ?></div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     PRICING
     ============================================================ -->
<section id="pricing" class="py-24 lg:py-32 bg-slate-50">
    <div class="mx-auto max-w-7xl px-6 lg:px-8">
        <div class="max-w-2xl mx-auto text-center mb-12" data-anim="fade-up">
            <span class="kn-eyebrow">Precios simples</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight">Elige el plan que <span class="kn-headline-grad">se adapta a ti</span></h2>
            <p class="mt-5 text-lg text-slate-600">14 días de prueba en cualquier plan. Sin tarjeta. Cancela cuando quieras.</p>

            <!-- Toggle mensual/anual -->
            <div class="mt-8 inline-flex flex-col items-center">
                <div data-pricing-toggle class="kn-toggle">
                    <span class="kn-toggle-pill"></span>
                    <button data-period="monthly" class="active">Mensual</button>
                    <button data-period="yearly">Anual <span class="text-emerald-600 font-bold">−20%</span></button>
                </div>
            </div>
        </div>

        <div class="grid md:grid-cols-3 gap-6 max-w-6xl mx-auto" data-anim="stagger">
            <?php foreach ($plans as $p):
                $features = $p['features_json'] ? json_decode($p['features_json'], true) : [];
                $featured = ($p['code'] === 'pro');
            ?>
            <div class="kn-price-card <?= $featured ? 'kn-price-featured' : '' ?>">
                <?php if ($featured): ?>
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 inline-flex items-center gap-1 rounded-full bg-gradient-to-r from-amber-400 to-pink-400 text-amber-950 text-xs font-bold px-3 py-1 shadow-lg">
                    <svg xmlns="http://www.w3.org/2000/svg" width="12" height="12" viewBox="0 0 24 24" fill="currentColor"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Más popular
                </span>
                <?php endif; ?>

                <div class="relative">
                    <h3 class="text-lg font-bold"><?= e($p['name']) ?></h3>
                    <p class="text-sm <?= $featured ? 'text-slate-400' : 'text-slate-500' ?> mt-1">
                        <?= $p['code'] === 'starter' ? 'Para bufetes que arrancan' : ($p['code'] === 'pro' ? 'Para equipos en crecimiento' : 'Para firmas establecidas') ?>
                    </p>

                    <p class="mt-6 flex items-baseline gap-x-1">
                        <span class="text-5xl font-bold tracking-tight" data-price="<?= e($p['price_usd']) ?>">$<?= e(number_format((float)$p['price_usd'], 0)) ?></span>
                        <span class="text-sm <?= $featured ? 'text-slate-400' : 'text-slate-500' ?>" data-period-label>/mes</span>
                    </p>
                    <p data-savings class="hidden text-xs mt-1 <?= $featured ? 'text-emerald-300' : 'text-emerald-600' ?> font-semibold">Ahorras 20% pagando anual</p>

                    <a href="<?= e(url('onboarding?plan=' . $p['code'])) ?>"
                       class="mt-6 block w-full rounded-xl py-3 text-center text-sm font-semibold transition <?= $featured
                           ? 'bg-white text-indigo-700 hover:bg-slate-50 shadow-lg'
                           : 'bg-slate-900 text-white hover:bg-slate-800' ?>">
                        Empezar prueba gratis
                    </a>

                    <ul class="mt-8 space-y-3 text-sm <?= $featured ? 'text-slate-300' : 'text-slate-700' ?>">
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Hasta <strong><?= (int)$p['max_users'] ?> usuarios</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Hasta <strong><?= number_format((int)$p['max_cases']) ?> casos</strong></span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><strong><?= number_format((int)$p['max_storage_mb']) ?> MB</strong> de almacenamiento</span>
                        </li>
                        <?php if (!empty($features['emails'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span><strong><?= number_format((int)$features['emails']) ?> emails/mes</strong></span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['workflows'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Workflows automáticos</span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['two_factor'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Autenticación 2FA</span>
                        </li>
                        <?php endif; ?>
                        <?php if (!empty($features['priority_support'])): ?>
                        <li class="flex items-start gap-2">
                            <svg class="<?= $featured ? 'text-indigo-300' : 'text-indigo-600' ?> flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><polyline points="20 6 9 17 4 12"/></svg>
                            <span>Soporte prioritario</span>
                        </li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FAQ
     ============================================================ -->
<section id="faq" class="py-24 lg:py-32 bg-white">
    <div class="mx-auto max-w-3xl px-6 lg:px-8">
        <div class="text-center mb-12" data-anim="fade-up">
            <span class="kn-eyebrow">Preguntas frecuentes</span>
            <h2 class="mt-4 text-4xl lg:text-5xl font-bold tracking-tight">¿Tienes dudas? Las resolvemos.</h2>
        </div>

        <div class="space-y-3" data-anim="stagger">
            <?php
            $faqs = [
                ['¿Cómo funciona la prueba gratis?', 'Tienes 14 días para usar la plataforma con todas sus funciones, sin tarjeta de crédito. Si decides continuar, eliges un plan; si no, simplemente la cuenta queda inactiva. Tus datos se conservan 30 días por si decides volver.'],
                ['¿Qué tan seguro es el cifrado?', 'Usamos XChaCha20-Poly1305 (sodium) o AES-256-GCM como fallback — ambos son AEAD modernos. Cada bufete tiene su propia clave derivada con HKDF-SHA256 a partir de una clave maestra. Los archivos en disco también van cifrados y se verifica integridad SHA-256 al descargar.'],
                ['¿Puedo migrar mis datos actuales?', 'Sí. Soportamos importación CSV y Excel con mapeo guiado de campos. Para bufetes grandes (1000+ clientes) ofrecemos migración asistida sin costo en planes Pro y Enterprise.'],
                ['¿Tengo soporte en español?', 'Por supuesto. Todo el equipo de soporte habla español nativo. Atendemos por chat, email y videollamada según tu plan.'],
                ['¿Qué pasa si crezco y necesito más usuarios?', 'Puedes cambiar de plan en cualquier momento desde tu panel. El cobro se prorratea automáticamente. No hay penalizaciones ni contratos largos.'],
                ['¿Funciona en países distintos a EE.UU. y RD?', 'Sí. Tenemos catálogos para EE.UU., República Dominicana, México y Colombia, con más en camino. Si tu jurisdicción no está soportada, podemos agregarla en planes Enterprise.'],
            ];
            foreach ($faqs as $i => [$q, $a]):
            ?>
            <details class="kn-faq-item" <?= $i === 0 ? 'open' : '' ?>>
                <summary class="kn-faq-trigger">
                    <span><?= e($q) ?></span>
                    <svg class="kn-faq-icon text-slate-400" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"/></svg>
                </summary>
                <div class="kn-faq-body"><?= e($a) ?></div>
            </details>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- ============================================================
     FINAL CTA
     ============================================================ -->
<section class="py-16 lg:py-24">
    <div class="mx-auto max-w-7xl px-6 lg:px-8" data-anim="fade-up">
        <div class="kn-final-cta">
            <div class="relative z-10 max-w-2xl mx-auto">
                <span class="inline-flex items-center gap-2 rounded-full bg-white/10 px-3 py-1 text-xs font-medium ring-1 ring-white/20">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                    Activa tu cuenta hoy
                </span>
                <h2 class="mt-6 text-4xl lg:text-5xl font-bold tracking-tight">Listo para transformar tu bufete</h2>
                <p class="mt-5 text-lg text-indigo-100/80">
                    14 días gratis. Sin tarjeta. Sin riesgos.
                    Únete a los 142+ bufetes que ya operan con KyrosCounsel.
                </p>
                <div class="mt-8 flex items-center justify-center gap-3 flex-wrap">
                    <a href="<?= e(url('onboarding')) ?>" class="group inline-flex items-center gap-2 rounded-xl bg-white text-indigo-700 px-7 py-3.5 text-sm font-bold shadow-2xl hover:bg-slate-100 hover:scale-[1.02] transition-all">
                        Empezar ahora
                        <svg class="group-hover:translate-x-1 transition" xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                    </a>
                    <a href="<?= e(url('pricing')) ?>" class="inline-flex items-center gap-2 rounded-xl bg-white/10 text-white px-7 py-3.5 text-sm font-semibold ring-1 ring-white/20 hover:bg-white/20 transition">
                        Ver precios
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>
