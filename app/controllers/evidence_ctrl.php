<?php
function evidence_ctrl_seed_template($params) {
    $caseId = (int)$params['id'];
    $case = tenant_first('cases', ['id' => $caseId]);
    if (!$case) abort(404);
    $template = evidence_template($case['case_type']);
    if (empty($template)) {
        flash_set('error', 'No hay plantilla para este tipo de caso.');
        redirect(tenant_url('cases/' . $caseId) . '#evidence');
    }
    $existing = tenant_select('case_evidence', ['name'], ['case_id' => $caseId]);
    $existingNames = array_column($existing, 'name');
    $added = 0;
    foreach ($template as $i => [$name, $cat, $req]) {
        if (in_array($name, $existingNames, true)) continue;
        tenant_insert('case_evidence', [
            'case_id' => $caseId, 'name' => $name, 'category' => $cat,
            'is_required' => $req, 'is_received' => 0, 'sort_order' => $i,
        ]);
        $added++;
    }
    flash_set('success', $added . ' items agregados desde la plantilla USCIS.');
    redirect(tenant_url('cases/' . $caseId) . '#evidence');
}

function evidence_ctrl_create($params) {
    $caseId = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $caseId])) abort(404);
    $name = trim($_POST['name'] ?? '');
    if (mb_strlen($name) < 2) {
        flash_set('error', 'Nombre requerido.');
        redirect(tenant_url('cases/' . $caseId) . '#evidence');
    }
    tenant_insert('case_evidence', [
        'case_id' => $caseId, 'name' => $name,
        'category' => $_POST['category'] ?? null,
        'is_required' => isset($_POST['is_required']) ? 1 : 0,
        'is_received' => 0, 'sort_order' => 999,
    ]);
    redirect(tenant_url('cases/' . $caseId) . '#evidence');
}

function evidence_ctrl_toggle($params) {
    $caseId = (int)$params['id'];
    $eid = (int)$params['eid'];
    $ev = tenant_first('case_evidence', ['id' => $eid, 'case_id' => $caseId]);
    if (!$ev) abort(404);
    $rec = !$ev['is_received'];
    tenant_update('case_evidence',
        ['is_received' => $rec ? 1 : 0, 'received_at' => $rec ? date('Y-m-d') : null],
        ['id' => $eid]);
    redirect(tenant_url('cases/' . $caseId) . '#evidence');
}

function evidence_ctrl_delete($params) {
    $caseId = (int)$params['id'];
    tenant_delete('case_evidence', ['id' => (int)$params['eid'], 'case_id' => $caseId]);
    redirect(tenant_url('cases/' . $caseId) . '#evidence');
}
