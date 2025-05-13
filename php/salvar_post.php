<?php
// Inicia a sessão para garantir que o usuário esteja logado
session_start();
// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Verifica se o usuário está logado. Caso contrário, redireciona para a página de login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.html');
    exit();
}

// Verifica se o formulário foi enviado via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica se um arquivo foi enviado e se não houve erro durante o upload
    if (isset($_FILES['midia']) && $_FILES['midia']['error'] === 0) {
        // Recebe os dados do formulário: descrição do post, arquivo de mídia e ID do usuário
        $descricao = $_POST['descricao'];
        $midia = $_FILES['midia'];
        $usuario_id = $_SESSION['user_id'];

        // Verifica se a checkbox "ir_reels" foi marcada (1 = sim, 0 = não)
        $ir_reels = isset($_POST['ir_reels']) ? 1 : 0;

        // Define o diretório onde os arquivos serão armazenados
        $pastaDestino = 'posts/';

        // Cria o diretório caso não exista
        if (!is_dir($pastaDestino)) {
            mkdir($pastaDestino, 0777, true); // Permissões 0777 permitem leitura, gravação e execução
        }

        // Obtém a extensão do arquivo enviado
        $extensao = pathinfo($midia['name'], PATHINFO_EXTENSION);
        // Gera um nome único para o arquivo
        $nomeArquivo = uniqid() . '.' . $extensao;
        // Define o caminho completo de destino do arquivo
        $caminhoDestino = $pastaDestino . $nomeArquivo;

        // Move o arquivo enviado para o diretório de destino
        if (move_uploaded_file($midia['tmp_name'], $caminhoDestino)) {
            // Se o arquivo foi movido com sucesso, insere os dados no banco de dados

            // Insere o post na tabela "posts" do banco de dados
            $sql = "INSERT INTO posts (descricao, arquivo, usuario_id) VALUES (:descricao, :arquivo, :usuario_id)";
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':descricao', $descricao); // Vincula o valor da descrição
            $stmt->bindParam(':arquivo', $nomeArquivo); // Vincula o nome do arquivo
            $stmt->bindParam(':usuario_id', $usuario_id); // Vincula o ID do usuário

            // Se o post for inserido corretamente, verifica se o usuário escolheu publicar no Reels
            if ($stmt->execute()) {
                // Se a checkbox "ir_reels" foi marcada, insere o post no Reels também
                if ($ir_reels) {
                    $sqlReels = "INSERT INTO reels (descricao, arquivo, usuario_id) VALUES (:descricao, :arquivo, :usuario_id)";
                    $stmtReels = $conn->prepare($sqlReels);
                    $stmtReels->bindParam(':descricao', $descricao);
                    $stmtReels->bindParam(':arquivo', $nomeArquivo);
                    $stmtReels->bindParam(':usuario_id', $usuario_id);
                    $stmtReels->execute(); // Insere no Reels
                }

                // Após o sucesso da inserção, redireciona para a página do perfil
                header('Location: perfil.php');
                exit();
            } else {
                // Caso haja erro ao salvar no banco de dados, exibe uma mensagem
                echo "Erro ao salvar no banco de dados.";
            }
        } else {
            // Se houver erro ao mover o arquivo para o diretório, exibe uma mensagem
            echo "Erro ao mover o arquivo para o servidor.";
        }
    } else {
        // Caso não tenha sido enviado nenhum arquivo ou houve erro no upload
        echo "Erro ao enviar o arquivo.";
    }
} else {
    // Se o método de requisição não for POST
    echo "Método inválido.";
}
?>
