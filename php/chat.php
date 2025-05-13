<?php
session_start(); // Inicia a sessão PHP para acessar dados do usuário logado

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redireciona se não estiver logado
    exit();
}

require_once 'conexao.php'; // Conexão com o banco de dados

$usuarioId = $_SESSION['user_id']; // ID do usuário logado

// Verifica se o ID do destinatário foi passado via GET
if (!isset($_GET['destinatario_id'])) {
    header("Location: home.php"); // Redireciona se não for especificado
    exit();
}

$destinatarioId = (int)$_GET['destinatario_id']; // Converte para inteiro

// Verifica se os dois usuários se seguem mutuamente
$checkSeguindo = $conn->prepare(" 
    SELECT COUNT(*) FROM seguidores 
    WHERE (seguidor_id = :usuarioId AND seguido_id = :destinatarioId) 
    OR (seguidor_id = :destinatarioId AND seguido_id = :usuarioId)
");
$checkSeguindo->execute([
    ':usuarioId' => $usuarioId,
    ':destinatarioId' => $destinatarioId
]);

// Se não houver seguimento mútuo, bloqueia o acesso ao chat
if ($checkSeguindo->fetchColumn() < 2) {
    echo "Vocês precisam se seguir mutuamente para conversar.";
    exit();
}

// Busca dados do destinatário (nome e foto de perfil)
$stmt = $conn->prepare("
    SELECT u.username, p.foto_perfil 
    FROM usuario u 
    LEFT JOIN perfil p ON u.username = p.username
    WHERE u.id = :id
");
$stmt->execute([':id' => $destinatarioId]);
$destinatario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o destinatário não existir, mostra erro
if (!$destinatario) {
    echo "Usuário não encontrado.";
    exit();
}

// Se o formulário foi enviado (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $_POST['mensagem'] ?? ''; // Captura a mensagem
    $arquivoPath = null; // Inicializa o caminho do arquivo

    // Se um arquivo foi enviado
    if (!empty($_FILES['arquivo']['name'])) {
        $uploadDir = '../uploads/mensagens/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true); // Cria diretório se não existir

        $nomeArquivo = uniqid() . '_' . basename($_FILES['arquivo']['name']);
        $caminhoCompleto = $uploadDir . $nomeArquivo;

        // Move o arquivo para o diretório
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
            $arquivoPath = 'uploads/mensagens/' . $nomeArquivo; // Caminho salvo no banco
        }
    }

    // Insere a mensagem no banco
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

    // Redireciona para evitar reenvio ao atualizar a página
    header("Location: ?destinatario_id=" . $destinatarioId);
    exit();
}

// Busca todas as mensagens trocadas entre os dois usuários
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
$mensagens = $stmt->fetchAll(PDO::FETCH_ASSOC); // Array com todas as mensagens
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Chat com <?= htmlspecialchars($destinatario['username']) ?></title>
    <link rel="stylesheet" href="../css/chat.css">
</head>
<body>

<!-- Cabeçalho com botão de voltar -->
<header>
    <button class="btn-voltar" onclick="window.location.href='conversas.php'">×</button>
</header>

<!-- Exibe o nome e foto de quem está sendo conversado -->
<div class="header">
    <img src="<?= htmlspecialchars($destinatario['foto_perfil']) ?>" alt="Foto de perfil">
    <h2>Conversando com <?= htmlspecialchars($destinatario['username']) ?></h2>
</div>

<!-- Área onde as mensagens são exibidas -->
<div class="mensagens" id="mensagens">
    <?php foreach ($mensagens as $msg): ?>
        <div class="msg <?= $msg['remetente_id'] == $usuarioId ? 'eu' : 'outro' ?>">
            <?= nl2br(htmlspecialchars($msg['mensagem'])) ?>

            <?php if (!empty($msg['arquivo'])): ?>
                <?php 
                    $ext = strtolower(pathinfo($msg['arquivo'], PATHINFO_EXTENSION));
                    $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                    $arquivoUrl = '../' . $msg['arquivo'];
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

<!-- Formulário para envio de nova mensagem -->
<form class="formulario" method="POST" enctype="multipart/form-data">
    <textarea name="mensagem" placeholder="Digite sua mensagem..." rows="3"></textarea>
    <input type="file" name="arquivo" accept="image/*">
    <button type="submit">Enviar</button>
</form>

<!-- Script que rola a página até a última mensagem automaticamente -->
<script>
    window.onload = function() {
        var mensagensContainer = document.getElementById("mensagens");
        mensagensContainer.scrollTop = mensagensContainer.scrollHeight;
    };
</script>

</body>
</html>
