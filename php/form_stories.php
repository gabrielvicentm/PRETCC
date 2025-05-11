<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

require_once 'conexao.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['story'])) {
    $usuarioId = $_SESSION['user_id'];
    $arquivo = $_FILES['story'];

    if ($arquivo['error'] === UPLOAD_ERR_OK) {
        $extensao = pathinfo($arquivo['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '_' . basename($arquivo['name']);
        $diretorioDestino = 'stories/'; // Nova pasta
        $destino = $diretorioDestino . $nomeArquivo;

        // Cria a pasta 'stories' se não existir
        if (!is_dir($diretorioDestino)) {
            mkdir($diretorioDestino, 0777, true);
        }

        if (move_uploaded_file($arquivo['tmp_name'], $destino)) {
            // Salva no banco
            $sql = "INSERT INTO stories (usuario_id, caminho_arquivo, data_story) VALUES (:usuario_id, :caminho_arquivo, NOW())";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':usuario_id', $usuarioId);
            $stmt->bindParam(':caminho_arquivo', $destino);

            if ($stmt->execute()) {
                $mensagem = "Story postado com sucesso!";
            } else {
                $mensagem = "Erro ao salvar no banco de dados.";
            }
        } else {
            $mensagem = "Erro ao mover o arquivo.";
        }
    } else {
        $mensagem = "Erro no upload do arquivo.";
    }
}
?>



<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <link rel="stylesheet" href="../css/form_stories.css">
  <link rel="stylesheet" href="../css/home.css">
  <link rel="stylesheet" href="../css/postar.css">
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
</head>
<body>
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

  <div class="content"> 

    <form method="POST" enctype="multipart/form-data">
          <h2>Postar um novo Story</h2>
        <label for="story">Selecione uma imagem ou vídeo:</label><br>
        <input type="file" name="story" accept="image/*,video/*" required>
        <br>
<?php if (isset($mensagem)): ?>
    <?php
        $cor = (strpos($mensagem, 'sucesso') !== false) ? 'lightgreen' : 'red';
    ?>
    <span style="color: <?= $cor ?>;"><?= htmlspecialchars($mensagem) ?></span>
<?php endif; ?> 
<br>
<br>
        <input type="submit" value="Postar Story">
        <a href="postar.php"><button type="button">Postar post</button></a>
        </form>
  </div>
</body>
</html>
