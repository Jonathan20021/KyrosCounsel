<?php
/**
 * Cliente Stripe minimalista (curl directo, sin SDK).
 * Si STRIPE_SECRET_KEY no esta seteado, usa modo SIMULADO (paga con un click).
 *
 * Doc: https://stripe.com/docs/api/checkout/sessions/create
 */

if (!defined('STRIPE_SECRET_KEY')) define('STRIPE_SECRET_KEY', '');
if (!defined('STRIPE_PUBLIC_KEY')) define('STRIPE_PUBLIC_KEY', '');

function stripe_is_real() {
    return STRIPE_SECRET_KEY !== '' && strpos(STRIPE_SECRET_KEY, 'sk_') === 0;
}

/**
 * Crea Checkout Session en Stripe.
 * Devuelve [success, session_id, checkout_url].
 */
function stripe_create_session($amount_usd, $description, $success_url, $cancel_url) {
    if (!stripe_is_real()) {
        // Modo simulado: devuelve un id falso y el success_url ya construido
        return [true, 'sim_' . bin2hex(random_bytes(8)), null];
    }
    $cents = (int)round($amount_usd * 100);
    $payload = [
        'mode' => 'payment',
        'line_items[0][price_data][currency]' => 'usd',
        'line_items[0][price_data][unit_amount]' => $cents,
        'line_items[0][price_data][product_data][name]' => $description,
        'line_items[0][quantity]' => 1,
        'success_url' => $success_url,
        'cancel_url'  => $cancel_url,
    ];
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => http_build_query($payload),
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $resp = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($code >= 200 && $code < 300) {
        $data = json_decode($resp, true);
        return [true, $data['id'] ?? null, $data['url'] ?? null];
    }
    security_log('stripe.create_session_failed', ['http' => $code, 'response' => substr((string)$resp, 0, 500)]);
    return [false, null, null];
}

/**
 * Verifica el estado de una Checkout Session (real Stripe).
 */
function stripe_retrieve_session($session_id) {
    if (!stripe_is_real() || strpos($session_id, 'sim_') === 0) {
        return ['payment_status' => 'paid'];  // simulacion: siempre se considera pagado
    }
    $ch = curl_init('https://api.stripe.com/v1/checkout/sessions/' . urlencode($session_id));
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_USERPWD        => STRIPE_SECRET_KEY . ':',
        CURLOPT_TIMEOUT        => 15,
    ]);
    $resp = curl_exec($ch);
    curl_close($ch);
    return json_decode($resp, true) ?: [];
}
