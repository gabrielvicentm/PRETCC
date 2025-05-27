<?php
session_start(); // Inicia a sessão para controlar o usuário logado

// Se não estiver logado (user_id não existe na sessão), redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php'; // Importa a conexão com o banco de dados

$usuarioId = $_SESSION['user_id']; // Pega o id do usuário logado

// Se não foi passado o id do destinatário via GET, redireciona para home
if (!isset($_GET['destinatario_id'])) {
    header("Location: home.php");
    exit();
}

$destinatarioId = (int)$_GET['destinatario_id']; // Pega o id do destinatário e garante que seja inteiro

// Verifica se ambos usuários se seguem mutuamente (seguidores)
// Conta quantas relações de seguidores existem entre eles, nos dois sentidos
$checkSeguindo = $conn->prepare(" 
    SELECT COUNT(*) FROM seguidores 
    WHERE (seguidor_id = :usuarioId AND seguido_id = :destinatarioId) 
    OR (seguidor_id = :destinatarioId AND seguido_id = :usuarioId)
");
$checkSeguindo->execute([
    ':usuarioId' => $usuarioId,
    ':destinatarioId' => $destinatarioId
]);

// Se não tiver as duas relações (ou seja, não são seguidores mútuos), bloqueia o acesso ao chat
if ($checkSeguindo->fetchColumn() < 2) {
    exit("Vocês precisam se seguir mutuamente para conversar.");
}

// Busca dados do destinatário para exibir no chat (username e foto)
$stmt = $conn->prepare("
    SELECT u.username, p.foto_perfil 
    FROM usuario u 
    LEFT JOIN perfil p ON u.username = p.username
    WHERE u.id = :id
");
$stmt->execute([':id' => $destinatarioId]);
$destinatario = $stmt->fetch(PDO::FETCH_ASSOC);

// Se não encontrou o usuário destinatário, exibe mensagem de erro e para
if (!$destinatario) {
    exit("Usuário não encontrado.");
}

// Se o formulário foi enviado (método POST), processa o envio da mensagem
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mensagem = $_POST['mensagem'] ?? ''; // Mensagem digitada pelo usuário (pode estar vazia)
    $arquivoPath = null; // Variável que vai guardar o caminho do arquivo enviado, se houver

    // Se enviou um arquivo
    if (!empty($_FILES['arquivo']['name'])) {
        $uploadDir = '../uploads/mensagens/'; // Diretório onde vai salvar os arquivos

        // Cria o diretório se não existir
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);

        // Gera nome único para evitar sobrescrever arquivos
        $nomeArquivo = uniqid() . '_' . basename($_FILES['arquivo']['name']);
        $caminhoCompleto = $uploadDir . $nomeArquivo;

        // Move o arquivo temporário para a pasta de uploads
        if (move_uploaded_file($_FILES['arquivo']['tmp_name'], $caminhoCompleto)) {
            $arquivoPath = 'uploads/mensagens/' . $nomeArquivo; // Caminho relativo para salvar no banco
        }
    }

    // Insere a mensagem no banco, com remetente, destinatário, texto, arquivo e data atual
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

    // Após enviar a mensagem, recarrega a página para mostrar a conversa atualizada
    header("Location: ?destinatario_id=" . $destinatarioId);
    exit();
}

// Busca todas as mensagens trocadas entre os dois usuários ordenadas pela data
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
    <meta charset="UTF-8" />
    <title>Chat com <?= htmlspecialchars($destinatario['username']) ?></title> <!-- Título da página com o nome do destinatário -->
    <link rel="stylesheet" href="../css/chat.css" /> <!-- Link para o CSS do chat -->
</head>
<body>

<div class="chat-wrapper">
    <div class="chat-container">

        <header class="chat-header">
            <!-- Botão para voltar para a página de conversas -->
            <button class="btn-voltar" onclick="window.location.href='conversas.php'">×</button>

            <!-- Foto do perfil do destinatário -->
            <img class="chat-foto-perfil" src="<?= htmlspecialchars($destinatario['foto_perfil'] ?? 'default.png') ?>" alt="Foto de perfil" />

            <!-- Nome do destinatário -->
            <h2 class="chat-titulo"><?= htmlspecialchars($destinatario['username']) ?></h2>
        </header>

        <!-- Container que mostra as mensagens -->
        <div class="mensagens-container" id="mensagens">
            <?php foreach ($mensagens as $msg): ?>
                <!-- Mensagem enviada pelo usuário logado tem classe 'eu', do outro usuário tem 'outro' -->
                <div class="mensagem <?= $msg['remetente_id'] == $usuarioId ? 'eu' : 'outro' ?>">
                    <?= nl2br(htmlspecialchars($msg['mensagem'])) ?> <!-- Exibe o texto da mensagem -->

                    <?php if (!empty($msg['arquivo'])): 
                        // Verifica extensão para decidir se exibe imagem ou link para arquivo
                        $ext = strtolower(pathinfo($msg['arquivo'], PATHINFO_EXTENSION));
                        $isImage = in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp']);
                        $arquivoUrl = '../' . $msg['arquivo'];
                    ?>
                        <?php if ($isImage): ?>
                            <!-- Se for imagem, exibe dentro da mensagem -->
                            <img src="<?= htmlspecialchars($arquivoUrl) ?>" alt="Imagem enviada" class="mensagem-imagem" />
                        <?php else: ?>
                            <!-- Se não for imagem, exibe link para download/visualização -->
                            <a href="<?= htmlspecialchars($arquivoUrl) ?>" target="_blank" class="mensagem-arquivo">📎 Ver arquivo</a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- Formulário para enviar mensagens e arquivos -->
        <form class="chat-formulario" method="POST" enctype="multipart/form-data">
            <textarea class="chat-textarea" name="mensagem" placeholder="Digite sua mensagem..." rows="3"></textarea>

            <div class="chat-acoes">
                <!-- Label estilizado para o input de arquivo (ícone de clipe) -->
                <label class="chat-file-label" for="arquivo">&#128206;</label>

                <!-- Input para selecionar arquivo de imagem ou vídeo -->
                <input class="chat-file-input" type="file" id="arquivo" name="arquivo" accept="image/*,video/*" />

                <!-- Botão para enviar a mensagem e arquivo -->
                <button class="chat-botao" type="submit">Enviar</button>
            </div>
        </form>

    </div>
</div>

<script>
    // Ao carregar a página, faz o scroll automático para a última mensagem
    window.onload = function () {
        var mensagens = document.getElementById("mensagens");
        mensagens.scrollTop = mensagens.scrollHeight;
    };
</script>

</body>
</html>
