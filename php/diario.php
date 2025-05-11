<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Se não estiver logado, redireciona para a página de login
    header("Location: login.html");
    exit();
}

?>
<!-- O resto do HTML aqui -->



<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Home</title>
  <link rel="stylesheet" href="../css/diario.css">


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
  <?php
      echo "" . $_SESSION['user_name'];
  ?> </span></a>
    <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
  </div>

  <div class="content"> 
    <h1>Bem-vindo à Página Inicial</h1>
    <p>Em breve, seu feed vai aparecer aqui!(eu espero)</p>
    <div class="button-container">
        <a href="diario_form.php" class="diary-button">🏋️‍♂️ <span>Adicionar Treino</span></a>
        <a href="ver_treinos" class="diary-button">📅 <span>Histórico</span></a>
        <a href="estatisticas.html" class="diary-button">📊 <span>Estatísticas</span></a>
        <a href="metas.html" class="diary-button">🎯 <span>Metas</span></a>
        <a href="progresso.html" class="diary-button">📸 <span>Progresso Visual</span></a>
      </div>
      
  </div>
</body>
</html>