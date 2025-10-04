<?php
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET');
header('Access-Control-Allow-Headers: Content-Type');

try {
    if (!isset($_GET['id'])) {
        throw new Exception('ID da transação não fornecido');
    }

    $transactionId = $_GET['id'];
    $conn = getConnection();
// === Pushcut notify hook (executa no final do request) ===
require_once __DIR__ . '/pushcut_notify.php';
if (!isset($conn) && function_exists('getConnection')) { $conn = getConnection(); }
if (isset($conn)) {
    register_shutdown_function(function() use ($conn, $data){
        try {
            // 1) tenta extrair ID do payload (campos comuns)
            $ids = [];
            foreach (['transaction_id','transactionId','id','reference','payment_id','paymentId'] as $k) {
                if (isset($data->$k) && $data->$k !== '') $ids[] = (string)$data->$k;
            }
            $notified = false;
            foreach ($ids as $txid) {
                if (notify_pushcut_by_id($conn, $txid)) { $notified = true; break; }
            }
            // 2) fallback: consulta últimas aprovadas/pagas recentes
            if (!$notified) {
                $sql = "SELECT id FROM transactions
                          WHERE status IN ('approved','paid')
                            AND created_at >= (NOW() - INTERVAL 10 MINUTE)
                       ORDER BY created_at DESC
                          LIMIT 10";
                if ($res = $conn->query($sql)) {
                    while ($r = $res->fetch_assoc()) {
                        notify_pushcut_by_id($conn, $r['id']);
                    }
                }
            }
        } catch (\Throwable $e) {
            // silencioso
        }
    });
}
// === /Pushcut notify hook ===


$query = "SELECT status FROM transactions WHERE id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("s", $transactionId); 
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

    if (!$transaction) {
        throw new Exception('Transação não encontrada');
    }

    echo json_encode([
        'success' => true,
        'status' => $transaction['status']
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 