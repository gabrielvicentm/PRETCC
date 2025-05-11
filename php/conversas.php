<?php
session_start();
require_once 'conexao.php';

$usuarioId = $_SESSION['user_id'];

// Puxa apenas quem você segue ou é seguido (amigos mutuamente seguidos)
$stmt = $conn->prepare("
    SELECT u.id, u.username, p.foto_perfil
    FROM usuario u
    LEFT JOIN perfil p ON u.username = p.username
    WHERE u.id != :meu_id AND (
        EXISTS (
            SELECT 1 FROM seguidores s1 WHERE s1.seguidor_id = :meu_id AND s1.seguido_id = u.id
        ) AND EXISTS (
            SELECT 1 FROM seguidores s2 WHERE s2.seguidor_id = u.id AND s2.seguido_id = :meu_id
        )
    )
");
$stmt->bindParam(':meu_id', $usuarioId);
$stmt->execute();
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos</title>
    <link rel="stylesheet" href="../css/conversas.css">
</head>
<body>
<header>
    <h1>Amigos</h1>
    <button class="btn-voltar" onclick="window.location.href='home.php'">×</button>
</header>
    <div class="amigos-lista">
        <?php if (count($usuarios) > 0): ?>
            <ul>
                <?php foreach ($usuarios as $usuario): ?>
                    <li>
                        <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de <?= htmlspecialchars($usuario['username']) ?>" class="foto-perfil">
                        <a href="chat.php?destinatario_id=<?= $usuario['id'] ?>" class="nome-usuario">
                            <?= htmlspecialchars($usuario['username']) ?>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        <?php else: ?>
            <p class="msg-amigo">Você não tem amigos. Adicione mais amigos para conversar!</p>
        <?php endif; ?>
    </div>

</body>
</html>
