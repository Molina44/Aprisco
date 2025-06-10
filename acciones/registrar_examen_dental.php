<?php
session_start();
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabra = intval($_POST['id_cabra']);
    $fecha_examen = $_POST['fecha_examen'];
    $sin_muda = $_POST['sin_muda'];
    $pinzas = intval($_POST['pinzas']);
    $primeros_medios = intval($_POST['primeros_medios']);
    $segundos_medios = intval($_POST['segundos_medios']);
    $extremos = intval($_POST['extremos']);
    $desgaste = $_POST['desgaste'];
    $perdidas_dentales = $_POST['perdidas_dentales'];
    
    $sql = "INSERT INTO examenes_dentales (id_cabra, fecha_examen, sin_muda, pinzas, primeros_medios, segundos_medios, extremos, desgaste, perdidas_dentales) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('issiiiiis', 
        $id_cabra, 
        $fecha_examen, 
        $sin_muda, 
        $pinzas, 
        $primeros_medios, 
        $segundos_medios, 
        $extremos, 
        $desgaste, 
        $perdidas_dentales
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Examen dental registrado correctamente";
    } else {
        $_SESSION['error'] = "Error al registrar el examen dental: " . $conexion->error;
    }
    
    header("Location: ../index.php?pagina=examenes&tipo=dentales&id_cabra=$id_cabra");
    exit;
} else {
    header('Location: ../index.php');
}
exit;
?>