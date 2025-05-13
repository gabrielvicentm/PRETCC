<?php
session_start();
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    echo "Você precisa estar logado.";
    exit;
}

// Verifica se o formulário foi enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtém os dados do formulário
    $post_id = $_POST['post_id'];
    $comentario = trim($_POST['comentario']);
    $usuario_id = $_SESSION['user_id'];

    // Verifica se o comentário não está vazio
    if (!empty($comentario)) {
        // Prepara o SQL de inserção
        $sql = "INSERT INTO comentarios (reel_id, usuario_id, texto, data_comentario) VALUES (?, ?, ?, NOW())";
        try {
            $stmt = $conn->prepare($sql);
            $stmt->execute([$post_id, $usuario_id, $comentario]);

            // Redireciona o usuário de volta para a página de reels (ou página do post específico)
            header("Location: reels.php#post-$post_id"); // Exemplo de redirecionamento
            exit;
        } catch (PDOException $e) {
            // Em caso de erro no banco de dados, exibe uma mensagem
            echo "Erro ao enviar comentário: " . $e->getMessage();
        }
    } else {
        // Caso o comentário esteja vazio
        echo "O comentário não pode estar vazio.";
    }
}
?>
