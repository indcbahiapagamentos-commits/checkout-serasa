<?php
header('Content-Type: application/json; charset=utf-8');
$file = __DIR__ . '/config/pushcut.json';
if (!file_exists($file)) { echo json_encode(['ok'=>false,'error'=>'Configure a URL primeiro']); exit; }
$cfg = json_decode(file_get_contents($file), true);
$url = trim($cfg['pushcut_url'] ?? '');
if ($url === '') { echo json_encode(['ok'=>false,'error'=>'URL vazia']); exit; }

$title = $_POST['title'] ?? 'Teste do Painel';
$text  = $_POST['text']  ?? 'Notificação de teste';

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 12);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(['title'=>$title,'text'=>$text], JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES));
$resp = curl_exec($ch);
$code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$err  = curl_error($ch);
curl_close($ch);

echo json_encode($code>=200 && $code<300 ? ['ok'=>true] : ['ok'=>false,'http'=>$code,'error'=>$err,'resp'=>$resp]);
