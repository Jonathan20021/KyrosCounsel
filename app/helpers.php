<?php
/**
 * Helpers globales: escape, csrf, redirect, flash, view rendering.
 */

/** Escape HTML. SIEMPRE usar al imprimir variables en vistas. */
function e($v) {
    return htmlspecialchars((string)$v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
}

/**
 * Genera un par de colores deterministas (gradient + texto) para un nombre.
 * Cada usuario tiene SU paleta consistente entre vistas.
 * Devuelve [bg_gradient_css, text_color].
 */
function avatar_colors(string $seed): array {
    static $palettes = null;
    if ($palettes === null) {
        $palettes = [
            ['linear-gradient(135deg,#6366f1,#8b5cf6)', '#ffffff'],
            ['linear-gradient(135deg,#ec4899,#f43f5e)', '#ffffff'],
            ['linear-gradient(135deg,#10b981,#14b8a6)', '#ffffff'],
            ['linear-gradient(135deg,#f59e0b,#f97316)', '#ffffff'],
            ['linear-gradient(135deg,#3b82f6,#06b6d4)', '#ffffff'],
            ['linear-gradient(135deg,#a855f7,#d946ef)', '#ffffff'],
            ['linear-gradient(135deg,#ef4444,#f97316)', '#ffffff'],
            ['linear-gradient(135deg,#0ea5e9,#6366f1)', '#ffffff'],
            ['linear-gradient(135deg,#14b8a6,#22c55e)', '#ffffff'],
            ['linear-gradient(135deg,#8b5cf6,#ec4899)', '#ffffff'],
            ['linear-gradient(135deg,#0891b2,#0ea5e9)', '#ffffff'],
            ['linear-gradient(135deg,#f43f5e,#a855f7)', '#ffffff'],
        ];
    }
    $hash = abs(crc32(mb_strtolower(trim($seed))));
    return $palettes[$hash % count($palettes)];
}

/** Iniciales (1-2 letras) de un nombre completo. */
function avatar_initials(string $name, int $max = 2): string {
    $parts = preg_split('/\s+/', trim($name));
    if (!$parts || $parts[0] === '') return '?';
    $out = '';
    foreach (array_slice($parts, 0, $max) as $p) {
        $out .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    return $out !== '' ? $out : '?';
}

/**
 * "Hace 5 min", "ayer", "hace 3 días"... Útil para activity feeds.
 */
function time_ago($datetime, ?string $now = null): string {
    $ts = is_int($datetime) ? $datetime : strtotime((string)$datetime);
    if (!$ts) return '—';
    $now = $now ? strtotime($now) : time();
    $diff = $now - $ts;

    if ($diff < 0) return 'en el futuro';
    if ($diff < 45)        return 'hace un momento';
    if ($diff < 90)        return 'hace 1 min';
    if ($diff < 3600)      return 'hace ' . round($diff / 60) . ' min';
    if ($diff < 5400)      return 'hace 1 hora';
    if ($diff < 86400)     return 'hace ' . round($diff / 3600) . ' horas';
    if ($diff < 172800)    return 'ayer';
    if ($diff < 604800)    return 'hace ' . round($diff / 86400) . ' días';
    if ($diff < 1209600)   return 'hace 1 semana';
    if ($diff < 2419200)   return 'hace ' . round($diff / 604800) . ' semanas';
    if ($diff < 4838400)   return 'hace 1 mes';
    if ($diff < 29030400)  return 'hace ' . round($diff / 2419200) . ' meses';
    return 'hace ' . round($diff / 29030400) . ' años';
}

/**
 * Fecha legible para humanos: "Hoy 14:30", "Ayer", "Mar 15", "Mar 2025".
 */
function human_date($datetime, bool $with_time = false): string {
    $ts = is_int($datetime) ? $datetime : strtotime((string)$datetime);
    if (!$ts) return '—';
    $today = strtotime('today');
    $tomorrow = $today + 86400;
    $yesterday = $today - 86400;
    $time = $with_time ? ' ' . date('H:i', $ts) : '';

    if ($ts >= $today && $ts < $tomorrow)    return 'Hoy' . $time;
    if ($ts >= $tomorrow && $ts < $tomorrow + 86400) return 'Mañana' . $time;
    if ($ts >= $yesterday && $ts < $today)   return 'Ayer' . $time;

    $months_es = [1=>'Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
    $year = date('Y', $ts);
    if ($year === date('Y')) {
        return $months_es[(int)date('n', $ts)] . ' ' . (int)date('j', $ts) . $time;
    }
    return $months_es[(int)date('n', $ts)] . ' ' . $year;
}

/**
 * Badge HTML para statuses (casos, tareas, prioridades, invoices, etc.).
 * Devuelve un span estilizado con clases consistentes.
 */
function status_badge(string $status, string $kind = 'case'): string {
    static $maps = [
        'case' => [
            'draft'      => ['Borrador', 'badge-neutral',  '○'],
            'preparing'  => ['Preparando','badge-info',    '▸'],
            'filed'      => ['Filed',    'badge-info',     '↗'],
            'biometrics' => ['Biometría','badge-warning',  '◆'],
            'rfe'        => ['RFE',      'badge-warning',  '⚠'],
            'interview'  => ['Entrevista','badge-info',    '👤'],
            'hearing'    => ['Audiencia','badge-info',     '⚖'],
            'approved'   => ['Aprobado', 'badge-success',  '✓'],
            'denied'     => ['Denegado', 'badge-danger',   '✗'],
            'closed'     => ['Cerrado',  'badge-neutral',  '·'],
            'withdrawn'  => ['Retirado', 'badge-neutral',  '←'],
        ],
        'task' => [
            'pending'    => ['Pendiente','badge-warning',  '○'],
            'completed'  => ['Hecha',    'badge-success',  '✓'],
            'cancelled'  => ['Cancelada','badge-neutral',  '✗'],
        ],
        'priority' => [
            'urgent'     => ['Urgente',  'badge-danger',   '!'],
            'high'       => ['Alta',     'badge-warning',  '↑'],
            'normal'     => ['Normal',   'badge-info',     '·'],
            'low'        => ['Baja',     'badge-neutral',  '↓'],
        ],
        'invoice' => [
            'paid'       => ['Pagada',   'badge-success',  '✓'],
            'pending'    => ['Pendiente','badge-warning',  '○'],
            'failed'     => ['Fallida',  'badge-danger',   '✗'],
            'refunded'   => ['Reembol.', 'badge-neutral',  '↩'],
        ],
        'tenant' => [
            'active'     => ['Activo',   'badge-success',  '✓'],
            'trial'      => ['Trial',    'badge-info',     '⏱'],
            'suspended'  => ['Suspendido','badge-warning', '⏸'],
            'cancelled'  => ['Cancelado','badge-danger',   '✗'],
            'pending'    => ['Pendiente','badge-neutral',  '○'],
        ],
    ];
    $map = $maps[$kind] ?? $maps['case'];
    [$label, $class, $icon] = $map[$status] ?? [$status, 'badge-neutral', '·'];
    return '<span class="badge-soft ' . $class . '"><span class="opacity-70">' . e($icon) . '</span> ' . e($label) . '</span>';
}

/**
 * Renderiza avatar inline con iniciales y colores deterministas.
 * Tamaños: sm | md | lg | xl
 */
function avatar_render(string $name, string $size = 'md', ?string $seed = null): string {
    $sizes = [
        'sm' => 'h-7 w-7 text-[11px]',
        'md' => 'h-9 w-9 text-sm',
        'lg' => 'h-12 w-12 text-base',
        'xl' => 'h-16 w-16 text-xl',
    ];
    $cls = $sizes[$size] ?? $sizes['md'];
    [$bg, $fg] = avatar_colors($seed ?? $name);
    $initials = avatar_initials($name);
    return '<span class="inline-flex items-center justify-center rounded-full font-semibold flex-shrink-0 ' . $cls
        . '" style="background:' . $bg . ';color:' . $fg . ';">' . e($initials) . '</span>';
}

/** URL absoluta dentro de la app */
function url($path = '') {
    return rtrim(APP_URL, '/') . '/' . ltrim($path, '/');
}

/** URL dentro del tenant activo */
function tenant_url($path = '') {
    $t = current_tenant();
    if ($t) return url('t/' . $t['slug'] . '/' . ltrim($path, '/'));
    return url($path);
}

function redirect($path) {
    // Anti open-redirect: solo URLs internas
    if (preg_match('#^https?://#i', $path)) {
        if (strpos($path, APP_URL) !== 0) $path = APP_URL;
    }
    if ($path[0] !== '/' && strpos($path, 'http') !== 0) {
        $path = url($path);
    }
    header('Location: ' . $path);
    exit;
}

function abort($code, $message = '') {
    http_response_code($code);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!doctype html><meta charset="utf-8"><title>' . $code . '</title>';
    echo '<div style="font-family:system-ui;padding:40px;text-align:center">';
    echo '<h1 style="font-size:48px;margin:0">' . $code . '</h1>';
    echo '<p>' . e($message ?: 'Error') . '</p></div>';
    exit;
}

// ----- CSRF -----

function csrf_token() {
    if (empty($_SESSION['_csrf_token']) ||
        time() - ($_SESSION['_csrf_time'] ?? 0) > 1800) {
        $_SESSION['_csrf_token'] = bin2hex(random_bytes(32));
        $_SESSION['_csrf_time']  = time();
    }
    return $_SESSION['_csrf_token'];
}

function csrf_field() {
    return '<input type="hidden" name="_csrf" value="' . e(csrf_token()) . '">';
}

function csrf_verify() {
    $expected = $_SESSION['_csrf_token'] ?? '';
    $provided = $_POST['_csrf'] ?? ($_SERVER['HTTP_X_CSRF_TOKEN'] ?? '');
    if ($expected === '' || $provided === '' || !hash_equals($expected, $provided)) {
        error_log('[security] CSRF token invalido ip=' . ($_SERVER['REMOTE_ADDR'] ?? ''));
        abort(419, 'Token CSRF invalido o expirado.');
    }
}

// ----- Flash messages -----

function flash_set($key, $msg) {
    $_SESSION['_flash'][$key] = $msg;
}

function flash_get($key) {
    if (!isset($_SESSION['_flash'][$key])) return null;
    $msg = $_SESSION['_flash'][$key];
    unset($_SESSION['_flash'][$key]);
    return $msg;
}

function old($key, $default = '') {
    return $_SESSION['_old'][$key] ?? $default;
}

function old_clear() {
    unset($_SESSION['_old']);
}

// ----- Views -----

function view($name, $data = []) {
    $file = VIEWS_PATH . '/' . str_replace('.', '/', $name) . '.php';
    if (!is_file($file)) {
        throw new RuntimeException("Vista no encontrada: {$name}");
    }
    extract($data, EXTR_SKIP);
    ob_start();
    require $file;
    $content = ob_get_clean();
    echo $content;
}

function render_with_layout($layout, $view_name, $data = []) {
    extract($data, EXTR_SKIP);
    $view_file = VIEWS_PATH . '/' . str_replace('.', '/', $view_name) . '.php';
    if (!is_file($view_file)) {
        throw new RuntimeException("Vista no encontrada: {$view_name}");
    }
    ob_start();
    require $view_file;
    $content = ob_get_clean();

    $layout_file = VIEWS_PATH . '/layouts/' . $layout . '.php';
    if (!is_file($layout_file)) {
        throw new RuntimeException("Layout no encontrado: {$layout}");
    }
    require $layout_file;
}

// ----- Logger simple -----

function app_log($message, $context = []) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    @file_put_contents(STORAGE_PATH . '/logs/app.log', $line . PHP_EOL, FILE_APPEND);
}

function security_log($message, $context = []) {
    $line = '[' . date('Y-m-d H:i:s') . '] ' . $message;
    if ($context) $line .= ' ' . json_encode($context, JSON_UNESCAPED_UNICODE);
    @file_put_contents(STORAGE_PATH . '/logs/security.log', $line . PHP_EOL, FILE_APPEND);
}

// ----- Validacion minima -----

function is_valid_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false && strlen($email) <= 191;
}

function is_valid_slug($slug) {
    return is_string($slug) && preg_match('/^[a-z0-9][a-z0-9-]{1,62}$/', $slug);
}
