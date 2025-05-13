<?php
session_start(); // Inicia a sessão para acessar dados do usuário
require_once 'conexao.php'; // Inclui a conexão com o banco de dados

$usuarioId = $_SESSION['user_id']; // Pega o ID do usuário logado

// Consulta para buscar usuários que seguem e são seguidos pelo usuário logado (seguimento mútuo)
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
$stmt->bindParam(':meu_id', $usuarioId); // Define o valor do parâmetro :meu_id
$stmt->execute(); // Executa a consulta
$usuarios = $stmt->fetchAll(PDO::FETCH_ASSOC); // Obtém os dados dos usuários como array associativo
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Amigos</title>
    <link rel="stylesheet" href="../css/conversas.css"> <!-- Estilo da página de amigos -->
</head>
<body>

<!-- Cabeçalho da página -->
<header>
    <h1>Amigos</h1>
    <button class="btn-voltar" onclick="window.location.href='home.php'">×</button> <!-- Botão de voltar -->
</header>

<!-- Lista de amigos -->
<div class="amigos-lista">
    <?php if (count($usuarios) > 0): ?> <!-- Verifica se há amigos -->
        <ul>
            <?php foreach ($usuarios as $usuario): ?> <!-- Loop pelos amigos -->
                <li>
                    <!-- Foto de perfil do amigo -->
                    <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" 
                         alt="Foto de <?= htmlspecialchars($usuario['username']) ?>" 
                         class="foto-perfil">

                    <!-- Link para iniciar chat com o amigo -->
                    <a href="chat.php?destinatario_id=<?= $usuario['id'] ?>" class="nome-usuario">
                        <?= htmlspecialchars($usuario['username']) ?>
                    </a>
                </li>
            <?php endforeach; ?>
        </ul>
    <?php else: ?>
        <!-- Caso não existam amigos -->
        <p class="msg-amigo">Você não tem amigos. Adicione mais amigos para conversar!</p>
    <?php endif; ?>
</div>

</body>
</html>
