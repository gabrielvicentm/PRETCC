<?php
require_once 'conexao.php';

// Apagar stories com mais de 24 horas (1 dia)
$stmt = $conn->prepare("
    DELETE FROM stories 
    WHERE data_story < NOW() - INTERVAL 1 DAY
");
$stmt->execute();

