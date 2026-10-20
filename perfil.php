<?php

session_start();
require_once('db.php');

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];
$especialidades = [
    'frontend' => 'Frontend', 'backend' => 'Backend', 'fullstack' => 'Full Stack', 'movil' => 'Desarrollo móvil',
    'uiux' => 'UI/UX y diseño web', 'datos' => 'Datos y automatización', 'qa' => 'QA y optimización',
];
$niveles = ['junior' => 'Junior', 'semisenior' => 'Semi Senior', 'senior' => 'Senior'];

$demo = [
    1 => ['nombre' => 'Jeremy Saavedra', 'handle' => 'jeremysaavedra', 'rol' => 'Desarrollador Frontend & UI/UX',
        'bio' => 'Especializado en landing pages optimizadas para conversión y maquetación accesible. Me gusta convertir ideas en interfaces claras, rápidas y fáciles de usar.',
        'ubicacion' => 'Medellín, Colombia', 'desde' => 'mayo 2025',
        'skills' => ['HTML5', 'CSS3', 'JavaScript', 'UI/UX', 'Figma', 'Accesibilidad'],
        'edu' => [['Tecnólogo en Desarrollo de Software', 'Institución educativa (en curso)', '2023 - Actualidad']],
        'proyectos' => [
            ['LandingPro', 'Landing pages enfocadas en conversión para pequeños negocios.', ['HTML', 'CSS', 'JavaScript'], 'fa-rocket', ['#4f46e5', '#7c3aed']],
            ['UI Kit Accesible', 'Colección de componentes accesibles y responsivos.', ['CSS', 'Figma', 'UI/UX'], 'fa-palette', ['#0ea5e9', '#6366f1']],
        ],
        'stats' => [3, 12, 5, 8]],
    2 => ['nombre' => 'Andrés Mendoza', 'handle' => 'andresmendoza', 'rol' => 'Desarrollador Backend & Databases',
        'bio' => 'Experto en PHP, SQLite/MySQL y desarrollo de APIs para sistemas de gestión interna. Diseño bases de datos ordenadas y servicios fáciles de mantener.',
        'ubicacion' => 'Bogotá, Colombia', 'desde' => 'junio 2025',
        'skills' => ['PHP', 'SQLite', 'MySQL', 'APIs', 'Git', 'Linux'],
        'edu' => [['Ingeniería de Sistemas', 'Universidad (en curso)', '2022 - Actualidad']],
        'proyectos' => [
            ['Inventario Pro', 'Sistema de inventario con roles y reportes para pymes.', ['PHP', 'MySQL', 'JavaScript'], 'fa-boxes-stacked', ['#059669', '#0ea5e9']],
            ['API de Reservas', 'API REST para gestionar reservas con autenticación.', ['PHP', 'SQLite', 'APIs'], 'fa-database', ['#7c3aed', '#4f46e5']],
        ],
        'stats' => [2, 9, 11, 6]],
    3 => ['nombre' => 'Sofía Gómez', 'handle' => 'sofiagomez', 'rol' => 'Desarrolladora Python & Automatización',
        'bio' => 'Creación de scripts de automatización de tareas y scrapers de datos para negocios. Me motiva ahorrarle horas de trabajo repetitivo a los equipos.',
        'ubicacion' => 'Cali, Colombia', 'desde' => 'abril 2025',
        'skills' => ['Python', 'Django', 'Pandas', 'APIs', 'SQL', 'Git'],
        'edu' => [['Ingeniería de Datos', 'Universidad (en curso)', '2022 - Actualidad']],
        'proyectos' => [
            ['AutoReport', 'Automatiza reportes semanales desde hojas de cálculo.', ['Python', 'Pandas'], 'fa-chart-line', ['#f59e0b', '#ef4444']],
            ['PriceWatch', 'Scraper que monitorea precios y envía alertas.', ['Python', 'Django', 'APIs'], 'fa-spider', ['#14b8a6', '#4f46e5']],
        ],
        'stats' => [4, 15, 9, 7]],
    4 => ['nombre' => 'Daniel Acosta', 'handle' => 'danielacosta', 'rol' => 'Diseñador Web',
        'bio' => 'Diseño sitios web atractivos y funcionales que comunican bien la identidad de cada marca.',
        'ubicacion' => 'Barranquilla, Colombia', 'desde' => 'julio 2025',
        'skills' => ['Figma', 'HTML', 'CSS', 'JavaScript', 'Branding', 'UI/UX'],
        'edu' => [['Diseño Digital', 'Institución educativa', '2021 - 2024']],
        'proyectos' => [
            ['TravelGo', 'Plataforma de viajes con recomendaciones y mapa interactivo.', ['HTML', 'CSS', 'JavaScript', 'API'], 'fa-plane', ['#0ea5e9', '#6366f1']],
            ['Café Aurora', 'Sitio web para una cafetería con menú digital.', ['HTML', 'CSS', 'Figma'], 'fa-mug-hot', ['#b45309', '#7c2d12']],
        ],
        'stats' => [3, 8, 6, 4]],
    5 => ['nombre' => 'Camila Rodríguez', 'handle' => 'camilarodriguez', 'rol' => 'Desarrolladora WordPress',
        'bio' => 'Construyo y personalizo sitios en WordPress y tiendas con WooCommerce, cuidando velocidad y posicionamiento.',
        'ubicacion' => 'Cartagena, Colombia', 'desde' => 'marzo 2025',
        'skills' => ['WordPress', 'PHP', 'MySQL', 'WooCommerce', 'Elementor', 'SEO'],
        'edu' => [['Tecnología en Sistemas', 'Institución educativa', '2020 - 2023']],
        'proyectos' => [
            ['GestorPro', 'Sistema de gestión de proyectos con panel administrativo.', ['PHP', 'MySQL', 'JavaScript'], 'fa-list-check', ['#6366f1', '#8b5cf6']],
            ['Tienda Bohemia', 'Tienda online en WooCommerce con pagos integrados.', ['WordPress', 'WooCommerce'], 'fa-bag-shopping', ['#ec4899', '#f97316']],
        ],
        'stats' => [2, 10, 14, 9]],
    6 => ['nombre' => 'Lucas Martínez', 'handle' => 'lucasmartinez', 'rol' => 'E-commerce / Shopify',
        'bio' => 'Ayudo a emprendedores a lanzar y mejorar su tienda online en Shopify, desde el tema hasta el checkout.',
        'ubicacion' => 'Pereira, Colombia', 'desde' => 'agosto 2025',
        'skills' => ['Shopify', 'Liquid', 'JavaScript', 'CSS', 'Stripe', 'SEO'],
        'edu' => [['Marketing Digital', 'Institución educativa', '2021 - 2023']],
        'proyectos' => [
            ['Moda Local', 'Tienda Shopify con tema personalizado y catálogo dinámico.', ['Shopify', 'Liquid', 'CSS'], 'fa-shirt', ['#0ea5e9', '#14b8a6']],
            ['EcoMarket', 'Tienda de productos sostenibles optimizada para móviles.', ['Shopify', 'JavaScript'], 'fa-leaf', ['#16a34a', '#065f46']],
        ],
        'stats' => [2, 7, 8, 5]],
    7 => ['nombre' => 'Valentina Pérez', 'handle' => 'valentinaperez', 'rol' => 'Desarrolladora Full Stack Junior',
        'bio' => 'Desarrolladora full stack junior con ganas de aprender. Disfruto trabajar en equipo y construir productos de principio a fin.',
        'ubicacion' => 'Manizales, Colombia', 'desde' => 'septiembre 2025',
        'skills' => ['React', 'Node.js', 'JavaScript', 'MySQL', 'Git', 'HTML'],
        'edu' => [['Ingeniería de Software', 'Universidad (en curso)', '2023 - Actualidad']],
        'proyectos' => [
            ['TrackFit', 'App para seguimiento de hábitos y rutinas de ejercicio.', ['React', 'Node.js', 'MySQL'], 'fa-dumbbell', ['#8b5cf6', '#ec4899']],
            ['Taskly', 'Gestor de tareas colaborativo con tableros.', ['React', 'JavaScript'], 'fa-square-check', ['#4f46e5', '#0ea5e9']],
        ],
        'stats' => [2, 6, 4, 10]],
    8 => ['nombre' => 'Mateo Torres', 'handle' => 'mateotorres', 'rol' => 'QA & Optimización',
        'bio' => 'Me especializo en pruebas y optimización de rendimiento para que los sitios sean rápidos y confiables.',
        'ubicacion' => 'Bucaramanga, Colombia', 'desde' => 'octubre 2025',
        'skills' => ['Testing', 'Cypress', 'Lighthouse', 'JavaScript', 'Git', 'Optimización'],
        'edu' => [['Ingeniería de Sistemas', 'Universidad (en curso)', '2022 - Actualidad']],
        'proyectos' => [
            ['SpeedAudit', 'Auditorías de rendimiento con reportes automáticos.', ['Lighthouse', 'JavaScript'], 'fa-gauge-high', ['#f59e0b', '#ef4444']],
            ['TestSuite E2E', 'Pruebas end-to-end para tiendas en línea.', ['Cypress', 'JavaScript'], 'fa-vial', ['#14b8a6', '#6366f1']],
        ],
        'stats' => [1, 5, 3, 6]],
];


$id = isset($_GET['id']) ? intval($_GET['id']) : (isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : 0);
$perfil = null;
$fuente = null;

if ($id > 0) {
    // 1) Base de datos
    $conexion = $pdo ?? null;
    if ($conexion instanceof PDO) {
        try {
            $st = $conexion->prepare('SELECT * FROM usuarios WHERE id = ?');
            $st->execute([$id]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $esDuenoDb = isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id;
            if ($row && (($row['tipo'] ?? '') === 'programador' || $esDuenoDb)) {
                $partes = preg_split('/\s+/', trim($row['nombre']));
                $handle = strtolower(preg_replace('/[^a-z0-9]/i', '', iconv('UTF-8', 'ASCII//TRANSLIT', $row['nombre']) ?: $row['nombre']));
                $esp = $especialidades[$row['especialidad'] ?? ''] ?? '';
                $niv = $niveles[$row['nivel'] ?? ''] ?? '';
                $ts  = !empty($row['created_at']) ? strtotime($row['created_at']) : false;
                $perfil = [
                    'nombre'    => $row['nombre'],
                    'handle'    => substr($handle, 0, 18),
                    'rol'       => trim($esp . ($esp && $niv ? ' · ' : '') . $niv) ?: 'Programador',
                    'bio'       => $row['bio'] ?? '',
                    'ubicacion' => $row['ubicacion'] ?? '',
                    'desde'     => $ts ? ($meses[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts)) : '',
                    'skills'    => array_values(array_filter(array_map('trim', explode(',', (string)($row['habilidades'] ?? ''))))),
                    'edu'       => !empty($row['educacion']) ? [[$row['educacion'], '', '']] : [],
                    'proyectos' => [],
                    'stats'     => [0, 0, 0, 0],
                    'portafolio'=> $row['portafolio'] ?? '',
                ];
                $fuente = 'db';
            }
        } catch (PDOException $e) {
            error_log('perfil.php: ' . $e->getMessage()); // la tabla puede no existir todavía
        }
    }
    // 2) Datos de ejemplo
    if (!$perfil && isset($demo[$id])) {
        $perfil = $demo[$id];
        $fuente = 'demo';
    }
}

if (!$perfil) {
    http_response_code(404);
}

$esDueno = ($fuente === 'db' && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id);


$iniciales = '';
if ($perfil) {
    foreach (array_slice(preg_split('/\s+/', trim($perfil['nombre'])), 0, 2) as $p) {
        $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
    }
}
$gradientes = [['#6366f1', '#a855f7'], ['#0ea5e9', '#6366f1'], ['#10b981', '#0ea5e9'], ['#ec4899', '#8b5cf6'], ['#f59e0b', '#ef4444'], ['#14b8a6', '#4f46e5']];
$grad = $gradientes[$id % count($gradientes)];

$actividad = [];
if ($perfil) {
    if ($fuente === 'demo') {
        $actividad = [
            ['Subió un nuevo portafolio', 'hace 2 días', '#6366f1'],
            ['Comentó en un proyecto', 'hace 3 días', '#34d399'],
            ['Actualizó su perfil', 'hace 5 días', '#34d399'],
        ];
    } else {
        $actividad = [['Se unió a DevWeb' . ($perfil['desde'] ? ' en ' . $perfil['desde'] : ''), '', '#6366f1']];
    }
}

$estrellas = '';
$edificios = '';
if ($perfil) {
    mt_srand(11);
    for ($i = 0; $i < 28; $i++) {
        $estrellas .= '<circle cx="' . mt_rand(0, 900) . '" cy="' . mt_rand(2, 70) . '" r="' . (mt_rand(4, 10) / 10) . '" fill="#fff" opacity="' . (mt_rand(3, 8) / 10) . '"/>';
    }
    $x = 0;
    while ($x < 620) {
        $w = mt_rand(14, 34);
        $alto = mt_rand(18, 70);
        $y = 160 - $alto;
        $edificios .= "<rect x='$x' y='$y' width='$w' height='$alto' fill='#17143a'/>";
        for ($wy = $y + 5; $wy < 156; $wy += 9) {
            for ($wx = $x + 4; $wx < $x + $w - 3; $wx += 7) {
                if (mt_rand(0, 3) === 0) {
                    $edificios .= "<rect x='$wx' y='$wy' width='2' height='3' fill='#ffd27a' opacity='.85'/>";
                }
            }
        }
        $x += $w + mt_rand(0, 3);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $perfil ? h($perfil['nombre']) . ' - DevWeb' : 'Perfil no encontrado - DevWeb'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Caveat:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg: #060a16; --rail: #0a0e1a; --panel: #0a1124; --panel-2: #0c1530; --line: #18223a;
            --accent: #5b5ff5; --accent-soft: #8b90ff; --text: #e5e9f5; --muted: #8a96b3; --ok: #34d399;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif; color: var(--text); min-height: 100vh;
            background: radial-gradient(circle at 100% 0%, rgba(91,95,245,.10), transparent 40%), var(--bg);
        }
        a { color: inherit; text-decoration: none; }
        [hidden] { display: none !important; }

        .shell { display: flex; min-height: 100vh; }

        .rail {
            width: 78px; flex: none; background: var(--rail); border-right: 1px solid var(--line);
            display: flex; flex-direction: column; align-items: center; gap: 1.2rem;
            padding: 1.4rem 0; position: sticky; top: 0; height: 100vh; z-index: 30;
        }
        .rail-link {
            width: 2.75rem; height: 2.75rem; display: flex; align-items: center; justify-content: center;
            border-radius: .85rem; color: #8b97b3; font-size: 1.05rem; transition: all .15s; position: relative;
        }
        .rail-link:hover { color: #fff; background: rgba(255,255,255,.06); }
        .rail-link.active { background: var(--accent); color: #fff; box-shadow: 0 6px 18px rgba(91,95,245,.4); }
        .rail-link.bottom { margin-top: auto; }
        .rail-link::after {
            content: attr(data-label); position: absolute; left: calc(100% + .6rem); top: 50%; transform: translateY(-50%);
            background: #111a2e; border: 1px solid var(--line); color: #e2e8f0; font-size: .75rem; padding: .25rem .6rem;
            border-radius: .5rem; white-space: nowrap; opacity: 0; pointer-events: none; transition: opacity .15s; z-index: 60;
        }
        .rail-link:hover::after, .rail-link:focus-visible::after { opacity: 1; }
        .rail-link:focus-visible { outline: 2px solid var(--accent-soft); outline-offset: 2px; }

        .content { flex: 1; min-width: 0; }

        /* Header */
        .topbar {
            height: 72px; display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;
            padding: 0 clamp(1rem, 3vw, 2.5rem); border-bottom: 1px solid var(--line);
            background: rgba(6,10,22,.88); backdrop-filter: blur(8px); position: sticky; top: 0; z-index: 20;
        }
        .brand { display: flex; align-items: center; gap: .7rem; font-size: 1.7rem; font-weight: 700; letter-spacing: -.02em; }
        .brand .logo-sq { width: 46px; height: 46px; border-radius: 12px; background: linear-gradient(135deg, #5b5ff5, #7c3aed); display: grid; place-items: center; font-size: 1.1rem; color: #fff; }
        .brand b { color: var(--accent-soft); font-weight: 700; }
        .topnav { display: flex; gap: 2.5rem; margin-right: auto; margin-left: 1.5rem; }
        .topnav a { font-size: .95rem; opacity: .9; }
        .topnav a:hover { color: var(--accent-soft); }
        .topactions { display: flex; align-items: center; gap: .6rem; }
        .icon-btn { background: none; border: 0; color: var(--text); font-size: 1.1rem; padding: .6rem; border-radius: 50%; cursor: pointer; position: relative; }
        .icon-btn:hover { background: rgba(255,255,255,.06); }
        .icon-btn .dot { position: absolute; top: 8px; right: 8px; width: 8px; height: 8px; background: #d946ef; border-radius: 50%; }
        .mini-avatar { width: 38px; height: 38px; border-radius: 50%; display: grid; place-items: center; font-size: 1.3rem; border: 2px solid #3b3f9e; color: var(--accent-soft); }

        .page {
            max-width: 1500px; margin: 0 auto; padding: 1.6rem clamp(1rem, 3vw, 2.5rem) 3rem;
            display: grid; grid-template-columns: minmax(0, 1fr) 320px; gap: 1.5rem; align-items: start;
        }
        .card { background: linear-gradient(180deg, rgba(13,20,40,.9), rgba(9,14,29,.95)); border: 1px solid var(--line); border-radius: 18px; padding: 1.4rem 1.5rem; }
        .card h2 { margin: 0 0 1rem; font-size: 1.05rem; font-weight: 600; display: flex; align-items: center; gap: .8rem; }
        .card h2 i { color: var(--accent-soft); font-size: 1.25rem; }
        .card h2 a { margin-left: auto; font-size: .78rem; font-weight: 500; color: var(--accent-soft); }
        .stack { display: grid; gap: 1.25rem; }
        .muted { color: var(--muted); }
        .empty { color: var(--muted); font-size: .9rem; margin: 0; }

     
        .profile-head { padding: 0; overflow: hidden; }
        .banner { position: relative; height: 150px; }
        .banner svg { width: 100%; height: 100%; display: block; }
        .banner-text { position: absolute; right: 2.2rem; top: 1.1rem; font-family: 'Caveat', cursive; font-size: 1.45rem; line-height: 1.15; color: #e9e3ff; transform: rotate(-8deg); text-shadow: 0 2px 8px rgba(0,0,0,.45); }
        .head-body { position: relative; padding: 0 1.8rem 1.2rem 190px; min-height: 130px; }
        .avatar {
            position: absolute; left: 1.6rem; top: -78px; width: 150px; height: 150px; border-radius: 50%;
            display: grid; place-items: center; font-size: 3rem; font-weight: 700; color: #fff;
            border: 5px solid #5b5ff5; box-shadow: 0 0 0 5px #0a1124, 0 10px 30px rgba(91,95,245,.35);
        }
        .avatar .online { position: absolute; right: 8px; bottom: 10px; width: 24px; height: 24px; border-radius: 50%; background: var(--ok); border: 4px solid #0a1124; }
        .head-main { display: flex; justify-content: space-between; gap: 1rem; flex-wrap: wrap; padding-top: .9rem; }
        .head-main h1 { margin: 0; font-size: 1.7rem; font-weight: 700; letter-spacing: -.01em; }
        .handle { color: var(--muted); margin: .15rem 0 .6rem; }
        .headline { color: #c9d1ea; font-size: .92rem; line-height: 1.5; max-width: 34rem; margin: 0 0 .8rem; }
        .meta { display: flex; flex-wrap: wrap; gap: 1.6rem; font-size: .85rem; color: #c9d1ea; }
        .meta i { color: var(--muted); margin-right: .5rem; }
        .btn {
            display: inline-flex; align-items: center; gap: .6rem; height: 46px; padding: 0 1.4rem; border-radius: 11px;
            border: 1px solid #3b3f9e; background: #1b2150; color: #fff; font: inherit; font-weight: 500; cursor: pointer; transition: background .15s;
        }
        .btn:hover { background: var(--accent); }
        .btn.primary { background: var(--accent); border-color: var(--accent); box-shadow: 0 8px 22px rgba(91,95,245,.3); }
        .btn.primary:hover { background: #6b6ffa; }

        .tabs { display: flex; gap: 2.2rem; padding: 0 1.8rem; border-top: 1px solid var(--line); overflow-x: auto; }
        .tab { background: none; border: 0; color: var(--muted); font: inherit; font-size: .9rem; padding: 1.1rem .2rem; cursor: pointer; position: relative; white-space: nowrap; }
        .tab:hover { color: #fff; }
        .tab.on { color: #fff; font-weight: 500; }
        .tab.on::after { content: ""; position: absolute; left: -.4rem; right: -.4rem; bottom: 0; height: 3px; border-radius: 3px 3px 0 0; background: var(--accent); }

        .grid-main { display: grid; grid-template-columns: 1fr 1fr; gap: 1.25rem; align-items: start; margin-top: 1.25rem; }
        .grid-main.single { grid-template-columns: 1fr; }
        .about p { margin: 0; line-height: 1.65; color: #d4daee; font-size: .93rem; }

        .chips { display: flex; flex-wrap: wrap; gap: .6rem; }
        .chip { padding: .5rem 1.05rem; border-radius: 999px; border: 1px solid #2b3560; background: #0d1530; font-size: .82rem; color: #e1e6f7; }
        .chip.on { background: #28307c; border-color: #3b3f9e; color: #fff; }

        .edu-title { font-weight: 600; margin: 0 0 .25rem; }
        .edu-sub { color: var(--muted); font-size: .85rem; margin: 0 0 .7rem; }
        .edu-date { color: var(--muted); font-size: .8rem; }
        .edu-date i { margin-right: .5rem; }

        .project { display: flex; gap: 1rem; padding: .7rem; border: 1px solid var(--line); border-radius: 12px; background: rgba(8,13,28,.6); margin-bottom: .75rem; }
        .project:last-child { margin-bottom: 0; }
        .thumb { flex: none; width: 130px; height: 94px; border-radius: 9px; display: grid; place-items: center; font-size: 1.9rem; color: rgba(255,255,255,.92); }
        .project h3 { margin: .1rem 0 .3rem; font-size: .95rem; font-weight: 600; }
        .project p { margin: 0 0 .6rem; color: var(--muted); font-size: .82rem; line-height: 1.45; }
        .tags { display: flex; flex-wrap: wrap; gap: .4rem; }
        .tags span { font-size: .68rem; padding: .22rem .6rem; border-radius: 999px; background: #131b3f; border: 1px solid #232d5e; color: #cfd6f0; }

        .socials { display: flex; gap: .9rem; }
        .socials a { width: 42px; height: 42px; border-radius: 12px; background: #0d1530; display: grid; place-items: center; font-size: 1.35rem; transition: background .15s, transform .15s; }
        .socials a:hover { background: var(--accent); transform: translateY(-2px); }

       
        .side { display: grid; gap: 1.25rem; }
        .stat-grid { display: grid; grid-template-columns: 1fr 1fr; gap: .8rem; }
        .stat { border: 1px solid var(--line); border-radius: 12px; padding: .9rem 1rem; background: rgba(8,13,28,.6); }
        .stat b { display: block; font-size: 1.35rem; font-weight: 600; }
        .stat span { color: var(--muted); font-size: .82rem; }
        .verified { display: flex; align-items: center; gap: .6rem; color: #c9d1ea; font-size: .9rem; }
        .verified i { color: var(--ok); font-size: 1.1rem; }
        .quote { position: relative; overflow: hidden; }
        .quote q { display: block; quotes: none; font-size: 1.1rem; line-height: 1.45; padding-top: .3rem; max-width: 11rem; }
        .quote .mark { color: var(--accent); font-size: 1.4rem; display: block; }
        .quote svg { position: absolute; right: 1rem; bottom: 1.2rem; }
        .timeline { list-style: none; margin: 0; padding: 0; display: grid; gap: 1rem; }
        .timeline li { display: grid; grid-template-columns: 14px 1fr; gap: .8rem; align-items: start; font-size: .85rem; }
        .timeline .dot { width: 12px; height: 12px; border-radius: 50%; margin-top: .25rem; }
        .timeline small { display: block; color: var(--muted); font-size: .72rem; margin-top: .2rem; }

      
        .notfound { text-align: center; padding: 4rem 1.5rem; }
        .notfound i { font-size: 2.6rem; color: var(--accent-soft); margin-bottom: 1rem; }
        .notfound h1 { margin: 0 0 .5rem; }
        .notfound p { color: var(--muted); margin: 0 0 1.5rem; }

     
        @media (max-width: 1200px) { .page { grid-template-columns: 1fr; } .side { grid-template-columns: repeat(2, 1fr); } }
        @media (max-width: 900px) {
            .grid-main { grid-template-columns: 1fr; }
            .topnav { display: none; }
            .head-body { padding: 90px 1.2rem 1.2rem; }
            .avatar { left: 1.2rem; top: -60px; width: 120px; height: 120px; font-size: 2.4rem; }
            .banner-text { display: none; }
        }
        @media (max-width: 768px) { .rail { display: none; } }
        @media (max-width: 560px) { .side { grid-template-columns: 1fr; } .project { flex-direction: column; } .thumb { width: 100%; } }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<div class="shell">

    
    <aside class="rail" aria-label="Navegación lateral">
        <a href="index.php"       class="rail-link" data-label="Inicio"><i class="fa-solid fa-house"></i></a>
        <a href="#"               class="rail-link" data-label="Proyectos"><i class="fa-regular fa-file-lines"></i></a>
        <a href="#"               class="rail-link" data-label="Comunidad"><i class="fa-solid fa-user-group"></i></a>
        <a href="#"               class="rail-link" data-label="Mensajes"><i class="fa-regular fa-comments"></i></a>
        <a href="perfil.php<?php echo $id ? '?id=' . $id : ''; ?>" class="rail-link active" data-label="Perfil" aria-current="page"><i class="fa-solid fa-user-gear"></i></a>
        <a href="#"               class="rail-link" data-label="Calendario"><i class="fa-regular fa-calendar-days"></i></a>
        <a href="#"               class="rail-link" data-label="Estadísticas"><i class="fa-solid fa-chart-column"></i></a>
        <a href="#"               class="rail-link bottom" data-label="Configuración"><i class="fa-solid fa-gear"></i></a>
    </aside>

    <!-- <div class="content">
        <header class="topbar">
            <a href="index.php" class="brand" aria-label="DevWeb - Inicio">
                <span class="logo-sq"><i class="fa-solid fa-code"></i></span>
                <span>Dev<b>Web</b></span>
            </a>
            <nav class="topnav" aria-label="Principal">
                <a href="index.php">Inicio</a>
                <a href="portafolio.html">Portafolios</a>
                <a href="registro.php">Soy Programador</a>
            </nav>
            <div class="topactions">
                <button class="icon-btn" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
                <button class="icon-btn" aria-label="Notificaciones"><i class="fa-regular fa-bell"></i><span class="dot"></span></button>
                <a href="perfil.php" class="mini-avatar" aria-label="Mi perfil"><i class="fa-regular fa-circle-user"></i></a>
            </div>
        </header> -->

<?php if (!$perfil): ?>
        <main class="page" style="grid-template-columns:1fr">
            <section class="card notfound">
                <i class="fa-regular fa-face-frown"></i>
                <h1>Perfil no encontrado</h1>
                <p>El programador que buscas no existe o aún no ha completado su perfil.</p>
                <a href="index.php" class="btn primary"><i class="fa-solid fa-house"></i> Volver al inicio</a>
            </section>
        </main>
<?php else: ?>
        <main class="page">

            <div>
                <!-- Cabecera -->
                <section class="card profile-head">
                    <div class="banner" aria-hidden="true">
                        <svg viewBox="0 0 900 160" preserveAspectRatio="xMidYMid slice" xmlns="http://www.w3.org/2000/svg">
                            <defs>
                                <linearGradient id="sky" x1="0" y1="0" x2="0" y2="1">
                                    <stop offset="0" stop-color="#14123a"/>
                                    <stop offset=".55" stop-color="#5a3a8c"/>
                                    <stop offset="1" stop-color="#e0793a"/>
                                </linearGradient>
                            </defs>
                            <rect width="900" height="160" fill="url(#sky)"/>
                            <?php echo $estrellas; ?>
                            <path d="M0 120 L120 82 L240 116 L380 72 L520 112 L660 86 L780 112 L900 90 V160 H0Z" fill="#241a4d" opacity=".95"/>
                            <?php echo $edificios; ?>
                        </svg>
                        <div class="banner-text">Ideas<br>Código<br>Impacto</div>
                    </div>

                    <div class="head-body">
                        <div class="avatar" style="background:linear-gradient(135deg,<?php echo $grad[0]; ?>,<?php echo $grad[1]; ?>)" aria-label="Avatar de <?php echo h($perfil['nombre']); ?>">
                            <?php echo h($iniciales); ?>
                            <span class="online" title="Disponible"></span>
                        </div>

                        <div class="head-main">
                            <div>
                                <h1><?php echo h($perfil['nombre']); ?></h1>
                                <p class="handle">@<?php echo h($perfil['handle']); ?></p>
                                <p class="headline"><?php echo h($perfil['rol']); ?></p>
                                <div class="meta">
                                    <?php if (!empty($perfil['ubicacion'])): ?>
                                        <span><i class="fa-solid fa-location-dot"></i><?php echo h($perfil['ubicacion']); ?></span>
                                    <?php endif; ?>
                                    <?php if (!empty($perfil['desde'])): ?>
                                        <span><i class="fa-regular fa-calendar"></i>Se unió en <?php echo h($perfil['desde']); ?></span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <div>
                                <?php if ($esDueno): ?>
                                    <a href="editar_perfil.php" class="btn"><i class="fa-solid fa-pen"></i> Editar perfil</a>
                                <?php else: ?>
                                    <a href="contacto.php?dev=<?php echo (int)$id; ?>" class="btn primary"><i class="fa-regular fa-paper-plane"></i> Contactar</a>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <div class="tabs" role="tablist" aria-label="Secciones del perfil">
                        <button class="tab on" role="tab" data-tab="info" aria-selected="true">Información</button>
                        <button class="tab" role="tab" data-tab="portafolios" aria-selected="false">Portafolios</button>
                        <button class="tab" role="tab" data-tab="habilidades" aria-selected="false">Habilidades</button>
                        <button class="tab" role="tab" data-tab="actividad" aria-selected="false">Actividad</button>
                    </div>
                </section>

                <div class="grid-main" id="gridMain">
                    <div class="stack">
                        <section class="card about" data-show="info">
                            <h2><i class="fa-regular fa-user"></i> Sobre mí</h2>
                            <?php if (!empty($perfil['bio'])): ?>
                                <p><?php echo nl2br(h($perfil['bio'])); ?></p>
                            <?php else: ?>
                                <p class="empty">Este programador aún no ha agregado una descripción.</p>
                            <?php endif; ?>
                        </section>

                        <section class="card" data-show="info habilidades">
                            <h2><i class="fa-solid fa-gem"></i> Habilidades</h2>
                            <?php if ($perfil['skills']): ?>
                                <div class="chips">
                                    <?php foreach ($perfil['skills'] as $i => $sk): ?>
                                        <span class="chip <?php echo $i === 0 ? 'on' : ''; ?>"><?php echo h($sk); ?></span>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <p class="empty">Aún no hay habilidades registradas.</p>
                            <?php endif; ?>
                        </section>

                        <section class="card" data-show="info">
                            <h2><i class="fa-solid fa-graduation-cap"></i> Educación</h2>
                            <?php if ($perfil['edu']): ?>
                                <?php foreach ($perfil['edu'] as $ed): ?>
                                    <p class="edu-title"><?php echo h($ed[0]); ?></p>
                                    <?php if ($ed[1] !== ''): ?><p class="edu-sub"><?php echo h($ed[1]); ?></p><?php endif; ?>
                                    <?php if ($ed[2] !== ''): ?><div class="edu-date"><i class="fa-regular fa-calendar"></i><?php echo h($ed[2]); ?></div><?php endif; ?>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty">Sin información de educación.</p>
                            <?php endif; ?>
                        </section>

                        <section class="card" data-show="actividad" hidden>
                            <h2><i class="fa-regular fa-clock"></i> Actividad reciente</h2>
                            <ul class="timeline">
                                <?php foreach ($actividad as $a): ?>
                                    <li><span class="dot" style="background:<?php echo h($a[2]); ?>"></span><div><?php echo h($a[0]); ?><?php if ($a[1]): ?><small><?php echo h($a[1]); ?></small><?php endif; ?></div></li>
                                <?php endforeach; ?>
                            </ul>
                        </section>
                    </div>

                    <div class="stack">
                        <section class="card" data-show="info portafolios">
                            <h2><i class="fa-solid fa-briefcase"></i> Proyectos destacados
                                <a href="portafolio.html?id=<?php echo (int)$id; ?>">Ver todos <i class="fa-solid fa-arrow-right" style="font-size:.7rem;color:inherit"></i></a>
                            </h2>
                            <?php if ($perfil['proyectos']): ?>
                                <?php foreach ($perfil['proyectos'] as $pr): ?>
                                    <article class="project">
                                        <div class="thumb" style="background:linear-gradient(135deg,<?php echo h($pr[4][0]); ?>,<?php echo h($pr[4][1]); ?>)"><i class="fa-solid <?php echo h($pr[3]); ?>"></i></div>
                                        <div>
                                            <h3><?php echo h($pr[0]); ?></h3>
                                            <p><?php echo h($pr[1]); ?></p>
                                            <div class="tags"><?php foreach ($pr[2] as $t): ?><span><?php echo h($t); ?></span><?php endforeach; ?></div>
                                        </div>
                                    </article>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <p class="empty">Todavía no hay proyectos publicados.</p>
                            <?php endif; ?>
                        </section>

                        <section class="card" data-show="info">
                            <h2><i class="fa-solid fa-user-group"></i> Redes y contacto</h2>
                            <div class="socials">
                                <?php if ($fuente === 'demo'): ?>
                                    <a href="#" aria-label="GitHub"><i class="fa-brands fa-github"></i></a>
                                    <a href="#" aria-label="LinkedIn"><i class="fa-brands fa-linkedin"></i></a>
                                    <a href="#" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                                <?php elseif (!empty($perfil['portafolio'])): ?>
                                    <a href="<?php echo h($perfil['portafolio']); ?>" target="_blank" rel="noopener noreferrer" aria-label="Portafolio o enlace personal"><i class="fa-solid fa-link"></i></a>
                                <?php endif; ?>
                                <a href="contacto.php?dev=<?php echo (int)$id; ?>" aria-label="Enviar propuesta"><i class="fa-regular fa-envelope"></i></a>
                            </div>
                        </section>
                    </div>
                </div>
            </div>

            <aside class="side">
                <section class="card">
                    <h2><i class="fa-solid fa-chart-simple"></i> Estadísticas</h2>
                    <div class="stat-grid">
                        <div class="stat"><b><?php echo (int)$perfil['stats'][0]; ?></b><span>Portafolios</span></div>
                        <div class="stat"><b><?php echo (int)$perfil['stats'][1]; ?></b><span>Proyectos</span></div>
                        <div class="stat"><b><?php echo (int)$perfil['stats'][2]; ?></b><span>Seguidores</span></div>
                        <div class="stat"><b><?php echo (int)$perfil['stats'][3]; ?></b><span>Siguiendo</span></div>
                    </div>
                </section>

                <section class="card">
                    <h2><i class="fa-solid fa-shield-halved"></i> Perfil verificado</h2>
                    <div class="verified"><i class="fa-solid fa-circle-check"></i> <?php echo $esDueno ? 'Tu perfil está verificado' : 'Este perfil está verificado'; ?></div>
                </section>

                <section class="card quote">
                    <span class="mark"><i class="fa-solid fa-quote-left"></i></span>
                    <q>La constancia también es talento</q>
                    <svg width="90" height="50" viewBox="0 0 90 50" fill="none" aria-hidden="true">
                        <path d="M2 44 Q30 40 50 26 T84 8" stroke="#5b5ff5" stroke-width="1.5" stroke-linecap="round"/>
                        <path d="M78 2 l2 6 6 2 -6 2 -2 6 -2 -6 -6 -2 6 -2z" fill="none" stroke="#8b90ff" stroke-width="1.2"/>
                    </svg>
                </section>

                <section class="card">
                    <h2><i class="fa-regular fa-clock"></i> Última actividad</h2>
                    <ul class="timeline">
                        <?php foreach ($actividad as $a): ?>
                            <li><span class="dot" style="background:<?php echo h($a[2]); ?>"></span><div><?php echo h($a[0]); ?><?php if ($a[1]): ?><small><?php echo h($a[1]); ?></small><?php endif; ?></div></li>
                        <?php endforeach; ?>
                    </ul>
                </section>
            </aside>
        </main>

        <script>
            (function () {
                const tabs = document.querySelectorAll('.tab');
                const grid = document.getElementById('gridMain');
                const sections = grid.querySelectorAll('[data-show]');

                function setTab(name) {
                    tabs.forEach(t => {
                        const on = t.dataset.tab === name;
                        t.classList.toggle('on', on);
                        t.setAttribute('aria-selected', on);
                    });
                    sections.forEach(s => { s.hidden = !s.dataset.show.split(' ').includes(name); });
                    grid.classList.toggle('single', name !== 'info');
                }
                tabs.forEach(t => t.addEventListener('click', () => setTab(t.dataset.tab)));
                setTab('info');
            })();
        </script>
<?php endif; ?>
    </div>
</div>
</body>
</html>