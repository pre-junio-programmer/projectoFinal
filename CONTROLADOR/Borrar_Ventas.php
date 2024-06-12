<?php
session_start();
require_once "../MODELO/Manejo_Base.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_usuario = $_SESSION['id_usuario'];

    $productos = Base_Operaciones::obtenerProductosPorUsuario($id_usuario);

    foreach ($productos as $producto) {
        $id_producto = $producto['id_producto'];

        Base_Operaciones::borrarVentaCompra($id_usuario, $id_producto, 'id_usuario', 'id_producto', 'relacion_venta');
        Base_Operaciones::borrarElemento($id_producto, 'id_producto', 'producto');

        $ruta = "../img/productos/";
        $rutaArchivos = glob($ruta . $id_producto . '.*');
        foreach ($rutaArchivos as $rutaArchivo) {
            if (file_exists($rutaArchivo)) {
                unlink($rutaArchivo);
            }
        }
    }

    echo 'success';
} else {
    echo 'error: método de solicitud no válido';
}
?>
