<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__.'/config/pushcut.json';
if (!file_exists($file)) { echo json_encode(['ok'=>true,'pushcut_url'=>'']); exit; }
$j = json_decode(file_get_contents($file), true);
echo json_encode(['ok'=>true,'pushcut_url'=>$j['pushcut_url'] ?? '']);
