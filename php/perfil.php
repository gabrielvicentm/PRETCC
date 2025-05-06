<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.html");
    exit();
}

$username = isset($_GET['u']) ? $_GET['u'] : $_SESSION['user_name'];

$stmt = $conn->prepare("SELECT nome, bio, foto_perfil FROM perfil WHERE username = :username");
$stmt->execute([':username' => $username]);
$perfil = $stmt->fetch(PDO::FETCH_ASSOC);

$perfil_proprio = ($username === $_SESSION['user_name']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Perfil</title>
  <link rel="stylesheet" href="../css/home.css">
  <style>
        body {
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
            margin-left: 100px;
        }

        h1 {
            color: rgb(0, 70, 117);
        }

        .foto-redonda {
            border-radius: 50%;
            width: 150px;
            height: 150px;
            object-fit: cover;
        }

        button {
            background-color: rgb(0, 70, 117);
            color: #fff;
            padding: 10px 20px;
            border: none;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
        }

        button:hover {
            background-color: #2980b9;
        }

        a {
            color: white;
            text-decoration: none;
        }

        a:hover {
            text-decoration: underline;
        }

        /* Adicionando espaçamento entre a foto e o nome */
        .perfil-header {
            display: flex;
            align-items: center;
            gap: 20px;
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
    
    <?php if ($perfil): ?>
        <div class="perfil-header">
            <!-- Exibe a foto de perfil, usando a imagem default se não existir foto -->
            <img src="<?= htmlspecialchars($perfil['foto_perfil'] ?: '../uploads/default.jpg') ?>" alt="Foto de Perfil" class="foto-redonda">
            <div style="flex: 1;">
                <div style="display: flex; align-items: center; gap: 20px;">
                    <h2 style="margin: 0;"><?= htmlspecialchars($perfil['nome']) ?></h2>
                    <?php if ($perfil_proprio): ?>
                        <a href="edit_perfil.php">
                            <button style="font-size: 14px;">Editar Perfil</button>
                        </a>
                    <?php else: ?>
                        <form method="POST" action="seguir.php" style="display:inline;">
                            <input type="hidden" name="seguido" value="<?= htmlspecialchars($username) ?>">
                            <button type="submit">➕ Seguir</button>
                        </form>
                    <?php endif; ?>
                </div>

                <div style="color:rgba(211, 211, 211, 0.66); font-size: 14px; margin-top: 7px;">
                    @<?= htmlspecialchars($username) ?>
                </div>

                <?php
                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguidores = $stmt->fetchColumn();

                $stmt = $conn->prepare("SELECT COUNT(*) FROM seguidores WHERE seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                $stmt->execute([':username' => $username]);
                $seguindo = $stmt->fetchColumn();
                ?>

                <h4 style="margin-top: 15px; font-weight: normal; margin-left: 2px;">
                    <?php if ($seguidores > 0): ?>
                        <span style="cursor:pointer;" onclick="mostrarLista('seguidores')"><?= $seguidores ?> seguidores</span>
                    <?php else: ?>
                        <span style="color: pink;"><?= $seguidores ?> seguidores</span>
                    <?php endif; ?>
                    •
                    <?php if ($seguindo > 0): ?>
                        <span style="cursor:pointer;" onclick="mostrarLista('seguindo')"><?= $seguindo ?> seguindo</span>
                    <?php else: ?>
                        <span style="color:gray;"><?= $seguindo ?> seguindo</span>
                    <?php endif; ?>
                </h4>

                <div id="lista-seguidores" style="display:none; margin-top:20px;">
                    <h4>Seguidores</h4>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguidor_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguido_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div id="lista-seguindo" style="display:none; margin-top:20px;">
                    <h4>Seguindo</h4>
                    <ul>
                        <?php
                        $stmt = $conn->prepare("SELECT u.username, p.nome, p.foto_perfil FROM seguidores s
                            JOIN usuario u ON s.seguido_id = u.id
                            LEFT JOIN perfil p ON p.username = u.username
                            WHERE s.seguidor_id = (SELECT id FROM usuario WHERE username = :username)");
                        $stmt->execute([':username' => $username]);
                        foreach ($stmt as $row) {
                            echo '<li><a href="perfil.php?u=' . htmlspecialchars($row['username']) . '"><img src="' . htmlspecialchars($row['foto_perfil'] ?: 'uploads/default.jpg') . '" width="30" style="border-radius:50%;"> ' . htmlspecialchars($row['nome'] ?: $row['username']) . '</a></li>';
                        }
                        ?>
                    </ul>
                </div>

                <div>
                    <?= nl2br(htmlspecialchars($perfil['bio'])) ?>
                </div>
            </div>
        </div>
    <?php else: ?>
        <p>Perfil não encontrado.</p>
    <?php endif; ?>
</div>

<script>
function mostrarLista(tipo) {
    const seguidores = <?= $seguidores ?>;
    const seguindo = <?= $seguindo ?>;

    if (tipo === 'seguidores' && seguidores > 0) {
        document.getElementById('lista-seguidores').style.display = 'block';
        document.getElementById('lista-seguindo').style.display = 'none';
    } else if (tipo === 'seguindo' && seguindo > 0) {
        document.getElementById('lista-seguindo').style.display = 'block';
        document.getElementById('lista-seguidores').style.display = 'none';
    }
}
</script>
</body>
</html>
