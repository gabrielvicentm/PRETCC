<?php
// Conexão com o banco de dados
include('conexao.php');

// Função para adicionar um perfil ao banco de dados
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recebe os dados do formulário
    $nome = $_POST['nome'];
    $bio = $_POST['bio'];

    // Lidar com a foto de perfil
    $foto_perfil = ''; // Inicializa a variável foto_perfil

    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] == 0) {
        // Foto foi carregada
        $foto = $_FILES['foto_perfil'];

        // Verifica se a foto é válida (extensão .jpg, .jpeg, .png)
        $ext = pathinfo($foto['name'], PATHINFO_EXTENSION);
        if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
            $nome_arquivo = "foto_perfil_" . time() . "." . $ext;
            $diretorio = "uploads/";

            // Move a foto para o diretório "uploads"
            if (move_uploaded_file($foto['tmp_name'], $diretorio . $nome_arquivo)) {
                $foto_perfil = $nome_arquivo;
            }
        }
    }

    // Inserir os dados no banco de dados
    $sql = "INSERT INTO perfil (nome, bio, foto_perfil) VALUES (:nome, :bio, :foto_perfil)";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nome', $nome);
    $stmt->bindParam(':bio', $bio);
    $stmt->bindParam(':foto_perfil', $foto_perfil);
    $stmt->execute();

    echo "Perfil criado com sucesso!";
}

// Fecha a conexão
// $conn = null; // Você pode comentar essa linha caso queira manter a conexão ativa em outro lugar
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Criar Perfil</title>
</head>
<body>
    <h1>Criar Perfil de Usuário</h1>

    <!-- Formulário para criar um perfil -->
    <form method="POST" enctype="multipart/form-data">
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" required>
        <br><br>

        <label for="bio">Bio:</label>
        <textarea name="bio" id="bio" required></textarea>
        <br><br>

        <label for="foto_perfil">Foto de Perfil:</label>
        <input type="file" name="foto_perfil" id="foto_perfil" accept="image/*">
        <br><br>

        <button type="submit">Criar Perfil</button>
    </form>

</body>
</html>
