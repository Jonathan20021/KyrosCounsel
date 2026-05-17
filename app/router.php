<?php
/**
 * Router array-based con regex y middlewares.
 *
 * Una ruta:
 *   ['GET', '/path/{id:\d+}', ['ctrl_file', 'function_name'], ['auth','tenant','perm:x']]
 *
 * El controller se busca como funcion {ctrl_file}_{function_name}.
 * Ej: ['clients_ctrl','index'] -> funcion clients_ctrl_index($params)
 */

function router_dispatch(array $routes) {
    $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
    $path   = router_current_path();

    // CSRF check para metodos no seguros (excepto portal publico, donde el token actua como secreto)
    if (!in_array($method, ['GET','HEAD','OPTIONS'], true) && strpos($path, '/portal/') !== 0) {
        csrf_verify();
    }

    foreach ($routes as $route) {
        [$rmethod, $pattern, $handler] = $route;
        $middlewares = $route[3] ?? [];
        if ($rmethod !== $method) continue;

        $regex = router_compile($pattern);
        if (!preg_match($regex, $path, $m)) continue;

        $params = [];
        foreach ($m as $k => $v) {
            if (!is_int($k)) $params[$k] = $v;
        }

        foreach ($middlewares as $mw) router_apply_middleware($mw, $params);
        router_invoke($handler, $params);
        return;
    }

    abort(404, 'Pagina no encontrada.');
}

function router_current_path() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH) ?: '/';
    $script = $_SERVER['SCRIPT_NAME'] ?? '';
    $base = rtrim(str_replace('\\', '/', dirname($script)), '/');
    if ($base !== '' && strpos($path, $base) === 0) {
        $path = substr($path, strlen($base));
    }
    return '/' . trim($path, '/');
}

function router_compile($pattern) {
    $regex = preg_replace_callback('/\{([a-zA-Z_]\w*)(?::([^}]+))?\}/', function ($m) {
        $name = $m[1];
        $constr = $m[2] ?? '[^/]+';
        return '(?P<' . $name . '>' . $constr . ')';
    }, $pattern);
    return '#^' . $regex . '$#';
}

function router_apply_middleware($mw, $params) {
    if ($mw === 'guest')   { require_guest(); return; }
    if ($mw === 'auth')    { require_login(); return; }
    if ($mw === 'super_admin') { require_super_admin(); return; }
    if ($mw === 'tenant') {
        if (empty($params['tenant'])) abort(404);
        resolve_tenant($params['tenant']);
        return;
    }
    if (strpos($mw, 'perm:') === 0) {
        require_perm(substr($mw, 5));
        return;
    }
    throw new RuntimeException("Middleware desconocido: {$mw}");
}

function router_invoke($handler, $params) {
    [$file, $fn] = $handler;
    $func = $file . '_' . $fn;
    if (!function_exists($func)) {
        throw new RuntimeException("Controller no encontrado: {$func}");
    }
    $func($params);
}
