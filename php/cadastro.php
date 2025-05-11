
<?php
include 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = $_POST['username'];
    $email = $_POST['email']; 
    $senha = $_POST['senha']; 

    if (empty($username) || empty($email) || empty($senha)) { 
        die('Por favor, preencha todos os campos. Tente novamente <a href="cadastro.html">clicando aqui</a>.');
    }

    // Gerar o hash seguro da senha
    $senhaHash = password_hash($senha, PASSWORD_DEFAULT);

    try { 
        // Inserir o usuário na tabela usuario
        $sql = "INSERT INTO usuario (username, email, senha) VALUES (:username, :email, :senha)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':username', $username);
        $stmt->bindParam(':email', $email);
        $stmt->bindParam(':senha', $senhaHash);

        if ($stmt->execute()) { 
            // Cadastro do usuário foi bem-sucedido, agora cria o perfil com foto padrão
            $stmtPerfil = $conn->prepare("INSERT INTO perfil (username, nome, bio, foto_perfil) VALUES (:username, '', '', '../uploads/default.jpg')");
            $stmtPerfil->execute([':username' => $username]);
        
            echo "Cadastro realizado com sucesso!"; 
            header("Location: ../html/login.html");  
            exit(); 
        } else {
            echo "Erro ao cadastrar. Tente novamente <a href='cadastro.html'>clicando aqui</a>.";
        }
    } catch (PDOException $e) { 
        echo "Erro: " . $e->getMessage(); 
    }
}
?>
