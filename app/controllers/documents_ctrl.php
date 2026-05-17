<?php
/**
 * Documentos: upload cifrado en disco con sodium_secretstream.
 * Cada archivo se cifra con clave derivada del tenant. La clave nunca
 * sale del proceso.
 */

const ALLOWED_MIME_PREFIXES = ['image/', 'application/pdf', 'application/msword',
    'application/vnd.openxmlformats-officedocument', 'text/plain', 'text/csv'];
const MAX_UPLOAD_BYTES = 50 * 1024 * 1024; // 50MB

function documents_ctrl_index($params) {
    $tid = current_tenant()['id'];
    $docs = db_select(
        'SELECT d.*, c.case_number, cl.first_name, cl.last_name FROM documents d
         LEFT JOIN cases c ON c.id = d.case_id
         LEFT JOIN clients cl ON cl.id = d.client_id
         WHERE d.tenant_id = :t ORDER BY d.created_at DESC LIMIT 500',
        ['t' => $tid]
    );
    render_with_layout('tenant', 'documents.index', ['title' => 'Documentos', 'docs' => $docs]);
}

function documents_ctrl_upload($params) {
    $tid = current_tenant()['id'];
    if (empty($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
        flash_set('error', 'Error en el upload.');
        redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('documents'));
    }
    $f = $_FILES['file'];
    if ($f['size'] > MAX_UPLOAD_BYTES) {
        flash_set('error', 'Archivo demasiado grande (max 50MB).');
        redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('documents'));
    }
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime  = finfo_file($finfo, $f['tmp_name']);
    finfo_close($finfo);

    $allowed = false;
    foreach (ALLOWED_MIME_PREFIXES as $p) if (strpos($mime, $p) === 0) { $allowed = true; break; }
    if (!$allowed) {
        flash_set('error', 'Tipo de archivo no permitido: ' . $mime);
        redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('documents'));
    }

    // Sanitize filename
    $orig = $f['name'];
    $safeName = preg_replace('/[^A-Za-z0-9._\-]/u', '_', $orig);
    if (strlen($safeName) > 200) $safeName = substr($safeName, 0, 200);

    // Storage
    $dir = BASE_PATH . '/uploads/' . $tid;
    if (!is_dir($dir)) @mkdir($dir, 0700, true);
    // Bloquear ejecucion en /uploads via .htaccess
    $ht = $dir . '/.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht, "Require all denied\nphp_flag engine off\n");
    }

    $relName = bin2hex(random_bytes(16)) . '.enc';
    $absPath = $dir . '/' . $relName;
    $relPath = $tid . '/' . $relName;

    // Cifrado del archivo (sodium si esta, openssl si no)
    $plain = file_get_contents($f['tmp_name']);
    if ($plain === false) abort(500, 'No se pudo leer archivo.');
    $sha = hash('sha256', $plain);
    $blob = pii_encrypt($plain, -$tid); // -tid para diferenciar dominio "documentos"
    file_put_contents($absPath, $blob);
    @chmod($absPath, 0600);

    $id = tenant_insert('documents', [
        'case_id'      => (int)($_POST['case_id'] ?? 0) ?: null,
        'client_id'    => (int)($_POST['client_id'] ?? 0) ?: null,
        'name'         => $safeName,
        'category'     => isset(DOCUMENT_CATEGORIES[$_POST['category'] ?? '']) ? $_POST['category'] : 'other',
        'mime_type'    => $mime,
        'size_bytes'   => (int)$f['size'],
        'storage_path' => $relPath,
        'sha256_hash'  => $sha,
        'encrypted'    => 1,
        'uploaded_by'  => $_SESSION['user_id'] ?? null,
    ]);
    audit_record('document.uploaded', $_SESSION['user_id'] ?? null,
        ['id' => $id, 'name' => $safeName, 'size' => $f['size']]);
    workflow_dispatch('document.uploaded', [
        'document_id' => $id, 'case_id' => (int)($_POST['case_id'] ?? 0) ?: null,
        'category' => $_POST['category'] ?? 'other',
    ]);
    flash_set('success', 'Documento subido y cifrado.');
    redirect($_SERVER['HTTP_REFERER'] ?? tenant_url('documents'));
}

function documents_ctrl_download($params) {
    $id = (int)$params['id'];
    $tid = current_tenant()['id'];
    $doc = tenant_first('documents', ['id' => $id]);
    if (!$doc) abort(404);

    $abs = BASE_PATH . '/uploads/' . $doc['storage_path'];
    if (!is_file($abs)) abort(404, 'Archivo perdido.');

    $blob = file_get_contents($abs);
    if ($blob === false || strlen($blob) < 30) abort(500, 'Archivo corrupto.');
    try {
        $plain = pii_decrypt($blob, -$tid);
    } catch (Throwable $e) {
        abort(500, 'No se pudo descifrar.');
    }

    if (hash('sha256', $plain) !== $doc['sha256_hash']) {
        security_log('document.integrity_failed', ['id' => $id]);
        abort(500, 'Verificacion de integridad fallida.');
    }

    audit_record('document.downloaded', $_SESSION['user_id'] ?? null, ['id' => $id]);

    while (ob_get_level()) ob_end_clean();
    header('Content-Type: ' . $doc['mime_type']);
    header('Content-Length: ' . strlen($plain));
    header('Content-Disposition: attachment; filename="' . $doc['name'] . '"');
    header('X-Content-Type-Options: nosniff');
    echo $plain;
    exit;
}

function documents_ctrl_delete($params) {
    $id = (int)$params['id'];
    $doc = tenant_first('documents', ['id' => $id]);
    if (!$doc) abort(404);
    @unlink(BASE_PATH . '/uploads/' . $doc['storage_path']);
    tenant_delete('documents', ['id' => $id]);
    audit_record('document.deleted', $_SESSION['user_id'] ?? null, ['id' => $id]);
    flash_set('success', 'Documento eliminado.');
    redirect(tenant_url('documents'));
}
