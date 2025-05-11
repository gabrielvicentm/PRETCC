<?php
include 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $usuario = $_POST['usuario']; 
    $senha = $_POST['senha']; 

    if (empty($usuario) || empty($senha)) { 
        die('Por favor, preencha todos os campos. Tente novamente <a href="login.html">clicando aqui</a>.');
    }

    try {
        $sql = "SELECT * FROM usuario WHERE username = :usuario OR email = :usuario";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':usuario', $usuario);
        $stmt->execute();

        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($senha, $user['senha'])) { 
            // Inicia a sessão
            session_start();

            // Armazena o ID do usuário na sessão
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['username']; 

            // Redireciona para a home ou outra página protegida
            header("Location: ../php/home.php");  
            exit(); 
        } else {
            echo "Usuário ou senha inválidos. Tente novamente <a href='login.html'>clicando aqui</a>.";
        }
    } catch (PDOException $e) { 
        echo "Erro: " . $e->getMessage(); 
    }
}
?>  
