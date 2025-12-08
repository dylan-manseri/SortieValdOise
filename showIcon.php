<?php
include "conf/bd_conf.php";
if(isset($_GET["id"])){
    $stmt = $pdo->prepare("SELECT image, format 
                       FROM icons 
                       WHERE id_icon = ?");
    $stmt->execute([$_GET["id"]]);
    $row = $stmt->fetch();
    header("Content-Type: " . $row['format']);
    echo $row['image'];
}

