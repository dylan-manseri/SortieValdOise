<?php
include "conf/bd_conf.php";
if(isset($_GET["id"])){
    $stmt = $pdo->prepare("SELECT icon, type_mime 
                       FROM users 
                       WHERE login = ?");
    $stmt->execute([$_GET["id"]]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if(!$row){
        $stmt = $pdo->prepare("SELECT image, type_mime FROM propositions WHERE id_prop = ?");
        $stmt->execute([$_GET["id"]]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
    }
    else{
        header("Content-Type: " . $row['type_mime']);
        echo $row['icon'];
    }

    if(!$row){
        header("Content-Type: image/png");
    }
    else{
        header("Content-Type: " . $row['type_mime']);
        echo $row['image'];
    }

}

