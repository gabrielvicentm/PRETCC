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

  <!-- FullCalendar CSS/JS -->
  <link href='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css' rel='stylesheet' />
  <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js'></script>



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
  <h1>Seu Diário de Treino</h1>


  
  <div id='calendar' style="background-color: #1a1a1a; border-radius: 10px; padding: 20px;"></div>



<h2 style="margin-top: 40px;">Treinos do dia: <span id="dataSelecionada">Nenhuma data</span></h2>
<div id="treinosDoDia" style="margin-top: 20px;"></div>

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

$conn->close();
?>

  </div>

  
  <script>
document.addEventListener('DOMContentLoaded', function() {
  let calendarEl = document.getElementById('calendar');

  let calendar = new FullCalendar.Calendar(calendarEl, {
    initialView: 'dayGridMonth',
    height: 600,
    selectable: true,
    dateClick: function(info) {
      let dataSelecionada = info.dateStr;
      document.getElementById("dataSelecionada").textContent = dataSelecionada;
      fetchTreinos(dataSelecionada);
    }
  });

  calendar.render();
});

function fetchTreinos(data) {
  fetch('buscar_treinos_por_data.php?data=' + data)
    .then(response => response.text())
    .then(html => {
      document.getElementById('treinosDoDia').innerHTML = html;
    })
    .catch(error => {
      console.error("Erro ao buscar treinos:", error);
      document.getElementById('treinosDoDia').innerHTML = "<p style='color: red;'>Erro ao carregar treinos</p>";
    });
}
</script>


</body>
</html>
