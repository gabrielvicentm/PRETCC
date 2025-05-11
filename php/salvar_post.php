<?php
session_start();
require_once 'conexao.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['midia']) && $_FILES['midia']['error'] === 0) {
        $descricao = $_POST['descricao'];
        $midia = $_FILES['midia'];
        $usuario_id = $_SESSION['user_id'];
        $ir_reels = isset($_POST['ir_reels']) ? 1 : 0; // Verifica se a checkbox foi marcada (1 = sim, 0 = não)

        $pastaDestino = 'posts/';
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true);
        }

        $extensao = pathinfo($midia['name'], PATHINFO_EXTENSION);
        $nomeArquivo = uniqid() . '.' . $extensao;
        $caminhoDestino = $pastaDestino . $nomeArquivo;

        if (move_uploaded_file($midia['tmp_name'], $caminhoDestino)) {
            // Insere no banco de dados para o perfil do usuário
            $sql = "INSERT INTO posts (descricao, arquivo, usuario_id) VALUES (:descricao, :arquivo, :usuario_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':descricao', $descricao);
            $stmt->bindParam(':arquivo', $nomeArquivo);
            $stmt->bindParam(':usuario_id', $usuario_id);

            if ($stmt->execute()) {
                // Se a checkbox foi marcada, insere no Reels
                if ($ir_reels) {
                    $sqlReels = "INSERT INTO reels (descricao, arquivo, usuario_id) VALUES (:descricao, :arquivo, :usuario_id)";
                    $stmtReels = $conn->prepare($sqlReels);
                    $stmtReels->bindParam(':descricao', $descricao);
                    $stmtReels->bindParam(':arquivo', $nomeArquivo);
                    $stmtReels->bindParam(':usuario_id', $usuario_id);
                    $stmtReels->execute();
                }

                // Redireciona para a página do perfil
                header('Location: perfil.php');
                exit();
            } else {
                echo "Erro ao salvar no banco de dados.";
            }
        } else {
            echo "Erro ao mover o arquivo para o servidor.";
        }
    } else {
        echo "Erro ao enviar o arquivo.";
    }
} else {
    echo "Método inválido.";
}
?>
