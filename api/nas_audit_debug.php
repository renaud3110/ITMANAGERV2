<?php
header('Content-Type: application/json; charset=utf-8');
$r = ['method'=>$_SERVER['REQUEST_METHOD'], 'ct'=>$_SERVER['CONTENT_TYPE']??'', 'post'=>$_POST, 'files'=>array_keys($_FILES)];
if (!empty($_FILES['audit_text']['tmp_name'])) {
  $r['audit_len']=filesize($_FILES['audit_text']['tmp_name']);
  $r['preview']=substr(file_get_contents($_FILES['audit_text']['tmp_name']),0,150);
}
echo json_encode($r);
