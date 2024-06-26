<?php
session_start();
require_once "../MODELO/Manejo_Base.php";


$id_usuario = $_SESSION['id_usuario']; 
$ruta_base = '../img/usuario/';
$extensiones = ['jpg', 'png', 'jpeg'];

/*COMPRUEBA EN EL DIRECTORIO A VER SI EXISTE UNA IMAGEN CON NOMBRE IGUAL AL ID 
DEL USUARIO + CUALQUIERA DE LAS EXTENSIONES PERMITIDAS, SI NO TIENE IMAGEN 
SE MUESTRA LA DEFAULT
*/
$imagen_url = '';
foreach ($extensiones as $extension) {
    $ruta_imagen = $ruta_base . $id_usuario . '.' . $extension;
    if (file_exists($ruta_imagen)) {
        $imagen_url = $ruta_imagen;
        break;
    }
}
if ($imagen_url == '') {
    $imagen_url = '../img/usuario.png';
}

echo $imagen_url;
?>