<?php
require 'include/funciones.php';
session_start();
require_once('db.php');

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$especialidades = [
    'frontend'  => 'Frontend',
    'backend'   => 'Backend',
    'fullstack' => 'Full Stack',
    'movil'     => 'Desarrollo móvil',
    'uiux'      => 'UI/UX y diseño web',
    'datos'     => 'Datos y automatización',
    'qa'        => 'QA y optimización',
];
$niveles = [
    'junior'     => 'Junior (0 – 2 años)',
    'semisenior' => 'Semi Senior (2 – 5 años)',
    'senior'     => 'Senior (+5 años)',
];
$habilidades_validas = ['JavaScript', 'Python', 'React', 'Node.js', 'HTML', 'CSS', 'PHP', 'Otra'];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}

$errores = [];
$old = [
    'tipo'         => (($_GET['tipo'] ?? '') === 'cliente') ? 'cliente' : 'programador',
    'nombre'       => '',
    'email'        => '',
    'especialidad' => '',
    'nivel'        => '',
    'portafolio'   => '',
    'habilidades'  => [],
    'terminos'     => false,
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
    }

    $old['tipo']         = (($_POST['tipo'] ?? '') === 'cliente') ? 'cliente' : 'programador';
    $old['nombre']       = trim($_POST['nombre'] ?? '');
    $old['email']        = trim($_POST['email'] ?? '');
    $old['especialidad'] = $_POST['especialidad'] ?? '';
    $old['nivel']        = $_POST['nivel'] ?? '';
    $old['portafolio']   = trim($_POST['portafolio'] ?? '');
    $old['habilidades']  = array_values(array_intersect($habilidades_validas, (array)($_POST['habilidades'] ?? [])));
    $old['terminos']     = isset($_POST['terminos']);
    $password            = $_POST['password'] ?? '';
    $esProgramador       = ($old['tipo'] === 'programador');

    if ($old['nombre'] === '' || mb_strlen($old['nombre']) < 3) {
        $errores[] = 'Escribe tu nombre completo (mínimo 3 caracteres).';
    }
    if (!filter_var($old['email'], FILTER_VALIDATE_EMAIL)) {
        $errores[] = 'Ingresa un correo electrónico válido.';
    }
    if (strlen($password) < 8) {
        $errores[] = 'La contraseña debe tener al menos 8 caracteres.';
    }
    if ($esProgramador) {
        if (!isset($especialidades[$old['especialidad']])) {
            $errores[] = 'Selecciona tu especialidad principal.';
        }
        if (!isset($niveles[$old['nivel']])) {
            $errores[] = 'Selecciona tu nivel de experiencia.';
        }
        if ($old['portafolio'] !== '') {
            if (!preg_match('#^https?://#i', $old['portafolio'])) {
                $old['portafolio'] = 'https://' . $old['portafolio'];
            }
            if (!filter_var($old['portafolio'], FILTER_VALIDATE_URL)) {
                $errores[] = 'El enlace del portafolio no es válido.';
            }
        }
    }
    if (!$old['terminos']) {
        $errores[] = 'Debes aceptar los Términos y Condiciones y la Política de Privacidad.';
    }

    if (!$errores) {
        $conexion = $pdo ?? null;

        if (!($conexion instanceof PDO)) {
            $errores[] = 'No hay conexión con la base de datos. Revisa db.php.';
        } else {
            try {
                $stmt = $conexion->prepare('SELECT id FROM usuarios WHERE email = ?');
                $stmt->execute([$old['email']]);

                if ($stmt->fetch()) {
                    $errores[] = 'Ya existe una cuenta con ese correo. Inicia sesión o usa otro correo.';
                } else {
                    $stmt = $conexion->prepare(
                        'INSERT INTO usuarios (tipo, nombre, email, password_hash, especialidad, nivel, portafolio, habilidades)
                         VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
                    );
                    $stmt->execute([
                        $old['tipo'],
                        $old['nombre'],
                        $old['email'],
                        password_hash($password, PASSWORD_DEFAULT),
                        $esProgramador ? $old['especialidad'] : null,
                        $esProgramador ? $old['nivel'] : null,
                        $esProgramador && $old['portafolio'] !== '' ? $old['portafolio'] : null,
                        $esProgramador ? implode(',', $old['habilidades']) : null,
                    ]);

                    $nuevoId = (int)$conexion->lastInsertId();

                    session_regenerate_id(true);
                    $_SESSION['user_id']   = $nuevoId;
                    $_SESSION['user_tipo'] = $old['tipo'];
                    $_SESSION['csrf']      = bin2hex(random_bytes(32));

                    header('Location: ' . ($esProgramador ? 'perfil.php?id=' . $nuevoId : 'index.php'));
                    exit;
                }
            } catch (PDOException $e) {
                error_log('registro.php: ' . $e->getMessage());
                $errores[] = 'No pudimos crear tu cuenta en este momento. Inténtalo de nuevo en unos minutos.';
            }
        }
    }
}

$esCliente = ($old['tipo'] === 'cliente');
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DevSite - Crear cuenta</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <script src="https://accounts.google.com/gsi/client" async defer></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Caveat:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg: #060a16;
            --panel: #0a1020;
            --line: #18223a;
            --field: #0b1226;
            --accent: #5b5ff5;
            --accent-soft: #7c83ff;
            --text: #e5e9f5;
            --muted: #8a96b3;
            --danger: #f87171;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif;
            background:
                radial-gradient(circle at 0% 20%, rgba(91,95,245,.14), transparent 40%),
                radial-gradient(circle at 100% 100%, rgba(91,95,245,.08), transparent 40%),
                var(--bg);
            color: var(--text);
            min-height: 100vh;
        }
        a { color: inherit; }

        .rg-nav {
            display: flex; align-items: center; justify-content: space-between; gap: 1.5rem;
            height: 86px; padding: 0 clamp(1rem, 4vw, 3.5rem);
            border-bottom: 1px solid var(--line);
            background: rgba(6,10,22,.85); backdrop-filter: blur(8px);
            position: sticky; top: 0; z-index: 20;
        }
        .rg-brand { display: flex; align-items: center; gap: .6rem; text-decoration: none; font-size: 1.9rem; font-weight: 700; letter-spacing: -.02em; }
        .rg-brand .code { color: var(--accent); }
        .rg-brand span span { color: var(--accent-soft); }
        .rg-links { display: flex; gap: 2.5rem; margin-right: auto; margin-left: 2rem; }
        .rg-links a { text-decoration: none; color: var(--text); font-size: .95rem; opacity: .9; }
        .rg-links a:hover { color: var(--accent-soft); }
        .rg-login { display: flex; align-items: center; gap: 1rem; font-size: .85rem; color: var(--muted); }
        .rg-login a {
            text-decoration: none; color: var(--accent-soft); font-weight: 500;
            border: 1.5px solid var(--accent); border-radius: 10px; padding: .6rem 1.1rem; transition: background .15s, color .15s;
        }
        .rg-login a:hover { background: var(--accent); color: #fff; }

        .rg-page {
            max-width: 1320px; margin: 0 auto;
            padding: 2rem clamp(1rem, 4vw, 3rem) 3rem;
            display: grid; grid-template-columns: minmax(0, 1fr) minmax(0, 1.2fr); gap: 3rem; align-items: start;
        }

        .rg-hero h1 { font-size: clamp(2.2rem, 4vw, 3.1rem); line-height: 1.12; margin: 2.2rem 0 1.2rem; font-weight: 800; letter-spacing: -.02em; }
        .rg-hero h1 span { color: var(--accent); display: block; }
        .rg-hero .lead { color: #b9c2dc; font-size: 1.1rem; line-height: 1.55; max-width: 26rem; margin: 0 0 1.5rem; }
        .rg-logo-wrap { position: relative; display: flex; justify-content: center; align-items: center; max-width: 460px; margin: 0 0 2rem; }
        .rg-logo-wrap::before {
            content: ""; position: absolute; width: 75%; aspect-ratio: 1; border-radius: 50%;
            background: radial-gradient(circle, rgba(2,169,255,.35), transparent 70%); filter: blur(32px);
        }
        .rg-logo {
            position: relative; width: min(300px, 75%); height: auto; border-radius: 50%;
            box-shadow: 0 0 0 1px rgba(255,255,255,.08), 0 0 60px rgba(2,169,255,.3);
        }

        .rg-features { display: grid; gap: 1.4rem; }
        .rg-feature { display: flex; gap: 1rem; align-items: flex-start; }
        .rg-feature .ico {
            flex: 0 0 56px; height: 56px; border-radius: 50%;
            background: #131a38; display: flex; align-items: center; justify-content: center;
            color: var(--accent-soft); font-size: 1.25rem;
        }
        .rg-feature h3 { margin: .1rem 0 .3rem; font-size: 1.02rem; font-weight: 500; }
        .rg-feature p { margin: 0; color: var(--muted); font-size: .88rem; line-height: 1.6; max-width: 22rem; }
        .rg-tagline { font-family: 'Caveat', cursive; font-size: 1.5rem; color: var(--accent); margin: 2.2rem 0 0 4rem; transform: rotate(-3deg); width: fit-content; }
        .rg-tagline svg { display: block; margin-top: -.1rem; }

        .rg-card {
            background: linear-gradient(180deg, rgba(14,20,40,.9), rgba(9,14,29,.95));
            border: 1px solid var(--line); border-radius: 22px;
            padding: clamp(1.4rem, 3vw, 2.6rem);
        }
        .rg-card h2 { margin: 0 0 .4rem; font-size: clamp(1.6rem, 2.6vw, 2rem); font-weight: 700; }
        .rg-card h2 span { color: var(--accent-soft); }
        .rg-sub { color: #b9c2dc; margin: 0 0 1.6rem; }

        .alert {
            border: 1px solid rgba(248,113,113,.4); background: rgba(248,113,113,.08);
            color: #fecaca; border-radius: 12px; padding: .9rem 1.1rem; margin-bottom: 1.2rem; font-size: .88rem;
        }
        .alert ul { margin: .3rem 0 0; padding-left: 1.1rem; }

        .rg-tabs { display: grid; grid-template-columns: 1fr 1fr; margin-bottom: 1.4rem; border: 1px solid var(--line); border-radius: 12px; overflow: hidden; }
        .rg-tab { position: relative; cursor: pointer; }
        .rg-tab input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
        .rg-tab span { display: flex; align-items: center; justify-content: center; gap: .6rem; padding: 1rem; font-weight: 500; transition: background .15s; }
        .rg-tab input:checked + span { background: var(--accent); color: #fff; }
        .rg-tab input:focus-visible + span { outline: 2px solid #c7d2fe; outline-offset: -3px; }

        .field { margin-bottom: 1.15rem; }
        .field > label, .group-label { display: flex; align-items: center; gap: .7rem; font-size: .88rem; margin-bottom: .5rem; color: #dbe1f2; }
        .field > label i { color: var(--muted); width: 1.1rem; text-align: center; }
        .req { color: var(--text); }
        .control { position: relative; }
        .control > i.lead-ico { position: absolute; left: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); pointer-events: none; }
        .control input, .control select {
            width: 100%; height: 48px; background: var(--field); border: 1px solid var(--line); border-radius: 10px;
            color: var(--text); font: inherit; font-size: .9rem; padding: 0 1rem 0 2.9rem; transition: border-color .15s, box-shadow .15s;
        }
        .control select { appearance: none; -webkit-appearance: none; padding-right: 2.6rem; cursor: pointer; }
        .control select:invalid { color: #6b7694; }
        .control select option { color: var(--text); background: #0b1226; }
        .control input::placeholder { color: #5f6b88; }
        .control input:focus, .control select:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,95,245,.25); }
        .control .chev { position: absolute; right: 1rem; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: .8rem; pointer-events: none; }
        .toggle-pass { position: absolute; right: .5rem; top: 50%; transform: translateY(-50%); background: none; border: 0; color: var(--muted); padding: .6rem; cursor: pointer; border-radius: 8px; }
        .toggle-pass:hover { color: #fff; }
        .toggle-pass:focus-visible { outline: 2px solid var(--accent-soft); }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.2rem; }

        .pills { display: flex; flex-wrap: wrap; gap: .75rem; margin-bottom: 1.3rem; }
        .pill { position: relative; cursor: pointer; }
        .pill input { position: absolute; opacity: 0; inset: 0; cursor: pointer; }
        .pill span {
            display: inline-flex; align-items: center; gap: .6rem; padding: .6rem 1.1rem; border: 1px solid var(--line);
            border-radius: 999px; background: var(--field); font-size: .85rem; transition: all .15s;
        }
        .pill span::before { content: ""; width: 15px; height: 15px; border-radius: 50%; border: 1.5px solid var(--muted); transition: all .15s; }
        .pill:hover span { border-color: var(--accent); }
        .pill input:checked + span { border-color: var(--accent); background: rgba(91,95,245,.15); color: #fff; }
        .pill input:checked + span::before { background: var(--accent); border-color: var(--accent); box-shadow: inset 0 0 0 3px #131a38; }
        .pill input:focus-visible + span { outline: 2px solid var(--accent-soft); outline-offset: 2px; }

        .terms { display: flex; align-items: center; gap: .7rem; font-size: .85rem; margin-bottom: 1.2rem; color: #dbe1f2; }
        .terms input { width: 20px; height: 20px; accent-color: var(--accent); flex: none; cursor: pointer; }
        .terms a { color: var(--accent-soft); }
        .btn-submit {
            width: 100%; height: 50px; border: 0; border-radius: 10px; background: var(--accent); color: #fff;
            font: inherit; font-weight: 600; display: flex; align-items: center; justify-content: center; gap: .7rem; cursor: pointer;
            transition: background .15s, transform .1s; box-shadow: 0 10px 28px rgba(91,95,245,.3);
        }
        .btn-submit:hover { background: #6b6ffa; }
        .btn-submit:active { transform: scale(.99); }
        .divider { display: flex; align-items: center; gap: 1rem; margin: 1.4rem 0 1rem; color: var(--muted); font-size: .8rem; }
        .divider::before, .divider::after { content: ""; flex: 1; height: 1px; background: var(--line); }
        .social { display: flex; justify-content: center; gap: 1rem; }
        .social button {
            width: 46px; height: 46px; border-radius: 50%; border: 1px solid var(--line); background: #0d1428; color: #fff;
            font-size: 1.2rem; cursor: pointer; transition: border-color .15s, transform .15s;
        }
        .social button:hover { border-color: var(--accent); transform: translateY(-2px); }
        .social .g { color: #ea4335; } .social .li { color: #38a8ff; }

        .only-dev[hidden] { display: none !important; }

        @media (max-width: 980px) {
            .rg-page { grid-template-columns: 1fr; }
            .rg-links { display: none; }
            .rg-hero h1 { margin-top: .5rem; }
        }
        @media (max-width: 560px) {
            .row-2 { grid-template-columns: 1fr; }
            .rg-login span { display: none; }
            .rg-brand { font-size: 1.5rem; }
        }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>

    <header class="rg-nav">
        <a href="index.php" class="brand-logo">
        <img src="imagenes/logo-devsite2.png" alt="Logo" style="width: 50px; height: auto;"> Dev<span>Site</span>
    </a>
        </a>
        <nav class="rg-links" aria-label="Principal">
            <a href="index.php" class="active">Inicio</a>
            <a href="portafolio.html">Portafolios</a>
            <a href="perfil.php">Ver Perfil</a>
            <a href="registro.php" class="nav-link">Soy Programador</a>
            <a href="pagos.php" class="nav-link">Pagos</a>
            <a href="soporte.php" class="nav-link">Soporte</a>
        </nav>
        <div class="rg-login">
            <span>¿Ya tienes una cuenta?</span>
            <a href="login.php">Iniciar sesión</a>
        </div>
    </header>

    <div class="rg-page">

        <section class="rg-hero">
            <h1>Forma parte de <span>DevSite</span></h1>
            <p class="lead">Conecta tus habilidades de programación con proyectos reales y colabora con emprendedores de todo el mundo.</p>

            <div class="rg-logo-wrap">
                <img class="rg-logo" src="imagenes/logo-devsite2.png" alt="Logo" width="300" height="300">
            </div>

            <div class="rg-features">
                <div class="rg-feature">
                    <div class="ico"><i class="fa-solid fa-bolt"></i></div>
                    <div>
                        <h3>Encuentra proyectos que se ajusten a ti</h3>
                        <p>Elige entre cientos de oportunidades en diferentes áreas de desarrollo.</p>
                    </div>
                </div>
                <div class="rg-feature">
                    <div class="ico"><i class="fa-solid fa-shield-halved"></i></div>
                    <div>
                        <h3>Trabaja con seguridad</h3>
                        <p>Contamos con un sistema de pagos y valoraciones para que ambas partes estén protegidas.</p>
                    </div>
                </div>
                <div class="rg-feature">
                    <div class="ico"><i class="fa-solid fa-star"></i></div>
                    <div>
                        <h3>Muestra tu talento</h3>
                        <p>Crea un portafolio, destaca tus habilidades y consigue más clientes.</p>
                    </div>
                </div>
            </div>

            <div class="rg-tagline">
                Tu talento también construye ideas
                <svg width="90" height="10" viewBox="0 0 90 10" fill="none" aria-hidden="true"><path d="M2 7 Q45 -2 88 5" stroke="#5b5ff5" stroke-width="2" stroke-linecap="round"/></svg>
            </div>
        </section>

        <section class="rg-card">
            <h2 id="formTitle"><?php echo $esCliente ? 'Crear cuenta de <span>Cliente</span>' : 'Crear cuenta de <span>Programador</span>'; ?></h2>
            <p class="rg-sub" id="formSub">
                <?php echo $esCliente ? 'Completa la información para empezar a contratar programadores.' : 'Completa la información para empezar a recibir proyectos.'; ?>
            </p>

            <?php if ($errores): ?>
                <div class="alert" role="alert">
                    <strong>Revisa lo siguiente:</strong>
                    <ul>
                        <?php foreach ($errores as $e): ?>
                            <li><?php echo h($e); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form id="registroForm" action="registro.php" method="POST" novalidate>
                <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">

                <div class="rg-tabs" role="radiogroup" aria-label="Tipo de cuenta">
                    <label class="rg-tab">
                        <input type="radio" name="tipo" value="cliente" <?php echo $esCliente ? 'checked' : ''; ?>>
                        <span><i class="fa-regular fa-user"></i> Cliente</span>
                    </label>
                    <label class="rg-tab">
                        <input type="radio" name="tipo" value="programador" <?php echo !$esCliente ? 'checked' : ''; ?>>
                        <span><i class="fa-solid fa-code"></i> Programador</span>
                    </label>
                </div>

                <div class="field">
                    <label for="nombre"><i class="fa-regular fa-user"></i> Nombre completo <span class="req">*</span></label>
                    <div class="control">
                        <i class="fa-regular fa-user lead-ico"></i>
                        <input type="text" id="nombre" name="nombre" placeholder="Ej. Juan Pérez" autocomplete="name" maxlength="120" required value="<?php echo h($old['nombre']); ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="email"><i class="fa-regular fa-envelope"></i> Correo electrónico <span class="req">*</span></label>
                    <div class="control">
                        <i class="fa-regular fa-envelope lead-ico"></i>
                        <input type="email" id="email" name="email" placeholder="tu@correo.com" autocomplete="email" maxlength="190" required value="<?php echo h($old['email']); ?>">
                    </div>
                </div>

                <div class="field">
                    <label for="password"><i class="fa-solid fa-lock"></i> Contraseña <span class="req">*</span></label>
                    <div class="control">
                        <i class="fa-solid fa-lock lead-ico"></i>
                        <input type="password" id="password" name="password" placeholder="Mínimo 8 caracteres" autocomplete="new-password" minlength="8" required>
                        <button type="button" class="toggle-pass" id="togglePass" aria-label="Mostrar contraseña">
                            <i class="fa-regular fa-eye-slash"></i>
                        </button>
                    </div>
                </div>

                <div class="only-dev" id="devFields" <?php echo $esCliente ? 'hidden' : ''; ?>>
                    <div class="row-2">
                        <div class="field">
                            <label for="especialidad"><i class="fa-solid fa-code"></i> Especialidad principal <span class="req">*</span></label>
                            <div class="control">
                                <i class="fa-solid fa-code lead-ico"></i>
                                <select id="especialidad" name="especialidad" <?php echo $esCliente ? '' : 'required'; ?>>
                                    <option value="" disabled <?php echo $old['especialidad'] === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                                    <?php foreach ($especialidades as $val => $label): ?>
                                        <option value="<?php echo h($val); ?>" <?php echo $old['especialidad'] === $val ? 'selected' : ''; ?>><?php echo h($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down chev"></i>
                            </div>
                        </div>
                        <div class="field">
                            <label for="nivel"><i class="fa-solid fa-chart-simple"></i> Nivel de experiencia <span class="req">*</span></label>
                            <div class="control">
                                <i class="fa-solid fa-code lead-ico"></i>
                                <select id="nivel" name="nivel" <?php echo $esCliente ? '' : 'required'; ?>>
                                    <option value="" disabled <?php echo $old['nivel'] === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                                    <?php foreach ($niveles as $val => $label): ?>
                                        <option value="<?php echo h($val); ?>" <?php echo $old['nivel'] === $val ? 'selected' : ''; ?>><?php echo h($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                                <i class="fa-solid fa-chevron-down chev"></i>
                            </div>
                        </div>
                    </div>

                    <div class="field">
                        <label for="portafolio"><i class="fa-solid fa-link"></i> Portafolio o enlaces (opcional)</label>
                        <div class="control">
                            <i class="fa-solid fa-link lead-ico"></i>
                            <input type="text" id="portafolio" name="portafolio" placeholder="GitHub, Behance, sitio web, etc." maxlength="255" value="<?php echo h($old['portafolio']); ?>">
                        </div>
                    </div>

                    <div class="group-label" id="habLabel">Habilidades <span style="color:var(--muted)">(puedes seleccionar varias)</span></div>
                    <div class="pills" role="group" aria-labelledby="habLabel">
                        <?php foreach ($habilidades_validas as $hab): ?>
                            <label class="pill">
                                <input type="checkbox" name="habilidades[]" value="<?php echo h($hab); ?>" <?php echo in_array($hab, $old['habilidades'], true) ? 'checked' : ''; ?>>
                                <span><?php echo h($hab); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                </div>

                <label class="terms">
                    <input type="checkbox" name="terminos" required <?php echo $old['terminos'] ? 'checked' : ''; ?>>
                    <span>Acepto los <a href="#">Términos y Condiciones</a> y la <a href="#">Política de Privacidad</a> de DevSite.</span>
                </label>

                <button type="submit" class="btn-submit">
                    <i class="fa-solid fa-user-plus"></i> Crear cuenta
                </button>

                <div class="divider">o regístrate con</div>
                <div class="social">
                    <button type="button" class="g" id="googleLoginBtn" aria-label="Registrarse con Google">
                    <i class="fa-brands fa-google"></i>
                    </button>
                    <button type="button" aria-label="Registrarse con GitHub"><i class="fa-brands fa-github"></i></button>
                    <button type="button" class="li" aria-label="Registrarse con LinkedIn"><i class="fa-brands fa-linkedin"></i></button>
                </div>
            </form>
        </section>
    </div>

    <script>
        (function () {
            const radios   = document.querySelectorAll('input[name="tipo"]');
            const devBox   = document.getElementById('devFields');
            const devReq   = [document.getElementById('especialidad'), document.getElementById('nivel')];
            const title    = document.getElementById('formTitle');
            const sub      = document.getElementById('formSub');

            function applyTipo() {
                const esCliente = document.querySelector('input[name="tipo"]:checked').value === 'cliente';
                devBox.hidden = esCliente;
                devReq.forEach(el => el.required = !esCliente);
                title.innerHTML = esCliente ? 'Crear cuenta de <span>Cliente</span>' : 'Crear cuenta de <span>Programador</span>';
                sub.textContent = esCliente
                    ? 'Completa la información para empezar a contratar programadores.'
                    : 'Completa la información para empezar a recibir proyectos.';
            }
            radios.forEach(r => r.addEventListener('change', applyTipo));

            const pass = document.getElementById('password');
            const btn  = document.getElementById('togglePass');
            btn.addEventListener('click', function () {
                const show = pass.type === 'password';
                pass.type = show ? 'text' : 'password';
                btn.setAttribute('aria-label', show ? 'Ocultar contraseña' : 'Mostrar contraseña');
                btn.firstElementChild.className = show ? 'fa-regular fa-eye' : 'fa-regular fa-eye-slash';
            });

            const form = document.getElementById('registroForm');
            form.addEventListener('submit', function (e) {
                if (!form.checkValidity()) { e.preventDefault(); form.reportValidity(); }
            });

            applyTipo();
        })();
        // inyeccion de script para el funcionamiento de OAuthClient de google






    document.getElementById('googleLoginBtn').addEventListener('click', function() {
    // Inicializar Google Identity Services
    google.accounts.id.initialize({
        client_id: '863501876791-soh36hdev6afbbgib95ditaiiimhutno.apps.googleusercontent.com',
        callback: handleGoogleResponse
    });
    // Mostrar el prompt o disparar el flujo
    google.accounts.id.prompt(); 
});

function handleGoogleResponse(response) {
    // response.credential contiene el ID Token JWT generado por Google
    const idToken = response.credential;

    // Enviar este token por POST a un archivo backend (ej. login-google.php) para validarlo
    fetch('login-google.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ token: idToken })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = 'index.php'; // Redirigir al perfil o index
        } else {
            alert('Error al autenticar con Google: ' + data.message);
        }
    })
    .catch(err => console.error('Error:', err));
}
    </script>
</body>
</html>