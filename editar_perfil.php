<?php


session_start();
require_once('db.php');

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

$especialidades = [
    'frontend' => 'Frontend', 'backend' => 'Backend', 'fullstack' => 'Full Stack', 'movil' => 'Desarrollo móvil',
    'uiux' => 'UI/UX y diseño web', 'datos' => 'Datos y automatización', 'qa' => 'QA y optimización',
];
$niveles = ['junior' => 'Junior (0 – 2 años)', 'semisenior' => 'Semi Senior (2 – 5 años)', 'senior' => 'Senior (+5 años)'];
$catalogo = [
    'HTML', 'CSS', 'JavaScript', 'PHP', 'Python', 'React', 'Node.js', 'MySQL', 'SQLite', 'Django', 'Laravel', 'Tailwind',
    'Bootstrap', 'Figma', 'Git', 'API', 'UI/UX', 'WordPress', 'Shopify', 'React Native', 'Firebase', 'Pandas', 'Testing',
];

const MAX_BIO = 1000;

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}


$uid = (int)($_SESSION['user_id'] ?? 0);
$conexion = $pdo ?? null;
$usuario = null;

if ($uid > 0 && $conexion instanceof PDO) {
    try {
        $st = $conexion->prepare('SELECT * FROM usuarios WHERE id = ?');
        $st->execute([$uid]);
        $usuario = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log('editar_perfil.php: ' . $e->getMessage());
    }
}

if ($usuario && ($usuario['tipo'] ?? '') === 'cliente') {
    header('Location: perfil_cliente.php?id=' . $uid);   // el cliente edita su perfil directamente en su página
    exit;
}

/* ------------------------------------------------------------------
 * Valores iniciales del formulario (los que ya están guardados)
 * ------------------------------------------------------------------ */
$errores = [];
$old = [
    'nombre' => '', 'especialidad' => '', 'nivel' => '', 'ubicacion' => '', 'bio' => '',
    'educacion' => '', 'portafolio' => '', 'skills' => [], 'otras' => '',
];

if ($usuario) {
    foreach (['nombre', 'especialidad', 'nivel', 'ubicacion', 'bio', 'educacion', 'portafolio'] as $c) {
        $old[$c] = (string)($usuario[$c] ?? '');
    }
    // Las habilidades guardadas se reparten entre las del catálogo y "otras"
    $mapaCat = array_combine(array_map('mb_strtolower', $catalogo), $catalogo);
    $otras = [];
    foreach (array_filter(array_map('trim', explode(',', (string)($usuario['habilidades'] ?? '')))) as $s) {
        $k = mb_strtolower($s);
        if (isset($mapaCat[$k])) { $old['skills'][] = $mapaCat[$k]; } else { $otras[] = $s; }
    }
    $old['otras'] = implode(', ', $otras);
}

/* ------------------------------------------------------------------
 * Guardar
 * ------------------------------------------------------------------ */
if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST') {

    if (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        $old['nombre']       = trim($_POST['nombre'] ?? '');
        $old['especialidad'] = $_POST['especialidad'] ?? '';
        $old['nivel']        = $_POST['nivel'] ?? '';
        $old['ubicacion']    = trim($_POST['ubicacion'] ?? '');
        $old['bio']          = trim($_POST['bio'] ?? '');
        $old['educacion']    = trim($_POST['educacion'] ?? '');
        $old['portafolio']   = trim($_POST['portafolio'] ?? '');
        $old['skills']       = array_values(array_intersect($catalogo, (array)($_POST['skills'] ?? [])));
        $old['otras']        = trim($_POST['otras'] ?? '');

        if (mb_strlen($old['nombre']) < 3 || mb_strlen($old['nombre']) > 120) {
            $errores[] = 'Escribe tu nombre completo (entre 3 y 120 caracteres).';
        }
        if (!isset($especialidades[$old['especialidad']])) {
            $errores[] = 'Selecciona tu especialidad principal.';
        }
        if (!isset($niveles[$old['nivel']])) {
            $errores[] = 'Selecciona tu nivel de experiencia.';
        }
        if (mb_strlen($old['ubicacion']) > 120) {
            $errores[] = 'La ubicación no puede superar 120 caracteres.';
        }
        if (mb_strlen($old['bio']) > MAX_BIO) {
            $errores[] = '«Sobre mí» no puede superar ' . MAX_BIO . ' caracteres.';
        }
        if (mb_strlen($old['educacion']) > 255) {
            $errores[] = 'La educación no puede superar 255 caracteres.';
        }

        if ($old['portafolio'] !== '') {
            if (!preg_match('#^https?://#i', $old['portafolio'])) {
                $old['portafolio'] = 'https://' . $old['portafolio'];
            }
            $esquema = strtolower((string)parse_url($old['portafolio'], PHP_URL_SCHEME));
            if (!filter_var($old['portafolio'], FILTER_VALIDATE_URL) || !in_array($esquema, ['http', 'https'], true) || mb_strlen($old['portafolio']) > 255) {
                $errores[] = 'El enlace del portafolio no es válido.';
            }
        }

        // Habilidades: las marcadas + las escritas a mano (separadas por coma), sin repetir
        $lista = $old['skills'];
        $vistos = array_map('mb_strtolower', $lista);
        foreach (preg_split('/[,;\n]+/', $old['otras']) as $o) {
            $o = trim($o);
            if ($o === '') continue;
            if (!preg_match('/^[\p{L}\p{N} .+#\/&_-]{1,30}$/u', $o)) {
                $errores[] = 'La habilidad «' . mb_substr($o, 0, 30) . '» es muy larga (máx. 30) o tiene caracteres no permitidos.';
                continue;
            }
            if (!in_array(mb_strtolower($o), $vistos, true)) {
                $lista[] = $o;
                $vistos[] = mb_strtolower($o);
            }
        }
        if (count($lista) > 20) {
            $errores[] = 'Puedes agregar hasta 20 habilidades.';
        }
        $csv = implode(',', $lista);
        if (mb_strlen($csv) > 255) {
            $errores[] = 'La lista de habilidades es demasiado larga. Quita alguna.';
        }

        if (!$errores) {
            $o = function ($v) { return $v === '' ? null : $v; };
            try {
                $conexion->prepare(
                    'UPDATE usuarios SET nombre = ?, especialidad = ?, nivel = ?, ubicacion = ?, bio = ?, educacion = ?, portafolio = ?, habilidades = ? WHERE id = ?'
                )->execute([
                    $old['nombre'], $old['especialidad'], $old['nivel'], $o($old['ubicacion']), $o($old['bio']),
                    $o($old['educacion']), $o($old['portafolio']), $o($csv), $uid,
                ]);
                header('Location: editar_perfil.php?ok=1');
                exit;
            } catch (PDOException $e) {
                error_log('editar_perfil.php: ' . $e->getMessage());
                $errores[] = 'No pudimos guardar los cambios. Si es la primera vez, ejecuta instalar_tablas.php para agregar las columnas que faltan.';
            }
        }
    }
}

$guardado = isset($_GET['ok']);

// Datos para la vista previa
$iniciales = '';
$handle = '';
if ($usuario) {
    foreach (array_slice(preg_split('/\s+/', trim($old['nombre'])), 0, 2) as $p) {
        $iniciales .= mb_strtoupper(mb_substr($p, 0, 1));
    }
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $old['nombre']);
    $handle = substr(strtolower(preg_replace('/[^a-z0-9]/i', '', $ascii ?: $old['nombre'])), 0, 18);
}
$gradientes = [['#6366f1', '#a855f7'], ['#0ea5e9', '#6366f1'], ['#10b981', '#0ea5e9'], ['#ec4899', '#8b5cf6'], ['#f59e0b', '#ef4444'], ['#14b8a6', '#4f46e5']];
$grad = $gradientes[$uid % count($gradientes)];
$rolTexto = trim(($especialidades[$old['especialidad']] ?? '') . (isset($especialidades[$old['especialidad']], $niveles[$old['nivel']]) ? ' · ' : '') . (isset($niveles[$old['nivel']]) ? explode(' (', $niveles[$old['nivel']])[0] : ''));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar perfil - DevWeb</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg: #060a16; --line: #18223a; --field: #0b1226;
            --accent: #5b5ff5; --accent-soft: #8b90ff; --text: #e5e9f5; --muted: #8a96b3; --ok: #34d399;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif; color: var(--text); min-height: 100vh;
            background: radial-gradient(circle at 100% 0%, rgba(91,95,245,.10), transparent 40%), var(--bg);
        }
        a { color: inherit; }
        .shell { display: flex; min-height: 100vh; }
        .content { flex: 1; min-width: 0; }
        .page { max-width: 900px; margin: 0 auto; padding: 1.6rem clamp(1rem, 3vw, 2.5rem) 3rem; display: grid; gap: 1.25rem; }
        .title h1 { margin: 0 0 .3rem; font-size: 1.7rem; font-weight: 700; display: flex; align-items: center; gap: .8rem; }
        .title h1 i { color: var(--accent-soft); font-size: 1.4rem; }
        .title p { margin: 0; color: var(--muted); }

        .card { background: linear-gradient(180deg, rgba(13,20,40,.9), rgba(9,14,29,.95)); border: 1px solid var(--line); border-radius: 18px; padding: 1.4rem 1.5rem; }
        .card h2 { margin: 0 0 1.1rem; font-size: 1.05rem; font-weight: 600; display: flex; align-items: center; gap: .8rem; }
        .card h2 i { color: var(--accent-soft); font-size: 1.15rem; }

        .alert { border-radius: 12px; padding: .9rem 1.1rem; font-size: .88rem; border: 1px solid; }
        .alert.err { border-color: rgba(248,113,113,.4); background: rgba(248,113,113,.08); color: #fecaca; }
        .alert.ok  { border-color: rgba(52,211,153,.4); background: rgba(52,211,153,.08); color: #a7f3d0; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; }
        .alert ul { margin: .3rem 0 0; padding-left: 1.1rem; }

        /* Vista previa */
        .preview { display: flex; align-items: center; gap: 1.2rem; }
        .pv-avatar { flex: none; width: 76px; height: 76px; border-radius: 50%; display: grid; place-items: center; font-size: 1.6rem; font-weight: 700; color: #fff; border: 4px solid var(--accent); }
        .pv-name { font-size: 1.2rem; font-weight: 700; word-break: break-word; }
        .pv-meta { color: var(--muted); font-size: .85rem; margin-top: .15rem; }
        .pv-tag { color: var(--accent-soft); font-size: .72rem; text-transform: uppercase; letter-spacing: .06em; margin-bottom: .3rem; }

        /* Formulario */
        .row2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1.1rem; }
        .field { margin-bottom: 1.1rem; }
        .field:last-child { margin-bottom: 0; }
        label.lbl { display: block; font-size: .85rem; color: #dbe1f2; margin-bottom: .45rem; }
        .req { color: var(--accent-soft); }
        input[type=text], input[type=url], select, textarea {
            width: 100%; background: var(--field); border: 1px solid var(--line); border-radius: 11px; color: var(--text);
            font: inherit; font-size: .9rem; padding: 0 1rem; height: 46px; transition: border-color .15s, box-shadow .15s;
        }
        textarea { height: auto; min-height: 130px; padding: .85rem 1rem; line-height: 1.55; resize: vertical; }
        select { appearance: none; -webkit-appearance: none; cursor: pointer; padding-right: 2.4rem;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' fill='none' stroke='%238a96b3' stroke-width='1.8' stroke-linecap='round'/%3E%3C/svg%3E");
            background-repeat: no-repeat; background-position: right 1rem center; }
        select option { background: #0b1226; color: var(--text); }
        input::placeholder, textarea::placeholder { color: #5f6b88; }
        input:focus, select:focus, textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,95,245,.25); }
        .hint { color: var(--muted); font-size: .78rem; margin: .45rem 0 0; }
        .counter { text-align: right; color: var(--muted); font-size: .75rem; margin-top: .35rem; }
        .counter.warn { color: #fca5a5; }

        /* Habilidades */
        .pills { display: flex; flex-wrap: wrap; gap: .6rem; margin-bottom: 1.1rem; }
        .pill { position: relative; cursor: pointer; }
        .pill input { position: absolute; opacity: 0; inset: 0; cursor: pointer; margin: 0; }
        .pill span { display: inline-flex; align-items: center; padding: .5rem 1rem; border: 1px solid var(--line); border-radius: 999px; background: var(--field); font-size: .82rem; transition: all .15s; }
        .pill:hover span { border-color: var(--accent); }
        .pill input:checked + span { border-color: var(--accent); background: rgba(91,95,245,.2); color: #fff; }
        .pill input:focus-visible + span { outline: 2px solid var(--accent-soft); outline-offset: 2px; }

        /* Botones */
        .actions { display: flex; gap: .8rem; flex-wrap: wrap; }
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .6rem; height: 46px; padding: 0 1.5rem; border-radius: 11px;
            border: 1px solid #3b3f9e; background: #1b2150; color: #fff; font: inherit; font-size: .9rem; font-weight: 500; cursor: pointer; text-decoration: none; transition: background .15s;
        }
        .btn:hover { background: var(--accent); }
        .btn.primary { background: var(--accent); border-color: var(--accent); box-shadow: 0 8px 22px rgba(91,95,245,.3); }
        .btn.primary:hover { background: #6b6ffa; }
        .btn.ghost { background: transparent; border-color: var(--line); color: #c9d1ea; }
        .btn.ghost:hover { background: rgba(255,255,255,.06); }
        .btn:focus-visible { outline: 2px solid var(--accent-soft); outline-offset: 2px; }

        .notice { text-align: center; padding: 3.5rem 1.5rem; }
        .notice i { font-size: 2.6rem; color: var(--accent-soft); margin-bottom: 1rem; }
        .notice h1 { margin: 0 0 .5rem; }
        .notice p { color: var(--muted); margin: 0 0 1.5rem; }

        @media (max-width: 640px) { .row2 { grid-template-columns: 1fr; } .preview { flex-direction: column; text-align: center; } }
        @media (prefers-reduced-motion: reduce) { * { transition: none !important; } }
    </style>
</head>
<body>
<div class="shell">

    <?php $railActivo = ''; include 'rail.php'; ?>

    <div class="content">
        <?php include 'topbar.php'; ?>

<?php if (!$usuario): ?>
        <main class="page">
            <section class="card notice">
                <i class="fa-solid fa-lock"></i>
                <h1>Inicia sesión para editar tu perfil</h1>
                <p>Necesitas una cuenta de programador con la sesión iniciada para cambiar tu información.</p>
                <div class="actions" style="justify-content:center">
                    <a href="registro.php" class="btn primary"><i class="fa-solid fa-user-plus"></i> Crear cuenta</a>
                    <a href="index.php" class="btn ghost"><i class="fa-solid fa-house"></i> Ir al inicio</a>
                </div>
            </section>
        </main>
<?php else: ?>
        <main class="page">

            <div class="title">
                <h1><i class="fa-solid fa-pen-to-square"></i> Editar perfil</h1>
                <p>Cambia la información que se muestra en tu perfil público.</p>
            </div>

            <?php if ($guardado && !$errores): ?>
                <div class="alert ok" role="status">
                    <span><i class="fa-solid fa-circle-check"></i> Tus cambios se guardaron correctamente.</span>
                    <a href="perfil.php?id=<?php echo $uid; ?>" class="btn" style="height:38px;padding:0 1rem;font-size:.82rem"><i class="fa-regular fa-eye"></i> Ver mi perfil</a>
                </div>
            <?php endif; ?>
            <?php if ($errores): ?>
                <div class="alert err" role="alert">
                    <strong>Revisa lo siguiente:</strong>
                    <ul><?php foreach ($errores as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

            <!-- Vista previa en vivo -->
            <section class="card preview" aria-label="Vista previa">
                <div class="pv-avatar" id="pvAvatar" style="background:linear-gradient(135deg,<?php echo $grad[0]; ?>,<?php echo $grad[1]; ?>)"><?php echo h($iniciales); ?></div>
                <div>
                    <div class="pv-tag">Vista previa</div>
                    <div class="pv-name" id="pvNombre"><?php echo h($old['nombre']); ?></div>
                    <div class="pv-meta">@<span id="pvHandle"><?php echo h($handle); ?></span></div>
                    <div class="pv-meta" id="pvRol"><?php echo h($rolTexto); ?></div>
                    <div class="pv-meta" id="pvUbic"><?php echo $old['ubicacion'] !== '' ? '<i class="fa-solid fa-location-dot"></i> ' . h($old['ubicacion']) : ''; ?></div>
                </div>
            </section>

            <form method="POST" action="editar_perfil.php" style="display:grid;gap:1.25rem" id="formPerfil">
                <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">

                <section class="card">
                    <h2><i class="fa-regular fa-user"></i> Información básica</h2>

                    <div class="field">
                        <label class="lbl" for="nombre">Nombre completo <span class="req">*</span></label>
                        <input type="text" id="nombre" name="nombre" maxlength="120" required autocomplete="name" value="<?php echo h($old['nombre']); ?>">
                    </div>

                    <div class="row2 field">
                        <div>
                            <label class="lbl" for="especialidad">Especialidad principal <span class="req">*</span></label>
                            <select id="especialidad" name="especialidad" required>
                                <option value="" disabled <?php echo $old['especialidad'] === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                                <?php foreach ($especialidades as $v => $l): ?>
                                    <option value="<?php echo h($v); ?>" <?php echo $old['especialidad'] === $v ? 'selected' : ''; ?>><?php echo h($l); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div>
                            <label class="lbl" for="nivel">Nivel de experiencia <span class="req">*</span></label>
                            <select id="nivel" name="nivel" required>
                                <option value="" disabled <?php echo $old['nivel'] === '' ? 'selected' : ''; ?>>Selecciona una opción</option>
                                <?php foreach ($niveles as $v => $l): ?>
                                    <option value="<?php echo h($v); ?>" <?php echo $old['nivel'] === $v ? 'selected' : ''; ?>><?php echo h($l); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="field">
                        <label class="lbl" for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" maxlength="120" placeholder="Ej. Medellín, Colombia" autocomplete="address-level2" value="<?php echo h($old['ubicacion']); ?>">
                    </div>
                </section>

                <section class="card">
                    <h2><i class="fa-solid fa-align-left"></i> Sobre mí</h2>
                    <div class="field">
                        <label class="lbl" for="bio">Cuéntales a los clientes quién eres y qué sabes hacer</label>
                        <textarea id="bio" name="bio" maxlength="<?php echo MAX_BIO; ?>" rows="6" placeholder="Soy estudiante de desarrollo web, me gusta crear soluciones que generen un impacto real..."><?php echo h($old['bio']); ?></textarea>
                        <div class="counter" id="bioCount">0 / <?php echo MAX_BIO; ?></div>
                    </div>
                </section>

                <section class="card">
                    <h2><i class="fa-solid fa-graduation-cap"></i> Educación y enlaces</h2>
                    <div class="field">
                        <label class="lbl" for="educacion">Educación</label>
                        <input type="text" id="educacion" name="educacion" maxlength="255" placeholder="Ej. Tecnólogo en Desarrollo de Software — SENA (2023 - Actualidad)" value="<?php echo h($old['educacion']); ?>">
                    </div>
                    <div class="field">
                        <label class="lbl" for="portafolio">Portafolio o enlace personal</label>
                        <input type="text" id="portafolio" name="portafolio" maxlength="255" inputmode="url" placeholder="GitHub, Behance, sitio web..." value="<?php echo h($old['portafolio']); ?>">
                        <p class="hint">Si no empieza con http:// o https://, se agrega https:// automáticamente.</p>
                    </div>
                </section>

                <section class="card">
                    <h2><i class="fa-solid fa-gem"></i> Habilidades</h2>
                    <div class="pills" role="group" aria-label="Habilidades">
                        <?php foreach ($catalogo as $sk): ?>
                            <label class="pill">
                                <input type="checkbox" name="skills[]" value="<?php echo h($sk); ?>" <?php echo in_array($sk, $old['skills'], true) ? 'checked' : ''; ?>>
                                <span><?php echo h($sk); ?></span>
                            </label>
                        <?php endforeach; ?>
                    </div>
                    <div class="field">
                        <label class="lbl" for="otras">Otras habilidades</label>
                        <input type="text" id="otras" name="otras" placeholder="Separadas por coma: Vue, Laravel, Docker" value="<?php echo h($old['otras']); ?>">
                        <p class="hint">Hasta 20 habilidades en total.</p>
                    </div>
                </section>

                <div class="actions">
                    <button type="submit" class="btn primary" id="btnGuardar"><i class="fa-regular fa-floppy-disk"></i> Guardar cambios</button>
                    <a href="perfil.php?id=<?php echo $uid; ?>" class="btn ghost">Cancelar</a>
                </div>
            </form>
        </main>

        <script>
            (function () {
                const $ = id => document.getElementById(id);

                // Contador de "Sobre mí"
                const bio = $('bio'), cnt = $('bioCount'), MAX = <?php echo MAX_BIO; ?>;
                function contar() {
                    cnt.textContent = bio.value.length + ' / ' + MAX;
                    cnt.classList.toggle('warn', bio.value.length > MAX * 0.95);
                }
                bio.addEventListener('input', contar);
                contar();

                // Vista previa en vivo
                const nombre = $('nombre'), esp = $('especialidad'), niv = $('nivel'), ubic = $('ubicacion');
                function textoSelect(sel, cortar) {
                    if (!sel.value) return '';
                    const t = sel.options[sel.selectedIndex].text;
                    return cortar ? t.split(' (')[0] : t;
                }
                function actualizar() {
                    const n = nombre.value.trim();
                    $('pvNombre').textContent = n || 'Tu nombre';
                    $('pvHandle').textContent = n.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase().replace(/[^a-z0-9]/g, '').slice(0, 18) || 'usuario';
                    $('pvAvatar').textContent = n.split(/\s+/).filter(Boolean).slice(0, 2).map(p => p[0]).join('').toUpperCase() || '?';
                    $('pvRol').textContent = [textoSelect(esp, false), textoSelect(niv, true)].filter(Boolean).join(' · ');
                    const u = ubic.value.trim();
                    $('pvUbic').textContent = '';
                    if (u) {
                        const i = document.createElement('i');
                        i.className = 'fa-solid fa-location-dot';
                        $('pvUbic').appendChild(i);
                        $('pvUbic').appendChild(document.createTextNode(' ' + u));
                    }
                }
                [nombre, esp, niv, ubic].forEach(el => el.addEventListener('input', actualizar));
                esp.addEventListener('change', actualizar);
                niv.addEventListener('change', actualizar);

                // Evita enviar dos veces
                $('formPerfil').addEventListener('submit', function () {
                    const b = $('btnGuardar');
                    b.disabled = true;
                    b.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Guardando…';
                });
            })();
        </script>
<?php endif; ?>
    </div>
</div>
</body>
</html>