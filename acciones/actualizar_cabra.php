<?php
session_start();
require '../database.php';
require '../funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabra = intval($_POST['id_cabra']);
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $color = htmlspecialchars(trim($_POST['color']));
    $sexo = $_POST['sexo'];
    $id_raza = intval($_POST['id_raza']);
    $id_madre = !empty($_POST['id_madre']) ? intval($_POST['id_madre']) : NULL;
    $id_padre = !empty($_POST['id_padre']) ? intval($_POST['id_padre']) : NULL;
    $partos = !empty($_POST['partos']) ? intval($_POST['partos']) : NULL;
    $fecha_compra = !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : NULL;
    $observaciones = trim($_POST['observaciones'] ?? '');
    $imagen_actual = $_POST['imagen_actual'] ?? '';
    
    // Inicializar imagen (mantener la actual por defecto)
    $imagen = $imagen_actual;
    
    // Manejo de nueva imagen
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $carpetaImagenes = '../img/cabras/';
        if (!is_dir($carpetaImagenes)) {
            mkdir($carpetaImagenes, 0777, true);
        }
        
        $nombreImagen = md5(uniqid(rand(), true)) . '.jpg';
        move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaImagenes . $nombreImagen);
        $imagen = 'img/cabras/' . $nombreImagen;
        
        // Eliminar imagen anterior si existe
        if ($imagen_actual && file_exists('../' . $imagen_actual)) {
            unlink('../' . $imagen_actual);
        }
    }
    
    // Validar padres
          $errores = [];
$errores_padres = validarPadres($id_madre, $id_padre, $id_cabra);
$errores = array_merge($errores, $errores_padres);


    if ($id_madre && !validarSexoParentesco($id_madre, 'H')) {
        $errores[] = "La madre seleccionada no es una hembra";
    }
    
    if ($id_padre && !validarSexoParentesco($id_padre, 'M')) {
        $errores[] = "El padre seleccionado no es un macho";
    }
    
    // Validar que no se seleccione a sí misma como padre/madre
    if ($id_madre == $id_cabra || $id_padre == $id_cabra) {
        $errores[] = "Una cabra no puede ser padre/madre de sí misma";
    }
    
    // Validar fecha de nacimiento
    $fechaActual = new DateTime();
    $fechaNacimiento = new DateTime($fecha_nacimiento);
    if ($fechaNacimiento > $fechaActual) {
        $errores[] = "La fecha de nacimiento no puede ser futura";
    }
    
    // Validar partos solo para hembras
    if ($sexo == 'M' && !empty($partos)) {
        $errores[] = "Los machos no pueden tener registros de partos";
    }
    
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: ../index.php?pagina=cabras&subpagina=editar&id=$id_cabra");
        exit;
    }
    
    // Actualizar en base de datos
    $sql = "UPDATE cabras SET
        imagen = ?,
        nombre = ?,
        fecha_nacimiento = ?,
        color = ?,
        sexo = ?,
        id_raza = ?,
        id_madre = ?,
        id_padre = ?,
        partos = ?,
        fecha_compra = ?,
        observaciones = ?  
    WHERE id_cabra = ?";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'sssssiiiissi',  // 12 especificadores
        $imagen,
        $nombre,
        $fecha_nacimiento,
        $color,
        $sexo,
        $id_raza,
        $id_madre,
        $id_padre,
        $partos,
        $fecha_compra,
        $observaciones, 
        $id_cabra
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Cabra actualizada correctamente";
        header("Location: ../index.php?pagina=cabras&subpagina=ver&id=$id_cabra");
    } else {
        $_SESSION['error'] = "Error al actualizar la cabra: " . $conexion->error;
        header("Location: ../index.php?pagina=cabras&subpagina=editar&id=$id_cabra");
    }
} else {
    header('Location: ../index.php');
}
exit;
?>