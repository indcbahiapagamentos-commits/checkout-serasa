<?php
header('Content-Type: application/json; charset=utf-8');

function read_pushcut_url() {
  $body = file_get_contents('php://input');
  if ($body) {
    $j = json_decode($body, true);
    if (json_last_error() === JSON_ERROR_NONE && isset($j['pushcut_url'])) {
      return trim($j['pushcut_url']);
    }
  }
  if (isset($_POST['pushcut_url'])) return trim($_POST['pushcut_url']);
  if (isset($_GET['pushcut_url']))  return trim($_GET['pushcut_url']);
  return '';
}

$url = read_pushcut_url();
if ($url === '') { echo json_encode(['ok'=>false,'error'=>'URL vazia']); exit; }

$cfgDir = __DIR__ . '/config';
if (!is_dir($cfgDir) && !mkdir($cfgDir, 0775, true)) {
  echo json_encode(['ok'=>false,'error'=>'Não foi possível criar config/']); exit;
}

$file = $cfgDir . '/pushcut.json';
$payload = json_encode(['pushcut_url'=>$url], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE|JSON_PRETTY_PRINT);
if (@file_put_contents($file, $payload) === false) {
  echo json_encode(['ok'=>false,'error'=>'Falha ao salvar', 'file'=>$file]); exit;
}
echo json_encode(['ok'=>true,'saved'=>$url]);
