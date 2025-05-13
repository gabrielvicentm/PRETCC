<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $novoUsername = trim($_POST['username']);
    $novoEmail = trim($_POST['email']);
    $novaSenha = trim($_POST['senha']);

    // Verifica se username ou email já estão em uso por outro usuário
    $verifica = $conn->prepare("SELECT id FROM usuario WHERE (username = ? OR email = ?) AND id != ?");
    $verifica->execute([$novoUsername, $novoEmail, $user_id]);

    if ($verifica->rowCount() > 0) {
        echo "<script>alert('Username ou e-mail já estão em uso.'); window.history.back();</script>";
        exit;
    }

    if (!empty($novaSenha)) {
        $senhaHash = password_hash($novaSenha, PASSWORD_DEFAULT);
        $sql = "UPDATE usuario SET username = ?, email = ?, senha = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$novoUsername, $novoEmail, $senhaHash, $user_id]);
    } else {
        $sql = "UPDATE usuario SET username = ?, email = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->execute([$novoUsername, $novoEmail, $user_id]);
    }

    // Atualiza sessão
    $_SESSION['user_name'] = $novoUsername;

    echo "<script>alert('Dados atualizados com sucesso!'); window.location.href = 'configuracoes.php';</script>";
}
?>




