<?php

function verifImage($image){
    if($image['size'] > 300 * 1024){
        header("Location: profil.php?error=size");
        exit;
    }

    $info = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($info, $image['tmp_name']);
    finfo_close($info);

    $allowed = array('image/jpeg', 'image/png', 'image/webp');
    if(!in_array($type, $allowed)){     // Vérifie si le format est le bon
        header("Location: profil.php?error=file");
        exit;
    }

    $dim = getimagesize($image['tmp_name']);
    if(!$dim){          // Vérifie si c'est bien une image
        header("Location: profil.php?error=file");
        exit;
    }

    $width = $dim[0];
    $height = $dim[1];

    if($width > 256 || $height > 256){
        header("Location: profil.php?error=dim");
        exit;
    }

    match ($type){
        'image/jpeg' => $format = '.jpg',
        'image/png'  => $format = '.png',
        'image/webp' => $format = '.webp'
    };
    return [file_get_contents($_FILES['image']['tmp_name']), $type];
}

