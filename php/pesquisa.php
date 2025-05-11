<?php
require_once 'conexao.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$busca = $_GET['q'] ?? '';
$resultados_usuarios = [];
$resultados_midias = [];

if ($busca) {
    // Buscar usuários pelo nome ou username
    $stmt_usuarios = $conn->prepare("
        SELECT p.nome, p.username, p.foto_perfil 
        FROM perfil p 
        WHERE p.username LIKE :busca OR p.nome LIKE :busca
    ");
    $stmt_usuarios->execute([':busca' => "%$busca%"]);
    $resultados_usuarios = $stmt_usuarios->fetchAll(PDO::FETCH_ASSOC);

    // Buscar mídias (posts + reels) pela descrição ou nome de usuário
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
    $stmt_midias->execute([':busca' => "%$busca%"]);
    $resultados_midias = $stmt_midias->fetchAll(PDO::FETCH_ASSOC);
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pesquisa</title>
    <link rel="stylesheet" href="../css/pesquisar.css">
</head>
<body>

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

<div class="content">
    <form method="GET" action="pesquisa.php">
        <input type="text" name="q" placeholder="Buscar usuários ou mídias..." required value="<?= htmlspecialchars($busca) ?>">
        <button type="submit">🔍</button>
    </form>

    <?php if ($busca): ?>
        <!-- Resultados de usuários -->
        <h3 style="color: #fff;">Usuários encontrados</h3>
        <div class="usuarios-grid">
            <?php foreach ($resultados_usuarios as $usuario): ?>
                <div class="usuario-item">
                    <img class="perfil-img" src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto de perfil">
                    <div class="result-link">
                        <a href="perfil.php?u=<?= htmlspecialchars($usuario['username']) ?>">
                            @<?= htmlspecialchars($usuario['username']) ?> - <?= htmlspecialchars($usuario['nome']) ?>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($resultados_usuarios)): ?>
                <p style="color: gray;">Nenhum usuário encontrado.</p>
            <?php endif; ?>

        </div>

        <!-- Resultados de mídias -->
        <hr style="margin: 30px 0; border-color: #444;">
        <h3 style="color: #fff;">Posts</h3>
        <div class="posts-grid">
            <?php
            if ($resultados_midias) {
                foreach ($resultados_midias as $midia) {
                    $ext = pathinfo($midia['arquivo'], PATHINFO_EXTENSION);
                    echo "<div class='post-item'>";
                    if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                        echo "<img src='posts/" . htmlspecialchars($midia['arquivo']) . "' alt='Post' class='post-media'>";
                    } elseif (in_array(strtolower($ext), ['mp4', 'webm', 'ogg'])) {
                        echo "<video controls class='post-media'><source src='posts/" . htmlspecialchars($midia['arquivo']) . "' type='video/$ext'>Seu navegador não suporta o vídeo.</video>";
                    } else {
                        echo "<p>Arquivo não suportado.</p>";
                    }
                    echo "<p style='color: #ccc; margin-top: 8px;'>" . nl2br(htmlspecialchars($midia['descricao'])) . "</p>";
                    echo "<p style='color: #888; font-size: 13px;'>Postado por @<a href='perfil.php?u=" . htmlspecialchars($midia['username']) . "' style='color: orange;'>" . htmlspecialchars($midia['username']) . "</a></p>";
                    echo "</div>";
                }
            } else {
                echo "<p style='color: gray;'>Nenhuma mídia encontrada.</p>";
            }
            ?>
        </div>
    <?php endif; ?>
</div>
</body>
</html>
