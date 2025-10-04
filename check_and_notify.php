<?php
header('Content-Type: application/json; charset=utf-8');

// Config
$cfgFile = __DIR__ . '/config/pushcut.json';
if (!file_exists($cfgFile)) { echo json_encode(['ok'=>false,'error'=>'Configure a URL do Pushcut']); exit; }
$cfg = json_decode(file_get_contents($cfgFile), true);
$pushcutUrl = trim($cfg['pushcut_url'] ?? '');
if ($pushcutUrl === '') { echo json_encode(['ok'=>false,'error'=>'URL do Pushcut vazia']); exit; }

// Estado
$stateFile = __DIR__ . '/config/pushcut_state.json';
$seen = [];
if (file_exists($stateFile)) {
  $st = json_decode(file_get_contents($stateFile), true);
  if (isset($st['seen_ids']) && is_array($st['seen_ids'])) $seen = $st['seen_ids'];
}
$seenSet = array_flip($seen);

// DB
$loaded = false;
foreach ([__DIR__.'/config/database.php', __DIR__.'/database.php'] as $p) {
  if (file_exists($p)) { require_once $p; $loaded = true; break; }
}
if (!$loaded) { echo json_encode(['ok'=>false,'error'=>'database.php não encontrado']); exit; }
if (!isset($conn) && function_exists('getConnection')) { $conn = getConnection(); }
if (!isset($conn)) { echo json_encode(['ok'=>false,'error'=>'$conn não definido']); exit; }
if ($conn->connect_error) { echo json_encode(['ok'=>false,'error'=>'Erro de conexão: '.$conn->connect_error]); exit; }

// Busca últimas aprovadas/pagas
$sql = "SELECT id, customer_name, amount, service_type, created_at, status
          FROM transactions WHERE status IN ('approved','paid') AND created_at >= (NOW() - INTERVAL 6 HOUR) ORDER BY created_at DESC LIMIT 200";
$res = $conn->query($sql);
if (!$res) { echo json_encode(['ok'=>false,'error'=>'Falha na consulta SQL']); exit; }

$rows = [];
while ($r = $res->fetch_assoc()) $rows[] = $r;
$rows = array_reverse($rows); // mais antigas primeiro

$notified = 0;
$newSeen = $seen;

foreach ($rows as $row) {
  $id = strval($row['id']);
  if (isset($seenSet[$id])) continue;

  $name = $row['customer_name'] ?: 'Cliente';
  $service = $row['service_type'] ?: 'Serviço';
  $amount_brl = number_format((float)$row['amount'] / 100.0, 2, ',', '.');
  $created = $row['created_at'];

  // Dispara GET simples (sem payload) — usa o conteúdo que você configurou na notificação do Pushcut
  $ch = curl_init($pushcutUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 12);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($code>=200 && $code<300) {
    $notified++;
    $newSeen[] = $id;
    $seenSet[$id] = true;
  } else {
    echo json_encode(['ok'=>false,'error'=>'Falha ao notificar','http'=>$code,'resp'=>$resp,'id'=>$id]); exit;
  }}

if (count($newSeen) > 1000) $newSeen = array_slice($newSeen, -1000);
$persist = @file_put_contents($stateFile, json_encode(['seen_ids'=>$newSeen], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
if ($persist === false) { echo json_encode(['ok'=>false,'error'=>'Sem permissão para gravar em config/pushcut_state.json']); exit; }

echo json_encode(['ok'=>true,'notified'=>$notified,'checked'=>count($rows)]);
