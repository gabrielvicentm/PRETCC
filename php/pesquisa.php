<?php
require_once 'conexao.php'; // Inclui o arquivo de conexão com o banco de dados
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.html"); // Redireciona para o login se não estiver logado
    exit();
}

// Pega o valor da busca vindo pela URL (GET). Se não existir, define como string vazia
$busca = $_GET['q'] ?? '';

// Cria um array vazio para armazenar os resultados
$resultados = [];

// Se houve uma busca
if ($busca) {
    // Prepara a consulta SQL para procurar usuários por nome ou username parecidos com a busca
    $stmt = $conn->prepare("SELECT id, username, nome, foto_perfil FROM perfil WHERE username LIKE :busca OR nome LIKE :busca");
    $stmt->execute([':busca' => "%$busca%"]); // Executa a consulta com o termo de busca entre '%'
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC); // Armazena todos os resultados em formato associativo
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <title>Pesquisar Usuários</title>
    <link rel="stylesheet" href="../css/home.css">
    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            display: flex;
            background-color: #121212;
            color: #fff;
        }

        .sidebar {
            width: 250px;
            height: 100vh;
            background-color: #202020;
            border-right: 1px solid #555353;
            padding: 20px 10px;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .sidebar a {
            text-decoration: none;
            color: #fff;
            display: flex;
            align-items: center;
            padding: 10px;
            border-radius: 8px;
            transition: background 0.3s;
        }

        .sidebar a:hover {
            background-color: #1a1a1a;
        }

        .sidebar a span {
            margin-left: 10px;
        }

        .logo {
            font-size: 24px;
            font-weight: bold;
            margin-bottom: 30px;
        }

        .content {
            flex: 1;
            padding: 30px;
        }

        form {
            margin-bottom: 20px;
        }

        input[type="text"] {
            padding: 10px;
            width: 300px;
            border-radius: 6px;
            border: none;
            background-color: #1f1f1f;
            color: white;
        }

        button {
            padding: 10px 15px;
            background-color: rgb(0, 70, 117);
            border: none;
            color: black;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        ul {
            list-style: none;
            padding-left: 0;
        }

        li {
            margin-bottom: 15px;
        }

        img {
            border-radius: 50%;
            width: 40px;
            height: 40px;
            object-fit: cover;
            vertical-align: middle;
        }

        a.result-link {
            color: white;
            text-decoration: none;
            font-size: 18px;
            margin-left: 10px;
        }

        a.result-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="sidebar">
    <div class="logo" style="color: orange;">LOGO LINDA</div>
    <a href="home.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/home.png"/><span>Página Inicial</span></a>
    <a href="diario.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/dumbbell.png"/><span>Diário de Treino</span></a>
    <a href="pesquisa.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/search.png"/><span>Pesquisa</span></a>
    <a href="reels.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/film-reel.png"/><span>Vídeos Curtos</span></a>
    <a href="postar.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/plus-math.png"/><span>Postar</span></a>
    <a href="perfil.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/user.png"/><span><?= htmlspecialchars($_SESSION['user_name']) ?></span></a>
    <a href="logout.php"><img src="https://img.icons8.com/ios-filled/24/ffffff/logout-rounded-up.png" /><span>Sair</span></a>
</div>

<div class="content">

    <!-- Formulário de busca de usuários -->
    <form method="GET" action="pesquisa.php">
        <!-- Campo de texto para digitar o termo de busca -->
        <!-- O valor do campo é preenchido com o que o usuário já buscou (para manter o valor após envio) -->
        <input type="text" name="q" placeholder="Buscar usuários..." required value="<?= htmlspecialchars($busca) ?>">

        <!-- Botão de envio da busca -->
        <button type="submit">🔍</button>
    </form>

    <!-- Se o usuário fez uma busca (ou seja, a variável $busca não está vazia) -->
    <?php if ($busca): ?>
        <!-- Exibe um título indicando o termo buscado -->
        <h2>Resultados para "<?= htmlspecialchars($busca) ?>"</h2>

        <!-- Se houver resultados encontrados na busca -->
        <?php if ($resultados): ?>
            <ul>
                <!-- Para cada usuário encontrado nos resultados -->
                <?php foreach ($resultados as $usuario): ?>
                    <li>
                        <!-- Link para o perfil do usuário encontrado -->
                        <a class="result-link" href="perfil.php?u=<?= htmlspecialchars($usuario['username']) ?>">

                            <!-- Imagem de perfil do usuário -->
                            <img src="<?= htmlspecialchars($usuario['foto_perfil']) ?>" alt="Foto">

                            <!-- Exibe o nome de usuário (username) precedido de "@" -->
                            <div style="color:rgba(211, 211, 211, 0.66);">
                                @<?= htmlspecialchars($usuario['username']) ?>
                            </div>

                            <!-- Exibe o nome real do usuário -->
                            <div style="font-size: 15px;">
                                <?= htmlspecialchars($usuario['nome']) ?>
                            </div>

                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>

        <!-- Caso não tenha encontrado nenhum usuário -->
        <?php else: ?>
            <p>Nenhum usuário encontrado.</p>
        <?php endif; ?>
    <?php endif; ?>

</div>
</body>
</html>
