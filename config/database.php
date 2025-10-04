<?php
// Definição das constantes de conexão
define('DB_HOST',  'localhost');
define('DB_USER', 'nascimento');
define('DB_PASS', 'k7jd7npuq-{d');
define('DB_NAME', 'telanascimento');

// Função para criar a conexão usando MySQLi
function getConnection() {
    // Conectar ao banco de dados
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

    // Verificar erros de conexão
    if ($conn->connect_error) {
        error_log("Erro de conexão: " . $conn->connect_error);
        throw new Exception("Erro de conexão com o banco de dados");
    }
    
    return $conn;
}

// Exemplo de uso
try {
    $conn = getConnection();
} catch (Exception $e) {
    echo $e->getMessage();
}
?>