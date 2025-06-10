<?php
if (!isset($_GET['id'])) {
    die("ID de cabra no especificado");
}
$id_cabra = intval($_GET['id']);

// Llamada más segura con parámetros adicionales
$arbol = generarArbolGenealogico($id_cabra, 0, 10, []);
if (empty($arbol)) {
    die("No se pudo generar el árbol genealógico para esta cabra");
}
?>

<div class="ficha-cabra">
    <h2><?= htmlspecialchars($arbol['cabra']['nombre']) ?></h2>
    <img src="<?= htmlspecialchars($arbol['cabra']['imagen'] ?? 'img/default.jpg') ?>" alt="Cabra" width="200">
    
    <div class="detalles">
        <p><strong>Raza:</strong>  <?= htmlspecialchars($arbol['cabra']['raza']) ?></p>
        <p><strong>Sexo:</strong> <?= $arbol['cabra']['sexo'] == 'M' ? 'Macho' : 'Hembra' ?></p>
        <p><strong>Nacimiento:</strong> <?= $arbol['cabra']['fecha_nacimiento'] ?></p>
        <p><strong>Color:</strong> <?= htmlspecialchars($arbol['cabra']['color']) ?></p>
<p><strong>Observaciones:</strong> <?= htmlspecialchars($arbol['cabra']['observaciones']) ?></p>
       
    </div>
    
    </div>
    
    <h3>Árbol Genealógico</h3>
    <div class="arbol-genealogico">
        <?= visualizarArbol($arbol) ?>
    </div>
</div>  