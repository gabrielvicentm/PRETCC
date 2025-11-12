<?php
// 1. Adiciona o cabeçalho para permitir o acesso de outras origens (CORS)
// ESSA É A LINHA MAIS IMPORTANTE PARA CORRIGIR O ERRO
header("Access-Control-Allow-Origin: *");

// 2. Define o tipo de conteúdo da resposta como JSON
header('Content-Type: application/json; charset=utf-8');

try {
    // Conexão com o banco de dados
    $pdo = new PDO("mysql:host=localhost;dbname=apis", "root", "");
    
    // Configura o PDO para lançar exceções em caso de erro
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Prepara e executa a consulta
    $stmt = $pdo->query("SELECT id, frase, autor FROM api2");
    
    // Busca todos os resultados
    $frases = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Codifica o resultado para JSON e envia como resposta
    echo json_encode($frases, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

} catch (PDOException $e) {
    // Se qualquer erro acontecer no bloco 'try', ele será capturado aqui
    http_response_code(500); // Define o status HTTP para "Erro Interno do Servidor"
    // Envia uma resposta de erro clara em formato JSON
    echo json_encode(['erro' => 'Falha na API: ' . $e->getMessage()]);
}
?>
