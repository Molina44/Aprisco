<?php
require 'database.php';

// Función para obtener todas las cabras con información de raza
function obtenerCabras() {
    global $conexion;
    $sql = "SELECT c.*, r.nombre AS raza 
            FROM cabras c
            JOIN razas r ON c.id_raza = r.id_raza
            ORDER BY c.nombre";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función recursiva para el árbol genealógico
function generarArbolGenealogico($id_cabra, $generacion = 0, $max_profundidad = 10, $visitados = []) {
    global $conexion;
    
    // Prevenir recursión infinita o demasiado profunda
    if ($generacion > $max_profundidad || in_array($id_cabra, $visitados)) {
        return [];
    }
    
    $visitados[] = $id_cabra;
    
    $sql = "SELECT c.*, r.nombre AS raza 
            FROM cabras c
            LEFT JOIN razas r ON c.id_raza = r.id_raza
            WHERE c.id_cabra = ?";
    
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        die("Error en preparación: " . $conexion->error);
    }
    
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    $result = $stmt->get_result();
    $cabra = $result->fetch_assoc();
    
    if (!$cabra) {
        return [];
    }
    
    $arbol = [
        'cabra' => $cabra,
        'generacion' => $generacion,
        'madre' => null,
        'padre' => null
    ];
    
    // Obtener madre si existe
    if ($cabra['id_madre']) {
        $arbol['madre'] = generarArbolGenealogico(
            $cabra['id_madre'], 
            $generacion + 1, 
            $max_profundidad, 
            $visitados
        );
    }
    
    // Obtener padre si existe
    if ($cabra['id_padre']) {
        $arbol['padre'] = generarArbolGenealogico(
            $cabra['id_padre'], 
            $generacion + 1, 
            $max_profundidad, 
            $visitados
        );
    }
    
    return $arbol;
}
// Función para visualizar el árbol
function visualizarArbol($arbol) {
    if (empty($arbol)) return '';
    
    $html = '<div class="nodo" style="margin-left: ' . ($arbol['generacion'] * 30) . 'px">';
    $html .= '<div class="info-cabra">';
    $html .= '<strong>' . htmlspecialchars($arbol['cabra']['nombre']) . '</strong>';
    $html .= '<div class="detalles-cabra">';
    $html .= 'ID: ' . $arbol['cabra']['id_cabra'] . ' | ';
    $html .= ($arbol['cabra']['sexo'] == 'M' ? 'Macho' : 'Hembra') . ' | ';
    $html .= 'Nac: ' . date('d/m/Y', strtotime($arbol['cabra']['fecha_nacimiento']));
    $html .= '</div></div>';
    
    $html .= '<div class="relaciones">';
    if ($arbol['madre']) {
        $html .= '<div class="relacion madre">';
        $html .= '<div class="etiqueta">Madre:</div>';
        $html .= visualizarArbol($arbol['madre']);
        $html .= '</div>';
    }
    
    if ($arbol['padre']) {
        $html .= '<div class="relacion padre">';
        $html .= '<div class="etiqueta">Padre:</div>';
        $html .= visualizarArbol($arbol['padre']);
        $html .= '</div>';
    }
    $html .= '</div></div>';
    
    return $html;
}

// Función para obtener todas las razas
function obtenerRazas() {
    global $conexion;
    $result = $conexion->query("SELECT * FROM razas ORDER BY nombre");
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para registrar una nueva cabra con validación de padres
function registrarNuevaCabra($datos) {
    global $conexion;
    
    // Validar datos
    $errores = [];
    
    // Validar padres
    if (!empty($datos['id_madre']) && !validarSexoParentesco($datos['id_madre'], 'H')) {
        $errores[] = "La madre seleccionada no es una hembra";
    }
    
    if (!empty($datos['id_padre']) && !validarSexoParentesco($datos['id_padre'], 'M')) {
        $errores[] = "El padre seleccionado no es un macho";
    }
    
    // Validar fecha de nacimiento
    $fechaActual = new DateTime();
    $fechaNacimiento = new DateTime($datos['fecha_nacimiento']);
    if ($fechaNacimiento > $fechaActual) {
        $errores[] = "La fecha de nacimiento no puede ser futura";
    }
    
    // Validar partos solo para hembras
    if ($datos['sexo'] == 'M' && !empty($datos['partos'])) {
        $errores[] = "Los machos no pueden tener registros de partos";
    }
    
    if (!empty($errores)) {
        return ['exito' => false, 'errores' => $errores];
    }
    
    // Insertar en base de datos
    $observaciones = $datos['observaciones'] ?? '';
    
    $sql = "INSERT INTO cabras (
        imagen, nombre, fecha_nacimiento, color, sexo, id_raza, 
        id_madre, id_padre, partos, fecha_compra, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    $stmt->bind_param(
        'sssssiiiiss',
        $datos['imagen'],
        $datos['nombre'],
        $datos['fecha_nacimiento'],
        $datos['color'],
        $datos['sexo'],
        $datos['id_raza'],
        $id_madre,
        $id_padre,
        $partos,
        $fecha_compra,
        $observaciones
    );

    if ($stmt->execute()) {
        return ['exito' => true, 'id_cabra' => $conexion->insert_id];
    } else {
        return ['exito' => false, 'errores' => ["Error al registrar la cabra: " . $conexion->error]];
    }
}

// Función para obtener exámenes físicos de una cabra
function obtenerExamenesFisicos($id_cabra) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM examenes_fisicos WHERE id_cabra = ? ORDER BY fecha_examen DESC");
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para validar el sexo de un parentesco
function validarSexoParentesco($id_cabra, $sexo_esperado) {
    global $conexion;
    
    $sql = "SELECT sexo FROM cabras WHERE id_cabra = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $cabra = $result->fetch_assoc();
        return $cabra['sexo'] === $sexo_esperado;
    }
    
    return false;
}

// Función para obtener una cabra por su ID
function obtenerCabraPorId($id_cabra) {
    global $conexion;
    
    $sql = "SELECT c.*, r.nombre AS raza 
            FROM cabras c
            JOIN razas r ON c.id_raza = r.id_raza
            WHERE c.id_cabra = ?";
            
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->fetch_assoc();
}

// Función para obtener exámenes dentales de una cabra
function obtenerExamenesDentales($id_cabra) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM examenes_dentales WHERE id_cabra = ? ORDER BY fecha_examen DESC");
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener registros de peso de una cabra
function obtenerRegistrosPeso($id_cabra) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM registros_peso WHERE id_cabra = ? ORDER BY fecha_registro DESC");
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para obtener propietarios de una cabra
function obtenerPropietariosCabra($id_cabra) {
    global $conexion;
    $stmt = $conexion->prepare("SELECT * FROM propietarios_cabras WHERE id_cabra = ?");
    $stmt->bind_param('i', $id_cabra);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Función para registrar un nuevo examen físico
function registrarExamenFisico($datos) {
    global $conexion;
    
    $sql = "INSERT INTO examenes_fisicos (
        id_cabra, fecha_examen, condicion, condicion_corporal, 
        estado_genital, estado_ubre, puntuacion_darc, puntuacion_famacha
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'ississii',
        $datos['id_cabra'],
        $datos['fecha_examen'],
        $datos['condicion'],
        $datos['condicion_corporal'],
        $datos['estado_genital'],
        $datos['estado_ubre'],
        $datos['puntuacion_darc'],
        $datos['puntuacion_famacha']
    );
    
    return $stmt->execute();
}

// Función para registrar un nuevo examen dental
function registrarExamenDental($datos) {
    global $conexion;
    
    $sql = "INSERT INTO examenes_dentales (
        id_cabra, fecha_examen, sin_muda, pinzas, primeros_medios, 
        segundos_medios, extremos, desgaste, perdidas_dentales
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param(
        'issiiiiis',
        $datos['id_cabra'],
        $datos['fecha_examen'],
        $datos['sin_muda'],
        $datos['pinzas'],
        $datos['primeros_medios'],
        $datos['segundos_medios'],
        $datos['extremos'],
        $datos['desgaste'],
        $datos['perdidas_dentales']
    );
    
    return $stmt->execute();
}

// Función para registrar un nuevo peso
function registrarPeso($datos) {
    global $conexion;
    
    $sql = "INSERT INTO registros_peso (
        id_cabra, peso, fecha_registro, es_peso_nacimiento, rs_oservaciones
    ) VALUES (?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    $es_peso_nacimiento = isset($datos['es_peso_nacimiento']) ? 1 : 0;
    $stmt->bind_param(
        'idsis',
        $datos['id_cabra'],
        $datos['peso'],
        $datos['fecha_registro'],
        $es_peso_nacimiento,
        $datos['observaciones']
    );
    
    return $stmt->execute();
}

// Función para obtener todas las cabras para un dropdown
function obtenerCabrasParaDropdown() {
    global $conexion;
    $sql = "SELECT id_cabra, nombre, sexo FROM cabras ORDER BY nombre";
    $result = $conexion->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Función para registrar una nueva raza
function registrarRaza($nombre) {
    global $conexion;
    
    // Verificar si la raza ya existe
    $sql = "SELECT id_raza FROM razas WHERE nombre = ?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $nombre);
    $stmt->execute();
    
    if ($stmt->get_result()->num_rows > 0) {
        return ['exito' => false, 'error' => 'La raza ya existe'];
    }
    
    // Insertar nueva raza
    $sql = "INSERT INTO razas (nombre) VALUES (?)";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param('s', $nombre);
    
    if ($stmt->execute()) {
        return ['exito' => true, 'id_raza' => $conexion->insert_id];
    } else {
        return ['exito' => false, 'error' => $conexion->error];
    }
}

// Función para obtener la edad de una cabra en años y meses
function calcularEdad($fecha_nacimiento) {
    $nacimiento = new DateTime($fecha_nacimiento);
    $hoy = new DateTime();
    $diferencia = $hoy->diff($nacimiento);
    
    $edad = '';
    if ($diferencia->y > 0) {
        $edad .= $diferencia->y . ' año' . ($diferencia->y > 1 ? 's' : '');
    }
    if ($diferencia->m > 0) {
        if (!empty($edad)) $edad .= ', ';
        $edad .= $diferencia->m . ' mes' . ($diferencia->m > 1 ? 'es' : '');
    }
    if (empty($edad)) {
        $edad = $diferencia->d . ' día' . ($diferencia->d > 1 ? 's' : '');
    }
    
    return $edad;
}


function validarPadres($id_madre, $id_padre, $id_cabra_actual = null) {
    global $conexion;
    $errores = [];
    
    // Validar existencia y sexo de la madre
    if ($id_madre) {
        $sql = "SELECT sexo FROM cabras WHERE id_cabra = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $id_madre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errores[] = "La madre seleccionada (ID $id_madre) no existe";
        } else {
            $madre = $result->fetch_assoc();
            if ($madre['sexo'] !== 'H') {
                $errores[] = "La madre seleccionada (ID $id_madre) no es una hembra";
            }
        }
    }
    
    // Validar existencia y sexo del padre
    if ($id_padre) {
        $sql = "SELECT sexo FROM cabras WHERE id_cabra = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $id_padre);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 0) {
            $errores[] = "El padre seleccionado (ID $id_padre) no existe";
        } else {
            $padre = $result->fetch_assoc();
            if ($padre['sexo'] !== 'M') {
                $errores[] = "El padre seleccionado (ID $id_padre) no es un macho";
            }
        }
    }
    
    // Validar auto-referencia
    if ($id_cabra_actual) {
        if ($id_madre == $id_cabra_actual) {
            $errores[] = "Una cabra no puede ser su propia madre";
        }
        
        if ($id_padre == $id_cabra_actual) {
            $errores[] = "Una cabra no puede ser su propio padre";
        }
    }
    
    // Validar misma cabra como ambos padres
    if ($id_madre && $id_padre && $id_madre == $id_padre) {
        $errores[] = "La madre y el padre no pueden ser la misma cabra";
    }
    
    return $errores;
}

function obtenerAncestros($id_cabra, $profundidad = 5) {
    $ancestros = [];
    
    if ($profundidad <= 0) return $ancestros;
    
    $cabra = obtenerCabraPorId($id_cabra);
    
    if ($cabra['id_madre']) {
        $ancestros[] = $cabra['id_madre'];
        $ancestros = array_merge($ancestros, obtenerAncestros($cabra['id_madre'], $profundidad - 1));
    }
    
    if ($cabra['id_padre']) {
        $ancestros[] = $cabra['id_padre'];
        $ancestros = array_merge($ancestros, obtenerAncestros($cabra['id_padre'], $profundidad - 1));
    }
    
    return array_unique($ancestros);
}
?>