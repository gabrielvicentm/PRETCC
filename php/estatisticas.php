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
       <div class="logo">
  <img src="../img/logo.png" alt="Logo" style="height: 100px;">
</div>
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

echo "<div id='graficos-container' style='padding: 20px; max-width: 1000px; margin: auto;'>";
$dadosPorExercicio = [];

foreach ($exercicios as $exercicio) {
    $stmt = $conn->prepare("
        SELECT DATE(t.data_treino) AS data_treino, MAX(CAST(s.peso AS DECIMAL(10,2))) AS peso_max
        FROM treinos t
        JOIN series s ON s.treino_id = t.id
        WHERE t.user_id = ? AND t.exercicio_nome = ?
        GROUP BY DATE(t.data_treino)
        ORDER BY DATE(t.data_treino) ASC
    ");
    $stmt->execute([$userId, $exercicio]);
    $dados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($dados)) {
        $dadosPorExercicio[] = [
            'nome' => $exercicio,
            'dados' => $dados
        ];
    }
}
?>

<style>
    .grafico {
        margin-bottom: 60px;
    }

    .grafico h3 {
        text-align: center;
        font-size: 22px;
        color: #333;
        margin-bottom: 20px;
    }

    .barras {
        display: flex;
        align-items: flex-end;
        justify-content: center;
        gap: 20px;
        height: 300px;
        border-left: 1px solid #ccc;
        border-bottom: 1px solid #ccc;
        padding: 10px;
        position: relative;
    }

    .barra {
        width: 40px;
        background-color: #4CAF50;
        border-radius: 5px 5px 0 0;
        text-align: center;
        color: #333;
        font-size: 12px;
        display: flex;
        flex-direction: column;
        justify-content: flex-end;
        position: relative;
    }

    .barra span {
        margin-top: 5px;
        display: block;
    }

    .label-data {
        transform: rotate(-45deg);
        transform-origin: top right;
        white-space: nowrap;
        position: absolute;
        bottom: -35px;
        left: 50%;
        translate: -50%;
    }
</style>

<script>
    const dados = <?php echo json_encode($dadosPorExercicio); ?>;

    function renderGraficos(dados) {
        const container = document.getElementById('graficos-container');

        dados.forEach(exercicio => {
            const section = document.createElement('div');
            section.className = 'grafico';

            const titulo = document.createElement('h3');
            titulo.textContent = exercicio.nome;
            section.appendChild(titulo);

            const wrapper = document.createElement('div');
            wrapper.className = 'barras';

            // encontrar peso máximo para escala
            const maxPeso = Math.max(...exercicio.dados.map(d => parseFloat(d.peso_max)));

            exercicio.dados.forEach(d => {
                const barra = document.createElement('div');
                barra.className = 'barra';

                const altura = (parseFloat(d.peso_max) / maxPeso) * 100;
                barra.style.height = `${altura}%`;

                // Tooltip
                barra.title = `${d.peso_max} kg em ${d.data_treino}`;

                const valor = document.createElement('span');
                valor.textContent = `${d.peso_max}kg`;
                barra.appendChild(valor);

                const data = document.createElement('div');
                data.className = 'label-data';
                data.textContent = d.data_treino;
                barra.appendChild(data);

                wrapper.appendChild(barra);
            });

            section.appendChild(wrapper);
            container.appendChild(section);
        });
    }

    renderGraficos(dados);
</script>







  </div>
</body>
</html>
