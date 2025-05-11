<?php
require_once 'conexao.php';

if (!isset($_GET['usuario_id'])) {
    echo json_encode([]);
    exit();
}

$usuarioId = (int)$_GET['usuario_id'];

$stmt = $conn->prepare("SELECT caminho_arquivo FROM stories WHERE usuario_id = :usuario_id ORDER BY data_story ASC");
$stmt->bindParam(':usuario_id', $usuarioId);
$stmt->execute();

$stories = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($stories);

