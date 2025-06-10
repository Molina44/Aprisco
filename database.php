<?php
$host = 'localhost';
$usuario = 'root';
$password = '';
$dbname = 'aprisco';

$conexion = new mysqli($host, $usuario, $password, $dbname);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Establecer el conjunto de caracteres
$conexion->set_charset("utf8mb4");

?>