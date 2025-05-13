<?php
// Inicia a sessão para verificar se o usuário está logado.
session_start();

// Requer a conexão com o banco de dados.
require_once 'conexao.php';

// Verifica se o usuário está logado. Caso contrário, redireciona para a página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Atribui o ID do usuário logado à variável $usuarioId.
$usuarioId = $_SESSION['user_id'];

// Verifica se o ID do destinatário foi passado via GET. Caso contrário, retorna um array vazio em formato JSON.
if (!isset($_GET['destinatario_id'])) {
    echo json_encode([]); // Resposta vazia, caso não tenha destinatário.
    exit();
}

// Converte o valor de destinatario_id para um inteiro.
$destinatarioId = (int)$_GET['destinatario_id'];

// Prepara a consulta SQL para buscar as mensagens entre o usuário logado e o destinatário.
$stmt = $conn->prepare("
    SELECT * FROM mensagens
    WHERE (remetente_id = :usuario AND destinatario_id = :destinatario)
       OR (remetente_id = :destinatario AND destinatario_id = :usuario)
    ORDER BY data_envio ASC
");

// Executa a consulta passando os valores de ID do usuário e do destinatário.
$stmt->execute([
    ':usuario' => $usuarioId,        // Substitui :usuario com o ID do usuário logado.
    ':destinatario' => $destinatarioId  // Substitui :destinatario com o ID do destinatário.
]);

// Obtém todas as mensagens como um array associativo.
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Retorna as mensagens no formato JSON.
echo json_encode($mensagens); // Converte o array de mensagens para JSON e envia a resposta.
?>
