<?php
include 'conexao.php'; 

// verifica se a requisição é post, se for atriui valor ás vars e roda o resto do código
if ($_SERVER['REQUEST_METHOD'] === 'POST') { 
    $email = $_POST['email']; 
    $senha = $_POST['senha']; 
    // pega o valor dos campos do formulário e atribui as variáveis $nome $email e $senha


    if (empty($email) || empty($senha)) { 
    // Verifica se algum dos campos está vazio.
        die('Por favor, preencha todos os campos. Tente novamente <a href="cadastro.html">clicando aqui</a>.');
    }
    

    try { // Insere os dados na tabela usuarios, usando placeholders para segurança
        $sql = "INSERT INTO usuario ( email, senha) VALUES (:email, :senha)"; 
        $stmt = $conn->prepare($sql); 
        // Prepara a consulta sql para execução com o banco de dados.

        // Vincula o valor da variável  $email e $senha com o placeholder :email :senha na consulta SQL,
        $stmt->bindParam(':email', $email); 
        $stmt->bindParam(':senha', $senha); 

        // Executa a consulta se der certo vai para a página de login
        if ($stmt->execute()) { 
            echo "Cadastro realizado com sucesso!"; 
            header("Location: login.html");  
            exit(); 
            // para de rodar dps de direcionar pro login
        } else {
            echo "Erro ao cadastrar. Tente novamente <a href='cadastro.html'>clicando aqui</a>."; 
            // Exibe uma mensagem de erro se a execução da consulta falhar
        }
    } catch (PDOException $e) { 
        echo "Erro: " . $e->getMessage(); 
    }
}
?>
