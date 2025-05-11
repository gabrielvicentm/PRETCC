<?php
$servername = "localhost";
$username = "root"; // ou o usuário que você configurou no MySQL
$password = "";     // senha do MySQL (se não tiver senha, deixa vazio mesmo)
$dbname = "tarcisio";

try {
    $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
    // Define o modo de erro do PDO para exceção
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // echo "Conexão realizada com sucesso!"; // pode habilitar para teste
} catch(PDOException $e) {
    echo "Erro na conexão: " . $e->getMessage();
}
?>
