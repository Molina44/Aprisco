<h2>Listado de Cabras Registradas</h2>

<table class="tabla-datos">
    <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Raza</th>
            <th>Sexo</th>
            <th>Nacimiento</th>
            <th>Acciones</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach (obtenerCabras() as $cabra): ?>
        <tr>
            <td><?= $cabra['id_cabra'] ?></td>
            <td><?= htmlspecialchars($cabra['nombre']) ?></td>
            <td><?= htmlspecialchars($cabra['raza']) ?></td>
            <td><?= $cabra['sexo'] == 'M' ? 'Macho' : 'Hembra' ?></td>
            <td><?= $cabra['fecha_nacimiento'] ?></td>
            <td>
                <a href="index.php?pagina=cabras&subpagina=ver&id=<?= $cabra['id_cabra'] ?>">Ver</a>
                <a href="index.php?pagina=cabras&subpagina=editar&id=<?= $cabra['id_cabra'] ?>">Editar</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<a href="index.php?pagina=cabras&subpagina=registrar" class="boton">Registrar Nueva Cabra</a>