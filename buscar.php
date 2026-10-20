<?php
require 'include/funciones.php';
require_once __DIR__ . '/db.php';

$search = isset($_GET['q']) ? trim($_GET['q']) : '';

$sql = "SELECT p.*, pr.titulo AS proj_titulo, pr.descripcion AS proj_desc, pr.tecnologias AS proj_tech 
        FROM programadores p 
        LEFT JOIN proyectos pr ON p.id = pr.programador_id";

if (!empty($search)) {
    $sql .= " WHERE p.nombre LIKE :search OR p.rol LIKE :search OR pr.tecnologias LIKE :search";
}

$sql .= " GROUP BY p.id ORDER BY p.id ASC";

$stmt = $pdo->prepare($sql);

if (!empty($search)) {
    $stmt->bindValue(':search', '%' . $search . '%');
}

$stmt->execute();
$programadores = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explora Portafolios - DevSite</title>
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <link rel="stylesheet" href="recursos/styles.css">
</head>
<body>
<?php incluirTemplates('sidebar'); ?>
    <!-- Navbar Principal -->
    <header class="navbar">
        <a href="index.php" class="brand-logo">&lt;/&gt; Dev<span>Site</span></a>
        <nav class="nav-links">
            <a href="index.php">Inicio</a>
            <a href="buscar.php" class="active">Portafolios</a>
            <a href="contacto.php">contactar</a>
            <a href="soporte.php">Soporte</a>
            <a href="pagos.php">Pagos</a>
        </nav>
        <div class="nav-user-actions">
            <span class="search-icon">🔍</span>
            <span class="bell-icon">🔔</span>
            <div class="user-avatar-mini">👤</div>
        </div>
    </header>

    <div class="search-page-layout">


        <main class="search-main-content">
            <header class="search-banner">
                <h1>Explora los <span>portafolios</span><br>de nuestra comunidad</h1>
                <p>Descubre el talento de programadores independientes, revisa sus proyectos y encuentra al profesional ideal para tu idea.</p>
            </header>

            <div class="search-filter-bar">
                <form action="buscar.php" method="GET" class="search-input-group">
                    <span class="icon">🔍</span>
                    <input type="text" name="q" placeholder="Buscar por nombre, tecnología o palabra clave..." value="<?php echo htmlspecialchars($search); ?>">
                </form>

                <button class="btn-filter">🎛️ Filtros</button>

                <div class="category-pills">
                    <a href="buscar.php" class="pill <?php echo ($categoria == 'todos') ? 'active' : ''; ?>">Todos</a>
                    <a href="buscar.php?cat=web" class="pill <?php echo ($categoria == 'web') ? 'active' : ''; ?>">Web</a>
                    <a href="buscar.php?cat=mobile" class="pill <?php echo ($categoria == 'mobile') ? 'active' : ''; ?>">Mobile</a>
                    <a href="buscar.php?cat=backend" class="pill <?php echo ($categoria == 'backend') ? 'active' : ''; ?>">Backend</a>
                    <a href="buscar.php?cat=diseno" class="pill <?php echo ($categoria == 'diseno') ? 'active' : ''; ?>">Diseño</a>
                    <a href="buscar.php?cat=otros" class="pill <?php echo ($categoria == 'otros') ? 'active' : ''; ?>">Otros</a>
                </div>
            </div>

            <div class="section-title-row">
                <h2>🧬 Portafolios destacados</h2>
                <a href="buscar.php" class="link-see-all">Ver todos &rarr;</a>
            </div>

<div class="portfolios-grid">
    <?php if (!empty($programadores)): ?>
        <?php foreach ($programadores as $dev): ?>
            <article class="portfolio-card">
                <div class="card-dev-header">
                    <div class="dev-avatar <?php echo htmlspecialchars($dev['avatar_color'] ?? 'blue'); ?>">
                        <?php echo htmlspecialchars($dev['avatar_iniciales'] ?? 'DW'); ?>
                    </div>
                    <div class="dev-info-text">
                        <h3><?php echo htmlspecialchars($dev['nombre']); ?></h3>
                        <span class="dev-role"><?php echo htmlspecialchars($dev['rol']); ?></span>
                    </div>
                    <a href="perfil.php?id=<?php echo $dev['id']; ?>" class="btn-view-profile">Ver perfil &rarr;</a>
                </div>
                
                <div class="project-preview-box">
                    <h4><?php echo htmlspecialchars($dev['proj_titulo'] ?? 'Proyecto Destacado'); ?></h4>
                    <p><?php echo htmlspecialchars($dev['proj_desc'] ?? 'Explora el trabajo y código de este desarrollador.'); ?></p>
                </div>

                <div class="card-tech-tags">
                    <?php 
                    $techs = explode(',', $dev['proj_tech'] ?? 'HTML,CSS,JS');
                    foreach($techs as $tech): 
                    ?>
                        <span><?php echo htmlspecialchars(trim($tech)); ?></span>
                    <?php endforeach; ?>
                </div>

                <div class="card-footer-meta">
                    <span>👁️ 152</span>
                    <span>🕒 Hace 2 semanas</span>
                </div>
            </article>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-results">No se encontraron programadores con esa búsqueda.</p>
    <?php endif; ?>
</div>