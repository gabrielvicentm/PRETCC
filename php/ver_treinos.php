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
  <link rel="stylesheet" href="../css/home.css">


</head>
<body>
  <div class="sidebar">
      <div class="logo" style="color: orange;">LOGO LINDA</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="../php/diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
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
    <h1>Bem-vindo à Página de Visualização de treinos</h1>

    <?php

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Conexão com o banco (ajuste as credenciais conforme necessário)
$host = 'localhost';
$db = 'tarcisio';
$user = 'root';
$pass = ''; // ajuste sua senha
$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Falha na conexão: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

// Consulta os treinos do usuário
$sql = "SELECT * FROM treinos WHERE user_id = ? ORDER BY created_at DESC";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

echo "<h1>Seus Treinos</h1>";

while ($treino = $result->fetch_assoc()) {
    echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:20px;'>";
    echo "<strong>Data:</strong> " . date("d/m/Y", strtotime($treino['created_at'])) . "<br>";
    echo "<strong>Info do Treino:</strong> " . htmlspecialchars($treino['info_treino']) . "<br>";
    echo "<strong>Exercício:</strong> " . htmlspecialchars($treino['exercicio_nome']) . "<br>";

    // Buscar as séries relacionadas
    $sql_series = "SELECT * FROM series WHERE treino_id = ?";
    $stmt_series = $conn->prepare($sql_series);
    $stmt_series->bind_param("i", $treino['id']);
    $stmt_series->execute();
    $result_series = $stmt_series->get_result();

    echo "<ul>";
    while ($serie = $result_series->fetch_assoc()) {
        echo "<li>Peso: " . htmlspecialchars($serie['peso']) . " | Repetições: " . htmlspecialchars($serie['repeticoes']) . "</li>";
    }
    echo "</ul>";
    echo "</div>";
}

$conn->close();
?>

  </div>
</body>
</html>