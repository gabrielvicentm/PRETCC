<?php
// Inclui a conexão com o banco de dados e inicia a sessão
require_once 'conexao.php';
session_start();

// Verifica se o usuário está logado, caso contrário, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtém o termo de busca a partir do parâmetro 'q' na URL, se não houver busca, será uma string vazia
$busca = $_GET['q'] ?? ''; 
$resultados_usuarios = []; // Inicializa o array para armazenar os usuários encontrados
$resultados_midias = []; // Inicializa o array para armazenar as mídias (posts e reels) encontradas

// Se houver um termo de busca, executa as consultas para buscar usuários e mídias
if ($busca) {
    // Consulta para buscar usuários pelo nome ou username
    $stmt_usuarios = $conn->prepare("
        SELECT p.nome, p.username, p.foto_perfil 
        FROM perfil p 
        WHERE p.username LIKE :busca OR p.nome LIKE :busca
    ");
    $stmt_usuarios->execute([':busca' => "%$busca%"]); // Executa a consulta passando o termo de busca
    $resultados_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC); // Armazena os resultados de usuários encontrados

    // Consulta para buscar mídias (posts e reels) pela descrição ou nome de usuário
    $stmt_midias = $conn->prepare("
        SELECT posts.descricao, posts.arquivo, posts.data_post, u.username 
        FROM posts 
        JOIN usuario u ON u.id = posts.usuario_id 
        WHERE posts.descricao LIKE :busca OR u.username LIKE :busca

        UNION

        SELECT reels.descricao, reels.arquivo, reels.data_post, u.username 
        FROM reels 
        JOIN usuario u ON u.id = reels.usuario_id 
        WHERE reels.descricao LIKE :busca OR u.username LIKE :busca

        ORDER BY data_post DESC
    ");
    $stmt_midias->execute([':busca' => "%$busca%"]); // Executa a consulta passando o termo de busca
    $resultados_midias = $stmt_midias->fetchAll(PDO::FETCH_ASSOC); // Armazena os resultados de mídias encontradas
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa</title>
    <link rel="stylesheet" href="../css/pesquisar.css"> <!-- Inclui o CSS da página -->
</head>
<body>

<!-- Sidebar com links para outras páginas -->
<div class="sidebar">
    <div class="logo" style="color: orange;">LOGO LINDA</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></a>
    <a href="logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png"/><span>Sair</span></a>
</div>

<!-- Conteúdo da página -->
<div class="content">
    <!-- Formulário de pesquisa -->
    <form method="GET" action="pesquisa.php">
        <input type="text" name="q" placeholder="Buscar usuários ou mídias..." required value="<?= htmlspecialchars($busca) ?>"> <!-- Campo de busca -->
        <button type="submit">🔍</button> <!-- Botão de pesquisa -->
    </form>

    <?php if ($busca): ?> <!-- Se houver um termo de busca, exibe os resultados -->

        <!-- Resultados de usuários -->
        <h3 style="color: #fff;">Usuários encontrados</h3>
        <div class="usuarios-grid">
            <?php foreach ($resultados_usuarios as $usuario): ?> <!-- Para cada usuário encontrado, exibe o nome e foto de perfil -->
                <div class="usuario-item">
                    <img class="perfil-img" src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
                    <div class="result-link">
                        <a href="perfil.php?u=<?= htmlspecialchars($usuario['username']) ?>"> <!-- Link para o perfil do usuário -->
                            @<?= htmlspecialchars($usuario['username']) ?> - <?= htmlspecialchars($usuario['nome']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($resultados_usuarios)): ?> <!-- Caso não haja usuários encontrados -->
                <p style="color: gray;">Nenhum usuário encontrado.</p>
            <?php endif; ?>

        </div>

        <!-- Resultados de mídias -->
        <hr style="margin: 30px 0; border-color: #444;"> <!-- Separador entre as seções -->
        <h3 style="color: #fff;">Posts</h3>
        <div class="posts-grid">
            <?php
            if ($resultados_midias) { // Se houver mídias encontradas, exibe-as
                foreach ($resultados_midias as $midia) {
                    // Verifica a extensão do arquivo para exibir corretamente
                    $ext = pathinfo($midia['arquivo'], PATHINFO_EXTENSION);
                    echo "<div class='post-item'>";
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        echo "<img src='posts/" . htmlspecialchars($midia['arquivo']) . "' alt='Post' class='post-media'>"; // Exibe imagem
                    } elseif (in_array(strtolower($ext), ['mp4', 'webm', 'ogg'])) {
                        echo "<video controls class='post-media'><source src='posts/" . htmlspecialchars($midia['arquivo']) . "' type='video/$ext'>Seu navegador não suporta o vídeo.</video>"; // Exibe vídeo
                    } else {
                        echo "<p>Arquivo não suportado.</p>"; // Exibe mensagem para arquivos não suportados
                    }
                    echo "<p style='color: #ccc; margin-top: 8px;'>" . nl2br(htmlspecialchars($midia['descricao'])) . "</p>"; // Exibe a descrição do post
                    echo "<p style='color: #888; font-size: 13px;'>Postado por @<a href='perfil.php?u=" . htmlspecialchars($midia['username']) . "' style='color: orange;'>" . htmlspecialchars($midia['username']) . "</a></p>"; // Exibe o nome do usuário que postou
                    echo "</div>";
                }
            } else {
                echo "<p style='color: gray;'>Nenhuma mídia encontrada.</p>"; // Mensagem caso não haja mídias
            }
            ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
