<?php
session_start();
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = htmlspecialchars(trim($_POST['nombre']));
    
    // Verificar si la raza ya existe
    $sql = "SELECT id_raza FROM razas WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $nombre);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $_SESSION['error'] = "Esta raza ya está registrada";
        header('Location: ../index.php?pagina=cabras&subpagina=registrar');
        exit;
    }
    
    // Insertar nueva raza
    $sql = "INSERT INTO razas (nombre) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $nombre);
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Raza registrada correctamente";
    } else {
        $_SESSION['error'] = "Error al registrar la raza: " . $conexion->error;
    }
    
    header('Location: ../index.php?pagina=cabras&subpagina=registrar');
    exit;
} else {
    header('Location: ../index.php');
}
exit;
?>