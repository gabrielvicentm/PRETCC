<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    echo "Você precisa estar logado.";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $post_id = $_POST['post_id'];
    $comentario = trim($_POST['comentario']);
    $usuario_id = $_SESSION['user_id'];

    if (!empty($comentario)) {
        $sql = "INSERT INTO comentarios (reel_id, usuario_id, texto, data_comentario) VALUES (?, ?, ?, NOW())";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$post_id, $usuario_id, $comentario]);
        echo "Comentário enviado!";
    } else {
        echo "O comentário não pode estar vazio.";
    }
}
?>
