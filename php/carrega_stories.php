<?php
// Inclui o arquivo de conexão com o banco de dados
require_once 'conexao.php';

// Verifica se o parâmetro 'usuario_id' foi passado via GET
if (!isset($_GET['usuario_id'])) {
    // Se não foi passado, retorna um array vazio em formato JSON e encerra o script
    echo json_encode([]);
    exit();
}

// Converte o parâmetro 'usuario_id' para inteiro (boa prática para evitar SQL injection)
$usuarioId = (int)$_GET['usuario_id'];

// Prepara a consulta SQL para selecionar os caminhos dos arquivos de stories de um usuário específico
// A consulta ordena os resultados pela data do story em ordem crescente (mais antigos primeiro)
$stmt = $conn->prepare("
    SELECT caminho_arquivo 
    FROM stories 
    WHERE usuario_id = :usuario_id 
    ORDER BY data_story ASC
");

// Associa o valor do ID do usuário ao parâmetro da query SQL
$stmt->bindParam(':usuario_id', $usuarioId);

// Executa a consulta
$stmt->execute();

// Obtém todos os resultados como um array associativo
$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Converte o array de resultados em JSON e o imprime (retorna para quem fez a requisição)
echo json_encode($stories);
