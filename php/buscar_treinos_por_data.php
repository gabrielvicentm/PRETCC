<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    exit("Usuário não logado.");
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['data'])) {
    exit("Data não fornecida.");
}

$data = $_GET['data'];

$host = 'localhost';
$db = 'tarcisio';
$user = 'root';
$pass = ''; // ajuste se necessário

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    exit("Erro na conexão: " . $conn->connect_error);
}

$sql = "SELECT * FROM treinos WHERE user_id = ? AND DATE(data_treino) = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("is", $user_id, $data);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<p>Nenhum treino encontrado nesta data.</p>";
} else {
    while ($treino = $result->fetch_assoc()) {
        echo "<div style='border:1px solid #ccc; padding:10px; margin-bottom:20px;'>";
        echo "<strong>Info do Treino:</strong> " . htmlspecialchars($treino['info_treino']) . "<br>";
        echo "<strong>Exercício:</strong> " . htmlspecialchars($treino['exercicio_nome']) . "<br>";

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
}

$conn->close();
?>
