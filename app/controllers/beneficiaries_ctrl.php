<?php
function beneficiaries_ctrl_create($params) {
    $caseId = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $caseId])) abort(404);
    $first = trim($_POST['first_name'] ?? '');
    $last  = trim($_POST['last_name'] ?? '');
    $rel   = $_POST['relationship'] ?? '';
    if (mb_strlen($first) < 1 || mb_strlen($last) < 1 || $rel === '') {
        flash_set('error', 'Datos incompletos.');
        redirect(tenant_url('cases/' . $caseId));
    }
    $tid = current_tenant()['id'];
    tenant_insert('case_beneficiaries', [
        'case_id'      => $caseId,
        'first_name'   => $first,
        'last_name'    => $last,
        'relationship' => $rel,
        'date_of_birth'=> trim($_POST['date_of_birth'] ?? '') ?: null,
        'nationality'  => isset(COUNTRIES[$_POST['nationality'] ?? '']) ? $_POST['nationality'] : null,
        'passport_enc' => trim($_POST['passport'] ?? '') ? pii_encrypt($_POST['passport'], $tid) : null,
        'alien_number_enc' => trim($_POST['alien_number'] ?? '') ? pii_encrypt($_POST['alien_number'], $tid) : null,
        'notes'        => trim($_POST['notes'] ?? '') ?: null,
    ]);
    flash_set('success', 'Beneficiario agregado.');
    redirect(tenant_url('cases/' . $caseId) . '#beneficiaries');
}

function beneficiaries_ctrl_delete($params) {
    $caseId = (int)$params['id'];
    $bid = (int)$params['bid'];
    tenant_delete('case_beneficiaries', ['id' => $bid, 'case_id' => $caseId]);
    flash_set('success', 'Beneficiario eliminado.');
    redirect(tenant_url('cases/' . $caseId) . '#beneficiaries');
}
