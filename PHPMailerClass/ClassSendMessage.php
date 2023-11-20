<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: http://localhost:8000');
header('Access-Control-Allow-Methods: POST');
header('Access-Control-Allow-Headers: Content-Type');
header('Access-Control-Max-Age: 3600');

require '../../vendor/autoload.php';
include '../../src/Validator/ClassValidaEmail.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;

$config = include '../config.php';
$emailLocal = $config['email'];
$passwordLocal = $config['senha'];
$nameLocal = $config['name'];

$jsonData = file_get_contents("php://input");
$data = json_decode($jsonData, true);

// Dados do formulário
$nameToSend = $data['nome'];
$cellToSend = $data['telefone'];
$emailToSend = $data['email'];
$messageToSend = $data['mensagem'];

// Validar os campos
if (empty($nameToSend) || empty($cellToSend) || empty($emailToSend) || empty($messageToSend)) {
    http_response_code(400);
    echo json_encode(['erro' => true,'mensagem' => 'Todos os campos são obrigatórios.']);
    exit;
}

if (!ValidaEmail::ValidacaoDeEmail($emailToSend)) {
    http_response_code(400);
    echo json_encode(['erro' => true,'mensagem' => 'O email inserido não é válido.']);
    exit;
}

$mail = new PHPMailer(true);
try {
    // Configurações do servidor de email
    $mail->SMTPDebug = SMTP::DEBUG_SERVER;
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->SMTPAuth = true;
    $mail->Username = $emailLocal;
    $mail->Password = $passwordLocal;
    $mail->SMTPSecure = 'tls';
    $mail->Port = 587;
    $mail->AuthType = 'PLAIN';
    $mail->CharSet = 'UTF-8';

    // Configurações do email
    $mail->setFrom($emailLocal, $nameLocal);
    $mail->addAddress($emailToSend, $nameToSend);
    $mail->addReplyTo($emailLocal, $nameToSend);
    $mail->Body = $messageToSend;

    // Adicione os campos do formulário ao corpo do email
    $mail->Body .= "\n\nNome: " . $nameToSend;
    $mail->Body .= "\nTelefone: " . $cellToSend;
    $mail->Body .= "\nEmail: " . $emailToSend;
    $mail->Body .= "\nMensagem: " . $messageToSend;

    // Tenta enviar o email
    $result = $mail->send();
    if($result){
        ob_clean();
        http_response_code(200);
        echo json_encode(['sucesso' => true,'mensagem' => 'Mensagem enviada com sucesso.']);
        exit;
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['erro' => true,'mensagem' => "Erro ao enviar mensagem: {$mail->ErrorInfo}"]);
    exit;
}

?>
