<?php
session_start(); // Inicia a sessão para acessar variáveis da sessão
require_once 'conexao.php'; // Inclui a conexão com o banco de dados

// Verifica se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    http_response_code(403); // Retorna código HTTP 403 (proibido)
    echo "Usuário não autenticado."; // Mensagem de erro
    exit(); // Encerra a execução
}

// Verifica se o método da requisição é POST (formulário enviado)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario_id = $_SESSION['user_id']; // ID do usuário logado
    $reel_id = $_POST['reel_id'] ?? null; // ID do reel associado ao comentário
    $texto = trim($_POST['texto'] ?? ''); // Texto do comentário, removendo espaços extras

    // Verifica se o reel_id e o texto foram fornecidos
    if ($reel_id && $texto) {
        // Prepara a query para inserir o comentário
        $sql = "INSERT INTO comentarios (reel_id, usuario_id, texto) VALUES (:reel_id, :usuario_id, :texto)";
        $stmt = $conn->prepare($sql);

        // Associa os parâmetros aos valores recebidos
        $stmt->bindParam(':reel_id', $reel_id);
        $stmt->bindParam(':usuario_id', $usuario_id);
        $stmt->bindParam(':texto', $texto);

        // Executa o comando no banco de dados
        $stmt->execute();

        // Redireciona de volta à página de reels após comentar
        header("Location: reels.php");
        exit();
    } else {
        // Mensagem de erro se algum campo obrigatório estiver vazio
        echo "Dados incompletos.";
    }
}
?>
