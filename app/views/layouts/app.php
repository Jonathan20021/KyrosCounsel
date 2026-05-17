<!DOCTYPE html>
<html lang="es" class="bg-white">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="referrer" content="strict-origin-when-cross-origin">
    <title><?= e($title ?? APP_NAME) ?> · <?= e(APP_NAME) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=JetBrains+Mono:wght@400;500&display=swap">
    <?php require VIEWS_PATH . '/common/theme_init.php'; ?>
    <script nonce="<?= e(csp_nonce()) ?>">
      window.tailwind = window.tailwind || {};
      window.tailwind.config = {
        darkMode: 'class',
        theme: { extend: { fontFamily: { sans: ['Inter','system-ui','sans-serif'], mono: ['JetBrains Mono','monospace'] } } }
      };
    </script>
    <script src="https://cdn.tailwindcss.com" nonce="<?= e(csp_nonce()) ?>"></script>
    <link rel="stylesheet" href="<?= e(url('assets/app.css?v=' . filemtime(BASE_PATH . '/assets/app.css'))) ?>">
    <link rel="stylesheet" href="<?= e(url('assets/landing.css?v=' . filemtime(BASE_PATH . '/assets/landing.css'))) ?>">
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" nonce="<?= e(csp_nonce()) ?>"></script>
</head>
<body class="text-slate-900 antialiased min-h-screen font-sans selection:bg-indigo-200 selection:text-indigo-900">
<?php $err = flash_get('error'); $ok = flash_get('success'); ?>
<?php if ($err): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 6000)"><?= e($err) ?></div>
<?php endif; ?>
<?php if ($ok): ?>
<div class="fixed top-4 right-4 z-50 max-w-sm rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 shadow-lg fade-in"
     x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show=false, 6000)"><?= e($ok) ?></div>
<?php endif; ?>

<?= $content ?? '' ?>
</body>
</html>
