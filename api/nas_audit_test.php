<?php
header('Content-Type: application/json; charset=utf-8');
$audit = '';
if (!empty($_FILES['audit_text']['tmp_name']) && is_uploaded_file($_FILES['audit_text']['tmp_name'])) {
    $audit = file_get_contents($_FILES['audit_text']['tmp_name']);
}
$nasId = (int)($_POST['nas_id'] ?? 0);
echo json_encode([
    'ok' => true,
    'msg' => 'Test OK',
    'nas_id' => $nasId,
    'audit_received' => strlen($audit) > 0,
    'audit_length' => strlen($audit),
    'preview' => substr($audit, 0, 100)
], JSON_UNESCAPED_UNICODE);
