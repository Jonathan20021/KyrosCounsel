<?php
/**
 * Cliente Resend minimalista (curl directo, sin SDK).
 * Doc: https://resend.com/docs/api-reference/emails/send-email
 *
 * Si RESEND_API_KEY no esta configurada, hace dry-run (loguea pero no envia).
 */

if (!defined('RESEND_API_KEY')) define('RESEND_API_KEY', '');
if (!defined('RESEND_FROM'))     define('RESEND_FROM', 'KyrosCounsel <onboarding@resend.dev>');

function send_email($to, $subject, $html, $template = null, $tenant_id = null) {
    $row_id = null;
    try {
        // log queued
        db_run('INSERT INTO email_log (tenant_id, to_email, subject, template, status)
                VALUES (:t, :to, :s, :tpl, :st)', [
            't' => $tenant_id, 'to' => $to, 's' => $subject, 'tpl' => $template, 'st' => 'queued',
        ]);
        $row_id = (int)get_db()->lastInsertId();
    } catch (Throwable $e) {
        // si la tabla no existe aun (preinstall), continuamos
    }

    if (RESEND_API_KEY === '') {
        // Dry run: loguea contenido para que veas el mail en /storage/logs
        app_log('[email.dryrun] ' . $subject . ' -> ' . $to);
        @file_put_contents(STORAGE_PATH . '/logs/emails-dryrun.log',
            '[' . date('Y-m-d H:i:s') . "] TO: {$to}\nSUBJECT: {$subject}\n\n{$html}\n\n---\n\n",
            FILE_APPEND);
        if ($row_id) {
            db_run('UPDATE email_log SET status=:st, sent_at=:t WHERE id=:id',
                ['st' => 'sent', 't' => date('Y-m-d H:i:s'), 'id' => $row_id]);
        }
        return [true, 'dry-run'];
    }

    $payload = json_encode([
        'from'    => RESEND_FROM,
        'to'      => is_array($to) ? $to : [$to],
        'subject' => $subject,
        'html'    => $html,
    ]);

    $ch = curl_init('https://api.resend.com/emails');
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $payload,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . RESEND_API_KEY,
            'Content-Type: application/json',
        ],
        CURLOPT_TIMEOUT        => 15,
        CURLOPT_SSL_VERIFYPEER => true,
    ]);
    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $err  = curl_error($ch);
    curl_close($ch);

    if ($code >= 200 && $code < 300) {
        $data = json_decode($response, true);
        $providerId = $data['id'] ?? null;
        if ($row_id) {
            db_run('UPDATE email_log SET status=:st, provider_id=:pid, sent_at=:t WHERE id=:id',
                ['st' => 'sent', 'pid' => $providerId, 't' => date('Y-m-d H:i:s'), 'id' => $row_id]);
        }
        return [true, $providerId];
    }

    $msg = $err ?: ('HTTP ' . $code . ': ' . substr((string)$response, 0, 500));
    if ($row_id) {
        db_run('UPDATE email_log SET status=:st, error_message=:e WHERE id=:id',
            ['st' => 'failed', 'e' => $msg, 'id' => $row_id]);
    }
    security_log('email.failed', ['to' => $to, 'error' => $msg]);
    return [false, $msg];
}
