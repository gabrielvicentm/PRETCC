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
    <h1>Bem-vindo à Página Inicial</h1>
    <p>Em breve, seu feed vai aparecer aqui!(eu espero)</p>









    <?php
require_once 'conexao.php';

$userId = $_SESSION['user_id'] ?? null;
if (!$userId) {
    die("Usuário não autenticado.");
}

$stmt = $conn->prepare("SELECT DISTINCT exercicio_nome FROM treinos WHERE user_id = ?");
$stmt->execute([$userId]);
$exercicios = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($exercicios)) {
    echo "<p>Nenhum exercício encontrado.</p>";
    exit;
}

foreach ($exercicios as $exercicio) {
    $exercicio_nome = htmlspecialchars($exercicio ?? 'Sem nome');
    echo "<h3 style='margin-top: 40px; font-size: 22px; text-align: center; color: #333;'>$exercicio_nome</h3>";

    $stmt = $conn->prepare("
        SELECT t.data_treino, MAX(CAST(s.peso AS DECIMAL(10,2))) AS peso_max
        FROM treinos t
        JOIN series s ON s.treino_id = t.id
        WHERE t.user_id = ? AND t.exercicio_nome = ?
        GROUP BY t.data_treino
        ORDER BY t.data_treino ASC
    ");
    $stmt->execute([$userId, $exercicio]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($dados)) {
        echo "<p style='color: gray; text-align: center;'>Nenhum dado registrado ainda.</p>";
        continue;
    }

    $pesos = array_map(fn($d) => floatval($d['peso_max']), $dados);
    $maiorPeso = max($pesos);
    $menorPeso = min($pesos);

    // Escala lateral
    $intervalos = 5;
    $escala = [];
    for ($i = $intervalos; $i >= 0; $i--) {
        $valor = $menorPeso + (($maiorPeso - $menorPeso) * ($i / $intervalos));
        $escala[] = round($valor, 2);
    }

    echo '<div style="display: flex; justify-content: center; align-items: flex-end; gap: 20px; margin-bottom: 50px;">';

    // Eixo Y (escala de pesos)
    echo '<div style="display: flex; flex-direction: column; justify-content: space-between; height: 300px; padding-right: 10px; font-size: 12px; color: #333;">';
    foreach ($escala as $label) {
        echo '<div style="height: ' . (300 / $intervalos) . 'px; display: flex; align-items: center; justify-content: flex-end;">' . $label . ' kg</div>';
    }
    echo '</div>';

    // Gráfico de barras
    echo '<div style="position: relative; display: flex; align-items: flex-end; gap: 30px; height: 300px;">';

    foreach ($dados as $dado) {
        $peso = floatval($dado['peso_max']);
        $data = htmlspecialchars($dado['data_treino'] ?? 'Sem data');

        if ($maiorPeso != $menorPeso) {
            $altura = ($peso - $menorPeso) / ($maiorPeso - $menorPeso) * 300;
        } else {
            $altura = 300;
        }

        echo '<div style="text-align: center; position: relative; width: 40px;">';
        echo '<div style="
            width: 40px;
            height: ' . $altura . 'px;
            background-color: #4CAF50;
            border-radius: 5px 5px 0 0;
            margin-bottom: 5px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.3);
        " title="Peso: ' . $peso . ' kg"></div>';
        echo '<div style="font-size: 12px; color: #333;">' . $data . '</div>';
        echo '</div>';
    }

    echo '</div>';
    echo '</div>';
}
?>






  </div>
</body>
</html>
