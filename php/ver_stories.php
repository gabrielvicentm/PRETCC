<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

if (!isset($_GET['username'])) {
    echo "Usuário não especificado.";
    exit();
}

// Busca o ID do usuário pelo username
$username = $_GET['username'];

$stmtUser = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
$stmtUser->bindParam(':username', $username);
$stmtUser->execute();
$user = $stmtUser->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    echo "Usuário não encontrado.";
    exit();
}

$usuarioId = $user['id'];

// Registra a visualização do usuário nos stories
$stmt = $conn->prepare("SELECT id FROM stories WHERE usuario_id = :usuarioId ORDER BY data_story ASC");
$stmt->bindParam(':usuarioId', $usuarioId);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Registra a visualização dos stories
foreach ($stories as $story) {
    $stmtVisualizacao = $conn->prepare("
        INSERT INTO visualizacoes_stories (usuario_id, story_id) 
        VALUES (:viewerId, :storyId)
    ");
    $stmtVisualizacao->bindParam(':viewerId', $_SESSION['user_id']);
    $stmtVisualizacao->bindParam(':storyId', $story['id']);
    $stmtVisualizacao->execute();
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Stories de <?php echo htmlspecialchars($username); ?></title>
    <link rel="stylesheet" href="../css/ver_stories.css">
</head>
<body>

<a href="home.php">
    <span class="gambiarra">X</span>
</a>

<div class="story-container">
    <button class="prev-button" onclick="mostrarAnterior()">◀</button>
    <div class="story-media" id="storyMedia"></div>
    <button class="next-button" onclick="mostrarProximo()">▶</button>
</div>

<script>
    let stories = [];
    let indiceAtual = 0;

    async function carregarStories() {
        const response = await fetch("carrega_stories.php?usuario_id=<?php echo $usuarioId; ?>");
        stories = await response.json();

        if (stories.length > 0) {
            indiceAtual = 0;
            mostrarStory(indiceAtual);
        } else {
            document.getElementById("storyMedia").innerHTML = "<p>Nenhum story encontrado.</p>";
        }
    }

    function mostrarStory(indice) {
        const story = stories[indice];
        const container = document.getElementById("storyMedia");

        const extensao = story.caminho_arquivo.split('.').pop().toLowerCase();
        let html = "";

        if (["mp4", "webm", "ogg"].includes(extensao)) {
            html = `<video src="${story.caminho_arquivo}" controls autoplay></video>`;
        } else {
            html = `<img src="${story.caminho_arquivo}" alt="Story" style="max-width: 100%; max-height: 80vh;">`;
        }

        container.innerHTML = html;
    }

    function mostrarProximo() {
        if (indiceAtual < stories.length - 1) {
            indiceAtual++;
            mostrarStory(indiceAtual);
        }
    }

    function mostrarAnterior() {
        if (indiceAtual > 0) {
            indiceAtual--;
            mostrarStory(indiceAtual);
        }
    }

    // Carrega os stories ao abrir a página
    carregarStories();
</script>

</body>
</html>
