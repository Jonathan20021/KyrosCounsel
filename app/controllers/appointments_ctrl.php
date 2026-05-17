<?php
function appointments_ctrl_create($params) {
    $caseId = (int)$params['id'];
    if (!tenant_first('cases', ['id' => $caseId])) abort(404);
    $title = trim($_POST['title'] ?? '');
    $date  = trim($_POST['date'] ?? '');
    $time  = trim($_POST['time'] ?? '09:00');
    if (mb_strlen($title) < 2 || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
        flash_set('error', 'Titulo y fecha requeridos.');
        redirect(tenant_url('cases/' . $caseId) . '#appointments');
    }
    tenant_insert('case_appointments', [
        'case_id' => $caseId,
        'title'   => $title,
        'type'    => in_array($_POST['type'] ?? 'consultation', ['interview','biometrics','consultation','court','other'], true) ? $_POST['type'] : 'consultation',
        'starts_at' => $date . ' ' . (preg_match('/^\d{2}:\d{2}$/', $time) ? $time : '09:00') . ':00',
        'duration_min' => max(15, min(480, (int)($_POST['duration_min'] ?? 60))),
        'location' => trim($_POST['location'] ?? '') ?: null,
        'notes'    => trim($_POST['notes'] ?? '') ?: null,
        'status'   => 'scheduled',
    ]);
    flash_set('success', 'Cita agendada.');
    redirect(tenant_url('cases/' . $caseId) . '#appointments');
}

function appointments_ctrl_delete($params) {
    $caseId = (int)$params['id'];
    tenant_delete('case_appointments', ['id' => (int)$params['aid'], 'case_id' => $caseId]);
    redirect(tenant_url('cases/' . $caseId) . '#appointments');
}
