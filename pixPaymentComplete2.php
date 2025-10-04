<?php
require_once 'config/database.php';

header('Content-Type: application/json');


try {
    // Receber o payload do postback
    $payload = file_get_contents('php://input');
    $data = json_decode($payload);
    
    if (!$data) {
        throw new Exception('Payload inválido');
    }

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


    // Extrair dados relevantes
    $transactionId = $data->payment_code;
    $status = $data->payment_status;
    $paidAt = $data->approved_at ? date('Y-m-d H:i:s', strtotime($data->approved_at)) : null;

    // Registrar o log do postback
$stmt = $conn->prepare("
    INSERT INTO postback_logs (transaction_id, payload, status) 
    VALUES (?, ?, ?)
");

// Vincule os parâmetros e execute a declaração
$stmt->bind_param("iss", $transactionId, $payload, $status); // "i" para inteiro, "s" para strings
$stmt->execute();

    // Atualizar a transação
    $stmt = $conn->prepare("
        UPDATE transactions 
        SET status = ?,
            paid_at = ?,
            postback_received = TRUE
        WHERE id = ?
    ");
    $stmt->execute([$status, $paidAt, $transactionId]);

    // Verificar se a atualização foi bem sucedida
   if ($stmt->affected_rows === 0) {
    throw new Exception('Transação não encontrada: ' . $transactionId);
}

    // Se o status for 'approved', preparar resposta com upsell
    if ($status === 'approved') {
        // Buscar o tipo de serviço
$stmt = $conn->prepare("
    SELECT service_type, customer_email 
    FROM transactions 
    WHERE id = ?
");

// Vincule o parâmetro e execute a declaração
$stmt->bind_param("i", $transactionId); // "i" indica um inteiro
$stmt->execute();

// Obtenha o resultado da consulta
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();


        $response = [
            'success' => true,
            'message' => 'Pagamento processado com sucesso',
            'upsell' => [
                'show' => true,
                'type' => $transaction['service_type'],
                'products' => []
            ]
        ];

        if ($transaction['service_type'] === 'RG') {
            $response['upsell']['products'] = [
                [
                    'title' => 'Proteção Antifraude RG',
                    'description' => 'Proteja seu RG contra fraudes e clonagem',
                    'price' => 29.90
                ]
            ];
        } else if ($transaction['service_type'] === 'CNH') {
            $response['upsell']['products'] = [
                [
                    'title' => 'Proteção Veicular Premium',
                    'description' => 'Proteja sua CNH com nosso serviço completo',
                    'price' => 49.90
                ]
            ];
        }
        
        else if ($transaction['service_type'] !== 'CNH') {
            $response['upsell']['products'] = [
                [
                    'title' => $transaction['service_type'],
                    'description' => 'Serviço teste',
                    'price' => '49.90'
                ]
            ];
        }

        echo json_encode($response);
    } else {
        echo json_encode([
            'success' => true,
            'message' => 'Postback processado com sucesso',
            'status' => $status
        ]);
    }

} catch (Exception $e) {
    error_log('Erro no postback: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
} 