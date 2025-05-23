<?php
session_start(); // Inicia a sessão para verificar a autenticação do usuário

// Verifica se o usuário está logado. Caso contrário, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php'; // Conecta ao banco de dados

$viewerId = (int)$_SESSION['user_id']; // ID do usuário visualizador (usuário atual)

// Curtir post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['curtir']) && isset($_POST['post_id'])) {
    $postId = (int)$_POST['post_id']; // ID do post que foi curtido

    // Verifica se o usuário já curtiu o post
    $stmtCheck = $conn->prepare("SELECT * FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    $stmtCheck->execute([':uid' => $viewerId, ':pid' => $postId]);
    
    // Se já curtiu, remove a curtida, caso contrário, insere uma nova curtida
    if ($stmtCheck->rowCount() > 0) {
        $stmtDelete = $conn->prepare("DELETE FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
        $stmtDelete->execute([':uid' => $viewerId, ':pid' => $postId]);
    } else {
        $stmtInsert = $conn->prepare("INSERT INTO curtidas (usuario_id, postagem_id) VALUES (:uid, :pid)");
        $stmtInsert->execute([':uid' => $viewerId, ':pid' => $postId]);
    }

    // Redireciona para a página inicial após a ação
    header("Location: home.php");
    exit();
}

// Comentar post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario']) && isset($_POST['post_id'])) {
    $comentario = trim($_POST['comentario']); // Texto do comentário
    $postId = (int)$_POST['post_id']; // ID do post ao qual o comentário será associado
    
    // Verifica se o comentário não está vazio
    if ($comentario !== '') {
        // Insere o comentário no banco de dados
        $stmt = $conn->prepare("INSERT INTO comentarios (usuario_id, post_id, texto, data_comentario) VALUES (:uid, :pid, :texto, NOW())");
        $stmt->execute([':uid' => $viewerId, ':pid' => $postId, ':texto' => $comentario]);
    }
    // Redireciona de volta para a página inicial após o comentário ser feito
    header("Location: home.php");
    exit();
}

// Buscar todos os posts na página inicial
$stmt = $conn->prepare("
    SELECT p.id, p.usuario_id, p.descricao, p.arquivo, p.data_post, u.username, pf.foto_perfil
    FROM posts p
    JOIN usuario u ON u.id = p.usuario_id
    JOIN perfil pf ON u.username = pf.username
    ORDER BY p.data_post DESC
");
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC); // Retorna todos os posts

// Buscar usuários seguidos que possuem stories
$stmtUsersWithStories = $conn->prepare("
    SELECT u.id, u.username, pf.foto_perfil 
    FROM seguidores s
    JOIN usuario u ON u.id = s.seguido_id
    LEFT JOIN perfil pf ON u.username = pf.username
    WHERE s.seguidor_id = :viewerId
    AND EXISTS (
        SELECT 1 FROM stories st WHERE st.usuario_id = u.id
    )
");
$stmtUsersWithStories->bindParam(':viewerId', $viewerId);
$stmtUsersWithStories->execute();
$usersWithStories = $stmtUsersWithStories->fetchAll(PDO::FETCH_ASSOC); // Retorna os usuários com stories

// Verificar se o usuário já visualizou todos os stories
foreach ($usersWithStories as &$user) {
    // Conta o total de stories do usuário
    $stmtTotal = $conn->prepare("SELECT COUNT(*) FROM stories WHERE usuario_id = :uid");
    $stmtTotal->execute([':uid' => $user['id']]);
    $totalStories = $stmtTotal->fetchColumn();

    // Conta quantos stories foram visualizados pelo usuário
    $stmtViewed = $conn->prepare("
        SELECT COUNT(DISTINCT vs.story_id)
        FROM visualizacoes_stories vs
        JOIN stories s ON s.id = vs.story_id
        WHERE s.usuario_id = :uid AND vs.usuario_id = :viewerId
    ");
    $stmtViewed->execute([ ':uid' => $user['id'], ':viewerId' => $viewerId ]);
    $viewedCount = $stmtViewed->fetchColumn();

    // Marca se o usuário visualizou todos os stories
    $user['fully_viewed'] = ($viewedCount >= $totalStories);
}
unset($user); // Desfaz a referência para o último elemento do array
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Página Inicial</title>
  <link rel="stylesheet" href="../css/home.css">
</head>
<body>
  <div class="sidebar">
    <div class="logo">
  <img src="../img/logo.png" alt="Logo" style="height: 100px;">
</div>
    <!-- Links de navegação da barra lateral -->
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= $_SESSION['user_name']; ?></span></a>
    <a href="conversas.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png"/><span>Mensagens</span></a>
    <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png"/><span>Sair</span></a>
  </div>

  <div class="content">
    <!-- Exibe os stories dos usuários seguidos -->
    <?php if (!empty($usersWithStories)): ?>
      <div class="stories-bar">
        <?php foreach ($usersWithStories as $user): ?>
          <?php 
            // Verifica se o usuário já visualizou todos os stories
            $storyClass = $user['fully_viewed'] ? 'story-user viewed' : 'story-user'; 
          ?>
          <a href="ver_stories.php?username=<?= urlencode($user['username']); ?>" class="<?= $storyClass ?>">
            <div class="story-thumb">
              <img src="<?= htmlspecialchars($user['foto_perfil']); ?>" alt="Perfil de <?= htmlspecialchars($user['username']); ?>">
            </div>
            <div class="story-username"><?= htmlspecialchars($user['username']); ?></div>
          </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>

    <!-- Exibe os posts -->
    <div class="posts">
      <?php foreach ($posts as $post): ?>
        <div class="post">
          <div class="header">
            <img src="<?= htmlspecialchars($post['foto_perfil']); ?>" class="perfil-img">
            <span class="username"><?= htmlspecialchars($post['username']); ?></span>
          </div>

          <div class="media-container">
            <?php 
              $ext = strtolower(pathinfo($post['arquivo'], PATHINFO_EXTENSION));
              $mediaPath = 'posts/' . htmlspecialchars($post['arquivo']);

              // Exibe o arquivo de mídia (imagem ou vídeo)
              if (in_array($ext, ['jpg','jpeg','png','gif'])):
            ?>
              <img src="<?= $mediaPath ?>" class="media">
            <?php elseif ($ext === 'mp4'): ?>
              <video controls class="media">
                <source src="<?= $mediaPath ?>" type="video/mp4">
                Seu navegador não suporta o vídeo.
              </video>
            <?php endif; ?>
          </div>

          <?php 
            // Conta o número total de curtidas
            $stmtLikes = $conn->prepare("SELECT COUNT(*) FROM curtidas WHERE postagem_id = :pid");
            $stmtLikes->execute([':pid' => $post['id']]);
            $totalLikes = $stmtLikes->fetchColumn();

            // Verifica se o usuário já curtiu este post
            $stmtUserLiked = $conn->prepare("SELECT * FROM curtidas WHERE postagem_id = :pid AND usuario_id = :uid");
            $stmtUserLiked->execute([':pid' => $post['id'], ':uid' => $viewerId]);
            $userLiked = $stmtUserLiked->rowCount() > 0;
          ?>

          <div class="actions">
            <form method="POST">
              <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
              <button name="curtir" class="like-btn">
                <img src="https://img.icons8.com/ios-filled/24/<?= $userLiked ? 'ff0000' : 'ffffff'; ?>/like.png"/>
              </button>
            </form>
          </div>

          <div class="like-count">
            <?= $totalLikes ?> Curtidas
          </div>

          <div class="comments">
            <?php
              // Busca os comentários do post
              $stmtComments = $conn->prepare("SELECT c.texto, u.username, pf.foto_perfil 
                                              FROM comentarios c 
                                              JOIN usuario u ON u.id = c.usuario_id
                                              JOIN perfil pf ON pf.username = u.username
                                              WHERE c.post_id = :post_id");
              $stmtComments->execute([':post_id' => $post['id']]);
              $comments = $stmtComments->fetchAll(PDO::FETCH_ASSOC);

              // Exibe os comentários, se existirem
              if ($comments):
                foreach ($comments as $comment):
            ?>
              <div class="comment">
                <img src="<?= htmlspecialchars($comment['foto_perfil']); ?>" class="comment-img">
                <span class="comment-username"><?= htmlspecialchars($comment['username']); ?>:</span>
                <p class="comment-text"><?= htmlspecialchars($comment['texto']); ?></p>
              </div>
            <?php endforeach; else: ?>
              <p>Nenhum comentário ainda.</p>
            <?php endif; ?>
          </div>

          <form method="POST" class="comment-form">
            <input type="hidden" name="post_id" value="<?= $post['id']; ?>">
            <textarea name="comentario" placeholder="Adicionar comentário..."></textarea>
            <button type="submit">Comentar</button>
          </form>
        </div>
      <?php endforeach; ?>
    </div>
  </div>

  <script>
    // Salvar e restaurar posição do scroll
    window.onload = function() {
      const scrollPosition = sessionStorage.getItem('scrollPosition');
      if (scrollPosition) {
        window.scrollTo(0, scrollPosition);
      }
    }

    // Salva a posição do scroll antes de carregar a página
    window.onbeforeunload = function() {
      sessionStorage.setItem('scrollPosition', window.scrollY);
    }
  </script>
</body>
</html>
