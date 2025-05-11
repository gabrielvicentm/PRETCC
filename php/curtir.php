<?php
session_start();
require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['postagem_id'], $_POST['post_index'])) {
    $usuario_id = (int) $_POST['usuario_id'];
    $postagem_id = (int) $_POST['postagem_id'];
    $post_index = (int) $_POST['post_index'];

    $stmt = $conn->prepare("SELECT id FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    $stmt->execute([':uid' => $usuario_id, ':pid' => $postagem_id]);

    if ($stmt->fetch()) {
        // Já curtiu — remover
        $stmt = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    } else {
        // Não curtiu ainda — adicionar
        $stmt = $conn->prepare("INSERT INTO curtidas (usuario_id, postagem_id) VALUES (:uid, :pid)");
    }

    $stmt->execute([':uid' => $usuario_id, ':pid' => $postagem_id]);

    // Redirecionar de volta com âncora do post
    header("Location: reels.php#post-$post_index");
    exit();
}

header("Location: reels.php");
exit();
