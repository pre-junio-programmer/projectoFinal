<?php
session_start();
require_once "../MODELO/Manejo_Base.php";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    $nombre = $_POST['nombreProducto'];
    $descripcion = $_POST['descripcionProducto'];
    $categoria = $_POST['categoriaProducto'];
    $precio = $_POST['precioProducto'];
    $cantidad = $_POST['cantidadProducto'];
    $id_usuario = $_SESSION['id_usuario'];
    
    //INSERTAMOS EN PRODUCTO EL NOMBRE, DESC, CATEGORIA Y PRECIO 
    //Y EN RELACION_VENTA EL ID DEL PROD, USUARIO Y LA CANTIDAD A VENDER
    $idProducto=Base_Operaciones::insertarVenta($nombre, $descripcion, $categoria, $precio,$cantidad,$id_usuario);

$directorio_img = "../img/productos/";

//GUARDAMOS LA FOTO DEL PRODUCTO EN EL APARTADO DE FOTOS CON EL NOMBRE IGUAL AL ID DEL PRODUCTO
if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] == UPLOAD_ERR_OK) {
    $fileTmpPath = $_FILES["foto"]["tmp_name"];
    $fileExtension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
    $nuevoNombreArchivo = $idProducto . "." . $fileExtension;
    $imagen = $directorio_img . $nuevoNombreArchivo;

    if (move_uploaded_file($fileTmpPath, $imagen)) {
        header("Location: ../VISTA/MisVentas.html");
        exit();
    } else {
        header("Location: ../VISTA/VentaAsistida.html?error=1");
        exit();
    }
} else {
    header("Location: ../VISTA/MisVentas.html");
    exit();
}
}
?>
