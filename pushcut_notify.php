<?php
/**
 * pushcut_notify.php
 * ----------------------------------------------------------------
 * Helper para notificar o Pushcut quando uma transação virar APPROVED/PAID
 * - Lê a URL do Pushcut em config/pushcut.json
 * - Evita duplicadas com config/pushcut_state.json (seen_ids)
 * - Dispara GET simples (sem payload) para usar a notificação já configurada no Pushcut
 * 
 * Uso:
 *   require_once __DIR__.'/pushcut_notify.php';
 *   notify_pushcut_by_id($conn, $transactionId); // chama DEPOIS de marcar o status como approved/paid
 */

function notify_pushcut_by_id($conn, $txId) {
  // Carrega URL do Pushcut
  $cfgFile = __DIR__ . '/config/pushcut.json';
  if (!file_exists($cfgFile)) return false;
  $cfg = json_decode(file_get_contents($cfgFile), true);
  $pushcutUrl = trim($cfg['pushcut_url'] ?? '');
  if ($pushcutUrl === '') return false;

  // Carrega estado de IDs já notificados
  $stateFile = __DIR__ . '/config/pushcut_state.json';
  $seen = [];
  if (file_exists($stateFile)) {
    $st = json_decode(file_get_contents($stateFile), true);
    if (isset($st['seen_ids']) && is_array($st['seen_ids'])) $seen = $st['seen_ids'];
  }
  $txIdStr = (string)$txId;
  if (in_array($txIdStr, $seen, true)) return true; // já enviado

  // Confirma no banco se a transação existe e está aprovada/paga
  $stmt = $conn->prepare("SELECT id, status FROM transactions WHERE id = ? LIMIT 1");
  $stmt->bind_param("s", $txIdStr);
  if (!$stmt->execute()) return false;
  $res = $stmt->get_result();
  if (!$res || $res->num_rows === 0) return false;
  $row = $res->fetch_assoc();
  $status = strtolower($row['status'] ?? '');
  if (!in_array($status, ['approved','paid'], true)) return false;

  // Dispara GET simples no Pushcut (sem payload)
  $ch = curl_init($pushcutUrl);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_TIMEOUT, 10);
  $resp = curl_exec($ch);
  $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  $err  = curl_error($ch);
  curl_close($ch);

  if ($code >= 200 && $code < 300) {
    // Persiste o ID como notificado
    $seen[] = $txIdStr;
    if (count($seen) > 1000) $seen = array_slice($seen, -1000);
    @file_put_contents($stateFile, json_encode(['seen_ids'=>$seen], JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE));
    return true;
  }
  return false;
}
