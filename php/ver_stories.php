<?php
// Inicia a sessão
session_start();
// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redireciona para a página de login caso não esteja logado
    exit();
}

// Verifica se o parâmetro 'username' foi passado na URL
if (!isset($_GET['username'])) {
    echo "Usuário não especificado."; // Exibe uma mensagem de erro se o username não for informado
    exit();
}

// Captura o valor do username a partir da URL
$username = $_GET['username'];

// Prepara a consulta para buscar o ID do usuário pelo username
$stmtUser = $conn->prepare("SELECT id FROM usuario WHERE username = :username");
$stmtUser->bindParam(':username', $username); // Faz o bind do parâmetro 'username'
$stmtUser->execute(); // Executa a consulta
$user = $stmtUser->fetch(PDO::FETCH_ASSOC); // Recupera o usuário correspondente

// Verifica se o usuário foi encontrado
if (!$user) {
    echo "Usuário não encontrado."; // Exibe erro caso o usuário não exista
    exit();
}

// Recupera o ID do usuário
$usuarioId = $user['id'];

// Prepara a consulta para buscar todos os stories do usuário, ordenando pela data
$stmt = $conn->prepare("SELECT id FROM stories WHERE usuario_id = :usuarioId ORDER BY data_story ASC");
$stmt->bindParam(':usuarioId', $usuarioId); // Faz o bind do parâmetro 'usuario_id'
$stmt->execute(); // Executa a consulta
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC); // Recupera todos os stories

// Registra a visualização de cada story
foreach ($stories as $story) {
    // Prepara a consulta para registrar a visualização do story
    $stmtVisualizacao = $conn->prepare("
        INSERT INTO visualizacoes_stories (usuario_id, story_id) 
        VALUES (:viewerId, :storyId)
    ");
    $stmtVisualizacao->bindParam(':viewerId', $_SESSION['user_id']); // ID do usuário que visualizou
    $stmtVisualizacao->bindParam(':storyId', $story['id']); // ID do story visualizado
    $stmtVisualizacao->execute(); // Executa a inserção no banco de dados
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Stories de <?php echo htmlspecialchars($username); ?></title> <!-- Título da página com o nome do usuário -->
    <link rel="stylesheet" href="../css/ver_stories.css"> <!-- Link para o arquivo de estilo CSS -->
</head>
<body>

<a href="home.php">
    <span class="gambiarra">X</span> <!-- Link de retorno à página inicial -->
</a>

<!-- Container para exibir os stories -->
<div class="story-container">
    <button class="prev-button" onclick="mostrarAnterior()">◀</button> <!-- Botão para mostrar o story anterior -->
    <div class="story-media" id="storyMedia"></div> <!-- Área onde o story será exibido -->
    <button class="next-button" onclick="mostrarProximo()">▶</button> <!-- Botão para mostrar o próximo story -->
</div>

<script>
    let stories = []; // Array para armazenar os stories carregados
    let indiceAtual = 0; // Índice do story atual

    // Função assíncrona para carregar os stories do usuário
    async function carregarStories() {
        // Faz uma requisição para carregar os stories do usuário
        const response = await fetch("carrega_stories.php?usuario_id=<?php echo $usuarioId; ?>");
        stories = await response.json(); // Converte a resposta JSON para um array de stories

        // Verifica se existem stories
        if (stories.length > 0) {
            indiceAtual = 0; // Começa com o primeiro story
            mostrarStory(indiceAtual); // Exibe o primeiro story
        } else {
            document.getElementById("storyMedia").innerHTML = "<p>Nenhum story encontrado.</p>"; // Caso não haja stories, exibe uma mensagem
        }
    }

    // Função para exibir o story no índice atual
    function mostrarStory(indice) {
        const story = stories[indice]; // Obtém o story pelo índice
        const container = document.getElementById("storyMedia");

        const extensao = story.caminho_arquivo.split('.').pop().toLowerCase(); // Obtém a extensão do arquivo
        let html = "";

        // Verifica se o arquivo é de vídeo ou imagem e exibe o conteúdo adequadamente
        if (["mp4", "webm", "ogg"].includes(extensao)) {
            html = `<video src="${story.caminho_arquivo}" controls autoplay></video>`; // Exibe o vídeo com controle
        } else {
            html = `<img src="${story.caminho_arquivo}" alt="Story" style="max-width: 100%; max-height: 80vh;">`; // Exibe a imagem
        }

        container.innerHTML = html; // Atualiza o conteúdo da área do story
    }

    // Função para mostrar o próximo story
    function mostrarProximo() {
        if (indiceAtual < stories.length - 1) {
            indiceAtual++; // Incrementa o índice
            mostrarStory(indiceAtual); // Exibe o próximo story
        }
    }

    // Função para mostrar o story anterior
    function mostrarAnterior() {
        if (indiceAtual > 0) {
            indiceAtual--; // Decrementa o índice
            mostrarStory(indiceAtual); // Exibe o story anterior
        }
    }

    // Carrega os stories ao abrir a página
    carregarStories();
</script>

</body>
</html>
