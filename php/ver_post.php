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

if (isset($_GET['file'])) {
    $arquivo = $_GET['file'];
    $caminhoArquivo = 'posts/' . $arquivo;

    if (file_exists($caminhoArquivo)) {
        // Buscar a descrição do banco de dados usando PDO
        $sql = "SELECT descricao FROM posts WHERE arquivo = :arquivo";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':arquivo', $arquivo);
        $stmt->execute();

        $descricao = $stmt->fetchColumn();

        if ($descricao) {
            echo "<h1>Descrição do Post</h1>";
            echo "<p>" . htmlspecialchars($descricao) . "</p>";

            
            // Mostrar a mídia
            $extensao = pathinfo($arquivo, PATHINFO_EXTENSION);

            if (in_array(strtolower($extensao), ['jpg', 'jpeg', 'png', 'gif'])) {
                echo "<h1>Imagem do Post</h1>";
                echo "<img src='" . $caminhoArquivo . "' alt='Post Image' style='max-width:100%;'><br>";
            } elseif (in_array(strtolower($extensao), ['mp4', 'avi', 'mov'])) {
                echo "<h1>Vídeo do Post</h1>";
                echo "<video width='640' height='360' controls><source src='" . $caminhoArquivo . "' type='video/" . $extensao . "'>Seu navegador não suporta o formato de vídeo.</video><br>";
            }

            echo "<br><a href='upload_post.php'>Voltar</a>";
        } else {
            echo "Post não encontrado no banco de dados.";
        }
    } else {
        echo "Arquivo não encontrado.";
    }
} else {
    echo "Post não especificado.";
}

$conn = null; // Fechar a conexão PDO
?>

</div></body></html>