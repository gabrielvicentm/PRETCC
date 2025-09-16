
<?php
include 'conexao.php'; 

if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $username = $_POST['username'];
    $email = $_POST['email']; 
    $senha = $_POST['senha']; 
    $cep = $_POST['cep'];
    $logradouro = $_POST['logradouro'];
    $bairro = $_POST['bairro'];
    $localidade = $_POST['localidade']; 
    $uf = $_POST['uf'];

    // Validação de campos obrigatórios
    if (empty($username) || empty($email) || empty($senha) || empty($cep) || empty($logradouro) || empty($bairro) || empty($localidade) || empty($uf)) { 
        die('Por favor, preencha todos os campos. Tente novamente <a href="cadastro.html">clicando aqui</a>.');
    }

    // Validação do username
    if (!preg_match('/^[a-zA-Z0-9._]+$/', $username)) {
        die('Nome de usuário inválido. Use apenas letras, números, ponto e underline. <a href="cadastro.html">Tente novamente</a>.');
    }

    // Continuação com o hash da senha e o cadastro...


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
            $stmtPerfil = $conn->prepare("
                INSERT INTO perfil (username, nome, bio, foto_perfil, cep, logradouro, bairro, localidade, uf) 
                VALUES (:username, '', '', '../uploads/default.jpg', :cep, :logradouro, :bairro, :localidade, :uf)
            ");
            $stmtPerfil->execute([
                ':username' => $username, ':cep' => $cep, ':logradouro' => $logradouro, 
                ':bairro' => $bairro, ':localidade' => $localidade, ':uf' => $uf
            ]);
        
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
