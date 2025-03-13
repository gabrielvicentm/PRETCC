<?php
include 'conexao.php';

// verifica se a requisição é post, se for atriui valor ás vars e roda o resto do código
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $senha = $_POST['senha'];


    try {
        // sleciona todos os usuarios cadastrados pra posteriormente ver se tem um usuario com esse email e senha
        $sql = "SELECT * FROM usuarios WHERE email = :email AND senha = :senha";
        $stmt = $conn->prepare($sql);

        // Associa os valores aos placeholders pra evitar malandragem(hacker)
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senha);
        // Executa
        $stmt->execute();

        // Verifica se encontrou algum usuário
        if ($stmt->rowCount() == 1) {
            header("Location: index.html"); // redireciona para a página do carrinho
            exit();// para de rodar dps de direcionar pro index
        } else {
            echo "Usuário ou senha inválidos."; 
            header("Location: login.html"); // redireciona para a página de login
        }
    } catch (PDOException $e) {
        echo "Erro: " . $e->getMessage();
    }
}
?>
