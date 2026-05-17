<?php
/**
 * 2FA TOTP nativo (RFC 6238) - sin librerias.
 * Compatible con Google Authenticator / Authy / 1Password.
 */

function totp_generate_secret($length = 20) {
    return totp_base32_encode(random_bytes($length));
}

function totp_now($secret, $time = null, $digits = 6, $period = 30) {
    if ($time === null) $time = time();
    $counter = floor($time / $period);
    $binCounter = pack('N*', 0) . pack('N*', $counter); // 8 bytes big-endian
    $key = totp_base32_decode($secret);
    $hash = hash_hmac('sha1', $binCounter, $key, true);
    $offset = ord($hash[19]) & 0xf;
    $code = (
        ((ord($hash[$offset    ]) & 0x7f) << 24) |
        ((ord($hash[$offset + 1]) & 0xff) << 16) |
        ((ord($hash[$offset + 2]) & 0xff) <<  8) |
        ( ord($hash[$offset + 3]) & 0xff)
    ) % (10 ** $digits);
    return str_pad((string)$code, $digits, '0', STR_PAD_LEFT);
}

/** Verifica con tolerancia de +/-1 ventana (90s total) */
function totp_verify($secret, $code, $time = null) {
    if ($time === null) $time = time();
    $code = preg_replace('/\D/', '', (string)$code);
    if (strlen($code) !== 6) return false;
    foreach ([-1, 0, 1] as $offset) {
        if (hash_equals(totp_now($secret, $time + $offset * 30), $code)) return true;
    }
    return false;
}

function totp_provisioning_uri($secret, $label, $issuer = 'KyrosCounsel') {
    $label = rawurlencode($issuer) . ':' . rawurlencode($label);
    $params = http_build_query([
        'secret' => $secret,
        'issuer' => $issuer,
        'algorithm' => 'SHA1',
        'digits' => 6,
        'period' => 30,
    ]);
    return "otpauth://totp/{$label}?{$params}";
}

/** QR generado por Google Charts API (simple, sin libs) */
function totp_qr_url($otpauth, $size = 200) {
    return 'https://api.qrserver.com/v1/create-qr-code/?size=' . $size . 'x' . $size .
           '&data=' . rawurlencode($otpauth);
}

// ---- Base32 (RFC 4648) ----

function totp_base32_encode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $bits = '';
    foreach (str_split($data) as $c) {
        $bits .= str_pad(decbin(ord($c)), 8, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 5) as $chunk) {
        $chunk = str_pad($chunk, 5, '0', STR_PAD_RIGHT);
        $out .= $alphabet[bindec($chunk)];
    }
    return $out;
}

function totp_base32_decode($data) {
    $alphabet = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ234567';
    $data = strtoupper(rtrim($data, '='));
    $bits = '';
    foreach (str_split($data) as $c) {
        $idx = strpos($alphabet, $c);
        if ($idx === false) continue;
        $bits .= str_pad(decbin($idx), 5, '0', STR_PAD_LEFT);
    }
    $out = '';
    foreach (str_split($bits, 8) as $chunk) {
        if (strlen($chunk) === 8) $out .= chr(bindec($chunk));
    }
    return $out;
}
