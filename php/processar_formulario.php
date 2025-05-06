<?php
session_start();

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

// Inclui a conexão com o banco
require_once 'conexao.php';

$user_id = $_SESSION['user_id'];
$infotreino = $_POST['infotreino'] ?? '';
$data_treino = $_POST['data_treino'] ?? null; // Captura a data do formulário

// Preparar para inserir os exercícios
$exercicios = [];
foreach ($_POST as $key => $value) {
    if (strpos($key, 'exercicio_') === 0 && !strpos($key, '_serie_')) {
        $index = explode('_', $key)[1];
        $exercicio_nome = $value;
        $num_series = $_POST["series_$index"] ?? 0;
        $series = [];

        for ($i = 1; $i <= $num_series; $i++) {
            $peso = $_POST["exercicio_{$index}_serie_{$i}_peso"] ?? '';
            $reps = $_POST["exercicio_{$index}_serie_{$i}_repeticoes"] ?? '';
            $series[] = ['peso' => $peso, 'reps' => $reps];
        }

        $exercicios[] = [
            'nome' => $exercicio_nome,
            'series' => $series
        ];
    }
}

// Agora insere no banco
try {
    foreach ($exercicios as $ex) {
        // Insere o exercício principal com a data do treino
        $stmt = $conn->prepare("INSERT INTO treinos (user_id, data_treino, info_treino, exercicio_nome) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $data_treino, $infotreino, $ex['nome']]);
        $treino_id = $conn->lastInsertId();

        // Insere as séries correspondentes
        foreach ($ex['series'] as $serie) {
            $stmtSerie = $conn->prepare("INSERT INTO series (treino_id, peso, repeticoes) VALUES (?, ?, ?)");
            $stmtSerie->execute([$treino_id, $serie['peso'], $serie['reps']]);
        }
    }

    echo "Treino salvo com sucesso!";
    // Redirecionar se quiser:
    // header("Location: diario.php?sucesso=1");

} catch (PDOException $e) {
    echo "Erro ao salvar treino: " . $e->getMessage();
}

header("Location: diario.php"); // Redireciona para a pagina principal do diário de treino
exit();
?>