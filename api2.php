<?php
    header('Content-Type:application/json;charset=utf-8');

    $pdo = new PDO("mysql:host=localhost;dbname=apis","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("select * from api2");
    $exercicios = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($exercicios,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
   
    ?>

