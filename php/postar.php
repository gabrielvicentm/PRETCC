<!DOCTYPE html>
<html lang="pt-br">
<head>
    <link rel="stylesheet" href="../css/postar.css">
    <link rel="stylesheet" href="../css/home.css">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Postar Mídia</title>

    <script>
        function previewFile(input) {
            const preview = document.getElementById('preview-img');
            const container = document.getElementById('preview-container');
            const file = input.files[0];
            const reader = new FileReader();

            reader.addEventListener("load", function () {
                preview.src = reader.result;
                preview.style.display = 'block';
                container.style.lineHeight = 0;
            }, false);

            if (file) {
                reader.readAsDataURL(file);
            }
        }
    </script>
</head>
<body>
<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php';

// Busca stories recentes (opcional: últimos 24h)
$sql = "
    SELECT s.caminho_arquivo, s.data_story, u.username, p.foto_perfil
    FROM stories s
    JOIN usuario u ON s.usuario_id = u.id
    LEFT JOIN perfil p ON u.username = p.username
    ORDER BY s.data_story DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute();
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="sidebar">
    <div class="logo" style="color: orange;">LOGO LINDA</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span>
        <?php echo $_SESSION['user_name']; ?>
    </span></a>
    <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
</div>

<div class="container">
    <form action="salvar_post.php" method="POST" enctype="multipart/form-data">
        <h1>Enviar Post</h1>
        <label for="descricao">Descrição:</label>
        <textarea name="descricao" id="descricao" rows="4" cols="50" required></textarea>

        <label for="midia">Escolha uma Mídia (imagem ou vídeo):</label>
        <div class="file-wrapper">
            <div class="file-preview" id="preview-container">
                <img id="preview-img" src="" alt="preview">
                Selecionar arquivo
            </div>
            <input type="file" name="midia" id="midia" required onchange="previewFile(this)">
        </div>

        <!-- Checkbox para decidir se a mídia vai para o Reels -->
        <label for="ir_reels">
            <input type="checkbox" name="ir_reels" id="ir_reels">
            Você quer que essa mídia vá para o Reels?
        </label>

        <button type="submit">Postar</button>
        <a href="form_stories.php"><button type="button">Postar stories</button></a>
    </form>
</div>
</body>
</html>
