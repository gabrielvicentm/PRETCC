<html><head>
<style>
  body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #121212;
    margin: 0;
    padding: 20px;
    color: #e0e0e0;
  }

  .container {
    max-width: 800px;
    margin: auto;
    background-color: #1e1e1e;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 4px 10px rgba(0,0,0,0.5);
  }

  h1, h2 {
    color: #ffffff;
  }

  input[type="text"],
  textarea,
  select {
    width: 100%;
    padding: 10px;
    margin-top: 8px;
    margin-bottom: 16px;
    border: 1px solid #444;
    border-radius: 6px;
    background-color: #2c2c2c;
    color: #fff;
    box-sizing: border-box;
  }

  input[type="file"] {
    opacity: 0;
    position: absolute;
    width: 100%;
    height: 100%;
    cursor: pointer;
  }

  .custom-file-upload {
    display: block;
    width: 150px;
    height: 150px;
    border: 2px dashed #555;
    border-radius: 12px;
    background-color: #2c2c2c;
    color: #aaa;
    text-align: center;
    line-height: 150px;
    margin-bottom: 20px;
    position: relative;
    cursor: pointer;
    transition: background-color 0.3s ease;
  }

  .custom-file-upload:hover {
    background-color: #333;
  }

  input[type="submit"],
  button {
    background-color: #4CAF50;
    color: white;
    padding: 12px 24px;
    border: none;
    border-radius: 6px;
    cursor: pointer;
    font-size: 16px;
  }

  input[type="submit"]:hover,
  button:hover {
    background-color: #45a049;
  }

  .post {
    border-bottom: 1px solid #333;
    padding-bottom: 20px;
    margin-bottom: 20px;
  }

  .post img {
    width: 100%;
    max-height: 500px;
    object-fit: cover;
    border-radius: 8px;
    margin-top: 10px;
  }

  label {
    font-weight: bold;
    display: block;
    margin-bottom: 6px;
  }

  .file-wrapper {
    position: relative;
  }
</style>
</head><body><div class='container'>
<?php
// Conectar ao banco de dados usando PDO
$dsn = 'mysql:host=localhost;dbname=tarcisio;charset=utf8';
$username = 'root'; // seu usuário do banco de dados
$password = ''; // sua senha do banco de dados

try {
    $conn = new PDO($dsn, $username, $password);
    // Configurar o PDO para lançar exceções em caso de erro
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Erro na conexão: ' . $e->getMessage();
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verifique se o campo 'midia' foi enviado corretamente
    if (isset($_FILES['midia']) && $_FILES['midia']['error'] == 0) {
        $descricao = $_POST['descricao'];
        $mídia = $_FILES['midia'];

        // Verificar se a pasta 'posts' existe, caso contrário, criar
        $pastaDestino = 'posts/';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        // Gerar um nome único para o arquivo
        $extensao = pathinfo($mídia['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '.' . $extensao;
        $caminhoDestino = $pastaDestino . $nomeArquivo;

        // Mover a mídia para a pasta de destino
        if (move_uploaded_file($mídia['tmp_name'], $caminhoDestino)) {
            // Inserir informações no banco de dados usando PDO
            $sql = "INSERT INTO posts (descricao, arquivo) VALUES (:descricao, :arquivo)";
            $stmt = $conn->prepare($sql);
            
            // Bind dos parâmetros
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':arquivo', $nomeArquivo);


            if ($stmt->execute()) {
                // Deu tudo certo, vai redirecionar para ver_post.php
                header('Location: ver_post.php?file=' . urlencode($nomeArquivo));
                exit(); // Interrompe a execução do script após o redirecionamento
            } else {
                echo "Erro ao salvar no banco de dados.";
            }            
        } else {
            echo "Erro ao mover o arquivo para o servidor.";
        }
    } else {
        echo "Erro ao enviar o arquivo ou o arquivo não foi selecionado.";
    }
} else {
    echo "Método inválido.";
}

$conn = null; // Fechar a conexão PDO
?>

</div></body></html>