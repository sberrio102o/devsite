<?php 
// Aseguramos que $opcion exista (si no se pasó, por defecto será 'a')
$opcion = $opcion ?? 'a'; 
?>

<header class="navbar">
    <a href="index.php" class="brand-logo">
        <img src="imagenes/logo-devsite2.png" alt="Logo" style="width: 50px; height: auto;"> Dev<span>Site</span>
    </a>
    <nav class="nav-links">
        <a href="index.php" class="nav-link <?php echo ($opcion === 'a') ? 'active' : ''; ?>">Inicio</a>
        <a href="portafolio.php" class="nav-link <?php echo ($opcion === 'b') ? 'active' : ''; ?>">Portafolios</a>
        <a href="perfil.php">Ver Perfil</a>
        <a href="registro.php" class="nav-link">Soy Programador</a>
        
        <!-- Cambiamos las clases dinámicamente según la opción elegida -->
        <a href="pagos.php" class="nav-link <?php echo ($opcion === 'c') ? 'active' : ''; ?>">Pagos</a>
        <a href="soporte.php" class="nav-link <?php echo ($opcion === 'd') ? 'active' : ''; ?>">Soporte</a>
    </nav>
    
</header>