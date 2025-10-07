<?php
    header('Content-Type:application/json;charset=utf-8');

    $pdo = new PDO("mysql:host=localhost;dbname=apis","root","");
    $pdo->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
    $stmt = $pdo->query("select * from api3"); 
    $sabores = $stmt->fetchAll(PDO::FETCH_ASSOC); 

    echo json_encode($sabores,JSON_PRETTY_PRINT|JSON_UNESCAPED_UNICODE);
   
    ?>