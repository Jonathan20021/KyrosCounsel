<?php
/**
 * Payment links publicos. El token actua como secreto.
 * Stripe Checkout (real o simulado segun config).
 */

function _pay_resolve($token) {
    if (!preg_match('/^[a-f0-9]{40}$/', $token)) abort(404);
    $row = db_one(
        'SELECT pl.*, c.case_number, c.title AS case_title,
                cl.first_name, cl.last_name, cl.email AS client_email,
                t.name AS tenant_name, t.brand_color, t.country,
                cp.concept
         FROM payment_links pl
         JOIN cases c ON c.id = pl.case_id
         JOIN clients cl ON cl.id = c.client_id
         JOIN tenants t ON t.id = pl.tenant_id
         JOIN case_payments cp ON cp.id = pl.payment_id
         WHERE pl.token = :tok LIMIT 1', ['tok' => $token]
    );
    if (!$row) abort(404);
    if ($row['status'] === 'expired' || strtotime($row['expires_at']) < time()) {
        abort(410, 'Este link de pago ha expirado.');
    }
    return $row;
}

function pay_ctrl_show($params) {
    $link = _pay_resolve($params['token']);
    render_with_layout('app', 'pay.show', [
        'title' => 'Pago — ' . $link['tenant_name'],
        'link' => $link,
    ]);
}

function pay_ctrl_checkout($params) {
    $link = _pay_resolve($params['token']);
    if ($link['status'] === 'paid') {
        redirect(url('pay/' . $link['token'] . '/success'));
    }
    $success_url = url('pay/' . $link['token'] . '/success?session_id={CHECKOUT_SESSION_ID}');
    $cancel_url  = url('pay/' . $link['token'] . '/cancel');
    $description = $link['concept'] . ' (' . $link['case_number'] . ')';

    [$ok, $sessionId, $checkoutUrl] = stripe_create_session(
        (float)$link['amount_usd'], $description, $success_url, $cancel_url
    );
    if (!$ok) abort(500, 'No se pudo crear la sesion de pago.');

    db_run('UPDATE payment_links SET stripe_session_id = :sid WHERE id = :id',
        ['sid' => $sessionId, 'id' => $link['id']]);

    if (stripe_is_real() && $checkoutUrl) {
        redirect($checkoutUrl);
    }
    // Simulado: redirige directamente a success con el sim id (UX-equivalente al flow Stripe real)
    redirect(url('pay/' . $link['token'] . '/success?session_id=' . urlencode($sessionId)));
}

function pay_ctrl_success($params) {
    $link = _pay_resolve($params['token']);
    if ($link['status'] !== 'paid') {
        // Verifica con Stripe (o simulacion auto-aprueba)
        $sess = stripe_retrieve_session($link['stripe_session_id'] ?? '');
        if (($sess['payment_status'] ?? '') === 'paid') {
            db_run('UPDATE payment_links SET status = "paid", paid_at = NOW() WHERE id = :id', ['id' => $link['id']]);
            db_run('UPDATE case_payments SET status = "paid", paid_at = NOW(), method = "card" WHERE id = :id',
                ['id' => $link['payment_id']]);
            audit_record('payment.paid_online', null, [
                'payment_id' => $link['payment_id'], 'amount' => $link['amount_usd'],
                'session' => $link['stripe_session_id'] ?? null,
            ], (int)$link['tenant_id']);
            // Refresca para mostrar status pagado
            $link['status'] = 'paid';
        }
    }
    render_with_layout('app', 'pay.success', [
        'title' => 'Pago confirmado',
        'link' => $link,
    ]);
}

function pay_ctrl_cancel($params) {
    $link = _pay_resolve($params['token']);
    render_with_layout('app', 'pay.cancel', [
        'title' => 'Pago cancelado',
        'link' => $link,
    ]);
}
