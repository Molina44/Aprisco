<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
require 'funciones.php';


// Verificar autenticación
if (!isset($_SESSION['usuario'])) {
    header('Location: auth.php');
    exit;
}

$pagina = $_GET['pagina'] ?? 'inicio';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Cabras - Aprisco</title>
    <link rel="stylesheet" href="estilos/estilo.css">
</head>
<body>
    <?php include 'includes/cabecera.php'; ?>
    <?php include 'includes/navegacion.php'; ?>

    <main class="contenido-principal">
        <?php
        // Sistema de enrutamiento básico
        switch ($pagina) {
            case 'cabras':
                $subpagina = $_GET['subpagina'] ?? 'listar';
                include "templates/cabras/{$subpagina}.php";
                break;
                
            case 'examenes':
                $tipo = $_GET['tipo'] ?? 'fisicos';
                include "templates/examenes/{$tipo}.php";
                break;
                
            case 'pesos':
                include "templates/pesos/registrar.php";
                break;
                
            case 'propietarios':
                $subpagina = $_GET['subpagina'] ?? 'listar';
                include "templates/propietarios/{$subpagina}.php";
                break;
                
            default:
                echo '<h1>Bienvenido al Sistema de Gestión de Cabras</h1>';
                echo '<p>Seleccione una opción del menú para comenzar</p>';
        }
        ?>
    </main>

    <?php include 'includes/pie.php'; ?>
</body>
</html>