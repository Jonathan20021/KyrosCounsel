<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex">
    <title>Mantenimiento · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap">
    <style nonce="<?= e(csp_nonce()) ?>">
        :root { color-scheme: dark; }
        * { box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh;
            font-family: 'Inter', system-ui, sans-serif;
            background: linear-gradient(180deg, #0f172a 0%, #1e1b4b 100%);
            color: #f1f5f9;
            display: flex; align-items: center; justify-content: center;
            padding: 2rem; position: relative; overflow: hidden;
        }
        .orb {
            position: absolute; border-radius: 9999px; filter: blur(80px); opacity: 0.5; pointer-events: none;
        }
        .orb-1 { top: -10%; left: -10%; width: 480px; height: 480px; background: radial-gradient(circle, rgba(99,102,241,0.55), transparent 70%); }
        .orb-2 { bottom: -10%; right: -10%; width: 520px; height: 520px; background: radial-gradient(circle, rgba(236,72,153,0.40), transparent 70%); }
        .card {
            position: relative; z-index: 1;
            max-width: 480px; text-align: center;
            padding: 2.5rem 2rem;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.08);
            border-radius: 24px;
            box-shadow: 0 30px 80px -20px rgba(0,0,0,0.6);
        }
        .icon {
            width: 64px; height: 64px; margin: 0 auto 1.25rem;
            border-radius: 16px;
            background: linear-gradient(135deg, #6366f1, #8b5cf6);
            display: flex; align-items: center; justify-content: center;
            box-shadow: 0 12px 32px -8px rgba(99,102,241,0.5);
        }
        h1 { font-size: 1.875rem; font-weight: 800; letter-spacing: -0.02em; margin: 0 0 0.5rem; }
        .grad { background: linear-gradient(110deg, #818cf8, #c084fc, #f472b6); -webkit-background-clip: text; background-clip: text; color: transparent; }
        p { color: #cbd5e1; line-height: 1.65; font-size: 0.95rem; margin: 0 0 1.5rem; }
        .pill {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.4rem 0.9rem;
            border-radius: 9999px;
            background: rgba(99,102,241,0.15);
            border: 1px solid rgba(99,102,241,0.3);
            font-size: 0.75rem; font-weight: 600; color: #a5b4fc;
        }
        .dot { display: inline-block; width: 6px; height: 6px; border-radius: 9999px; background: #fbbf24; box-shadow: 0 0 10px #fbbf24; animation: pulse 2s infinite; }
        @keyframes pulse { 0%,100% { opacity: 1; } 50% { opacity: 0.4; } }
        .footer { margin-top: 1.5rem; font-size: 0.75rem; color: #64748b; }
        a { color: #818cf8; text-decoration: none; }
    </style>
</head>
<body>
    <div class="orb orb-1"></div>
    <div class="orb orb-2"></div>
    <div class="card">
        <div class="icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" style="color:#fff;"><path d="M14.7 6.3a1 1 0 000 1.4l1.6 1.6a1 1 0 001.4 0l3.77-3.77a6 6 0 01-7.94 7.94l-6.91 6.91a2.12 2.12 0 01-3-3l6.91-6.91a6 6 0 017.94-7.94l-3.76 3.76z"/></svg>
        </div>
        <span class="pill"><span class="dot"></span> Mantenimiento programado</span>
        <h1 class="grad" style="margin-top:1.25rem;">Volvemos pronto</h1>
        <p><?= e($message ?? 'Estamos realizando mejoras. Volvemos pronto.') ?></p>
        <div class="footer">
            <?= e(APP_NAME) ?> · <a href="mailto:soporte@kyroscounsel.com">soporte@kyroscounsel.com</a>
        </div>
    </div>
</body>
</html>
