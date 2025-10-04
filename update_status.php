<?php

require_once 'config/database.php';

header('Content-Type: application/json');

if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro na conexão com o banco de dados.']));
}

$data = json_decode(file_get_contents('php://input'), true);
$id = $data['id'];
$status = $data['status'];

$stmt = $conn->prepare("UPDATE gateway SET status = ? WHERE id = ?");
$stmt->bind_param('ii', $status, $id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao atualizar o status.']);
}

$stmt->close();
$conn->close();