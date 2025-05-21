<?php
// Inicia a sessão para verificar se o usuário está logado.
session_start();

// Requer a conexão com o banco de dados.
require_once 'conexao.php';

// Verifica se o usuário está logado. Caso contrário, redireciona para a página de login.
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Se não for passado um nome de usuário via GET, usa o nome do usuário logado como padrão.
$username = isset($_GET['u']) ? $_GET['u'] : $_SESSION['user_name'];

// Prepara e executa a consulta para buscar os dados do perfil do usuário.
$stmt = $conn->prepare("SELECT nome, bio, foto_perfil FROM perfil WHERE username = :username");
$stmt->execute([':username' => $username]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

// Verifica se o perfil acessado é o perfil próprio do usuário logado.
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
    <!-- Barra lateral com links de navegação -->
     <div class="logo">
  <img src="../img/logo.png" alt="Logo" style="height: 100px;">
</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></a>
    <a href="logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
  </div>
  
  <div class="content">
    <!-- Verifica se o perfil foi encontrado no banco de dados -->
    <?php if ($perfil): ?>
        <div class="perfil-header">
            <!-- Exibe a foto de perfil, se não houver foto usa uma imagem padrão -->
            <img src="<?= htmlspecialchars($perfil['foto_perfil'] ?: '../uploads/default.jpg') ?>" alt="Foto de Perfil" class="foto-redonda">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <!-- Nome do usuário no perfil -->
                    <h2 style="margin: 0;"><?= htmlspecialchars($perfil['nome']) ?></h2>
                    <!-- Se for o perfil próprio, exibe o botão para editar o perfil -->
                    <?php if ($perfil_proprio): ?>
                        <a href="edit_perfil.php">
                            <button style="font-size: 14px;">Editar Perfil</button>
                        </a>
                    <?php else: ?>
                        <!-- Se não for o perfil próprio, exibe a opção de seguir -->
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
                // Conta o número de seguidores do usuário
                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguidores = $stmt->fetchColumn();

                // Conta o número de pessoas que o usuário está seguindo
                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguindo = $stmt->fetchColumn();
                ?>

                <h4 style="margin-top: 15px; font-weight: normal; margin-left: 2px;">
                    <!-- Exibe os seguidores e seguindo, com a possibilidade de exibir as listas -->
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

                <!-- Lista de seguidores -->
                <div id="lista-seguidores" style="display:none; margin-top:20px;">
                    <h4>Seguidores</h4>
                    <ul>
                        <?php
                        // Busca os seguidores do usuário
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguidor_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            // Exibe cada seguidor como um link para o perfil
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- Lista de pessoas que o usuário está seguindo -->
                <div id="lista-seguindo" style="display:none; margin-top:20px;">
                    <h4>Seguindo</h4>
                    <ul>
                        <?php
                        // Busca as pessoas que o usuário está seguindo
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguido_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            // Exibe cada pessoa seguida como um link para o perfil
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <!-- Exibe a biografia do usuário -->
                <div>
                    <?= nl2br(htmlspecialchars($perfil['bio'])) ?>
                </div>

                <!-- Exibe os posts do usuário -->
                <hr style="margin: 30px 0; border-color: #444;">
                <h3 style="color: #fff;">Posts</h3>
                <div class="posts-grid">
                    <?php
                    // Busca os posts do usuário
                    $stmt = $conn->prepare("SELECT descricao, arquivo, data_post FROM posts WHERE usuario_id = (SELECT id FROM usuario WHERE username = :username) ORDER BY data_post DESC");
                    $stmt->execute([':username' => $username]);
                    $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    // Exibe os posts, dependendo do tipo de mídia (imagem ou vídeo)
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
            </div>
        </div>
    <?php else: ?>
        <!-- Se o perfil não for encontrado -->
        <p>Perfil não encontrado.</p>
    <?php endif; ?>
</div>

<script>
// Função para exibir ou esconder as listas de seguidores ou pessoas seguidas
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
