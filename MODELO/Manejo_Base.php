<?php
class Base_Operaciones {
    
    public static function conexion() {
        try {
            $conexion = new PDO('mysql:host=localhost; dbname=tfg_base_def', 'root', '');
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $conexion->exec("SET CHARACTER SET UTF8");
        } catch (Exception $e) {
            echo "Linea de error: " . $e->getLine();
            die("Erro" . $e->getMessage());
        }
        return $conexion;
    }

    public static function seleccionarValor($valorBuscar,$valorSeleccionar,$campo,$tabla) {
        $conexion = Base_Operaciones::conexion();
        $buscarValor = "SELECT {$valorSeleccionar} FROM {$tabla} WHERE {$campo} = :valor";
        $ejecutarValor = $conexion->prepare($buscarValor);
        $ejecutarValor->bindValue(":valor", $valorBuscar);
        $ejecutarValor->execute();
        $resultado = $ejecutarValor->fetch(PDO::FETCH_ASSOC);
        if ($resultado) {
            return $resultado[$valorSeleccionar];
        } else {
            return null; 
        }    
    }

    public static function seleccionarValores($valorBuscar, $valorSeleccionar, $campo, $tabla) {
        $conexion = Base_Operaciones::conexion();
        $buscarValores = "SELECT {$valorSeleccionar} FROM {$tabla} WHERE {$campo} = :valor";
        $ejecutarValores = $conexion->prepare($buscarValores);
        $ejecutarValores->bindValue(":valor", $valorBuscar);
        $ejecutarValores->execute();
        $resultados = $ejecutarValores->fetchAll(PDO::FETCH_COLUMN, 0);
        
        return $resultados;
    }

    public static function comprobarCampoUnico($valor,$campo,$tabla) {
        $conexion = Base_Operaciones::conexion();
        $buscarCampo = "SELECT COUNT(*) as count FROM {$tabla} WHERE {$campo} = :valor";
        $ejecutarCampo = $conexion->prepare($buscarCampo);
        $ejecutarCampo->bindValue(":valor", $valor);
        $ejecutarCampo->execute();
        $existeCampo = $ejecutarCampo->fetch(PDO::FETCH_ASSOC);
        return $existeCampo['count'] > 0;   
    }

    public static function obtenerUltimoId($tabla, $campo) {
        $conexion = Base_Operaciones::conexion();
        $sql = "SELECT MAX($campo) AS ultimo_id FROM $tabla";
        $consulta = $conexion->query($sql);
        $resultado = $consulta->fetch(PDO::FETCH_ASSOC);
        return $resultado['ultimo_id'];
    }

    public static function insertarUsuario($nombreUsu, $direccion, $correoUsu, $contraUsu) {
        $conexion = Base_Operaciones::conexion();
        $nombreExistente = Base_Operaciones::comprobarCampoUnico($nombreUsu,"nombre_u",'usuario');
        $emailExistente = Base_Operaciones::comprobarCampoUnico($correoUsu,"correo_u",'usuario');
        $idMaximo = Base_Operaciones::obtenerUltimoId('usuario','id_usuario');
        $idNuevo = $idMaximo+1;
        $saldo=0;
        if($nombreExistente) {
            $resultado = "A";
            return $resultado;
        } else if($emailExistente) {
            $resultado = "B";
            return $resultado;
        } else {
            $sql = "INSERT INTO usuario (id_usuario,nombre_u,direccion_u,correo_u,contra_u,saldo_u) VALUES (:id,:usuario,:dire,:correo,:contra,:saldo)";
            $resultado = $conexion->prepare($sql);
            $resultado->bindValue(":usuario", $nombreUsu);
            $resultado->bindValue(":contra", $contraUsu);
            $resultado->bindValue(":correo", $correoUsu);
            $resultado->bindValue(":dire", $direccion);
            $resultado->bindValue(":id", $idNuevo);
            $resultado->bindValue(":saldo", $saldo);
            $resultado->execute();
            $resultado = "C";
            session_start();
            $_SESSION['nombreDeSesion'] = $nombreUsu;   
            $_SESSION['id_usuario'] = $idNuevo;
            $_SESSION['direccion_u'] = $direccion;
            $_SESSION['correo_u'] = $correoUsu;
            $_SESSION['saldo_u'] = 0;
            return $resultado;
        }
    }

    public static function borrarUsuTotal($idUsuario) {
        $conexion = Base_Operaciones::conexion();
        $idsComentarios = Base_Operaciones::seleccionarValores($idUsuario,'id_comentario','id_usuario','Relacion_Comentario');
        Base_Operaciones::borrarElemento($idUsuario,'id_usuario','Relacion_Comentario');
        Base_Operaciones::borrarElementos($idsComentarios,'id_comentario','comentario');
        Base_Operaciones::borrarElemento($idUsuario,'id_usuario','Metodo_Pago');
        $idsProductos = Base_Operaciones::seleccionarValores($idUsuario,'id_producto','id_usuario','Relacion_Venta');
        Base_Operaciones::borrarElemento($idUsuario,'id_usuario','Relacion_Venta');
        Base_Operaciones::borrarElemento($idUsuario,'id_usuario','Compra_Realizada');
        Base_Operaciones::borrarElementos($idsProductos,'id_productos','producto');
        Base_Operaciones::borrarElemento($idUsuario,'id_usuario','Usuario');

    
    }

    public static function insertarTarjeta($numTarjeta, $tipoTarjeta, $nombreTarjeta, $fechaTarjeta,$CVV,$nombreUsu) {
        $conexion = Base_Operaciones::conexion();
        $numExistente = Base_Operaciones::comprobarCampoUnico($numTarjeta,"num_tarjeta","metodo_pago");
        $idUsuario = Base_Operaciones::seleccionarValor($nombreUsu,'id_usuario','nombre_u','usuario');
        $saldo_aleatorio = rand(0.00, 1000000.00);
        if($numExistente) {
            $resultado = "A";
            return $resultado;
        } else {
            $sql = "INSERT INTO metodo_pago (num_tarjeta,Tipo,fecha_cad,num_secreto,nombre_u_tarjeta,saldo_tarjeta,id_usuario) VALUES (:numero,:tipo,:fecha,:cvv,:nombre,:saldo,:id_usu)";
            $resultado = $conexion->prepare($sql);
            $resultado->bindValue(":numero", $numTarjeta);
            $resultado->bindValue(":tipo", $tipoTarjeta);
            $resultado->bindValue(":nombre", $nombreTarjeta);
            $resultado->bindValue(":fecha", $fechaTarjeta);
            $resultado->bindValue(":cvv", $CVV);
            $resultado->bindValue(":saldo", $saldo_aleatorio);
            $resultado->bindValue(":id_usu", $idUsuario);
            $resultado->execute();   
            $resultado = "B";
            return $resultado;
        }
    }
    public static function insertarVenta($nombreProd, $descripcionProd, $categoriaProd, $precioProd, $cantidadProd, $idUsuario) {
        $conexion = Base_Operaciones::conexion();
    
        //SE COMPRUEBA QUE EXISTE EL PRODUCTO EN LA TABLA PRODUCTO
        $sql = "SELECT id_producto, cantidad_p FROM producto WHERE nombre_p = :nombre AND descripcion_p = :descripcion AND categoria_p = :categoria AND precio_p = :precio";
        $resultado = $conexion->prepare($sql);
        $resultado->bindValue(":nombre", $nombreProd);
        $resultado->bindValue(":descripcion", $descripcionProd);
        $resultado->bindValue(":categoria", $categoriaProd);
        $resultado->bindValue(":precio", $precioProd);
        $resultado->execute();
        $productoExistente = $resultado->fetch(PDO::FETCH_ASSOC);
    
        if ($productoExistente) {
            $idProductoExistente = $productoExistente['id_producto'];
            
            //SE COMPRUEBA SI EXISTE LA IMAGEN DEL PRODUCTO EN LA CARPETA productos
            $directorio_img = "../img/productos/";
            $fileExtension = strtolower(pathinfo($_FILES["foto"]["name"], PATHINFO_EXTENSION));
            $imagenExistente = $directorio_img . $idProductoExistente . "." . $fileExtension;
            
            if (file_exists($imagenExistente)) {
                //SI LA FOTO YA EXISTE SE ACTUALIZA LA CANTIDAD SIN CREAR UNA NUEVA FILA
                $nuevaCantidad = $productoExistente['cantidad_p'] + $cantidadProd;
    
                $sql = "UPDATE producto SET cantidad_p = :cantidad WHERE id_producto = :id";
                $resultado = $conexion->prepare($sql);
                $resultado->bindValue(":cantidad", $nuevaCantidad);
                $resultado->bindValue(":id", $idProductoExistente);
                $resultado->execute();
    
                //SE ACTUALIZA LA TABLA RELACION VENTA CON LA NUEVA CANTIDAD DEL PRODUCTO
                $sql = "UPDATE relacion_venta SET cantidad_vr = cantidad_vr + :cantidad WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
                $resultado = $conexion->prepare($sql);
                $resultado->bindValue(":cantidad", $cantidadProd);
                $resultado->bindValue(":id_usuario", $idUsuario);
                $resultado->bindValue(":id_producto", $idProductoExistente);
                $resultado->execute();
    
                return $idProductoExistente;
            } else {
                //SI LA FOTO NO EXISTE SE INSERTA UN NUEVO REGISTRO
                $idMaximo = Base_Operaciones::obtenerUltimoId('producto', 'id_producto');
                $idNuevo = $idMaximo + 1;
    
                $sql = "INSERT INTO producto (id_producto, nombre_p, categoria_p, descripcion_p, precio_p, cantidad_p) VALUES (:id, :nombre, :categoria, :descripcion, :precio, :cantidad)";
                $resultado = $conexion->prepare($sql);
                $resultado->bindValue(":id", $idNuevo);
                $resultado->bindValue(":nombre", $nombreProd);
                $resultado->bindValue(":descripcion", $descripcionProd);
                $resultado->bindValue(":categoria", $categoriaProd);
                $resultado->bindValue(":precio", $precioProd);
                $resultado->bindValue(":cantidad", $cantidadProd);
                $resultado->execute();
    
                $idProductoInsertado = $idNuevo;
    
                $sql = "INSERT INTO relacion_venta (id_usuario, id_producto, cantidad_vr) VALUES (:id_usuario, :id_producto, :cantidad_v)";
                $resultado = $conexion->prepare($sql);
                $resultado->bindValue(":id_producto", $idProductoInsertado);
                $resultado->bindValue(":id_usuario", $idUsuario);
                $resultado->bindValue(":cantidad_v", $cantidadProd);
                $resultado->execute();
    
                return $idProductoInsertado;
            }
        } else {
            //SI EL PRODUCTO NO EXISTE SE INSERTA UN NUEVO REGISTRO
            $idMaximo = Base_Operaciones::obtenerUltimoId('producto', 'id_producto');
            $idNuevo = $idMaximo + 1;
    
            $sql = "INSERT INTO producto (id_producto, nombre_p, categoria_p, descripcion_p, precio_p, cantidad_p) VALUES (:id, :nombre, :categoria, :descripcion, :precio, :cantidad)";
            $resultado = $conexion->prepare($sql);
            $resultado->bindValue(":id", $idNuevo);
            $resultado->bindValue(":nombre", $nombreProd);
            $resultado->bindValue(":descripcion", $descripcionProd);
            $resultado->bindValue(":categoria", $categoriaProd);
            $resultado->bindValue(":precio", $precioProd);
            $resultado->bindValue(":cantidad", $cantidadProd);
            $resultado->execute();
    
            $idProductoInsertado = $idNuevo;
    
            $sql = "INSERT INTO relacion_venta (id_usuario, id_producto, cantidad_vr) VALUES (:id_usuario, :id_producto, :cantidad_v)";
            $resultado = $conexion->prepare($sql);
            $resultado->bindValue(":id_producto", $idProductoInsertado);
            $resultado->bindValue(":id_usuario", $idUsuario);
            $resultado->bindValue(":cantidad_v", $cantidadProd);
            $resultado->execute();
    
            return $idProductoInsertado;
        }
    }
    
    
    

    public static function inicioExitoso($nombreIntroducido, $contraIntroducida) {
        $conexion = Base_Operaciones::conexion();
        
        $sql = "SELECT * FROM usuario WHERE nombre_u = :nombreUsu AND contra_u = :contraUsu";
        $resultado = $conexion->prepare($sql);
        $resultado->bindValue(":nombreUsu", $nombreIntroducido);
        $resultado->bindValue(":contraUsu", $contraIntroducida);
        $resultado->execute();
        
        if ($resultado->rowCount() > 0) {
            $usuario = $resultado->fetch(PDO::FETCH_ASSOC);
            session_start();
            $_SESSION['nombreDeSesion'] = $nombreIntroducido;
            $_SESSION['id_usuario'] = $usuario['id_usuario'];
            $_SESSION['direccion_u'] = $usuario['direccion_u'];
            $_SESSION['correo_u'] = $usuario['correo_u'];
            $_SESSION['saldo_u'] = $usuario['saldo_u'];
            
            return true;
        } else {
            return false; 
        }
    }
    public static function mostrarProductos($categoria) {
        $conexion = Base_Operaciones::conexion();     
        $sql = "SELECT id_producto,nombre_p, descripcion_p FROM producto WHERE categoria_p = :categoria";
        $consultaProducto = $conexion->prepare($sql);
        $consultaProducto->bindParam(":categoria", $categoria);
        $consultaProducto->execute();
        $productos = $consultaProducto->fetchAll(PDO::FETCH_ASSOC); 
        return $productos;
    }
    
    public static function extraerDatos($clave, $campo, $tabla) {
        $conexion = Base_Operaciones::conexion();
        $sql = "SELECT * FROM $tabla WHERE $campo = :clave";
        $consultaElemento = $conexion->prepare($sql);
        $consultaElemento->bindParam(":clave", $clave);
        $consultaElemento->execute();
        $datos = $consultaElemento->fetchAll(PDO::FETCH_ASSOC);
        return $datos;
    }
    

    public static function borrarElemento($id_elemento,$campo_comparar,$tabla) {
        $conexion = Base_Operaciones::conexion();
        $borrarTodo = "DELETE FROM {$tabla} WHERE {$campo_comparar} = :primK";
        $borrarDefinitivo = $conexion->prepare($borrarTodo);
        $borrarDefinitivo->bindValue(":primK", $id_elemento);
        $borrarDefinitivo->execute();
    }

    public static function borrarElementos($valoresBuscar, $campo_comparar, $tabla) {
        $conexion = Base_Operaciones::conexion();
        if (!empty($valoresBuscar)) {
            $borrarProducto = "DELETE FROM {$tabla} WHERE {$campo_comparar} = :valor";
            $resultadoBP = $conexion->prepare($borrarProducto);
            foreach ($valoresBuscar as $valorBuscar) {
                $resultadoBP->bindValue(":valor", $valorBuscar);
                $resultadoBP->execute();
            }
        }
    }

    public static function borrarVentaCompra($id_elemento,$id_elemento2,$campo_comparar,$campo_comparar2,$tabla) {
        $conexion = Base_Operaciones::conexion();
        $borrarTodo = "DELETE FROM {$tabla} WHERE {$campo_comparar} = :fork1 AND {$campo_comparar2}=:fork2";        
        $borrarDefinitivo = $conexion->prepare($borrarTodo);
        $borrarDefinitivo->bindValue(":fork1", $id_elemento);
        $borrarDefinitivo->bindValue(":fork2", $id_elemento2);
        $borrarDefinitivo->execute();
    }

    public static function seleccionarVentaCompra($valorABuscar,$id_elemento,$id_elemento2,$campo_comparar,$campo_comparar2,$tabla) {
        $conexion = Base_Operaciones::conexion();
        $selectTodo = "SELECT {$valorABuscar} FROM {$tabla} WHERE {$campo_comparar} = :fork1 AND {$campo_comparar2}=:fork2";        
        $selectDef = $conexion->prepare($selectTodo);
        $selectDef->bindValue(":fork1", $id_elemento);
        $selectDef->bindValue(":fork2", $id_elemento2);
        $selectDef->execute();
        $resultados = $selectDef->fetchAll(PDO::FETCH_COLUMN, 0);
        return $resultados;
    }

    public static function updateCampo($valor_comparar, $valor_nuevo, $campo_comparar, $campo_update, $tabla) {
        $conexion = Base_Operaciones::conexion();
        $sql = "UPDATE {$tabla} SET {$campo_update} = :elementoNuevo WHERE {$campo_comparar} = :elementoBuscar";
        $resultado = $conexion->prepare($sql);
        $resultado->bindValue(":elementoNuevo", $valor_nuevo);
        $resultado->bindValue(":elementoBuscar", $valor_comparar);
        $resultado->execute();
    }

    public static function updateVentaCompra($valor_comparar,$valor_comparar2, $valor_nuevo, $campo_comparar,$campo_comparar2, $campo_update, $tabla) {
        $conexion = Base_Operaciones::conexion();
        $sql = "UPDATE {$tabla} SET {$campo_update} = :elementoNuevo WHERE {$campo_comparar} = :elementoBuscar AND {$campo_comparar2}= :elementoBuscar2";
        $resultado = $conexion->prepare($sql);
        $resultado->bindValue(":elementoNuevo", $valor_nuevo);
        $resultado->bindValue(":elementoBuscar", $valor_comparar);
        $resultado->bindValue(":elementoBuscar2", $valor_comparar2);
        $resultado->execute();
    }

    public static function comprobarCampoUnicoUser($valor, $campo, $id_usuario) {
        $conexion = Base_Operaciones::conexion();
        $buscarCampo = "SELECT COUNT(*) as count FROM usuario WHERE {$campo} = :valor AND id_usuario != :id_usuario";
        $ejecutarCampo = $conexion->prepare($buscarCampo);
        $ejecutarCampo->bindValue(":valor", $valor);
        $ejecutarCampo->bindValue(":id_usuario", $id_usuario);
        $ejecutarCampo->execute();
        $existeCampo = $ejecutarCampo->fetch(PDO::FETCH_ASSOC);
        return $existeCampo['count'] > 0;   
    }

    public static function updateUser($id_usuario, $nombre, $direccion, $email) {
        $conexion = Base_Operaciones::conexion();
        $nombreExistente = Base_Operaciones::comprobarCampoUnicoUser($nombre, 'nombre_u', $id_usuario);
        $emailExistente = Base_Operaciones::comprobarCampoUnicoUser($email, 'correo_u', $id_usuario);
        if($nombreExistente) {
            return "A";
        } else if($emailExistente) {
            return "B";
        } else {
                $sql = "UPDATE usuario SET nombre_u = :nombre, direccion_u = :direccion, correo_u = :correo WHERE id_usuario = :id";
                $resultado = $conexion->prepare($sql);
                $resultado->bindValue(":id", $id_usuario);
                $resultado->bindValue(":nombre", $nombre);
                $resultado->bindValue(":direccion", $direccion);
                $resultado->bindValue(":correo", $email);
                $resultado->execute();
                $_SESSION['nombreDeSesion'] = $nombre;
                $_SESSION['direccion_u'] = $direccion;
                $_SESSION['correo_u'] = $email;
                
                return "C";
            } 
    }

    public static function obtenerComentariosPorProducto($id_producto) {
        $conexion = Base_Operaciones::conexion();
        
        $sql = "SELECT c.id_comentario, u.nombre_u AS nombre_usuario, c.valoracion_c, c.comentario_c 
                FROM Comentario c
                JOIN Relacion_Comentario rc ON c.id_comentario = rc.id_comentario
                JOIN Usuario u ON rc.id_usuario = u.id_usuario
                WHERE rc.id_producto = :id_producto";
        
        $consultaComentario = $conexion->prepare($sql);
        $consultaComentario->bindParam(":id_producto", $id_producto);
        $consultaComentario->execute();
        
        $comentarios = $consultaComentario->fetchAll(PDO::FETCH_ASSOC);
        
        return $comentarios;
    }
    

    public static function eliminarComentario($id_comentario) {
        $conexion = Base_Operaciones::conexion();
    
        try {

            $conexion->beginTransaction();
            $sqlRelacion = "DELETE FROM Relacion_Comentario WHERE id_comentario = :id_comentario";
            $consultaRelacion = $conexion->prepare($sqlRelacion);
            $consultaRelacion->bindParam(":id_comentario", $id_comentario);
            $consultaRelacion->execute();
            $sqlComentario = "DELETE FROM Comentario WHERE id_comentario = :id_comentario";
            $consultaComentario = $conexion->prepare($sqlComentario);
            $consultaComentario->bindParam(":id_comentario", $id_comentario);
            $consultaComentario->execute();
    

            $conexion->commit();
        } catch (Exception $e) {

            $conexion->rollBack();
            throw $e;
        }
    }
    public static function obtenerProductoPorId($id_producto) {
        $conexion = Base_Operaciones::conexion();
    
        $sql = "SELECT nombre_p, descripcion_p, precio_p, cantidad_p FROM producto WHERE id_producto = :id_producto";
        $consultaProducto = $conexion->prepare($sql);
        $consultaProducto->bindParam(":id_producto", $id_producto);
        $consultaProducto->execute();
    
        $producto = $consultaProducto->fetch(PDO::FETCH_ASSOC);
    
        return $producto;
    }

    public static function obtenerProductosPorUsuario($id_usuario) {
        $conexion = Base_Operaciones::conexion();
    
        $query = "SELECT p.id_producto 
                  FROM producto p
                  JOIN relacion_venta rv ON p.id_producto = rv.id_producto
                  WHERE rv.id_usuario = :id_usuario";

        $stmt = $conexion->prepare($query);
        $stmt->bindValue(':id_usuario', $id_usuario, PDO::PARAM_INT);
        $stmt->execute();
    
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
        return $productos;
    }
    
    public static function agregarComentario($id_usuario, $id_producto, $valoracion, $comentario) {
        $conexion = Base_Operaciones::conexion();
    
        try {
            $conexion->beginTransaction();
            $sqlComentario = "INSERT INTO Comentario (valoracion_c, comentario_c) VALUES (:valoracion, :comentario)";
            $consultaComentario = $conexion->prepare($sqlComentario);
            $consultaComentario->bindParam(":valoracion", $valoracion);
            $consultaComentario->bindParam(":comentario", $comentario);
            $consultaComentario->execute();
            $id_comentario = $conexion->lastInsertId();
            $sqlRelacion = "INSERT INTO Relacion_Comentario (id_usuario, id_producto, id_comentario) VALUES (:id_usuario, :id_producto, :id_comentario)";
            $consultaRelacion = $conexion->prepare($sqlRelacion);
            $consultaRelacion->bindParam(":id_usuario", $id_usuario);
            $consultaRelacion->bindParam(":id_producto", $id_producto);
            $consultaRelacion->bindParam(":id_comentario", $id_comentario);
            $consultaRelacion->execute();
    
            $conexion->commit();
        } catch (Exception $e) {
            $conexion->rollBack();
            throw $e;
        }
    }
    
    public static function insertarCompra($fecha, $cantidad, $idUsuario, $idProducto) {
        $conexion = Base_Operaciones::conexion();
        $sql = "INSERT INTO Compra_Realizada (fecha_c, cantidad_c, id_usuario, id_producto) VALUES (:fecha, :cantidad, :id_usuario, :id_producto)";
        $resultado = $conexion->prepare($sql);
        $resultado->bindValue(":fecha", $fecha);
        $resultado->bindValue(":cantidad", $cantidad);
        $resultado->bindValue(":id_usuario", $idUsuario);
        $resultado->bindValue(":id_producto", $idProducto);
        $resultado->execute();
    }
    
    public static function obtenerComentariosPorProductoOrdenados($id_producto, $orden) {
        $conexion = Base_Operaciones::conexion();
        
        $sql = "SELECT c.id_comentario, u.nombre_u AS nombre_usuario, c.valoracion_c, c.comentario_c 
                FROM Comentario c
                JOIN Relacion_Comentario rc ON c.id_comentario = rc.id_comentario
                JOIN Usuario u ON rc.id_usuario = u.id_usuario
                WHERE rc.id_producto = :id_producto
                ORDER BY c.valoracion_c $orden";
        
        $consultaComentario = $conexion->prepare($sql);
        $consultaComentario->bindParam(":id_producto", $id_producto);
        $consultaComentario->execute();
        
        $comentarios = $consultaComentario->fetchAll(PDO::FETCH_ASSOC);
        
        return $comentarios;
    }

    public static function borrarComentariosAsociados($id_usuario, $id_producto) {
        $conexion = Base_Operaciones::conexion();

        $seleccionarComentarios = "SELECT id_comentario FROM Relacion_Comentario WHERE id_usuario = :id_usuario AND id_producto = :id_producto";
        $ejecutarSeleccion = $conexion->prepare($seleccionarComentarios);
        $ejecutarSeleccion->bindValue(":id_usuario", $id_usuario);
        $ejecutarSeleccion->bindValue(":id_producto", $id_producto);
        $ejecutarSeleccion->execute();
        $comentarios = $ejecutarSeleccion->fetchAll(PDO::FETCH_ASSOC);
    

        foreach ($comentarios as $comentario) {
            $id_comentario = $comentario['id_comentario'];
            $borrarRelacionComentario = "DELETE FROM Relacion_Comentario WHERE id_comentario = :id_comentario";
            $ejecutarBorrado = $conexion->prepare($borrarRelacionComentario);
            $ejecutarBorrado->bindValue(":id_comentario", $id_comentario);
            $ejecutarBorrado->execute();
    

            $borrarComentario = "DELETE FROM Comentario WHERE id_comentario = :id_comentario";
            $ejecutarBorradoComentario = $conexion->prepare($borrarComentario);
            $ejecutarBorradoComentario->bindValue(":id_comentario", $id_comentario);
            $ejecutarBorradoComentario->execute();
        }
    }
    

}

?>