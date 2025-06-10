<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Cabras - Aprisco</title>
    <link rel="stylesheet" href="../estilos/estilo.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        function mostrarMensajes() {
            <?php if (isset($_SESSION['exito'])): ?>
                setTimeout(() => {
                    document.querySelector('.mensaje-exito').style.display = 'none';
                }, 5000);
            <?php endif; ?>
            
            <?php if (isset($_SESSION['error'])): ?>
                setTimeout(() => {
                    document.querySelector('.mensaje-error').style.display = 'none';
                }, 5000);
            <?php endif; ?>
        }
        document.addEventListener('DOMContentLoaded', mostrarMensajes);
    </script>
</head>
<body>
    <header class="cabecera-principal">
        <div class="logo">
            <i class="fas fa-paw"></i>
            <h1>Aprisco - Gestión Caprina</h1>
        </div>
        <div class="info-usuario">
            <?php if (isset($_SESSION['usuario'])): ?>
                <span><i class="fas fa-user"></i> <?= htmlspecialchars($_SESSION['usuario']) ?></span>
                <span>Rol: <?= htmlspecialchars($_SESSION['rol']) ?></span>
            <?php endif; ?>
        </div>
    </header>

    <!-- Mensajes de retroalimentación -->
    <?php if (isset($_SESSION['exito'])): ?>
        <div class="mensaje-exito">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['exito']) ?>
            <button onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['exito']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="mensaje-error">
            <i class="fas fa-exclamation-triangle"></i>
            <?= htmlspecialchars($_SESSION['error']) ?>
            <button onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <?php if (!empty($_SESSION['errores'])): ?>
        <div class="mensaje-error">
            <i class="fas fa-exclamation-triangle"></i>
            <ul>
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
            <button onclick="this.parentElement.style.display='none'">&times;</button>
        </div>
        <?php unset($_SESSION['errores']); ?>
    <?php endif; ?>