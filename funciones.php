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

// Función recursiva para el árbol genealógico con prevención de ciclos
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
////////////////////////////////////////////////////////////////
// Función para visualizar el árbol
function visualizarArbol($arbol, $nivel = 0) {
    // Limitar a 4 generaciones (niveles 0 a 3)
    if (empty($arbol) || $nivel > 3) {
        return '';
    }
    
    // Obtener detalles de la cabra
    $nombre = $arbol['cabra']['nombre'] ?? 'Desconocido';
    $raza = $arbol['cabra']['raza'] ?? 'No especificada';
    $sexo = ($arbol['cabra']['sexo'] ?? '') == 'M' ? 'Macho' : 'Hembra';
    $fecha = $arbol['cabra']['fecha_nacimiento'] ?? 'Desconocida';
    $generacion = $arbol['generacion'] ?? 0;
    $id = $arbol['cabra']['id'] ?? '';
    
    // Formatear fecha
    $fechaFormateada = ($fecha !== 'Desconocida') ? date('d/m/Y', strtotime($fecha)) : $fecha;
    
    // Crear atributos adicionales
    $tooltipInfo = "ID: $id | Generación: $generacion";
    $claseGeneracion = $generacion > 0 ? 'generacion-' . $generacion : '';
    if ($generacion < 0) {
        $claseGeneracion = 'generacion-hijo';
    }
    
    // Iniciar HTML del nodo
    $html = '<div class="nodo ' . $claseGeneracion . '" ';
    $html .= 'style="margin-left: ' . (abs($generacion) * 30) . 'px" ';
    $html .= 'title="' . htmlspecialchars($tooltipInfo) . '" ';
    $html .= 'data-goat-id="' . htmlspecialchars($id) . '" ';
    $html .= 'data-generacion="' . $generacion . '">';
    
    // 1. SECCIÓN DE PADRES
    $htmlPadres = '';
    if ($nivel < 3) { // Solo mostrar padres si estamos en niveles 0-2
        if (!empty($arbol['padre']) || !empty($arbol['madre'])) {
            $htmlPadres .= '<div class="padres">';
            
            if (!empty($arbol['padre'])) {
                $htmlPadres .= '<div class="relacion padre">';
                $htmlPadres .= '<div class="etiqueta">Padre</div>';
                $htmlPadres .= visualizarArbol($arbol['padre'], $nivel + 1);
                $htmlPadres .= '</div>';
            }

            if (!empty($arbol['madre'])) {
                $htmlPadres .= '<div class="relacion madre">';
                $htmlPadres .= '<div class="etiqueta">Madre</div>';
                $htmlPadres .= visualizarArbol($arbol['madre'], $nivel + 1);
                $htmlPadres .= '</div>';
            }
            
            $htmlPadres .= '</div>';
        }
    }
    
    // 2. INFORMACIÓN DE LA CABRA ACTUAL
    $htmlCabra = '<div class="info-cabra">';
    $htmlCabra .= '<strong>' . htmlspecialchars($nombre) . '</strong>';
    $htmlCabra .= '<div class="detalles-cabra">';
    $htmlCabra .= '<div class="detalle-item"><span class="icono-sexo">' . ($sexo == 'Macho' ? '♂' : '♀') . '</span> ' . $sexo . '</div>';
    $htmlCabra .= '<div class="detalle-item"><span class="icono-raza">🐐</span> ' . htmlspecialchars($raza) . '</div>';
    $htmlCabra .= '<div class="detalle-item"><span class="icono-fecha">📅</span> ' . $fechaFormateada . '</div>';
    if ($generacion != 0) {
        $htmlCabra .= '<div class="detalle-item"><span class="icono-gen">🧬</span> Gen. ' . abs($generacion) . '</div>';
    }
    $htmlCabra .= '</div></div>';

    // 3. SECCIÓN DE HIJOS
    $htmlHijos = '';
    if ($nivel < 3) { // Solo mostrar hijos si estamos en niveles 0-2
        if (!empty($arbol['hijos']) && is_array($arbol['hijos'])) {
            $htmlHijos .= '<div class="hijos">';
            $htmlHijos .= '<div class="etiqueta-hijos">Hijos</div>';
            
            foreach ($arbol['hijos'] as $hijo) {
                // Asignar generación negativa a los hijos
                $hijo['generacion'] = ($generacion - 1);
                $htmlHijos .= '<div class="hijo">';
                $htmlHijos .= visualizarArbol($hijo, $nivel + 1);
                $htmlHijos .= '</div>';
            }
            
            $htmlHijos .= '</div>';
        }
    }
    
    // Ensamblar secciones
    $html .= $htmlPadres . $htmlCabra . $htmlHijos;
    $html .= '</div>';
    
    return $html;
}

//////////////////////////////////////////////////////////////
// Función para obtener todas las razas
function obtenerRazas() {
    global $conexion;
    $result = $conexion->query("SELECT * FROM razas ORDER BY nombre");
    return $result->fetch_all(MYSQLI_ASSOC);
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
            LEFT JOIN razas r ON c.id_raza = r.id_raza
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

//////////////
// Función para validar padres
function validarPadres($id_madre, $id_padre, $id_cabra_actual = null) {
    global $conexion;
    $errores = [];
    
    // Validar existencia y sexo de la madre
    if ($id_madre) {
        $sql = "SELECT sexo, fecha_nacimiento FROM cabras WHERE id_cabra = ?";
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
        $sql = "SELECT sexo, fecha_nacimiento FROM cabras WHERE id_cabra = ?";
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
    
    // Validar edades de los padres
    if ($id_madre || $id_padre) {
        // Obtener fecha de nacimiento de la cabra actual
        $sql = "SELECT fecha_nacimiento FROM cabras WHERE id_cabra = ?";
        $stmt = $conexion->prepare($sql);
        $stmt->bind_param('i', $id_cabra_actual);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $cabra = $result->fetch_assoc();
            $fechaNacimiento = new DateTime($cabra['fecha_nacimiento']);
            
            // Validar madre
            if ($id_madre) {
                $fechaNacMadre = new DateTime($madre['fecha_nacimiento']);
                if ($fechaNacMadre > $fechaNacimiento) {
                    $errores[] = "La madre debe ser mayor que la cría";
                }
            }
            
            // Validar padre
            if ($id_padre) {
                $fechaNacPadre = new DateTime($padre['fecha_nacimiento']);
                if ($fechaNacPadre > $fechaNacimiento) {
                    $errores[] = "El padre debe ser mayor que la cría";
                }
            }
        }
    }
    
    return $errores;
}

// Función para registrar una nueva cabra
function registrarNuevaCabra($datos) {
    global $conexion;
    
    // Validar datos
    $errores = [];
    
    // Validar padres
    $errores_padres = validarPadres($datos['id_madre'] ?? null, $datos['id_padre'] ?? null);
    $errores = array_merge($errores, $errores_padres);
    
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
    $sql = "INSERT INTO cabras (
        imagen, nombre, fecha_nacimiento, color, sexo, id_raza, 
        id_madre, id_padre, partos, fecha_compra, observaciones
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = $conexion->prepare($sql);
    
    // Manejar valores nulos
    $id_madre = !empty($datos['id_madre']) ? $datos['id_madre'] : null;
    $id_padre = !empty($datos['id_padre']) ? $datos['id_padre'] : null;
    $partos = !empty($datos['partos']) ? $datos['partos'] : null;
    $fecha_compra = !empty($datos['fecha_compra']) ? $datos['fecha_compra'] : null;
    
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
        $datos['observaciones']
    );
    
    if ($stmt->execute()) {
        return ['exito' => true, 'id_cabra' => $conexion->insert_id];
    } else {
        return ['exito' => false, 'errores' => ["Error al registrar la cabra: " . $conexion->error]];
    }
}

// Función para actualizar una cabra
function actualizarCabra($id_cabra, $datos) {
    global $conexion;
    
    // Validar datos
    $errores = [];
    
    // Validar padres
    $errores_padres = validarPadres($datos['id_madre'] ?? null, $datos['id_padre'] ?? null, $id_cabra);
    $errores = array_merge($errores, $errores_padres);
    
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
    
    // Actualizar en base de datos
    $sql = "UPDATE cabras SET
        imagen = ?,
        nombre = ?,
        fecha_nacimiento = ?,
        color = ?,
        sexo = ?,
        id_raza = ?,
        id_madre = ?,
        id_padre = ?,
        partos = ?,
        fecha_compra = ?,
        observaciones = ?
    WHERE id_cabra = ?";
    
    $stmt = $conexion->prepare($sql);
    
    // Manejar valores nulos
    $id_madre = !empty($datos['id_madre']) ? $datos['id_madre'] : null;
    $id_padre = !empty($datos['id_padre']) ? $datos['id_padre'] : null;
    $partos = !empty($datos['partos']) ? $datos['partos'] : null;
    $fecha_compra = !empty($datos['fecha_compra']) ? $datos['fecha_compra'] : null;
    
    $stmt->bind_param(
        'sssssiiiissi',
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
        $datos['observaciones'],
        $id_cabra
    );
    
    if ($stmt->execute()) {
        return ['exito' => true];
    } else {
        return ['exito' => false, 'errores' => ["Error al actualizar la cabra: " . $conexion->error]];
    }
}
?>