<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo "Usuário não autenticado.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id'];
    $reel_id = $_POST['reel_id'] ?? null;
    $texto = trim($_POST['texto'] ?? '');

    if ($reel_id && $texto) {
        $sql = "INSERT INTO comentarios (reel_id, usuario_id, texto) VALUES (:reel_id, :usuario_id, :texto)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':reel_id', $reel_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':texto', $texto);
        $stmt->execute();
        header("Location: reels.php"); // redireciona de volta
        exit();
    } else {
        echo "Dados incompletos.";
    }
}
?>
