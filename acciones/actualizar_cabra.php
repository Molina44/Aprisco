<?php
session_start();
require '../database.php';
require '../funciones.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../index.php');
    exit;
}

// Recoger datos del formulario
$id_cabra = intval($_POST['id_cabra']);
$datos = [
    'imagen' => $_POST['imagen_actual'] ?? '',
    'nombre' => trim($_POST['nombre']),
    'fecha_nacimiento' => $_POST['fecha_nacimiento'],
    'color' => trim($_POST['color'] ?? ''),
    'sexo' => $_POST['sexo'],
    'id_raza' => intval($_POST['id_raza']),
    'id_madre' => !empty($_POST['id_madre']) ? intval($_POST['id_madre']) : null,
    'id_padre' => !empty($_POST['id_padre']) ? intval($_POST['id_padre']) : null,
    'partos' => !empty($_POST['partos']) ? $_POST['partos'] : null,
    'fecha_compra' => !empty($_POST['fecha_compra']) ? $_POST['fecha_compra'] : null,
    'observaciones' => trim($_POST['observaciones'] ?? '')
];

// Validación adicional: asegurar que si se selecciona un padre, también se seleccione una madre
if ($datos['id_padre'] && !$datos['id_madre']) {
    $_SESSION['errores'] = ["Debe seleccionar una madre si selecciona un padre"];
    header("Location: ../index.php?pagina=cabras&subpagina=editar&id=$id_cabra");
    exit;
}
// Manejo de nueva imagen
if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === UPLOAD_ERR_OK) {
    $carpetaImagenes = '../img/cabras/';
    if (!is_dir($carpetaImagenes)) {
        mkdir($carpetaImagenes, 0777, true);
    }
    
    $nombreImagen = md5(uniqid(rand(), true)) . '.jpg';
    move_uploaded_file($_FILES['imagen']['tmp_name'], $carpetaImagenes . $nombreImagen);
    $datos['imagen'] = 'img/cabras/' . $nombreImagen;
    
    // Eliminar imagen anterior si existe
    $imagen_actual = $_POST['imagen_actual'] ?? '';
    if ($imagen_actual && file_exists('../' . $imagen_actual)) {
        unlink('../' . $imagen_actual);
    }
}
$resultado = actualizarCabra($id_cabra, $datos);

if ($resultado['exito']) {
    $_SESSION['exito'] = "Cabra actualizada correctamente";
    header("Location: ../index.php?pagina=cabras&subpagina=ver&id=$id_cabra");
} else {
    $_SESSION['errores'] = $resultado['errores'];
    header("Location: ../index.php?pagina=cabras&subpagina=editar&id=$id_cabra");
}
exit;
?>