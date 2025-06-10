<?php
session_start();
require '../database.php';
require '../funciones.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validación y sanitización básica
        // Recoger datos
    $id_cabra = intval($_POST['id_cabra']);
    // ... otros campos ...
    $id_madre = !empty($_POST['id_madre']) ? intval($_POST['id_madre']) : null;
    $id_padre = !empty($_POST['id_padre']) ? intval($_POST['id_padre']) : null;

    $nombre = htmlspecialchars(trim($_POST['nombre']));
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $color = htmlspecialchars(trim($_POST['color']));
    $sexo = $_POST['sexo'];
    $id_raza = intval($_POST['id_raza']);
    $id_madre = !empty($_POST['id_madre']) ? intval($_POST['id_madre']) : NULL;
    $id_padre = !empty($_POST['id_padre']) ? intval($_POST['id_padre']) : NULL;
    $partos = !empty($_POST['partos']) ? intval($_POST['partos']) : NULL;
    $partos = !empty($_POST['observaciones']) ? intval($_POST['observaciones']) : NULL;
    $fecha_compra = !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : NULL;
    
    // Manejo de imagen
    $imagen = NULL;
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
        $carpetaImagenes = '../img/cabras/';
        if (!is_dir($carpetaImagenes)) {
            mkdir($carpetaImagenes, 0777, true);
        }
        
        $nombreImagen = md5(uniqid(rand(), true)) . '.jpg';
        move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaImagenes . $nombreImagen);
        $imagen = 'img/cabras/' . $nombreImagen;
    }
    
    // Validar padres
       
    // Inicializar errores
    $errores = [];
    
    // Validaciones básicas
    if (empty($nombre)) {
        $errores[] = "El nombre es obligatorio";
    }
    
    // Validar padres
$errores_padres = validarPadres($id_madre, $id_padre);
    $errores = array_merge($errores, $errores_padres);  

    $errores = [];
    if ($id_madre && !validarSexoParentesco($id_madre, 'H')) {
        $errores[] = "La madre seleccionada no es una hembra";
    }
    
    if ($id_padre && !validarSexoParentesco($id_padre, 'M')) {
        $errores[] = "El padre seleccionado no es un macho";
    }
    
    if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header('Location: ../index.php?pagina=cabras&subpagina=registrar');
        exit;
    }

        if (!empty($errores)) {
        $_SESSION['errores'] = $errores;
        header("Location: ../index.php?pagina=cabras&subpagina=editar&id=$id_cabra");
        exit;
    }
    
    // Insertar en base de datos
    $sql = "INSERT INTO cabras (imagen, nombre, fecha_nacimiento, color, sexo, id_raza, id_madre, id_padre, partos, fecha_compra, observaciones) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";  // 11 parámetros
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('sssssiiiiss',  // 11 especificadores
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
        $observaciones 
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Cabra registrada correctamente";
        header('Location: ../index.php?pagina=cabras&subpagina=listar');
    } else {
        $_SESSION['error'] = "Error al registrar la cabra: " . $conexion->error;
        header('Location: ../index.php?pagina=cabras&subpagina=registrar');
    }
} else {
    header('Location: ../index.php');
}
exit;
?>