<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$usuarioId = $_SESSION['user_id'];

if (!isset($_GET['destinatario_id'])) {
    echo json_encode([]);
    exit();
}

$destinatarioId = (int)$_GET['destinatario_id'];

// Buscar mensagens
$stmt = $conn->prepare("
    SELECT * FROM mensagens
    WHERE (remetente_id = :usuario AND destinatario_id = :destinatario)
       OR (remetente_id = :destinatario AND destinatario_id = :usuario)
    ORDER BY data_envio ASC
");
$stmt->execute([
    ':usuario' => $usuarioId,
    ':destinatario' => $destinatarioId
]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retornar as mensagens como JSON
echo json_encode($mensagens);
?>
