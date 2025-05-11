<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php';

$usuarioId = $_SESSION['user_id'];

// Verifica se o ID do destinatário foi passado
if (!isset($_GET['destinatario_id'])) {
    header("Location: home.php");
    exit();
}

$destinatarioId = (int)$_GET['destinatario_id'];

// Verifica se são seguidores mútuos
$checkSeguindo = $conn->prepare(" 
    SELECT COUNT(*) FROM seguidores 
    WHERE (seguidor_id = :usuarioId AND seguido_id = :destinatarioId) 
    OR (seguidor_id = :destinatarioId AND seguido_id = :usuarioId)
");
$checkSeguindo->execute([
    ':usuarioId' => $usuarioId,
    ':destinatarioId' => $destinatarioId
]);

if ($checkSeguindo->fetchColumn() < 2) {
    echo "Vocês precisam se seguir mutuamente para conversar.";
    exit();
}

// Busca dados do destinatário
$stmt = $conn->prepare("
    SELECT u.username, p.foto_perfil 
    FROM usuario u 
    LEFT JOIN perfil p ON u.username = p.username
    WHERE u.id = :id
");
$stmt->execute([':id' => $destinatarioId]);
$destinatario = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$destinatario) {
    echo "Usuário não encontrado.";
    exit();
}

// Envia mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $_POST['mensagem'] ?? '';
    $arquivoPath = null;

    if (!empty($_FILES['arquivo']['name'])) {
        $uploadDir = '../uploads/mensagens/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        $nomeArquivo = uniqid() . '_' . basename($_FILES['arquivo']['name']);
        $caminhoCompleto = $uploadDir . $nomeArquivo;

        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
            $arquivoPath = 'uploads/mensagens/' . $nomeArquivo; // sem ../
        }
    }

    $stmt = $conn->prepare("
        INSERT INTO mensagens (remetente_id, destinatario_id, mensagem, arquivo, data_envio)
        VALUES (:remetente, :destinatario, :mensagem, :arquivo, NOW())
    ");
    $stmt->execute([
        ':remetente' => $usuarioId,
        ':destinatario' => $destinatarioId,
        ':mensagem' => $mensagem,
        ':arquivo' => $arquivoPath
    ]);

    // Redireciona para evitar reenvio no refresh
    header("Location: ?destinatario_id=" . $destinatarioId);
    exit();
}

// Busca mensagens
$stmt = $conn->prepare("
    SELECT * FROM mensagens
    WHERE (remetente_id = :usuario AND destinatario_id = :destinatario)
       OR (remetente_id = :destinatario AND destinatario_id = :usuario)
    ORDER BY data_envio ASC
");
$stmt->execute([
    ':usuario' => $usuarioId,
    ':destinatario' => $destinatarioId
]);
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Chat com <?= htmlspecialchars($destinatario['username']) ?></title>
    <link rel="stylesheet" href="../css/chat.css">
</head>
<body>

<header>
    <button class="btn-voltar" onclick="window.location.href='conversas.php'">×</button>
</header>

<div class="header">
    <img src="<?= htmlspecialchars($destinatario['foto_perfil']) ?>" alt="Foto de perfil">
    <h2>Conversando com <?= htmlspecialchars($destinatario['username']) ?></h2>
</div>

<div class="mensagens" id="mensagens">
    <?php foreach ($mensagens as $msg): ?>
        <div class="msg <?= $msg['remetente_id'] == $usuarioId ? 'eu' : 'outro' ?>">
            <?= nl2br(htmlspecialchars($msg['mensagem'])) ?>

            <?php if (!empty($msg['arquivo'])): ?>
                <?php 
                    $ext = strtolower(pathinfo($msg['arquivo'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $arquivoUrl = '../' . $msg['arquivo']; // Adiciona ../ para acessar a pasta corretamente
                ?>
                <?php if ($isImage): ?>
                    <img src="<?= htmlspecialchars($arquivoUrl) ?>" alt="Imagem enviada">
                <?php else: ?>
                    <a href="<?= htmlspecialchars($arquivoUrl) ?>" target="_blank">📎 Ver arquivo</a>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
</div>

<form class="formulario" method="POST" enctype="multipart/form-data">
    <textarea name="mensagem" placeholder="Digite sua mensagem..." rows="3"></textarea>
    <input type="file" name="arquivo" accept="image/*">
    <button type="submit">Enviar</button>
</form>

<script>
    // Quando a página carrega, rola automaticamente para a última mensagem
    window.onload = function() {
        var mensagensContainer = document.getElementById("mensagens");
        // Rola automaticamente para a última mensagem
        mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
    };
</script>

</body>
</html>