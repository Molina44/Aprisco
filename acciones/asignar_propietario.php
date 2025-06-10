<?php
session_start();
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabra = intval($_POST['id_cabra']);
    $cedula = htmlspecialchars(trim($_POST['cedula']));
    $nombre_completo = htmlspecialchars(trim($_POST['nombre_completo']));
    $correo = htmlspecialchars(trim($_POST['correo']));
    $telefono = htmlspecialchars(trim($_POST['telefono']));
    
    // Verificar si el propietario ya existe
    $sql = "SELECT id_propietario FROM propietarios_cabras 
            WHERE cedula = ? AND id_cabra = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('si', $cedula, $id_cabra);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Este propietario ya está asignado a esta cabra";
        header("Location: ../index.php?pagina=propietarios&subpagina=asignar&id_cabra=$id_cabra");
        exit;
    }
    
    // Insertar nuevo propietario
    $sql = "INSERT INTO propietarios_cabras (id_cabra, cedula, nombre_completo, correo, telefono) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('issss', 
        $id_cabra, 
        $cedula, 
        $nombre_completo, 
        $correo, 
        $telefono
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Propietario asignado correctamente";
    } else {
        $_SESSION['error'] = "Error al asignar propietario: " . $conexion->error;
    }
    
    header("Location: ../index.php?pagina=propietarios&subpagina=listar&id_cabra=$id_cabra");
    exit;
} else {
    header('Location: ../index.php');
}
exit;
?>