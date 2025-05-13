<?php
session_start(); // Inicia a sessão para acessar as variáveis de sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    // Redireciona para a tela de login se não estiver autenticado
    header("Location: login.html");
    exit();
}

require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco

$user_id = $_SESSION['user_id'];
$username = $_SESSION['user_name'];

// Busca os dados do perfil atual do usuário logado
$stmt = $conn->prepare("SELECT nome, bio, foto_perfil FROM perfil WHERE username = :username");
$stmt->execute([':username' => $username]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

// Se o perfil não for encontrado, mostra erro e link para redirecionamento
if (!$perfil) {
    die('Perfil não encontrado. <a href="perfil.php">Ir para o perfil</a>.');
}

// Se o formulário for enviado (método POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = $_POST['nome'] ?? '';
    $bio = $_POST['bio'] ?? '';
    $foto_perfil = '';

    // Verifica se foi enviada uma nova foto de perfil
    if (!empty($_FILES['foto_perfil']['name'])) {
        $extensao = pathinfo($_FILES['foto_perfil']['name'], PATHINFO_EXTENSION);
        $novo_nome = uniqid() . '.' . $extensao;
        $caminho_foto = '../uploads/' . $novo_nome;

        // Move o arquivo para a pasta de uploads
        if (move_uploaded_file($_FILES['foto_perfil']['tmp_name'], $caminho_foto)) {
            $foto_perfil = $caminho_foto;
        }
    }

    // Monta a query para atualizar o perfil
    $sql = "UPDATE perfil SET nome = :nome, bio = :bio";
    if ($foto_perfil) {
        $sql .= ", foto_perfil = :foto_perfil";
    }
    $sql .= " WHERE username = :username";

    // Prepara e executa a atualização
    $stmt = $conn->prepare($sql);
    $params = [
        ':nome' => $nome,
        ':bio' => $bio,
        ':username' => $username
    ];
    if ($foto_perfil) {
        $params[':foto_perfil'] = $foto_perfil;
    }
    $stmt->execute($params);

    // Redireciona de volta ao perfil após salvar
    header("Location: perfil.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Editar Perfil</title>
  <link rel="stylesheet" href="../css/home.css">
  <style>
    body {
      background-color: #121212;
      color: #fff;
      font-family: Arial, sans-serif;
    }

    .sidebar {
      position: fixed;
      left: 0;
      top: 0;
      height: 100%;
      width: 220px;
      background-color: #1e1e1e;
      padding-top: 20px;
    }

    .sidebar a {
      display: flex;
      align-items: center;
      padding: 10px;
      text-decoration: none;
      color: white;
      transition: background 0.3s;
    }

    .sidebar a:hover {
      background-color: #333;
    }

    .sidebar img {
      margin-right: 10px;
    }

    .content {
      margin-left: 240px;
      padding: 20px;
    }

    h1 {
      color: rgb(0, 70, 117);
    }

    label {
      display: block;
      margin-top: 15px;
      font-weight: bold;
    }

    input[type="text"],
    textarea,
    input[type="file"] {
      width: 100%;
      padding: 10px;
      margin-top: 5px;
      background-color: #1e1e1e;
      color: white;
      border: 1px solid #333;
      border-radius: 4px;
    }

    input[disabled] {
      color: #aaa;
    }

    button,
    .na {
      margin-top: 20px;
      background-color: rgb(0, 70, 117);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      transition: background 0.3s ease;
      font-weight: bold;
      text-decoration: none;
      display: inline-block;
    }

    button:hover,
    .na:hover {
      background-color: #1976d2;
    }

    .container {
      max-width: 600px;
      margin: auto;
    }

    .foto-redonda {
      border-radius: 50%;
      width: 150px;
      height: 150px;
      object-fit: cover;
      margin-bottom: 20px;
    }
  </style>
</head>
<body>

<!-- Sidebar de navegação -->
<div class="sidebar">
  <div class="logo" style="color: orange;">LOGO LINDA</div>
  <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
  <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
  <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
  <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
  <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
  <a href="../php/perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></a>
  <a href="../php/logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png"/><span>Sair</span></a>
</div>

<!-- Conteúdo principal -->
<div class="content">
  <div class="container">
    <h1>Editar Perfil</h1>
    <form method="POST" enctype="multipart/form-data">

      <!-- Foto de perfil atual -->
      <?php if (!empty($perfil['foto_perfil'])): ?>
          <img src="<?= htmlspecialchars($perfil['foto_perfil']) ?>" alt="Foto de Perfil" class="foto-redonda">
      <?php endif; ?>

      <input type="file" name="foto_perfil" id="foto_perfil">

      <p>
        <label>Username (fixo):</label>
        <input type="text" value="<?= htmlspecialchars($username) ?>" disabled>
      </p>

      <p>
        <label for="nome">Nome:</label>
        <input type="text" name="nome" id="nome" value="<?= htmlspecialchars($perfil['nome']) ?>" required>
      </p>

      <p>
        <label for="bio">Bio:</label>
        <textarea name="bio" id="bio" rows="4" required><?= htmlspecialchars($perfil['bio']) ?></textarea>
      </p>

      <button type="submit">Salvar ✅</button>
      <br><br>
      <a href="perfil.php" class="na">👈 Voltar ao Perfil</a>
    </form>
  </div>
</div>

</body>
</html>
