<?php


// Obtener listado de razas para el dropdown
$razas = [];
$sqlRazas = "SELECT * FROM razas ORDER BY nombre";
$resultRazas = $conexion->query($sqlRazas);
if ($resultRazas) {
    $razas = $resultRazas->fetch_all(MYSQLI_ASSOC);
}

// Obtener listado de cabras para selección de padres
$cabras = [];
$sqlCabras = "SELECT id_cabra, nombre, sexo FROM cabras ORDER BY nombre";
$resultCabras = $conexion->query($sqlCabras);
if ($resultCabras) {
    $cabras = $resultCabras->fetch_all(MYSQLI_ASSOC);
}

// Filtrar hembras para madres
$hembras = array_filter($cabras, function($cabra) {
    return $cabra['sexo'] === 'H';
});

// Filtrar machos para padres
$machos = array_filter($cabras, function($cabra) {
    return $cabra['sexo'] === 'M';
});
?>

<div class="contenedor-formulario">
    <h2><i class="fas fa-plus-circle"></i> Registrar Nueva Cabra</h2>
    
    <form method="POST" action="../acciones/registrar_cabra.php" enctype="multipart/form-data" class="formulario">
        <div class="columnas">
            <div class="columna">
                <h3>Información Básica</h3>
                
                <div class="campo-formulario">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100">
                </div>
                
                <div class="campo-formulario">
                    <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required>
                </div>
                
                <div class="campo-formulario">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" maxlength="50">
                </div>
                
                <div class="campo-formulario">
                    <label>Sexo *</label>
                    <div class="opciones-radio">
                        <label>
                            <input type="radio" name="sexo" value="M" required> Macho
                        </label>
                        <label>
                            <input type="radio" name="sexo" value="H" required> Hembra
                        </label>
                    </div>
                </div>
                
                <div class="campo-formulario">
                    <label for="id_raza">Raza *</label>
                    <select id="id_raza" name="id_raza" required>
                        <option value="">Seleccione una raza</option>
                        <?php foreach ($razas as $raza): ?>
                            <option value="<?= $raza['id_raza'] ?>"><?= htmlspecialchars($raza['nombre']) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="boton-pequeno" onclick="abrirModalRaza()">
                        <i class="fas fa-plus"></i> Nueva Raza
                    </button>
                </div>
                
                <div class="campo-formulario">
                    <label for="partos">Número de Partos (solo hembras)</label>
                    <input type="number" id="partos" name="partos" min="0">
                </div>
            </div>
            
          <div class="columna">
    <h3>Información Genealógica</h3>
    
<div class="campo-formulario">
    <label for="id_madre">Madre (hembra)</label>
    <select id="id_madre" name="id_madre" class="select-parentesco" data-tipo="madre">
        <option value="">Seleccione una madre</option>
        <?php foreach ($hembras as $hembra): 
            $fechaNacimiento = !empty($hembra['fecha_nacimiento']) ? date('Y-m-d', strtotime($hembra['fecha_nacimiento'])) : ''; ?>
            <option value="<?= $hembra['id_cabra'] ?>" 
                    data-sexo="<?= $hembra['sexo'] ?>" 
                    data-fecha-nac="<?= htmlspecialchars($fechaNacimiento) ?>">
                <?= htmlspecialchars($hembra['nombre']) ?> (Identificacion: <?= $hembra['id_cabra'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <div class="mensaje-error" id="error-madre" style="display:none;"></div>
</div>

<div class="campo-formulario">
    <label for="id_padre">Padre (macho)</label>
    <select id="id_padre" name="id_padre" class="select-parentesco" data-tipo="padre">
        <option value="">Seleccione un padre</option>
        <?php foreach ($machos as $macho): 
            $fechaNacimiento = !empty($macho['fecha_nacimiento']) ? date('Y-m-d', strtotime($macho['fecha_nacimiento'])) : ''; ?>
            <option value="<?= $macho['id_cabra'] ?>" 
                    data-sexo="<?= $macho['sexo'] ?>" 
                    data-fecha-nac="<?= htmlspecialchars($fechaNacimiento) ?>">
                <?= htmlspecialchars($macho['nombre']) ?> (Identificacion: <?= $macho['id_cabra'] ?>)
            </option>
        <?php endforeach; ?>
    </select>
    <div class="mensaje-error" id="error-padre" style="display:none;"></div>
</div>

    <div class="campo-formulario">
        <label>Relación Genealógica</label>
        <div id="relacion-genealogica" class="info-relacion">
            <p>Seleccione madre y padre para ver la relación</p>
        </div>
    </div>
    
    <!-- Panel de validación genealógica -->
    <div id="panel-validacion-genealogica" class="panel-validacion" style="display:none;">
        <h4>Validación de Parentesco</h4>
        <ul id="lista-validacion"></ul>
    </div>
</div>
            
            <div class="columna">
                <h3>Información Adicional</h3>
                
                <div class="campo-formulario">
                    <label for="imagen">Fotografía</label>
                    <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png">
                    <div class="preview-imagen" id="preview-imagen">
                        <img src="" alt="Previsualización" style="display: none;">
                        <p>No se ha seleccionado imagen</p>
                    </div>
                </div>
                
                <div class="campo-formulario">
                    <label for="fecha_compra">Fecha de Compra/Adquisición</label>
                    <input type="date" id="fecha_compra" name="fecha_compra">
                </div>
                
               <!-- En la sección de Información Adicional -->
<div class="campo-formulario">
    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" rows="3" maxlength="255"></textarea>
</div>
            </div>
        </div>
        
        <div class="acciones-formulario">
            <button type="submit" class="boton boton-verde">
                <i class="fas fa-save"></i> Registrar Cabra
            </button>
            <a href="index.php?pagina=cabras&subpagina=listar" class="boton boton-gris">
                <i class="fas fa-times"></i> Cancelar
            </a>
        </div>
    </form>
</div>

<!-- Modal para nueva raza -->
<div id="modal-raza" class="modal">
    <div class="modal-contenido">
        <span class="cerrar-modal" onclick="cerrarModalRaza()">&times;</span>
        <h3>Registrar Nueva Raza</h3>
        <form id="form-raza" method="POST" action="../acciones/registrar_raza.php">
            <div class="campo-formulario">
                <label for="nombre_raza">Nombre de la Raza *</label>
                <input type="text" id="nombre_raza" name="nombre" required maxlength="100">
            </div>
            <button type="submit" class="boton boton-verde">
                <i class="fas fa-save"></i> Guardar Raza
            </button>
        </form>
    </div>
</div>

<script>


    // Validación en tiempo real de padres
document.querySelectorAll('.select-parentesco').forEach(select => {
    select.addEventListener('change', function() {
        const tipo = this.dataset.tipo;
        const errorElement = document.getElementById(`error-${tipo}`);
        const idSeleccionado = this.value;
        const fechaNacimiento = new Date(document.getElementById('fecha_nacimiento').value);
        
        // Limpiar mensajes previos
        errorElement.style.display = 'none';
        errorElement.textContent = '';
        
        if (idSeleccionado) {
            const opcion = this.querySelector(`option[value="${idSeleccionado}"]`);
            const fechaNacPadre = new Date(opcion.dataset.fechaNac);
            
            // Validar sexo
            const sexoEsperado = tipo === 'madre' ? 'H' : 'M';
            if (opcion.dataset.sexo !== sexoEsperado) {
                errorElement.textContent = `Error: La cabra seleccionada debe ser ${tipo === 'madre' ? 'hembra' : 'macho'}`;
                errorElement.style.display = 'block';
                this.value = '';
            }
            
            // Validar edad (padre debe ser más viejo)
            if (fechaNacimiento && fechaNacPadre && fechaNacPadre > fechaNacimiento) {
                errorElement.textContent = `Error: El ${tipo} debe ser mayor que la cabra que está registrando`;
                errorElement.style.display = 'block';
                this.value = '';
            }
        }
        
        // Actualizar relación genealógica y validación
        actualizarRelacion();
        validarParentescoCompleto();
    });
});

// Validar cuando cambia la fecha de nacimiento
document.getElementById('fecha_nacimiento').addEventListener('change', function() {
    validarParentescoCompleto();
});

// Función para validar todo el parentesco
function validarParentescoCompleto() {
    const panel = document.getElementById('panel-validacion-genealogica');
    const lista = document.getElementById('lista-validacion');
    lista.innerHTML = '';
    
    const idMadre = document.getElementById('id_madre').value;
    const idPadre = document.getElementById('id_padre').value;
    const fechaNacimiento = new Date(document.getElementById('fecha_nacimiento').value);
    
    // Solo mostrar panel si hay padres seleccionados
    if (!idMadre && !idPadre) {
        panel.style.display = 'none';
        return;
    }
    
    panel.style.display = 'block';
    
    // Validar madre
    if (idMadre) {
        const opcionMadre = document.querySelector(`#id_madre option[value="${idMadre}"]`);
        const fechaNacMadre = new Date(opcionMadre.dataset.fechaNac);
        
        if (fechaNacimiento && fechaNacMadre && fechaNacMadre > fechaNacimiento) {
            const li = document.createElement('li');
            li.className = 'invalido';
            li.innerHTML = '<i class="fas fa-times-circle"></i> La madre debe ser mayor que la cría';
            lista.appendChild(li);
        } else {
            const li = document.createElement('li');
            li.className = 'valido';
            li.innerHTML = '<i class="fas fa-check-circle"></i> Madre válida';
            lista.appendChild(li);
        }
    }
    
    // Validar padre
    if (idPadre) {
        const opcionPadre = document.querySelector(`#id_padre option[value="${idPadre}"]`);
        const fechaNacPadre = new Date(opcionPadre.dataset.fechaNac);
        
        if (fechaNacimiento && fechaNacPadre && fechaNacPadre > fechaNacimiento) {
            const li = document.createElement('li');
            li.className = 'invalido';
            li.innerHTML = '<i class="fas fa-times-circle"></i> El padre debe ser mayor que la cría';
            lista.appendChild(li);
        } else {
            const li = document.createElement('li');
            li.className = 'valido';
            li.innerHTML = '<i class="fas fa-check-circle"></i> Padre válido';
            lista.appendChild(li);
        }
    }
    
    // Validar que no sean la misma cabra
    if (idMadre && idPadre && idMadre === idPadre) {
        const li = document.createElement('li');
        li.className = 'invalido';
        li.innerHTML = '<i class="fas fa-times-circle"></i> La madre y el padre no pueden ser la misma cabra';
        lista.appendChild(li);
    }
    
    // Validar que no sean padres de sí mismos
    const idCabraActual = <?= $cabra['id_cabra'] ?? 'null' ?>;
    if (idCabraActual) {
        if (idMadre && idMadre == idCabraActual) {
            const li = document.createElement('li');
            li.className = 'invalido';
            li.innerHTML = '<i class="fas fa-times-circle"></i> Una cabra no puede ser madre de sí misma';
            lista.appendChild(li);
        }
        
        if (idPadre && idPadre == idCabraActual) {
            const li = document.createElement('li');
            li.className = 'invalido';
            li.innerHTML = '<i class="fas fa-times-circle"></i> Una cabra no puede ser padre de sí misma';
            lista.appendChild(li);
        }
    }
    
    // Si no hay errores, mostrar mensaje positivo
    if (lista.children.length === 0) {
        const li = document.createElement('li');
        li.className = 'valido';
        li.innerHTML = '<i class="fas fa-check-circle"></i> Relación genealógica válida';
        lista.appendChild(li);
    }
}

    // Previsualización de imagen
    document.getElementById('imagen').addEventListener('change', function(e) {
        const preview = document.getElementById('preview-imagen');
        const img = preview.querySelector('img');
        const msg = preview.querySelector('p');
        
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                img.src = e.target.result;
                img.style.display = 'block';
                msg.style.display = 'none';
            }
            
            reader.readAsDataURL(this.files[0]);
        } else {
            img.style.display = 'none';
            msg.style.display = 'block';
        }
    });
    
    // Validación de padres
    document.getElementById('id_madre').addEventListener('change', actualizarRelacion);
    document.getElementById('id_padre').addEventListener('change', actualizarRelacion);
    
    function actualizarRelacion() {
        const madre = document.getElementById('id_madre').value;
        const padre = document.getElementById('id_padre').value;
        const contenedor = document.getElementById('relacion-genealogica');
        
        if (!madre && !padre) {
            contenedor.innerHTML = '<p>Seleccione madre y padre para ver la relación</p>';
            return;
        }
        
        let mensaje = '';
        
        if (madre && padre) {
            mensaje = `<p><i class="fas fa-check-circle verde"></i> Relación completa: madre y padre seleccionados</p>`;
        } else if (madre) {
            mensaje = `<p><i class="fas fa-info-circle azul"></i> Solo madre seleccionada (sin padre)</p>`;
        } else {
            mensaje = `<p><i class="fas fa-info-circle azul"></i> Solo padre seleccionado (sin madre)</p>`;
        }
        
        contenedor.innerHTML = mensaje;
    }
    
    // Funciones para el modal de raza
    function abrirModalRaza() {
        document.getElementById('modal-raza').style.display = 'block';
    }
    
    function cerrarModalRaza() {
        document.getElementById('modal-raza').style.display = 'none';
    }
    
    // Cerrar modal al hacer clic fuera de él
    window.addEventListener('click', function(e) {
        const modal = document.getElementById('modal-raza');
        if (e.target === modal) {
            cerrarModalRaza();
        }
    });
    
    // Manejo del formulario de raza
    document.getElementById('form-raza').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.exito) {
                // Actualizar el dropdown de razas
                const selectRazas = document.getElementById('id_raza');
                const nuevaOpcion = document.createElement('option');
                nuevaOpcion.value = data.id_raza;
                nuevaOpcion.textContent = data.nombre;
                nuevaOpcion.selected = true;
                selectRazas.appendChild(nuevaOpcion);
                
                // Cerrar modal y resetear formulario
                cerrarModalRaza();
                this.reset();
                
                // Mostrar mensaje de éxito
                alert(`Raza "${data.nombre}" registrada exitosamente`);
            } else {
                alert(data.error || 'Error al registrar la raza');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Error en la solicitud');
        });
    });
</script>

<style>
/* Estilos para validación genealógica */
.panel-validacion {
    background-color: #f8f9fa;
    border: 1px solid #e9ecef;
    border-radius: 5px;
    padding: 15px;
    margin-top: 15px;
}

.panel-validacion h4 {
    margin-top: 0;
    color: #2c3e50;
    border-bottom: 1px solid #dee2e6;
    padding-bottom: 8px;
}

#lista-validacion {
    list-style-type: none;
    padding: 0;
    margin: 0;
}

#lista-validacion li {
    padding: 8px 5px;
    border-bottom: 1px solid #eee;
    display: flex;
    align-items: center;
}

#lista-validacion li:last-child {
    border-bottom: none;
}

#lista-validacion li.valido {
    color: #28a745;
}

#lista-validacion li.invalido {
    color: #dc3545;
}

#lista-validacion i {
    margin-right: 8px;
    font-size: 1.2em;
}

.mensaje-error {
    color: #dc3545;
    font-size: 0.85rem;
    margin-top: 5px;
}
</style>