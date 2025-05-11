<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$username = isset($_GET['u']) ? $_GET['u'] : $_SESSION['user_name'];

$stmt = $conn->prepare("SELECT nome, bio, foto_perfil FROM perfil WHERE username = :username");
$stmt->execute([':username' => $username]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

$perfil_proprio = ($username === $_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="stylesheet" href="../css/perfil.css">
  
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
    <a href="logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
  </div>
  <div class="content">
    
    <?php if ($perfil): ?>
        <div class="perfil-header">
            <!-- Exibe a foto de perfil, usando a imagem default se não existir foto -->
            <img src="<?= htmlspecialchars($perfil['foto_perfil'] ?: '../uploads/default.jpg') ?>" alt="Foto de Perfil" class="foto-redonda">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <h2 style="margin: 0;"><?= htmlspecialchars($perfil['nome']) ?></h2>
                    <?php if ($perfil_proprio): ?>
                        <a href="edit_perfil.php">
                            <button style="font-size: 14px;">Editar Perfil</button>
                        </a>
                    <?php else: ?>
                        <form method="POST" action="seguir.php" style="display:inline;">
                            <input type="hidden" name="seguido" value="<?= htmlspecialchars($username) ?>">
                            <button type="submit">➕ Seguir</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div style="color:rgba(211, 211, 211, 0.66); font-size: 14px; margin-top: 7px;">
                    @<?= htmlspecialchars($username) ?>
                </div>

                <?php
                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguidores = $stmt->fetchColumn();

                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguindo = $stmt->fetchColumn();
                ?>

                <h4 style="margin-top: 15px; font-weight: normal; margin-left: 2px;">
                    <?php if ($seguidores > 0): ?>
                        <span style="cursor:pointer;" onclick="mostrarLista('seguidores')"><?= $seguidores ?> seguidores</span>
                    <?php else: ?>
                        <span style="color: pink;"><?= $seguidores ?> seguidores</span>
                    <?php endif; ?>
                    •
                    <?php if ($seguindo > 0): ?>
                        <span style="cursor:pointer;" onclick="mostrarLista('seguindo')"><?= $seguindo ?> seguindo</span>
                    <?php else: ?>
                        <span style="color:gray;"><?= $seguindo ?> seguindo</span>
                    <?php endif; ?>
                </h4>

                <div id="lista-seguidores" style="display:none; margin-top:20px;">
                    <h4>Seguidores</h4>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguidor_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div id="lista-seguindo" style="display:none; margin-top:20px;">
                    <h4>Seguindo</h4>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguido_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div>
                    <?= nl2br(htmlspecialchars($perfil['bio'])) ?>
                </div>



<!-- CÓDIGO DO ABNER --> <!-- CÓDIGO DO ABNER -->
<!-- CÓDIGO DO ABNER -->
<!-- CÓDIGO DO ABNER -->

<hr style="margin: 30px 0; border-color: #444;">

<h3 style="color: #fff;">Posts</h3>
<div class="posts-grid">
    <?php
    $stmt = $conn->prepare("SELECT descricao, arquivo, data_post FROM posts WHERE usuario_id = (SELECT id FROM usuario WHERE username = :username) ORDER BY data_post DESC");
    $stmt->execute([':username' => $username]);
    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($posts) {
        foreach ($posts as $post) {
            $ext = pathinfo($post['arquivo'], PATHINFO_EXTENSION);
            echo "<div class='post-item'>";
            if (in_array(strtolower($ext), ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
                echo "<img src='posts/" . htmlspecialchars($post['arquivo']) . "' alt='Post' class='post-media'>";
            } elseif (in_array(strtolower($ext), ['mp4', 'webm', 'ogg'])) {
                echo "<video controls class='post-media'><source src='posts/" . htmlspecialchars($post['arquivo']) . "' type='video/$ext'>Seu navegador não suporta o vídeo.</video>";
            } else {
                echo "<p>Arquivo não suportado.</p>";
            }
            echo "<p style='color: #ccc; margin-top: 8px;'>" . nl2br(htmlspecialchars($post['descricao'])) . "</p>";
            echo "</div>";
        }
    } else {
        echo "<p style='color: gray;'>Nenhum post encontrado.</p>";
    }
    ?>
</div>

<!-- CÓDIGO DO ABNER -->
<!-- CÓDIGO DO ABNER -->
<!-- CÓDIGO DO ABNER -->
<!-- CÓDIGO DO ABNER -->





            </div>
        </div>
    <?php else: ?>
        <p>Perfil não encontrado.</p>
    <?php endif; ?>
</div>

<script>
function mostrarLista(tipo) {
    const seguidores = <?= $seguidores ?>;
    const seguindo = <?= $seguindo ?>;

    if (tipo === 'seguidores' && seguidores > 0) {
        document.getElementById('lista-seguidores').style.display = 'block';
        document.getElementById('lista-seguindo').style.display = 'none';
    } else if (tipo === 'seguindo' && seguindo > 0) {
        document.getElementById('lista-seguindo').style.display = 'block';
        document.getElementById('lista-seguidores').style.display = 'none';
    }
}
</script>
</body>
</html>