<?php
/**
 * Headers HTTP de seguridad + utilidades cripto.
 *
 * Cifrado PII: usa sodium (XChaCha20-Poly1305) si esta disponible.
 * Si no, hace fallback a openssl AES-256-GCM (tambien AEAD seguro).
 */

function send_security_headers() {
    if (headers_sent()) return;
    if (empty($GLOBALS['csp_nonce'])) {
        $GLOBALS['csp_nonce'] = bin2hex(random_bytes(16));
    }
    $nonce = $GLOBALS['csp_nonce'];
    $isProd = APP_ENV === 'production';

    // 'unsafe-eval' es requerido por el build CDN de Alpine.js 3.x (usa Function() para evaluar
    // expresiones inline x-show/x-bind/etc). Migrar a @alpinejs/csp implicaria reescribir 43+
    // expresiones como Alpine.data() — refactor desproporcionado vs el riesgo en este stack.
    $scriptSrc = "'self' 'nonce-{$nonce}' 'unsafe-eval' https://cdn.tailwindcss.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com";
    $styleSrc  = "'self' 'unsafe-inline' https://cdn.tailwindcss.com https://fonts.googleapis.com";
    $fontSrc   = "'self' data: https://fonts.gstatic.com";
    $imgSrc    = "'self' data: blob: https://api.qrserver.com";
    // connect-src incluye cdn.jsdelivr.net para que devtools pueda cargar sourcemaps (Chart.js, etc).
    $connectSrc = "'self' https://cdn.jsdelivr.net";

    $csp = [
        "default-src 'self'",
        "script-src {$scriptSrc}",
        "style-src {$styleSrc}",
        "img-src {$imgSrc}",
        "font-src {$fontSrc}",
        "connect-src {$connectSrc}",
        "frame-ancestors 'none'",
        "base-uri 'self'",
        "form-action 'self'",
        "object-src 'none'",
    ];

    header('Content-Security-Policy: ' . implode('; ', $csp));
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header('Referrer-Policy: strict-origin-when-cross-origin');
    header('Permissions-Policy: geolocation=(), microphone=(), camera=(), payment=()');
    header('X-Permitted-Cross-Domain-Policies: none');
    header_remove('X-Powered-By');
    if ($isProd) {
        header('Strict-Transport-Security: max-age=63072000; includeSubDomains; preload');
    }
}

function csp_nonce() { return $GLOBALS['csp_nonce'] ?? ''; }

// ===================================================================
// Cifrado de PII
// ===================================================================

function _crypto_use_sodium() {
    return function_exists('sodium_crypto_secretbox')
        && defined('SODIUM_CRYPTO_SECRETBOX_KEYBYTES');
}

function _crypto_master_key() {
    $raw = base64_decode(ENCRYPTION_KEY, true);
    if ($raw === false) {
        throw new RuntimeException('ENCRYPTION_KEY no es base64 valido.');
    }
    // Aceptamos cualquier longitud >= 32; truncamos/expandimos a 32
    if (strlen($raw) < 32) {
        $raw = hash_hkdf('sha256', $raw, 32, 'master');
    } else {
        $raw = substr($raw, 0, 32);
    }
    return $raw;
}

function _crypto_tenant_key($tenant_id = null) {
    $master = _crypto_master_key();
    if ($tenant_id === null) return $master;
    return hash_hkdf('sha256', $master, 32, 'tenant:' . (int)$tenant_id);
}

function pii_encrypt($plain, $tenant_id = null) {
    if ($plain === null || $plain === '') return null;
    $key = _crypto_tenant_key($tenant_id);

    if (_crypto_use_sodium()) {
        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = sodium_crypto_secretbox((string)$plain, $nonce, $key);
        if (function_exists('sodium_memzero')) sodium_memzero($key);
        return 'sb:' . base64_encode($nonce . $cipher);
    }

    // Fallback: openssl AES-256-GCM (AEAD)
    $iv = random_bytes(12);
    $tag = '';
    $cipher = openssl_encrypt((string)$plain, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
    if ($cipher === false) throw new RuntimeException('openssl_encrypt fallo');
    return 'gc:' . base64_encode($iv . $tag . $cipher);
}

function pii_decrypt($payload, $tenant_id = null) {
    if ($payload === null || $payload === '') return null;
    $prefix = substr($payload, 0, 3);
    $b64    = substr($payload, 3);
    $raw    = base64_decode($b64, true);
    if ($raw === false) throw new RuntimeException('Ciphertext invalido.');
    $key = _crypto_tenant_key($tenant_id);

    if ($prefix === 'sb:') {
        if (!_crypto_use_sodium()) throw new RuntimeException('Necesita sodium para descifrar.');
        $nonce = substr($raw, 0, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $cipher = substr($raw, SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);
        $plain = sodium_crypto_secretbox_open($cipher, $nonce, $key);
        if (function_exists('sodium_memzero')) sodium_memzero($key);
        if ($plain === false) throw new RuntimeException('Fallo descifrado.');
        return $plain;
    }
    if ($prefix === 'gc:') {
        $iv = substr($raw, 0, 12);
        $tag = substr($raw, 12, 16);
        $cipher = substr($raw, 28);
        $plain = openssl_decrypt($cipher, 'aes-256-gcm', $key, OPENSSL_RAW_DATA, $iv, $tag);
        if ($plain === false) throw new RuntimeException('Fallo descifrado AES-GCM.');
        return $plain;
    }
    throw new RuntimeException('Formato de ciphertext desconocido.');
}

function pii_blind_index($value) {
    $key = _crypto_master_key();
    $hash = hash_hmac('sha256', mb_strtolower(trim((string)$value)), $key, true);
    return bin2hex($hash);
}
