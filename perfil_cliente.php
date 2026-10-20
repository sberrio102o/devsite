<?php

session_start();
require_once('db.php');

const MAX_PROYECTO = 10 * 1024 * 1024;   
const MAX_IMAGEN   = 5 * 1024 * 1024;    
const MAX_ARCHIVOS = 50;                 
const MAX_TEXTO    = 5000;
const MAX_REDES    = 2000;

function h($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }

function formatoBytes($b) {
    if ($b >= 1048576) return round($b / 1048576, 1) . ' MB';
    if ($b >= 1024)    return round($b / 1024) . ' KB';
    return $b . ' B';
}

function linkify($texto) {
    $seguro = htmlspecialchars((string)$texto, ENT_QUOTES, 'UTF-8');
    $seguro = preg_replace_callback('~https?://[^\s<]+~i', function ($m) {
        $url = $m[0];
        $extra = '';
        while ($url !== '' && strpos('.,;:!?)', substr($url, -1)) !== false) {
            $extra = substr($url, -1) . $extra;
            $url = substr($url, 0, -1);
        }
        return '<a href="' . $url . '" target="_blank" rel="noopener noreferrer nofollow">' . $url . '</a>' . $extra;
    }, $seguro);
    return nl2br($seguro);
}

// Tipos de archivo permitidos: extensión => tipos MIME reales aceptados
$zipMimes = ['application/zip', 'application/x-zip-compressed', 'application/octet-stream'];
$permitidos = [
    'jpg'  => ['image/jpeg'],
    'jpeg' => ['image/jpeg'],
    'png'  => ['image/png'],
    'webp' => ['image/webp'],
    'gif'  => ['image/gif'],
    'pdf'  => ['application/pdf'],
    'txt'  => ['text/plain'],
    'docx' => array_merge(['application/vnd.openxmlformats-officedocument.wordprocessingml.document'], $zipMimes),
    'xlsx' => array_merge(['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'], $zipMimes),
    'pptx' => array_merge(['application/vnd.openxmlformats-officedocument.presentationml.presentation'], $zipMimes),
    'zip'  => $zipMimes,
];
$extImagenes = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
$extProyectos = array_keys($permitidos);

function dirCliente($id) { return __DIR__ . '/uploads/clientes/' . (int)$id; }
function urlArchivo($id, $archivo) { return 'uploads/clientes/' . (int)$id . '/' . rawurlencode($archivo); }

function protegerCarpeta($dir) {
    $ht = $dir . '/.htaccess';
    if (!file_exists($ht)) {
        @file_put_contents($ht,
            "Options -Indexes\n" .
            "<IfModule mod_php.c>\n  php_flag engine off\n</IfModule>\n" .
            "<IfModule mod_php7.c>\n  php_flag engine off\n</IfModule>\n" .
            "<FilesMatch \"\\.(php|phtml|phar|php[0-9])$\">\n  Require all denied\n</FilesMatch>\n"
        );
    }
}

function normalizarArchivos($f) {
    $salida = [];
    if (!is_array($f) || !isset($f['name'])) return $salida;
    if (!is_array($f['name'])) return [$f];
    foreach ($f['name'] as $i => $nombre) {
        $salida[] = ['name' => $nombre, 'type' => $f['type'][$i], 'tmp_name' => $f['tmp_name'][$i], 'error' => $f['error'][$i], 'size' => $f['size'][$i]];
    }
    return $salida;
}

function procesarSubida($f, $id, $permitidos, $extPermitidas, $max) {
    if ($f['error'] !== UPLOAD_ERR_OK) {
        $txt = ($f['error'] === UPLOAD_ERR_INI_SIZE || $f['error'] === UPLOAD_ERR_FORM_SIZE)
            ? 'supera el tamaño que permite el servidor' : 'no se pudo subir';
        return [null, null, $txt];
    }
    if ($f['size'] > $max) return [null, null, 'supera el límite de ' . formatoBytes($max)];

    $ext = strtolower(pathinfo($f['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, $extPermitidas, true)) return [null, null, 'tipo de archivo no permitido'];

    if (!class_exists('finfo')) return [null, null, 'falta la extensión PHP "fileinfo" en el servidor'];
    $mime = (new finfo(FILEINFO_MIME_TYPE))->file($f['tmp_name']);
    if (!in_array($mime, $permitidos[$ext], true)) return [null, null, 'el contenido no coincide con su extensión'];

    $dir = dirCliente($id);
    if (!is_dir($dir) && !mkdir($dir, 0755, true)) return [null, null, 'no se pudo crear la carpeta de archivos'];
    protegerCarpeta($dir);

    $nombre = bin2hex(random_bytes(16)) . '.' . $ext;   // nombre aleatorio no recierdo por que KAJSHDFDJSKA
    if (!move_uploaded_file($f['tmp_name'], $dir . '/' . $nombre)) return [null, null, 'no se pudo guardar'];
    return [$nombre, $mime, null];
}

function guardarPerfilCliente(PDO $c, $id, array $campos) {
    $st = $c->prepare('SELECT 1 FROM clientes_perfil WHERE usuario_id = ?');
    $st->execute([$id]);
    if (!$st->fetchColumn()) {
        $c->prepare('INSERT INTO clientes_perfil (usuario_id) VALUES (?)')->execute([$id]);
    }
    $sets = [];
    $vals = [];
    foreach ($campos as $col => $valor) {   
        $sets[] = "$col = ?";
        $vals[] = $valor;
    }
    $vals[] = $id;
    $c->prepare('UPDATE clientes_perfil SET ' . implode(', ', $sets) . ', updated_at = CURRENT_TIMESTAMP WHERE usuario_id = ?')->execute($vals);
}

function redirigir($id, $ok, $ancla = '') {
    header('Location: perfil_cliente.php?id=' . (int)$id . '&ok=' . urlencode($ok) . $ancla);
    exit;
}

$meses = ['enero','febrero','marzo','abril','mayo','junio','julio','agosto','septiembre','octubre','noviembre','diciembre'];

if (empty($_SESSION['csrf'])) {
    $_SESSION['csrf'] = bin2hex(random_bytes(32));
}


$id = isset($_GET['id']) ? (int)$_GET['id'] : (int)($_SESSION['user_id'] ?? 0);
$conexion = $pdo ?? null;
$usuario = null;

if ($id > 0 && $conexion instanceof PDO) {
    try {
        $st = $conexion->prepare('SELECT * FROM usuarios WHERE id = ?');
        $st->execute([$id]);
        $usuario = $st->fetch(PDO::FETCH_ASSOC) ?: null;
    } catch (PDOException $e) {
        error_log('perfil_cliente.php: ' . $e->getMessage());
    }
}

if ($usuario && ($usuario['tipo'] ?? '') !== 'cliente') {
    header('Location: perfil.php?id=' . $id);   // los programadores tienen su propia plantilla por que kwjshdjska mr gusta sufrir y desidi hacer esto en lugar de seguir el consejo de mie strellita 
    exit;
}
if (!$usuario) {
    http_response_code(404);
}

$esDueno = $usuario && isset($_SESSION['user_id']) && (int)$_SESSION['user_id'] === $id;
$errores = [];
$mensajes = [];
$faltanTablas = false;
$textoTienda = null;   
$textoRedes = null;

if ($usuario && $_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!$esDueno) {
        http_response_code(403);
        exit('No tienes permiso para editar este perfil.');
    }

    if (empty($_POST) && empty($_FILES) && (int)($_SERVER['CONTENT_LENGTH'] ?? 0) > 0) {
        $errores[] = 'Los archivos superan el tamaño máximo que acepta el servidor (post_max_size en php.ini).';
    } elseif (!hash_equals($_SESSION['csrf'], $_POST['csrf'] ?? '')) {
        $errores[] = 'La sesión del formulario expiró. Recarga la página e inténtalo de nuevo.';
    } else {
        try {
            switch ($_POST['accion'] ?? '') {

                case 'proyectos':
                    $archivos = normalizarArchivos($_FILES['proyectos'] ?? []);
                    $st = $conexion->prepare('SELECT COUNT(*) FROM clientes_proyectos WHERE usuario_id = ?');
                    $st->execute([$id]);
                    $total = (int)$st->fetchColumn();
                    $subidos = 0;

                    foreach ($archivos as $f) {
                        if ($f['error'] === UPLOAD_ERR_NO_FILE) continue;
                        $nombreMostrar = mb_substr(basename($f['name']), 0, 255);
                        if ($total >= MAX_ARCHIVOS) {
                            $errores[] = 'Alcanzaste el máximo de ' . MAX_ARCHIVOS . ' proyectos.';
                            break;
                        }
                        [$guardado, $mime, $err] = procesarSubida($f, $id, $permitidos, $extProyectos, MAX_PROYECTO);
                        if ($err) {
                            $errores[] = '«' . $nombreMostrar . '»: ' . $err . '.';
                            continue;
                        }
                        $conexion->prepare('INSERT INTO clientes_proyectos (usuario_id, nombre_original, archivo, mime, tamano) VALUES (?, ?, ?, ?, ?)')
                                 ->execute([$id, $nombreMostrar, $guardado, $mime, (int)$f['size']]);
                        $subidos++;
                        $total++;
                    }

                    if ($subidos && !$errores) {
                        redirigir($id, 'proyectos', '#proyectos');
                    } elseif ($subidos) {
                        $mensajes[] = $subidos . ' archivo(s) subido(s).';
                    } elseif (!$errores) {
                        $errores[] = 'No seleccionaste ningún archivo.';
                    }
                    break;

                case 'eliminar':
                    $pid = (int)($_POST['proyecto_id'] ?? 0);
                    $st = $conexion->prepare('SELECT archivo FROM clientes_proyectos WHERE id = ? AND usuario_id = ?');
                    $st->execute([$pid, $id]);
                    $archivo = $st->fetchColumn();
                    if ($archivo) {
                        $conexion->prepare('DELETE FROM clientes_proyectos WHERE id = ? AND usuario_id = ?')->execute([$pid, $id]);
                        @unlink(dirCliente($id) . '/' . basename($archivo));
                        redirigir($id, 'eliminado', '#proyectos');
                    }
                    $errores[] = 'No se encontró ese proyecto.';
                    break;

                case 'tienda':
                    $texto = trim($_POST['tienda_texto'] ?? '');
                    $textoTienda = $texto;
                    if (mb_strlen($texto) > MAX_TEXTO) {
                        $errores[] = 'El texto de la tienda no puede superar ' . MAX_TEXTO . ' caracteres.';
                    }

                    $st = $conexion->prepare('SELECT tienda_imagen FROM clientes_perfil WHERE usuario_id = ?');
                    $st->execute([$id]);
                    $imagenActual = $st->fetchColumn() ?: null;
                    $campos = ['tienda_texto' => $texto === '' ? null : $texto];
                    $borrarDespues = null;

                    $f = $_FILES['tienda_imagen'] ?? null;
                    if ($f && $f['error'] !== UPLOAD_ERR_NO_FILE) {
                        if (!$errores) {
                            [$guardado, , $err] = procesarSubida($f, $id, $permitidos, $extImagenes, MAX_IMAGEN);
                            if ($err) {
                                $errores[] = 'La imagen: ' . $err . '.';
                            } else {
                                $campos['tienda_imagen'] = $guardado;
                                $borrarDespues = $imagenActual;
                            }
                        }
                    } elseif (!empty($_POST['quitar_imagen']) && $imagenActual) {
                        $campos['tienda_imagen'] = null;
                        $borrarDespues = $imagenActual;
                    }

                    if (!$errores) {
                        guardarPerfilCliente($conexion, $id, $campos);
                        if ($borrarDespues) {
                            @unlink(dirCliente($id) . '/' . basename($borrarDespues));
                        }
                        redirigir($id, 'tienda', '#tienda');
                    }
                    break;

                case 'redes':
                    $texto = trim($_POST['redes'] ?? '');
                    $textoRedes = $texto;
                    if (mb_strlen($texto) > MAX_REDES) {
                        $errores[] = 'Las redes no pueden superar ' . MAX_REDES . ' caracteres.';
                    } else {
                        guardarPerfilCliente($conexion, $id, ['redes' => $texto === '' ? null : $texto]);
                        redirigir($id, 'redes', '#redes');
                    }
                    break;

                default:
                    $errores[] = 'Acción no válida.';
            }
        } catch (PDOException $e) {
            error_log('perfil_cliente.php: ' . $e->getMessage());
            $faltanTablas = true;
            $errores[] = 'No pudimos guardar los cambios. Verifica que ejecutaste instalar_tablas.php.';
        }
    }
}


$perfilCliente = ['tienda_texto' => null, 'tienda_imagen' => null, 'redes' => null];
$proyectos = [];

if ($usuario && $conexion instanceof PDO) {
    try {
        $st = $conexion->prepare('SELECT tienda_texto, tienda_imagen, redes FROM clientes_perfil WHERE usuario_id = ?');
        $st->execute([$id]);
        $fila = $st->fetch(PDO::FETCH_ASSOC);
        if ($fila) $perfilCliente = $fila;

        $st = $conexion->prepare('SELECT id, nombre_original, archivo, mime, tamano, created_at FROM clientes_proyectos WHERE usuario_id = ? ORDER BY id DESC');
        $st->execute([$id]);
        $proyectos = $st->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        error_log('perfil_cliente.php: ' . $e->getMessage());
        $faltanTablas = true;
    }
}

$valorTienda = $textoTienda !== null ? $textoTienda : (string)($perfilCliente['tienda_texto'] ?? '');
$valorRedes  = $textoRedes !== null ? $textoRedes : (string)($perfilCliente['redes'] ?? '');

// Datos de la cabecera
$nombre = $usuario['nombre'] ?? '';
$handle = '';
if ($usuario) {
    $ascii = @iconv('UTF-8', 'ASCII//TRANSLIT', $nombre);
    $handle = substr(strtolower(preg_replace('/[^a-z0-9]/i', '', $ascii ?: $nombre)), 0, 18);
}
$desde = '';
if ($usuario && !empty($usuario['created_at']) && ($ts = strtotime($usuario['created_at']))) {
    $desde = $meses[(int)date('n', $ts) - 1] . ' ' . date('Y', $ts);
}

$okMensajes = [
    'proyectos' => 'Archivos subidos correctamente.',
    'eliminado' => 'Proyecto eliminado.',
    'tienda'    => 'Información de la tienda guardada.',
    'redes'     => 'Redes guardadas.',
];
if (isset($_GET['ok'], $okMensajes[$_GET['ok']])) {
    $mensajes[] = $okMensajes[$_GET['ok']];
}

function iconoArchivo($archivo) {
    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
    $mapa = [
        'pdf' => 'fa-file-pdf', 'doc' => 'fa-file-word', 'docx' => 'fa-file-word',
        'xls' => 'fa-file-excel', 'xlsx' => 'fa-file-excel', 'ppt' => 'fa-file-powerpoint', 'pptx' => 'fa-file-powerpoint',
        'zip' => 'fa-file-zipper', 'txt' => 'fa-file-lines',
    ];
    return $mapa[$ext] ?? 'fa-file';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $usuario ? h($nombre) . ' - DevWeb' : 'Perfil no encontrado - DevWeb'; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="icon" type="image/x-icon" href="imagenes/logo-devsite2.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <style>
        :root {
            --bg: #060a16; --panel: #0a1124; --line: #18223a; --field: #0b1226;
            --accent: #5b5ff5; --accent-soft: #8b90ff; --text: #e5e9f5; --muted: #8a96b3; --ok: #34d399; --danger: #f87171;
        }
        * { box-sizing: border-box; }
        html, body { margin: 0; }
        body {
            font-family: 'Inter', system-ui, sans-serif; color: var(--text); min-height: 100vh;
            background: radial-gradient(circle at 100% 0%, rgba(91,95,245,.10), transparent 40%), var(--bg);
        }
        a { color: inherit; }
        [hidden] { display: none !important; }
        .shell { display: flex; min-height: 100vh; }
        .content { flex: 1; min-width: 0; }

        .page { max-width: 1100px; margin: 0 auto; padding: 1.6rem clamp(1rem, 3vw, 2.5rem) 3rem; display: grid; gap: 1.25rem; }
        .card { background: linear-gradient(180deg, rgba(13,20,40,.9), rgba(9,14,29,.95)); border: 1px solid var(--line); border-radius: 18px; padding: 1.4rem 1.5rem; }
        .card h2 { margin: 0; font-size: 1.1rem; font-weight: 600; display: flex; align-items: center; gap: .8rem; }
        .card h2 i { color: var(--accent-soft); font-size: 1.2rem; }
        .card-head { display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-wrap: wrap; margin-bottom: 1.1rem; }
        .muted { color: var(--muted); }
        .hint { color: var(--muted); font-size: .8rem; margin: .5rem 0 0; }

        /* Avisos */
        .alert { border-radius: 12px; padding: .9rem 1.1rem; font-size: .88rem; border: 1px solid; }
        .alert.err { border-color: rgba(248,113,113,.4); background: rgba(248,113,113,.08); color: #fecaca; }
        .alert.ok  { border-color: rgba(52,211,153,.4); background: rgba(52,211,153,.08); color: #a7f3d0; }
        .alert ul { margin: .3rem 0 0; padding-left: 1.1rem; }

        /* Cabecera */
        .head { display: flex; align-items: center; gap: 1.6rem; flex-wrap: wrap; }
        .avatar {
            flex: none; width: 130px; height: 130px; border-radius: 50%; display: grid; place-items: center;
            background: #0d1430; border: 5px solid var(--accent); color: var(--accent-soft); font-size: 3rem;
            box-shadow: 0 0 0 5px #0a1124, 0 10px 30px rgba(91,95,245,.3);
        }
        .head h1 { margin: 0; font-size: 1.8rem; font-weight: 700; letter-spacing: -.01em; word-break: break-word; }
        .head .handle { color: var(--muted); margin: .2rem 0 .7rem; }
        .badges { display: flex; flex-wrap: wrap; gap: .7rem; font-size: .85rem; color: #c9d1ea; }
        .badges span { display: inline-flex; align-items: center; gap: .5rem; }
        .badges i { color: var(--muted); }
        .pill { padding: .25rem .8rem; border-radius: 999px; background: #131a3f; border: 1px solid #232d5e; color: var(--accent-soft); font-size: .78rem; }

        /* Botones */
        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: .6rem; height: 44px; padding: 0 1.3rem; border-radius: 11px;
            border: 1px solid #3b3f9e; background: #1b2150; color: #fff; font: inherit; font-size: .9rem; font-weight: 500; cursor: pointer; text-decoration: none; transition: background .15s;
        }
        .btn:hover { background: var(--accent); }
        .btn.primary { background: var(--accent); border-color: var(--accent); box-shadow: 0 8px 22px rgba(91,95,245,.3); }
        .btn.primary:hover { background: #6b6ffa; }
        .btn.small { height: 36px; padding: 0 .9rem; font-size: .8rem; }
        .btn.danger { border-color: rgba(248,113,113,.4); background: transparent; color: #fca5a5; }
        .btn.danger:hover { background: rgba(248,113,113,.15); }
        .btn:focus-visible, .dropzone:focus-visible { outline: 2px solid var(--accent-soft); outline-offset: 2px; }

        /* Proyectos */
        .dropzone { border: 2px dashed #2b3560; border-radius: 14px; padding: 2.2rem 1rem; text-align: center; color: var(--muted); transition: border-color .15s, background .15s; }
        .dropzone.over { border-color: var(--accent); background: rgba(91,95,245,.08); }
        .dropzone i { font-size: 2rem; color: var(--accent-soft); margin-bottom: .6rem; display: block; }
        .dropzone p { margin: 0; font-size: .9rem; }
        .files { display: grid; grid-template-columns: repeat(auto-fill, minmax(210px, 1fr)); gap: 1rem; }
        .file { border: 1px solid var(--line); border-radius: 14px; overflow: hidden; background: rgba(8,13,28,.6); display: flex; flex-direction: column; }
        .file .thumb { height: 130px; display: grid; place-items: center; background: #0d1530; color: var(--accent-soft); font-size: 2.4rem; overflow: hidden; }
        .file .thumb img { width: 100%; height: 100%; object-fit: cover; display: block; }
        .file .info { padding: .8rem .9rem .4rem; }
        .file .name { font-size: .85rem; font-weight: 500; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .file .meta { color: var(--muted); font-size: .75rem; margin-top: .2rem; }
        .file .actions { display: flex; gap: .5rem; padding: .6rem .9rem .9rem; margin-top: auto; }
        .file .actions form { margin: 0; }
        .aviso-js { color: #fca5a5; font-size: .85rem; margin: .8rem 0 0; }

        /* Fila inferior: tienda + redes */
        .two { display: grid; grid-template-columns: 2fr 1fr; gap: 1.25rem; align-items: start; }
        textarea {
            width: 100%; background: var(--field); border: 1px solid var(--line); border-radius: 12px; color: var(--text);
            font: inherit; font-size: .9rem; line-height: 1.55; padding: .9rem 1rem; resize: vertical; min-height: 130px;
        }
        textarea::placeholder { color: #5f6b88; }
        textarea:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(91,95,245,.25); }
        .row { display: flex; align-items: center; gap: .8rem; flex-wrap: wrap; margin-top: .9rem; }
        .preview { margin-top: .9rem; }
        .preview img { max-width: 100%; max-height: 260px; border-radius: 12px; border: 1px solid var(--line); display: block; }
        .check { display: inline-flex; align-items: center; gap: .5rem; font-size: .85rem; color: #c9d1ea; cursor: pointer; }
        .check input { accent-color: var(--accent); width: 18px; height: 18px; }
        .read { font-size: .92rem; line-height: 1.65; color: #d4daee; margin: 0; word-break: break-word; }
        .read a { color: var(--accent-soft); }
        .empty { color: var(--muted); font-size: .9rem; margin: 0; }

        .notfound { text-align: center; padding: 4rem 1.5rem; }
        .notfound i { font-size: 2.6rem; color: var(--accent-soft); margin-bottom: 1rem; }

        @media (max-width: 900px) { .two { grid-template-columns: 1fr; } }
        @media (max-width: 560px) { .avatar { width: 100px; height: 100px; font-size: 2.2rem; } }
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
            <section class="card notfound">
                <i class="fa-regular fa-face-frown"></i>
                <h1>Perfil no encontrado</h1>
                <p class="muted">La cuenta que buscas no existe.</p>
                <a href="index.php" class="btn primary"><i class="fa-solid fa-house"></i> Volver al inicio</a>
            </section>
        </main>
<?php else: ?>
        <main class="page">

            <?php if ($faltanTablas): ?>
                <div class="alert err" role="alert">Falta preparar la base de datos: ejecuta <strong>instalar_tablas.php</strong> una vez para crear las tablas de clientes.</div>
            <?php endif; ?>
            <?php foreach ($mensajes as $m): ?>
                <div class="alert ok" role="status"><i class="fa-solid fa-circle-check"></i> <?php echo h($m); ?></div>
            <?php endforeach; ?>
            <?php if ($errores): ?>
                <div class="alert err" role="alert">
                    <strong>Revisa lo siguiente:</strong>
                    <ul><?php foreach ($errores as $e): ?><li><?php echo h($e); ?></li><?php endforeach; ?></ul>
                </div>
            <?php endif; ?>

           
            <section class="card head">
                <div class="avatar" aria-hidden="true"><i class="fa-solid fa-store"></i></div>
                <div>
                    <h1><?php echo h($nombre); ?></h1>
                    <p class="handle">@<?php echo h($handle); ?></p>
                    <div class="badges">
                        <span class="pill">Cliente</span>
                        <?php if ($desde): ?><span><i class="fa-regular fa-calendar"></i> Se unió en <?php echo h($desde); ?></span><?php endif; ?>
                    </div>
                </div>
            </section>

            <section class="card" id="proyectos">
                <div class="card-head">
                    <h2><i class="fa-solid fa-folder-open"></i> <?php echo $esDueno ? 'Mis proyectos' : 'Proyectos'; ?>
                        <?php if ($proyectos): ?><span class="muted" style="font-weight:400;font-size:.85rem">(<?php echo count($proyectos); ?>)</span><?php endif; ?>
                    </h2>

                    <?php if ($esDueno): ?>
                        <form id="formProyectos" method="POST" action="perfil_cliente.php?id=<?php echo (int)$id; ?>" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                            <input type="hidden" name="accion" value="proyectos">
                            <input type="file" id="inputProyectos" name="proyectos[]" multiple hidden
                                   accept=".<?php echo h(implode(',.', $extProyectos)); ?>">
                            <button type="button" class="btn primary" id="btnAgregar"><i class="fa-solid fa-plus"></i> Agregar proyectos</button>
                        </form>
                    <?php endif; ?>
                </div>

                <?php if ($esDueno): ?><p class="aviso-js" id="avisoArchivos" role="alert" hidden></p><?php endif; ?>

                <?php if ($proyectos): ?>
                    <div class="files" id="zonaArchivos">
                        <?php foreach ($proyectos as $p):
                            $esImg = in_array(strtolower(pathinfo($p['archivo'], PATHINFO_EXTENSION)), $extImagenes, true);
                            $url = urlArchivo($id, $p['archivo']);
                        ?>
                            <article class="file">
                                <div class="thumb">
                                    <?php if ($esImg): ?>
                                        <img src="<?php echo h($url); ?>" alt="<?php echo h($p['nombre_original']); ?>" loading="lazy">
                                    <?php else: ?>
                                        <i class="fa-regular <?php echo h(iconoArchivo($p['archivo'])); ?>"></i>
                                    <?php endif; ?>
                                </div>
                                <div class="info">
                                    <div class="name" title="<?php echo h($p['nombre_original']); ?>"><?php echo h($p['nombre_original']); ?></div>
                                    <div class="meta"><?php echo h(formatoBytes((int)$p['tamano'])); ?></div>
                                </div>
                                <div class="actions">
                                    <a class="btn small" href="<?php echo h($url); ?>" target="_blank" rel="noopener noreferrer"><i class="fa-solid fa-arrow-up-right-from-square"></i> Abrir</a>
                                    <?php if ($esDueno): ?>
                                        <form method="POST" action="perfil_cliente.php?id=<?php echo (int)$id; ?>" onsubmit="return confirm('¿Eliminar este proyecto?');">
                                            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                                            <input type="hidden" name="accion" value="eliminar">
                                            <input type="hidden" name="proyecto_id" value="<?php echo (int)$p['id']; ?>">
                                            <button type="submit" class="btn small danger" aria-label="Eliminar <?php echo h($p['nombre_original']); ?>"><i class="fa-regular fa-trash-can"></i></button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                            </article>
                        <?php endforeach; ?>
                    </div>
                <?php elseif ($esDueno): ?>
                    <div class="dropzone" id="zonaArchivos" tabindex="0" role="button" aria-label="Agregar proyectos">
                        <i class="fa-solid fa-cloud-arrow-up"></i>
                        <p>Aún no has agregado proyectos.<br>Pulsa <strong>Agregar proyectos</strong> o arrastra aquí tus archivos.</p>
                    </div>
                <?php else: ?>
                    <p class="empty">Todavía no hay proyectos publicados.</p>
                <?php endif; ?>

                <?php if ($esDueno): ?>
                    <p class="hint">Formatos: <?php echo h(strtoupper(implode(', ', $extProyectos))); ?> · hasta <?php echo h(formatoBytes(MAX_PROYECTO)); ?> por archivo.</p>
                <?php endif; ?>
            </section>

      
            <div class="two">

                <section class="card" id="tienda">
                    <div class="card-head"><h2><i class="fa-solid fa-store"></i> Información de la tienda</h2></div>

                    <?php if ($esDueno): ?>
                        <form method="POST" action="perfil_cliente.php?id=<?php echo (int)$id; ?>#tienda" enctype="multipart/form-data">
                            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                            <input type="hidden" name="accion" value="tienda">

                            <label for="tienda_texto" class="muted" style="font-size:.85rem;display:block;margin-bottom:.5rem">Escríbela como texto…</label>
                            <textarea id="tienda_texto" name="tienda_texto" rows="7" maxlength="<?php echo MAX_TEXTO; ?>"
                                      placeholder="Nombre del negocio, qué vendes, horarios, ubicación, lo que necesitas del programador..."><?php echo h($valorTienda); ?></textarea>

                            <div class="row">
                                <input type="file" id="inputTienda" name="tienda_imagen" accept=".<?php echo h(implode(',.', $extImagenes)); ?>" hidden>
                                <button type="button" class="btn small" id="btnImagen"><i class="fa-regular fa-image"></i> …o adjunta una imagen</button>
                                <span class="muted" id="nombreImagen" style="font-size:.8rem"></span>
                                <?php if (!empty($perfilCliente['tienda_imagen'])): ?>
                                    <label class="check"><input type="checkbox" name="quitar_imagen" value="1"> Quitar la imagen actual</label>
                                <?php endif; ?>
                            </div>

                            <div class="preview" id="previewTienda">
                                <?php if (!empty($perfilCliente['tienda_imagen'])): ?>
                                    <img src="<?php echo h(urlArchivo($id, $perfilCliente['tienda_imagen'])); ?>" alt="Imagen de la información de la tienda">
                                <?php endif; ?>
                            </div>
                            <p class="hint">Imagen JPG, PNG, WEBP o GIF de hasta <?php echo h(formatoBytes(MAX_IMAGEN)); ?>. Puedes usar solo texto, solo imagen o ambos.</p>

                            <div class="row"><button type="submit" class="btn primary"><i class="fa-regular fa-floppy-disk"></i> Guardar información</button></div>
                        </form>
                    <?php else: ?>
                        <?php if (!empty($perfilCliente['tienda_texto'])): ?>
                            <p class="read"><?php echo nl2br(h($perfilCliente['tienda_texto'])); ?></p>
                        <?php endif; ?>
                        <?php if (!empty($perfilCliente['tienda_imagen'])): ?>
                            <div class="preview"><img src="<?php echo h(urlArchivo($id, $perfilCliente['tienda_imagen'])); ?>" alt="Información de la tienda"></div>
                        <?php endif; ?>
                        <?php if (empty($perfilCliente['tienda_texto']) && empty($perfilCliente['tienda_imagen'])): ?>
                            <p class="empty">Aún no hay información de la tienda.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>

                <section class="card" id="redes">
                    <div class="card-head"><h2><i class="fa-solid fa-user-group"></i> Redes</h2></div>

                    <?php if ($esDueno): ?>
                        <form method="POST" action="perfil_cliente.php?id=<?php echo (int)$id; ?>#redes">
                            <input type="hidden" name="csrf" value="<?php echo h($_SESSION['csrf']); ?>">
                            <input type="hidden" name="accion" value="redes">
                            <label for="redes_texto" class="muted" style="font-size:.85rem;display:block;margin-bottom:.5rem">Una por línea</label>
                            <textarea id="redes_texto" name="redes" rows="6" maxlength="<?php echo MAX_REDES; ?>"
                                      placeholder="Instagram: @mitienda&#10;Facebook: facebook.com/mitienda&#10;Web: https://mitienda.com"><?php echo h($valorRedes); ?></textarea>
                            <p class="hint">Los enlaces que empiecen con https:// se vuelven clicables.</p>
                            <div class="row"><button type="submit" class="btn primary"><i class="fa-regular fa-floppy-disk"></i> Guardar redes</button></div>
                        </form>
                    <?php else: ?>
                        <?php if (!empty($perfilCliente['redes'])): ?>
                            <p class="read"><?php echo linkify($perfilCliente['redes']); ?></p>
                        <?php else: ?>
                            <p class="empty">Aún no hay redes agregadas.</p>
                        <?php endif; ?>
                    <?php endif; ?>
                </section>
            </div>
        </main>

<?php if ($esDueno): ?>
        <script>
            (function () {
                const MAX = <?php echo MAX_PROYECTO; ?>;
                const form  = document.getElementById('formProyectos');
                const input = document.getElementById('inputProyectos');
                const btn   = document.getElementById('btnAgregar');
                const zona  = document.getElementById('zonaArchivos');
                const aviso = document.getElementById('avisoArchivos');

                // "Agregar proyectos" abre el explorador de archivos y sube lo que se elija 
                function enviar() {
                    aviso.hidden = true;
                    const grandes = Array.from(input.files).filter(f => f.size > MAX);
                    if (grandes.length) {
                        aviso.textContent = 'Estos archivos superan el límite: ' + grandes.map(f => f.name).join(', ');
                        aviso.hidden = false;
                        input.value = '';
                        return;
                    }
                    if (input.files.length) {
                        btn.disabled = true;
                        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Subiendo…';
                        form.submit();
                    }
                }
                btn.addEventListener('click', () => input.click());
                input.addEventListener('change', enviar);

                // Arrastrar y soltar sobre la zona de proyectos que caricada >:(
                if (zona) {
                    ['dragenter', 'dragover'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.add('over'); }));
                    ['dragleave', 'drop'].forEach(ev => zona.addEventListener(ev, e => { e.preventDefault(); zona.classList.remove('over'); }));
                    zona.addEventListener('drop', e => {
                        if (e.dataTransfer.files.length) { input.files = e.dataTransfer.files; enviar(); }
                    });
                    zona.addEventListener('click', e => { if (zona.classList.contains('dropzone')) input.click(); });
                    zona.addEventListener('keydown', e => { if ((e.key === 'Enter' || e.key === ' ') && zona.classList.contains('dropzone')) { e.preventDefault(); input.click(); } });
                }

                // Imagen de la tienda:D deberia quitar esto mas tarde
                const inImg = document.getElementById('inputTienda');
                const btnImg = document.getElementById('btnImagen');
                const prev = document.getElementById('previewTienda');
                const nombre = document.getElementById('nombreImagen');
                btnImg.addEventListener('click', () => inImg.click());
                inImg.addEventListener('change', () => {
                    const f = inImg.files[0];
                    if (!f) return;
                    nombre.textContent = f.name;
                    const img = document.createElement('img');
                    img.src = URL.createObjectURL(f);
                    img.alt = 'Vista previa de la imagen seleccionada';
                    prev.innerHTML = '';
                    prev.appendChild(img);
                });
            })();
        </script>
<?php endif; ?>
<?php endif; ?>
    </div>
</div>
</body>
</html>