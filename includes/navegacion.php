<nav class="navegacion-principal">
    <ul>
        <li>
            <a href="index.php">
                <i class="fas fa-home"></i>
                <span>Inicio</span>
            </a>
        </li>
        <li>
            <a href="index.php?pagina=cabras">
                <i class="fas fa-goat"></i>
                <span>Cabras</span>
            </a>
            <ul class="submenu">
                <li><a href="index.php?pagina=cabras&subpagina=listar">Listar Cabras</a></li>
                <li><a href="index.php?pagina=cabras&subpagina=registrar">Registrar Nueva</a></li>
                <li><a href="index.php?pagina=cabras&subpagina=arbol">Árbol Genealógico</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?pagina=examenes">
                <i class="fas fa-stethoscope"></i>
                <span>Exámenes</span>
            </a>
            <ul class="submenu">
                <li><a href="index.php?pagina=examenes&tipo=fisicos">Físicos</a></li>
                <li><a href="index.php?pagina=examenes&tipo=dentales">Dentales</a></li>
                <li><a href="index.php?pagina=examenes&tipo=otros">Otros</a></li>
            </ul>
        </li>
        <li>
            <a href="index.php?pagina=pesos">
                <i class="fas fa-weight-scale"></i>
                <span>Registro de Pesos</span>
            </a>
        </li>
        <li>
            <a href="index.php?pagina=propietarios">
                <i class="fas fa-users"></i>
                <span>Propietarios</span>
            </a>
        </li>
        <li class="cerrar-sesion">
            <a href="logout.php">
                <i class="fas fa-sign-out-alt"></i>
                <span>Cerrar Sesión</span>
            </a>
        </li>
    </ul>
</nav>