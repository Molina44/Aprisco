<?php
require 'database.php';

// Función para obtener todas las cabras
function obtenerCabras() {
    global $conexion;
    $sql = "SELECT c.*, r.nombre AS raza 
            FROM cabras c
            JOIN razas r ON c.id_raza = r.id_raza";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función recursiva para el árbol genealógico
function generarArbolGenealogico($id_cabra, $generacion = 0) {
    global $conexion;
    
    $sql = "SELECT * FROM cabras WHERE id_cabra = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    $cabra = $stmt->get_result()->fetch_assoc();
    
    if (!$cabra) return [];
    
    $arbol = [
        'cabra' => $cabra,
        'generacion' => $generacion,
        'madre' => null,
        'padre' => null
    ];
    
    // Obtener madre si existe
    if ($cabra['id_madre']) {
        $arbol['madre'] = generarArbolGenealogico($cabra['id_madre'], $generacion + 1);
    }
    
    // Obtener padre si existe
    if ($cabra['id_padre']) {
        $arbol['padre'] = generarArbolGenealogico($cabra['id_padre'], $generacion + 1);
    }
    
    return $arbol;
}

// Función para visualizar el árbol
function visualizarArbol($arbol) {
    if (empty($arbol)) return '';
    
    $html = '<div class="nodo" style="margin-left: ' . ($arbol['generacion'] * 30) . 'px">';
    $html .= '<strong>' . htmlspecialchars($arbol['cabra']['nombre']) . '</strong>';
    $html .= ' (ID: ' . $arbol['cabra']['id_cabra'] . ')';
    
    if ($arbol['madre']) {
        $html .= visualizarArbol($arbol['madre']);
    }
    
    if ($arbol['padre']) {
        $html .= visualizarArbol($arbol['padre']);
    }
    
    $html .= '</div>';
    return $html;
}
function obtenerRazas() {
    global $conexion;
    $result = $conexion->query("SELECT * FROM razas");
    return $result->fetch_all(MYSQLI_ASSOC);
}

function registrarNuevaCabra($datos) {
    global $conexion;
    // Validar datos y ejecutar inserción
    // Verificar que los padres existan y sean del sexo adecuado
}

function obtenerExamenesFisicos($id_cabra) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM examenes_fisicos WHERE id_cabra = ?");
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}
?>