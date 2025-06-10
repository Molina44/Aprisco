<?php
session_start();
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabra = intval($_POST['id_cabra']);
    $peso = floatval($_POST['peso']);
    $fecha_registro = $_POST['fecha_registro'];
    $es_peso_nacimiento = isset($_POST['es_peso_nacimiento']) ? 1 : 0;
    $observaciones = htmlspecialchars(trim($_POST['observaciones']));
    
    $sql = "INSERT INTO registros_peso (id_cabra, peso, fecha_registro, es_peso_nacimiento, rs_oservaciones) 
            VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('idsis', 
        $id_cabra, 
        $peso, 
        $fecha_registro, 
        $es_peso_nacimiento, 
        $observaciones
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Peso registrado correctamente";
    } else {
        $_SESSION['error'] = "Error al registrar el peso: " . $conexion->error;
    }
    
    header("Location: ../index.php?pagina=pesos&id_cabra=$id_cabra");
    exit;
} else {
    header('Location: ../index.php');
}
exit;
?>