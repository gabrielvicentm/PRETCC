<?php
// Inicia a sessão para garantir que o usuário esteja logado
session_start(); 
// Inclui a conexão com o banco de dados
require_once 'conexao.php';

// Verifica se o usuário está logado. Se não estiver, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Obtém o ID do usuário da sessão
$usuario_id = $_SESSION['user_id'];

// Verifica se o formulário foi enviado via POST e se os parâmetros necessários estão presentes
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['comentario'], $_POST['post_id'], $_POST['post_index'])) {
    $comentario = trim($_POST['comentario']); // Obtém o texto do comentário
    $post_id = filter_input(INPUT_POST, 'post_id', FILTER_VALIDATE_INT); // Valida o ID do post
    $post_index = filter_input(INPUT_POST, 'post_index', FILTER_VALIDATE_INT); // Valida o índice do post

    // Se os campos estão preenchidos corretamente, insere o comentário no banco de dados
    if ($comentario !== '' && $post_id !== false && $post_index !== false) {
        // Prepara o comando SQL para inserir o comentário
        $sql_insert = "INSERT INTO comentarios (usuario_id, post_id, texto, data_comentario) VALUES (:usuario_id, :post_id, :texto, NOW())";
        $stmt_insert = $conn->prepare($sql_insert);
        $stmt_insert->execute([
            ':usuario_id' => $usuario_id,
            ':post_id' => $post_id,
            ':texto' => $comentario
        ]);
        // Redireciona para o post com o comentário adicionado
        header("Location: reels.php#post-$post_index");
        exit();
    }
}

// Consulta os posts para exibição na página
$sql = "
    SELECT r.*, u.username, pr.foto_perfil
    FROM reels r
    JOIN usuario u ON r.usuario_id = u.id
    LEFT JOIN perfil pr ON u.username = pr.username
    ORDER BY r.data_post DESC
";
$stmt = $conn->prepare($sql);
$stmt->execute();
$posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Consulta os comentários de todos os posts
$sql_coment = "
    SELECT c.*, u.username, p.foto_perfil 
    FROM comentarios c
    JOIN usuario u ON c.usuario_id = u.id
    LEFT JOIN perfil p ON u.username = p.username
    ORDER BY c.data_comentario ASC
";
$stmt_c = $conn->prepare($sql_coment);
$stmt_c->execute();
$comentarios_raw = $stmt_c->fetchAll(PDO::FETCH_ASSOC);

// Organiza os comentários por post_id
$comentarios = [];
foreach ($comentarios_raw as $c) {
    $comentarios[$c['post_id']][] = $c;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Reels</title>
  <link rel="stylesheet" href="../css/reels.css"/>
</head>
<body>

<!-- Sidebar com links de navegação -->
<div class="sidebar">
   <div class="logo">
  <img src="../img/logo.png" alt="Logo" style="height: 100px;">
</div>
  <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
  <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
  <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
  <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
  <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= $_SESSION['user_name']; ?></span></a>
    <a href="conversas.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/speech-bubble.png"/><span>Mensagens</span></a>
  <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png"/><span>Sair</span></a>
</div>

<!-- Container que vai exibir os posts -->
<div class="container" id="reels-container">
<?php foreach ($posts as $index => $post): ?>

  <?php
    $stmt_like = $conn->prepare("SELECT COUNT(*) FROM curtidas WHERE usuario_id = :uid AND postagem_id = :pid");
    $stmt_like->execute([':uid' => $usuario_id, ':pid' => $post['id']]);
    $curtido = $stmt_like->fetchColumn() > 0;

    $stmt_total = $conn->prepare("SELECT COUNT(*) FROM curtidas WHERE postagem_id = :pid");
    $stmt_total->execute([':pid' => $post['id']]);
    $totalCurtidas = $stmt_total->fetchColumn();
  ?>
  <!-- Exibe cada post -->
  <div class="post" data-index="<?= $index ?>" id="post-<?= $index ?>" style="<?= $index === 0 ? '' : 'display:none;' ?>">
    <div class="header">
      <!-- Exibe o nome de usuário e foto de perfil -->
      <a href="perfil.php?usuario=<?= htmlspecialchars($post['username']) ?>">
        <img src="<?= htmlspecialchars($post['foto_perfil']) ?>" alt="Perfil" class="perfil-img">
      </a>
      <span class="username"><?= htmlspecialchars($post['username']) ?></span>
    </div>

    <!-- Descrição do post -->
    <div class="descricao-post">
      <p><strong><?= htmlspecialchars($post['descricao']) ?></strong></p>
    </div>

    <!-- Exibição da mídia (imagem ou vídeo) -->
    <div class="media-container">
      <div class="media">
        <?php
          $ext = strtolower(pathinfo($post['arquivo'], PATHINFO_EXTENSION)); // Obtém a extensão do arquivo
          $file = 'posts/' . $post['arquivo'];
          // Verifica o tipo de mídia (imagem ou vídeo) e exibe o conteúdo corretamente
          if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif', 'webp'])) {
              echo "<img src='$file' alt='Post'>";
          } elseif (in_array($ext, ['mp4', 'webm', 'ogg'])) {
              echo "<video src='$file' controls autoplay loop></video>";
          } else {
              echo "Tipo de mídia não suportado.";
          }
        ?>

        <div class="actions">
          <!-- Formulário para curtir o post -->
          <form action="curtir.php" method="POST" style="display:inline;">
            <input type="hidden" name="usuario_id" value="<?= $usuario_id ?>">
            <input type="hidden" name="postagem_id" value="<?= $post['id'] ?>">
            <input type="hidden" name="post_index" value="<?= $index ?>">
            <button type="submit" class="like-btn <?= $curtido ? 'liked' : '' ?>">❤️</button>
          </form>
          <span class="like-count"><?= $totalCurtidas ?></span>
          <!-- Botão para exibir ou ocultar comentários -->
          <button class="comment-btn" onclick="toggleCommentsPanel(this)">💬</button>
        </div>

        <!-- Navegação entre posts -->
        <?php if (count($posts) > 1): ?>
        <div class="arrow arrow-up" onclick="mudarPost(-1)">▲</div>
        <div class="arrow arrow-down" onclick="mudarPost(1)">▼</div>
        <?php endif; ?>
      </div>

      <!-- Painel de comentários -->
      <div class="comments-panel">
        <h3>Comentários</h3>
        <div class="comments-list">
          <?php if (!empty($comentarios[$post['id']])): ?>
            <!-- Exibe os comentários de cada post -->
            <?php foreach ($comentarios[$post['id']] as $coment): ?>
              <div class="comentario">
                <a href="perfil.php?usuario=<?= htmlspecialchars($coment['username']) ?>">
                  <img src="<?= htmlspecialchars($coment['foto_perfil'] ?? '') ?>" class="comentario-img" alt="Perfil"/>
                </a>
                <p><strong><?= htmlspecialchars($coment['username']) ?>:</strong> <?= htmlspecialchars($coment['texto']) ?></p>
              </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p>Sem comentários ainda.</p>
          <?php endif; ?>
        </div>

        <!-- Formulário para enviar um comentário -->
        <form class="comment-form" method="POST" action="reels.php">
          <input type="hidden" name="post_id" value="<?= $post['id'] ?>">
          <input type="hidden" name="post_index" value="<?= $index ?>">
          <input type="text" name="comentario" placeholder="Digite um comentário..." required>
          <button type="submit">Enviar</button>
        </form>
      </div>
    </div>
  </div>
<?php endforeach; ?>
</div>

<script>
// Função para mostrar o post baseado no índice
let indexAtual = 0;
const posts = document.querySelectorAll('.post');

function mostrarPost(index) {
  posts.forEach((post, i) => {
    const video = post.querySelector("video");
    post.style.display = i === index ? 'flex' : 'none';
    if (video) {
      if (i === index) {
        video.play();
      } else {
        video.pause();
        video.currentTime = 0;
      }
    }
  });
  indexAtual = index;
}

// Função para mudar o post (para cima ou para baixo)
function mudarPost(direcao) {
  let novoIndex = indexAtual + direcao;
  if (novoIndex < 0) novoIndex = posts.length - 1;
  if (novoIndex >= posts.length) novoIndex = 0;
  mostrarPost(novoIndex);
  window.location.hash = `post-${novoIndex}`;
}

// Função para exibir ou ocultar o painel de comentários
function toggleCommentsPanel(button) {
  const panel = button.closest('.media-container').querySelector('.comments-panel');
  panel.classList.toggle('visible');
}

// Configura o post inicial com base no fragmento da URL
document.addEventListener('DOMContentLoaded', () => {
  const fragment = window.location.hash;
  if (fragment.startsWith("#post-")) {
    const idx = parseInt(fragment.replace("#post-", "")); 
    if (!isNaN(idx) && idx >= 0 && idx < posts.length) {
      mostrarPost(idx);
      return;
    }
  }
  mostrarPost(0);
});
</script>

</body>
</html>
