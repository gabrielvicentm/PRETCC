<?php
session_start(); // Inicia a sessão para manter o estado de login do usuário

// Verifica se o usuário está logado, caso contrário redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redireciona para o login se o usuário não estiver logado
    exit(); // Termina a execução do script
}

// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

$user_id = $_SESSION['user_id']; // Pega o ID do usuário logado a partir da sessão
$infotreino = $_POST['infotreino'] ?? ''; // Pega informações adicionais sobre o treino, se enviadas
$data_treino = $_POST['data_treino'] ?? null; // Captura a data do treino a partir do formulário

// Inicializa um array para armazenar os exercícios do treino
$exercicios = [];

// Loop através de todas as chaves do POST para buscar os exercícios e suas séries
foreach ($_POST as $key => $value) {
    // Verifica se o campo é referente a um exercício (com a chave começando com 'exercicio_')
    if (strpos($key, 'exercicio_') === 0 && !strpos($key, '_serie_')) {
        // Extrai o índice do exercício (por exemplo, exercício_1, exercício_2)
        $index = explode('_', $key)[1];
        
        // Pega o nome do exercício
        $exercicio_nome = $value;
        
        // Captura o número de séries para o exercício
        $num_series = $_POST["series_$index"] ?? 0;
        
        $series = []; // Inicializa um array para armazenar as séries do exercício

        // Loop para capturar as informações de cada série (peso e repetições)
        for ($i = 1; $i <= $num_series; $i++) {
            $peso = $_POST["exercicio_{$index}_serie_{$i}_peso"] ?? ''; // Pega o peso da série
            $reps = $_POST["exercicio_{$index}_serie_{$i}_repeticoes"] ?? ''; // Pega as repetições da série
            // Adiciona as informações de peso e repetições da série no array
            $series[] = ['peso' => $peso, 'reps' => $reps];
        }

        // Adiciona o exercício com suas respectivas séries no array de exercícios
        $exercicios[] = [
            'nome' => $exercicio_nome,
            'series' => $series
        ];
    }
}

// Tenta inserir os dados no banco de dados
try {
    // Loop para inserir cada exercício e suas séries no banco
    foreach ($exercicios as $ex) {
        // Insere o exercício principal com o nome, a data do treino e as informações adicionais
        $stmt = $conn->prepare("INSERT INTO treinos (user_id, data_treino, info_treino, exercicio_nome) VALUES (?, ?, ?, ?)");
        $stmt->execute([$user_id, $data_treino, $infotreino, $ex['nome']]);
        
        // Pega o ID do treino recém inserido para associar as séries a ele
        $treino_id = $conn->lastInsertId();

        // Loop para inserir as séries correspondentes ao exercício no banco
        foreach ($ex['series'] as $serie) {
            // Insere cada série (peso e repetições) no banco associada ao ID do treino
            $stmtSerie = $conn->prepare("INSERT INTO series (treino_id, peso, repeticoes) VALUES (?, ?, ?)");
            $stmtSerie->execute([$treino_id, $serie['peso'], $serie['reps']]);
        }
    }

    // Exibe uma mensagem de sucesso após a inserção dos dados
    echo "Treino salvo com sucesso!";
    // Caso queira redirecionar para uma página de sucesso, descomente a linha abaixo:
    // header("Location: diario.php?sucesso=1");

} catch (PDOException $e) {
    // Caso ocorra algum erro ao salvar no banco, exibe a mensagem de erro
    echo "Erro ao salvar treino: " . $e->getMessage();
}

// Redireciona para a página principal do diário de treino após a execução
header("Location: diario.php");
exit();
?>
