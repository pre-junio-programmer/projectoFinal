<?php
session_start();
require_once "../MODELO/Manejo_Base.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    
    $valor_nombre = $_SESSION['nombreDeSesion'];
    $tarjeta_elegida = $_POST['radioTarjeta'];
    $cantidad_manejo = floatval($_POST['cantidadIntroducida']);

    $id_usuario = $_SESSION['id_usuario'];
    $saldo_usuario = $_SESSION['saldo_u'];

    //SELECCIONAMOS EL DINERO EN LA TARJETA Y COMPROBAMOS SI TIENE SUFICIENTE
    $saldo_tarjeta = Base_Operaciones::seleccionarValor($tarjeta_elegida, 'saldo_tarjeta', 'num_tarjeta', 'metodo_pago');

    $cantidad_final = $saldo_tarjeta - $cantidad_manejo;
    $saldo_usuario_final = $saldo_usuario + $cantidad_manejo;

    //AÑADIMOS EL SALDO SI TIENE SUFICIENTE DINERO EN LA TARJETA U SI NO REDIRIGIMOS DICIENDO QUE NO HAY SALDO
    if ($cantidad_final > 0) {
        $_SESSION['saldo_u']=$saldo_usuario_final;
        Base_Operaciones::updateCampo($tarjeta_elegida, $cantidad_final, 'num_tarjeta', 'saldo_tarjeta', 'metodo_pago');
        Base_Operaciones::updateCampo($id_usuario, $saldo_usuario_final, 'id_usuario', 'saldo_u', 'usuario');
        header("Location: ../VISTA/PaginaPrincipal.html");
        exit();
    } else {
        header("Location: ../VISTA/AniadirFondos.html?error=1");
        exit();
    }
}
?>