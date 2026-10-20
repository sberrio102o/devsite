<?php
session_start();
require_once('db.php');

header('Content-Type: application/json; charset=utf-8');

$input = json_decode(file_get_contents('php://input'), true);
$idToken = $input['token'] ?? '';

if (empty($idToken)) {
    echo json_encode(['success' => false, 'message' => 'Token de Google no proporcionado.']);
    exit;
}

$url = 'https://oauth2.googleapis.com/tokeninfo?id_token=' . urlencode($idToken);
$respuestaGoogle = @file_get_contents($url);

if ($respuestaGoogle === false) {
    echo json_encode(['success' => false, 'message' => 'No se pudo verificar el token con Google.']);
    exit;
}

$payload = json_decode($respuestaGoogle, true);

$clientIdEsperado = '863501876791-soh36hdev6afbbgib95ditaiiimhutno.apps.googleusercontent.com';

if (!isset($payload['email']) || (isset($payload['aud']) && $payload['aud'] !== $clientIdEsperado)) {
    echo json_encode(['success' => false, 'message' => 'Token de Google inválido o no autorizado.']);
    exit;
}

$googleId = $payload['sub']; // ID único del usuario en Google
$email    = $payload['email'];
$nombre   = $payload['name'] ?? 'Usuario de Google';

$conexion = $pdo ?? null;
if (!($conexion instanceof PDO)) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión con la base de datos.']);
    exit;
}

try {
    // 1. Buscar si la cuenta de Google ya está vinculada
    $stmtOauth = $conexion->prepare('
        SELECT u.id, u.tipo, u.nombre, u.email 
        FROM oauth_cuentas o
        JOIN usuarios u ON o.usuario_id = u.id
        WHERE o.proveedor = ? AND o.proveedor_id = ?
    ');
    $stmtOauth->execute(['google', $googleId]);
    $usuario = $stmtOauth->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $usuarioId = $usuario['id'];
        $tipoUsuario = $usuario['tipo'];
    } else {
        // 2. Verificar si el correo ya existe en la tabla usuarios de forma clásica
        $stmtEmail = $conexion->prepare('SELECT id, tipo, nombre, email FROM usuarios WHERE email = ?');
        $stmtEmail->execute([$email]);
        $usuario = $stmtEmail->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            $usuarioId = $usuario['id'];
            $tipoUsuario = $usuario['tipo'];

            // Vincular cuenta de Google al usuario existente
            $stmtInsertOauth = $conexion->prepare('INSERT INTO oauth_cuentas (usuario_id, proveedor, proveedor_id) VALUES (?, ?, ?)');
            $stmtInsertOauth->execute([$usuarioId, 'google', $googleId]);
        } else {
            // 3. Crear usuario nuevo por completo
            $tipoPorDefecto = 'cliente'; 

            $conexion->beginTransaction();

            $stmtInsertUser = $conexion->prepare('
                INSERT INTO usuarios (tipo, nombre, email, password_hash, created_at)
                VALUES (?, ?, ?, NULL, CURRENT_TIMESTAMP)
            ');
            $stmtInsertUser->execute([$tipoPorDefecto, $nombre, $email]);
            $usuarioId = (int)$conexion->lastInsertId();

            $stmtInsertOauth = $conexion->prepare('INSERT INTO oauth_cuentas (usuario_id, proveedor, proveedor_id) VALUES (?, ?, ?)');
            $stmtInsertOauth->execute([$usuarioId, 'google', $googleId]);

            $conexion->commit();
            $tipoUsuario = $tipoPorDefector ?? $tipoPorDefecto;
        }
    }

    // Configurar la sesión de forma segura
    session_regenerate_id(true);
    $_SESSION['user_id']   = $usuarioId;
    $_SESSION['user_tipo'] = $tipoUsuario;
    $_SESSION['csrf']      = bin2hex(random_bytes(32));

    // Responder con éxito y ordenar la redirección a index.php
    echo json_encode([
        'success' => true,
        'redirect' => 'index.php'
    ]);

} catch (PDOException $e) {
       if ($conexion && $conexion->inTransaction()) {
        $conexion->rollBack();
    }
    error_log('login-google.php error: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno al procesar el usuario.']);
}