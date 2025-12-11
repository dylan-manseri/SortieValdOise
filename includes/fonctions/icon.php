<?php

/**
 * Vérifie l'image en passant par plusieurs tests de sécurité.
 * Taille de l'image, dimension, format, etc...
 * @param $image : L'image en binaire
 * @param $page : La page où seront redirigé les erreurs
 * @param $wantedWidth : La hauteur autorisé
 * @param $wantedHeight : La largeur autorisé
 * @return array|void : Un tableau contenant les information (image et format) OU rien si erreur
 */
function verifImage($image, $page, $wantedWidth, $wantedHeight){
    if($image['size'] > 300 * 1024){
        header("Location: profil.php?error=size");
        exit;
    }

    $info = finfo_open(FILEINFO_MIME_TYPE);
    $type = finfo_file($info, $image['tmp_name']);
    finfo_close($info);

    $allowed = array('image/jpeg', 'image/png', 'image/webp');
    if(!in_array($type, $allowed)){     // Vérifie si le format est le bon
        header("Location: $page.php?error=file");
        exit;
    }

    $dim = getimagesize($image['tmp_name']);
    if(!$dim){          // Vérifie si c'est bien une image
        header("Location: $page.php?error=file");
        exit;
    }

    $width = $dim[0];
    $height = $dim[1];

    if($width > $wantedWidth || $height > $wantedHeight){
        header("Location: $page.php?error=dim");
        exit;
    }

    match ($type){
        'image/jpeg' => $format = '.jpg',
        'image/png'  => $format = '.png',
        'image/webp' => $format = '.webp'
    };
    return [file_get_contents($_FILES['image']['tmp_name']), $type];
}

