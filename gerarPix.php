<?php
require_once 'config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');

$dominioatual = 'https://'.$_SERVER['HTTP_HOST'];

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('Método não permitido');
    }
    
    // Recebe o JSON do corpo da requisição
    $jsonData = file_get_contents('php://input');
    $data = json_decode($jsonData);

    // Validações básicas
    if (!$data || !isset($data->nome) || !isset($data->cpf) || !isset($data->email) || !isset($data->titulo)) {
        throw new Exception('Dados incompletos');
    }

    
    if (!$data || !isset($data->nome) || !isset($data->cpf) || !isset($data->email) || !isset($data->titulo)) {
        throw new Exception('Dados incompletos');
    }
    

    
    $conn = getConnection();
    $querygate = 'SELECT * FROM gateway WHERE status = 1';
    $stmt = $conn->prepare($querygate);
    $stmt->execute();
    $result = $stmt->get_result();
    $dadosgateway = $result->fetch_assoc();
    
    if (!$dadosgateway) {
     throw new Exception('Nenhum Gateway Ativo');
    }
    
    $serviceType = trim(explode('-', $data->titulo)[0]);

    if ($dadosgateway['gateway'] == 'streetpay') {

    $url = "https://api.streetpay.com.br/functions/v1/transactions";
    $secretKey = $dadosgateway['chave'];
    $authorization = 'Basic ' . base64_encode($secretKey);

    $cpfLimpo = preg_replace('/[^\d]/', '', $data->cpf);

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: $authorization",
        'Content-Type: application/json'
    ]);


    $requestData = [
        "customer" => [
            "name" => $data->nome,
            "email" => $data->email,
            "document" => [
                "type" => "cpf",
                "number" => $cpfLimpo
            ]
        ],
        "pix" => [
            "expiresInDays" => 1
        ],
        "paymentMethod" => "pix",
        "items" => [
            [
                "tangible" => false,
                "title" => "TR_0G",
                "unitPrice" => $data->valor,
                "quantity" => 1
            ]
        ],
        "amount" => $data->valor,
        "postbackUrl" => "".$dominioatual."/inicio/pixPaymentComplete.php"
    ];

    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($requestData));

    $response = curl_exec($ch);

    if (curl_errno($ch)) {
        throw new Exception('Erro na requisição: ' . curl_error($ch));
    }

    curl_close($ch);

    $responseData = json_decode($response);


    if (!$responseData || !isset($responseData->pix->qrcode)) {
        throw new Exception('Resposta inválida da API');
    }

    $conn = getConnection();
    $stmt = $conn->prepare("
        INSERT INTO transactions 
        (id, customer_name, customer_email, customer_cpf, service_type, 
         service_description, amount, status, pix_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $transactionId = $responseData->id;
    $customerName = $data->nome;
    $customerEmail = $data->email;
    $customerCpf = $cpfLimpo;
    $serviceDescription = $data->titulo;
    $amount = $data->valor;
    $status = 'waiting_payment';
    $pixCode = $responseData->pix->qrcode;

    $stmt->bind_param(
        'ssssssdss',
        $transactionId,
        $customerName,
        $customerEmail,
        $customerCpf,
        $serviceType,
        $serviceDescription,
        $amount,
        $status,
        $pixCode
    );

    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar a transação: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    $result = [
        "success" => true,
        "data" => [
            "qrcode_image" => "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($pixCode),
            "qrcode_text" => $pixCode,
            "transaction_id" => $transactionId
        ]
    ];

    echo json_encode($result);
    
    }
    
    else if ($dadosgateway['gateway'] == 'mangofy') {

    $cpfLimpo = preg_replace('/[^\d]/', '', $data->cpf);


$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://checkout.mangofy.com.br/api/v1/payment");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
'Content-Type: application/json',
'Accept: application/json',
'Authorization: 2de5feee80f4bb69cedde9456c8cfaa52tbfdyjpswfggdl63ldr9f5ujqwaatw',
'Store-Code: 969000c427117c855fce79bdc13c65e5',
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
   "ip" => "191.189.1.153", 
   "pix" => [
         "expires_in_days" => 5 
      ], 
   "items" => [
            [
               "item_id" => 1, 
               "quantity" => 1, 
               "unit_price" => $data->valor, 
               "description" => "SERVICO PRESTADO" 
            ] 
         ], 
   "api_enum" => 1, 
   "customer" => [
                  "ip" => "191.189.1.153", 
                  "name" => $data->nome, 
                  "email" => $data->email, 
                  "phone" => "(24) 98118-2442", 
                  "document" => $cpfLimpo 
               ], 
   "shipping" => [
                     "city" => "Resende", 
                     "state" => "RJ", 
                     "street" => "Avenida General Afonseca", 
                     "address" => "Avenida General Afonseca, 1475", 
                     "country" => "Brasil", 
                     "zip_code" => "27520174", 
                     "complement" => "Casa", 
                     "neighborhood" => "Manejo", 
                     "street_number" => "1475" 
                  ], 
   "installments" => 1,
   "postback_url" => "".$dominioatual."/inicio/pixPaymentComplete2.php",
   "external_code" => "Zo4yKjXG", 
   "payment_amount" => $data->valor, 
   "payment_format" => "regular", 
   "payment_method" => "pix", 
   "shipping_amount" => 0 
]));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

 if (curl_errno($ch)) {
        throw new Exception('Erro na requisição: ' . curl_error($ch));
    }

    curl_close($ch);

    $responseData = json_decode($response);


    if (!$responseData || !isset($responseData->pix->pix_qrcode_text)) {
        throw new Exception('Resposta inválida da API');
    }

        
   $stmt = $conn->prepare("
        INSERT INTO transactions 
        (id, customer_name, customer_email, customer_cpf, service_type, 
         service_description, amount, status, pix_code)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $transactionId = $responseData->payment_code;
    $customerName = $data->nome;
    $customerEmail = $data->email;
    $customerCpf = $cpfLimpo;
    $serviceDescription = $data->titulo;
    $amount = $data->valor;
    $status = 'waiting_payment';
    $pixCode = $responseData->pix->pix_qrcode_text;

    $stmt->bind_param(
        'ssssssdss',
        $transactionId,
        $customerName,
        $customerEmail,
        $customerCpf,
        $serviceType,
        $serviceDescription,
        $amount,
        $status,
        $pixCode
    );

    if (!$stmt->execute()) {
        throw new Exception('Erro ao salvar a transação: ' . $stmt->error);
    }

    $stmt->close();
    $conn->close();

    $result = [
        "success" => true,
        "data" => [
            "qrcode_image" => "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . urlencode($pixCode),
            "qrcode_text" => $pixCode,
            "transaction_id" => $transactionId
        ]
    ];

    echo json_encode($result);
        
    }    
    else {
        echo "outro gate";
    }

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
?>