<?php
// Verificar que se haya proporcionado un ID
if (!isset($_GET['id'])) {
    die("ID de cabra no especificado");
}

$id_cabra = $_GET['id'];
$cabra = obtenerCabraPorId($id_cabra);

// Si no se encuentra la cabra, mostrar error
if (!$cabra) {
    die("Cabra no encontrada");
}

// Obtener listado de razas para el dropdown
$razas = obtenerRazas();

// Obtener listado de cabras para selección de padres
$cabras = obtenerCabrasParaDropdown();

// Filtrar hembras para madres
$hembras = array_filter($cabras, function($c) {
    return $c['sexo'] === 'H';
});

// Filtrar machos para padres
$machos = array_filter($cabras, function($c) {
    return $c['sexo'] === 'M';
});
?>

<div class="contenedor-formulario">
    <h2><i class="fas fa-edit"></i> Editar Cabra: <?= htmlspecialchars($cabra['nombre']) ?></h2>
    
    <form method="POST" action="../acciones/actualizar_cabra.php" enctype="multipart/form-data" class="formulario">
        <input type="hidden" name="id_cabra" value="<?= $cabra['id_cabra'] ?>">
        
        <div class="columnas">
            <div class="columna">
                <h3>Información Básica</h3>
                
                <div class="campo-formulario">
                    <label for="nombre">Nombre *</label>
                    <input type="text" id="nombre" name="nombre" required maxlength="100" value="<?= htmlspecialchars($cabra['nombre']) ?>">
                </div>
                
                <div class="campo-formulario">
                    <label for="fecha_nacimiento">Fecha de Nacimiento *</label>
                    <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" required value="<?= $cabra['fecha_nacimiento'] ?>">
                </div>
                
                <div class="campo-formulario">
                    <label for="color">Color</label>
                    <input type="text" id="color" name="color" maxlength="50" value="<?= htmlspecialchars($cabra['color'] ?? '') ?>">
                </div>
                
                <div class="campo-formulario">
                    <label>Sexo *</label>
                    <div class="opciones-radio">
                        <label>
                            <input type="radio" name="sexo" value="M" required <?= $cabra['sexo'] == 'M' ? 'checked' : '' ?>> Macho
                        </label>
                        <label>
                            <input type="radio" name="sexo" value="H" required <?= $cabra['sexo'] == 'H' ? 'checked' : '' ?>> Hembra
                        </label>
                    </div>
                </div>
                
                <div class="campo-formulario">
                    <label for="id_raza">Raza *</label>
                    <select id="id_raza" name="id_raza" required>
                        <option value="">Seleccione una raza</option>
                        <?php foreach ($razas as $raza): ?>
                            <option value="<?= $raza['id_raza'] ?>" <?= $raza['id_raza'] == $cabra['id_raza'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($raza['nombre']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="button" class="boton-pequeno" onclick="abrirModalRaza()">
                        <i class="fas fa-plus"></i> Nueva Raza
                    </button>
                </div>
                
                <div class="campo-formulario">
                    <label for="partos">Número de Partos (solo hembras)</label>
                    <input type="number" id="partos" name="partos" min="0" value="<?= $cabra['partos'] ?? '' ?>">
                </div>
            </div>
            
            <div class="columna">
                <h3>Información Genealógica</h3>
                
                <div class="campo-formulario">
                    <label for="id_madre">Madre (hembra)</label>
                    <select id="id_madre" name="id_madre">
                        <option value="">Seleccione una madre</option>
                        <?php foreach ($hembras as $hembra): ?>
                            <option value="<?= $hembra['id_cabra'] ?>" <?= $hembra['id_cabra'] == $cabra['id_madre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($hembra['nombre']) ?> (ID: <?= $hembra['id_cabra'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="campo-formulario">
                    <label for="id_padre">Padre (macho)</label>
                    <select id="id_padre" name="id_padre">
                        <option value="">Seleccione un padre</option>
                        <?php foreach ($machos as $macho): ?>
                            <option value="<?= $macho['id_cabra'] ?>" <?= $macho['id_cabra'] == $cabra['id_padre'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($macho['nombre']) ?> (ID: <?= $macho['id_cabra'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="campo-formulario">
                    <label>Relación Genealógica</label>
                    <div id="relacion-genealogica" class="info-relacion">
                        <?php
                        $madreSeleccionada = $cabra['id_madre'] ? ' (ID: ' . $cabra['id_madre'] . ')' : '';
                        $padreSeleccionado = $cabra['id_padre'] ? ' (ID: ' . $cabra['id_padre'] . ')' : '';
                        ?>
                        <p>
                            <?php if ($cabra['id_madre'] && $cabra['id_padre']): ?>
                                <i class="fas fa-check-circle verde"></i> Relación completa: madre y padre seleccionados
                            <?php elseif ($cabra['id_madre']): ?>
                                <i class="fas fa-info-circle azul"></i> Solo madre seleccionada <?= $madreSeleccionada ?>
                            <?php elseif ($cabra['id_padre']): ?>
                                <i class="fas fa-info-circle azul"></i> Solo padre seleccionado <?= $padreSeleccionado ?>
                            <?php else: ?>
                                <i class="fas fa-exclamation-triangle naranja"></i> Sin padres registrados
                            <?php endif; ?>
                        </p>
                    </div>
                </div>
            </div>
            
            <div class="columna">
                <h3>Información Adicional</h3>
                
                <div class="campo-formulario">
                    <label for="imagen">Fotografía</label>
                    <input type="file" id="imagen" name="imagen" accept="image/jpeg,image/png">
                    <div class="preview-imagen" id="preview-imagen">
                        <?php if ($cabra['imagen']): ?>
                            <img src="<?= htmlspecialchars($cabra['imagen']) ?>" alt="Imagen actual">
                            <p>Imagen actual</p>
                        <?php else: ?>
                            <img src="" alt="Previsualización" style="display: none;">
                            <p>No hay imagen actual</p>
                        <?php endif; ?>
                    </div>
                    <input type="hidden" name="imagen_actual" value="<?= htmlspecialchars($cabra['imagen'] ?? '') ?>">
                </div>
                
                <div class="campo-formulario">
                    <label for="fecha_compra">Fecha de Compra/Adquisición</label>
                    <input type="date" id="fecha_compra" name="fecha_compra" value="<?= $cabra['fecha_compra'] ?? '' ?>">
                </div>
                
               <!-- En la sección de Información Adicional -->
<div class="campo-formulario">
    <label for="observaciones">Observaciones</label>
    <textarea id="observaciones" name="observaciones" rows="3" maxlength="255"><?= htmlspecialchars($cabra['observaciones'] ?? '') ?></textarea>
</div>
            </div>
        </div>
        
        <div class="acciones-formulario">
            <button type="submit" class="boton boton-verde">
                <i class="fas fa-save"></i> Actualizar Cabra
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
                if (msg) msg.style.display = 'none';
            }
            
            reader.readAsDataURL(this.files[0]);
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
            contenedor.innerHTML = '<p><i class="fas fa-exclamation-triangle naranja"></i> Sin padres registrados</p>';
            return;
        }
        
        let mensaje = '';
        
        if (madre && padre) {
            mensaje = `<p><i class="fas fa-check-circle verde"></i> Relación completa: madre y padre seleccionados</p>`;
        } else if (madre) {
            const madreSeleccionada = document.querySelector('#id_madre option:checked').textContent;
            mensaje = `<p><i class="fas fa-info-circle azul"></i> Solo madre seleccionada (${madreSeleccionada})</p>`;
        } else {
            const padreSeleccionado = document.querySelector('#id_padre option:checked').textContent;
            mensaje = `<p><i class="fas fa-info-circle azul"></i> Solo padre seleccionado (${padreSeleccionado})</p>`;
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