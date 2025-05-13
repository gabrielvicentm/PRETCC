






<?php
session_start();
require 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit;
}

$user_id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT username, email FROM usuario WHERE id = ?");
$stmt->execute([$user_id]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Configurações da Conta</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 50px; }
        form { max-width: 400px; margin: auto; }
        input { width: 100%; padding: 10px; margin: 10px 0; box-sizing: border-box; }
        button { padding: 10px 20px; background-color: #007bff; color: white; border: none; cursor: pointer; }
        h2 { text-align: center; }
    </style>

<link rel="stylesheet" href="../css/home.css">
</head>
<body>
  <div class="sidebar">
    <div class="logo" style="color: orange;">LOGO LINDA</div>
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
<h2>Configurações da Conta</h2>

<form method="post" action="form_config.php">
    <label>Username:</label>
    <input type="text" name="username" value="<?= htmlspecialchars($usuario['username']) ?>" required>

    <label>Email:</label>
    <input type="email" name="email" value="<?= htmlspecialchars($usuario['email']) ?>" required>

    <label>Nova Senha (opcional):</label>
    <input type="password" name="senha" placeholder="Deixe em branco para manter a atual">

    <button type="submit">Salvar Alterações</button>
</form>
</div>


</body>
</html>
