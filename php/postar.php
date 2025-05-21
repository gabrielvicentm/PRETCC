<!DOCTYPE html>
<html lang="pt-br">
<head>
    <!-- Link para os arquivos de estilo da página -->
    <link rel="stylesheet" href="../css/postar.css">
    <link rel="stylesheet" href="../css/home.css">
    
    <!-- Configurações de caracteres e responsividade -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    
    <title>Postar Mídia</title>

    <!-- Função JavaScript para pré-visualizar a mídia escolhida -->
    <script>
        function previewFile(input) {
            const preview = document.getElementById('preview-img');  // A imagem de preview
            const container = document.getElementById('preview-container'); // O container do preview
            const file = input.files[0];  // Arquivo escolhido
            const reader = new FileReader();  // Objeto FileReader para ler o arquivo

            reader.addEventListener("load", function () {
                preview.src = reader.result; // Atualiza a imagem de preview com o conteúdo do arquivo
                preview.style.display = 'block'; // Torna o preview visível
                container.style.lineHeight = 0; // Remove a linha de texto que está no container
            }, false);

            if (file) {
                reader.readAsDataURL(file); // Lê o arquivo como URL para a visualização
            }
        }
    </script>
</head>
<body>

<?php
session_start(); // Inicia a sessão para verificar se o usuário está logado

// Verifica se o usuário está logado, se não, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php'; // Inclui a conexão com o banco de dados

// Consulta para buscar os stories recentes de usuários
$sql = "
    SELECT s.caminho_arquivo, s.data_story, u.username, p.foto_perfil
    FROM stories s
    JOIN usuario u ON s.usuario_id = u.id
    LEFT JOIN perfil p ON u.username = p.username
    ORDER BY s.data_story DESC
";

// Executa a consulta ao banco
$stmt = $conn->prepare($sql);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazena os resultados dos stories
?>

<!-- Barra lateral com links para outras páginas do site -->
<div class="sidebar">
    <div class="logo">
  <img src="../img/logo.png" alt="Logo" style="height: 100px;">
</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span>
        <?php echo $_SESSION['user_name']; ?> <!-- Exibe o nome do usuário logado -->
    </span></a>
    <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
</div>

<!-- Container principal da página de postagem -->
<div class="container">
    <!-- Formulário de envio de mídia -->
    <form action="salvar_post.php" method="POST" enctype="multipart/form-data">
        <h1>Enviar Post</h1>

        <!-- Campo para inserir a descrição do post -->
        <label for="descricao">Descrição:</label>
        <textarea name="descricao" id="descricao" rows="4" cols="50" required></textarea>

        <!-- Campo para selecionar a mídia (imagem ou vídeo) -->
        <label for="midia">Escolha uma Mídia (imagem ou vídeo):</label>
        <div class="file-wrapper">
            <!-- Container para o preview da mídia -->
            <div class="file-preview" id="preview-container">
                <img id="preview-img" src="" alt="preview"> <!-- Imagem de preview -->
                Selecionar arquivo
            </div>
            <input type="file" name="midia" id="midia" required onchange="previewFile(this)"> <!-- Campo para escolher o arquivo -->
        </div>

        <!-- Checkbox para decidir se a mídia será postada como um Reels -->
        <label for="ir_reels">
            <input type="checkbox" name="ir_reels" id="ir_reels"> <!-- Checkbox para Reels -->
            Você quer que essa mídia vá para o Reels?
        </label>

        <!-- Botões de envio -->
        <button type="submit">Postar</button>
        <a href="form_stories.php"><button type="button">Postar stories</button></a>
    </form>
</div>

</body>
</html>
