<?php
// Inicia a sessão para garantir que o usuário esteja logado
session_start();

// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Verifica se o usuário está logado e se a variável 'seguido' foi passada via POST
// Se não, redireciona o usuário para a página de login
if (!isset($_SESSION['user_id']) || !isset($_POST['seguido'])) {
    header('Location: login.html');
    exit();
}

// Recebe o ID do seguidor da sessão e o username do usuário a ser seguido, enviado via POST
$seguidor_id = $_SESSION['user_id'];
$seguido_username = $_POST['seguido'];

// Consulta o ID do usuário a ser seguido com base no nome de usuário
$stmt = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
$stmt->execute([':username' => $seguido_username]);
$seguido = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o usuário não for encontrado, exibe uma mensagem de erro e encerra o script
if (!$seguido) {
    echo "Usuário não encontrado.";
    exit();
}

// Recebe o ID do usuário a ser seguido
$seguido_id = $seguido['id'];

// Verifica se o usuário está tentando seguir a si mesmo
// Se for o caso, redireciona para a página do perfil sem fazer nada
if ($seguidor_id == $seguido_id) {
    header('Location: perfil.php');
    exit();
}

// Insere o registro na tabela 'seguidores' se ainda não existir
// A cláusula IGNORE garante que se o relacionamento já existir, nada será feito
$stmt = $conn->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES (:seguidor, :seguido)");
$stmt->execute([
    ':seguidor' => $seguidor_id,
    ':seguido' => $seguido_id
]);

// Redireciona para a página do perfil do usuário seguido
header("Location: perfil.php?u=" . urlencode($seguido_username));
exit();
?>
