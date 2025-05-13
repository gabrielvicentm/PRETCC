<?php
session_start(); // Inicia a sessão para acessar dados do usuário logado
require_once 'conexao.php'; // Conecta ao banco de dados

// Verifica se a requisição é do tipo POST e se os dados necessários foram enviados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['usuario_id'], $_POST['postagem_id'], $_POST['post_index'])) {
    $usuario_id = (int) $_POST['usuario_id'];     // ID do usuário que está curtindo ou descurtindo
    $postagem_id = (int) $_POST['postagem_id'];   // ID da postagem
    $post_index = (int) $_POST['post_index'];     // Índice do post (usado para rolar até o post específico após reload)

    // Verifica se o usuário já curtiu essa postagem
    $stmt = $conn->prepare("SELECT id FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    $stmt->execute([':uid' => $usuario_id, ':pid' => $postagem_id]);

    if ($stmt->fetch()) {
        // Se já curtiu, remove a curtida (descurtir)
        $stmt = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    } else {
        // Se ainda não curtiu, insere uma nova curtida
        $stmt = $conn->prepare("INSERT INTO curtidas (usuario_id, postagem_id) VALUES (:uid, :pid)");
    }

    // Executa a inserção ou remoção
    $stmt->execute([':uid' => $usuario_id, ':pid' => $postagem_id]);

    // Redireciona de volta para a página de reels, rolando até o post correspondente
    header("Location: reels.php#post-$post_index");
    exit();
}

// Se os dados esperados não foram enviados, apenas redireciona de volta para a página
header("Location: reels.php");
exit();
