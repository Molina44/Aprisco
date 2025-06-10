 <?php
session_start();
require '../database.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_cabra = intval($_POST['id_cabra']);
    $fecha_examen = $_POST['fecha_examen'];
    $condicion = $_POST['condicion'];
    $condicion_corporal = intval($_POST['condicion_corporal']);
    $estado_genital = $_POST['estado_genital'];
    $estado_ubre = $_POST['estado_ubre'];
    $puntuacion_darc = !empty($_POST['puntuacion_darc']) ? intval($_POST['puntuacion_darc']) : NULL;
    $puntuacion_famacha = !empty($_POST['puntuacion_famacha']) ? intval($_POST['puntuacion_famacha']) : NULL;
    
    $sql = "INSERT INTO examenes_fisicos (id_cabra, fecha_examen, condicion, condicion_corporal, estado_genital, estado_ubre, puntuacion_darc, puntuacion_famacha) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('ississii', 
        $id_cabra, 
        $fecha_examen, 
        $condicion, 
        $condicion_corporal, 
        $estado_genital, 
        $estado_ubre, 
        $puntuacion_darc, 
        $puntuacion_famacha
    );
    
    if ($stmt->execute()) {
        $_SESSION['exito'] = "Examen físico registrado correctamente";
    } else {
        $_SESSION['error'] = "Error al registrar el examen físico: " . $conexion->error;
    }
    
    header("Location: ../index.php?pagina=examenes&tipo=fisicos&id_cabra=$id_cabra");
    exit;
} else {
    header('Location: ../index.php');
}
exit;
?>