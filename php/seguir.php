<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['seguido'])) {
    header('Location: login.html');
    exit();
}


$seguidor_id = $_SESSION['user_id'];
$seguido_username = $_POST['seguido'];

// Descobre o ID do seguido
$stmt = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
$stmt->execute([':username' => $seguido_username]);
$seguido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$seguido) {
    echo "Usuário não encontrado.";
    exit();
}

$seguido_id = $seguido['id'];

// Evita seguir a si mesmo
if ($seguidor_id == $seguido_id) {
    header('Location: perfil.php');
    exit();
}

// Insere a relação se ainda não existe
$stmt = $conn->prepare("INSERT IGNORE INTO seguidores (seguidor_id, seguido_id) VALUES (:seguidor, :seguido)");
$stmt->execute([
    ':seguidor' => $seguidor_id,
    ':seguido' => $seguido_id
]);

header("Location: perfil.php?u=" . urlencode($seguido_username));
exit();
?>
