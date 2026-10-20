<?php
/*
 *
 * Tablas:
 *   usuarios              clientes y programadores
 *   habilidades           catálogo de tecnologías (se rellena con valores iniciales)
 *   usuario_habilidades   habilidades de cada usuario        (muchos a muchos)
 *   proyectos             proyectos / portafolio de cada programador
 *   proyecto_tecnologias  tecnologías usadas en cada proyecto (muchos a muchos)
 *   propuestas            lo que se envía desde el formulario de contacto
 *   disponibilidad        estado y horario de cada programador
 *   tickets_soporte       solicitudes de soporte técnico
 *   oauth_cuentas         cuentas de Google/GitHub/LinkedIn vinculadas a un usuario
 *   clientes_perfil       información de la tienda y redes de cada cliente
 *   clientes_proyectos    archivos de proyectos subidos por cada cliente
 */
header('Content-Type: text/plain; charset=utf-8');


if (!in_array($_SERVER['REMOTE_ADDR'] ?? '', ['127.0.0.1', '::1'], true)) {
    http_response_code(403);
    exit("Este script solo se puede ejecutar en local.\n");
}

require_once('db.php');

$conexion = $pdo ?? null;
if (!($conexion instanceof PDO)) {
    exit("db.php no deja una conexión PDO en la variable \$pdo. Ajusta db.php o cambia el nombre en este archivo.\n");
}
$conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$driver = $conexion->getAttribute(PDO::ATTR_DRIVER_NAME);

if (!in_array($driver, ['sqlite', 'mysql'], true)) {
    exit("Tipo de base de datos no soportado por este script: $driver\n");
}
$sqlite = ($driver === 'sqlite');


if ($sqlite) {
    $conexion->exec('PRAGMA foreign_keys = ON');
}


$pk   = $sqlite ? 'INTEGER PRIMARY KEY AUTOINCREMENT' : 'INT AUTO_INCREMENT PRIMARY KEY';
$int  = $sqlite ? 'INTEGER' : 'INT';
$ts   = $sqlite ? 'DATETIME DEFAULT CURRENT_TIMESTAMP' : 'TIMESTAMP DEFAULT CURRENT_TIMESTAMP';
$tail = $sqlite ? '' : ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4';
$str  = fn($n) => $sqlite ? 'TEXT' : "VARCHAR($n)";

$tablas = [];

$tablas['usuarios'] = "
    CREATE TABLE IF NOT EXISTS usuarios (
        id $pk,
        tipo {$str(20)} NOT NULL,
        nombre {$str(120)} NOT NULL,
        email {$str(190)} NOT NULL UNIQUE,
        password_hash {$str(255)} NULL,
        especialidad {$str(60)},
        nivel {$str(30)},
        portafolio {$str(255)},
        habilidades {$str(255)},
        bio TEXT,
        ubicacion {$str(120)},
        educacion {$str(255)},
        created_at $ts
    )$tail";

$tablas['habilidades'] = "
    CREATE TABLE IF NOT EXISTS habilidades (
        id $pk,
        nombre {$str(60)} NOT NULL UNIQUE
    )$tail";

$tablas['usuario_habilidades'] = "
    CREATE TABLE IF NOT EXISTS usuario_habilidades (
        usuario_id $int NOT NULL,
        habilidad_id $int NOT NULL,
        PRIMARY KEY (usuario_id, habilidad_id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (habilidad_id) REFERENCES habilidades(id) ON DELETE CASCADE
    )$tail";

$tablas['proyectos'] = "
    CREATE TABLE IF NOT EXISTS proyectos (
        id $pk,
        usuario_id $int NOT NULL,
        titulo {$str(150)} NOT NULL,
        descripcion TEXT,
        url {$str(255)},
        imagen {$str(255)},
        categoria {$str(20)} NOT NULL DEFAULT 'web',
        vistas $int NOT NULL DEFAULT 0,
        destacado $int NOT NULL DEFAULT 0,
        created_at $ts,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )$tail";

$tablas['proyecto_tecnologias'] = "
    CREATE TABLE IF NOT EXISTS proyecto_tecnologias (
        proyecto_id $int NOT NULL,
        habilidad_id $int NOT NULL,
        PRIMARY KEY (proyecto_id, habilidad_id),
        FOREIGN KEY (proyecto_id) REFERENCES proyectos(id) ON DELETE CASCADE,
        FOREIGN KEY (habilidad_id) REFERENCES habilidades(id) ON DELETE CASCADE
    )$tail";

$tablas['propuestas'] = "
    CREATE TABLE IF NOT EXISTS propuestas (
        id $pk,
        programador_id $int NOT NULL,
        cliente_id $int NULL,
        empresa {$str(150)} NOT NULL,
        email {$str(190)} NOT NULL,
        mensaje TEXT NOT NULL,
        estado {$str(20)} NOT NULL DEFAULT 'pendiente',
        created_at $ts,
        FOREIGN KEY (programador_id) REFERENCES usuarios(id) ON DELETE CASCADE,
        FOREIGN KEY (cliente_id) REFERENCES usuarios(id) ON DELETE SET NULL
    )$tail";

$tablas['disponibilidad'] = "
    CREATE TABLE IF NOT EXISTS disponibilidad (
        usuario_id $int PRIMARY KEY,
        estado {$str(20)} NOT NULL DEFAULT 'disponible',
        horas_semana $int NOT NULL DEFAULT 0,
        dias {$str(7)} NOT NULL DEFAULT '1111100',
        disponible_desde DATE NULL,
        nota {$str(160)},
        updated_at $ts,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )$tail";

$tablas['tickets_soporte'] = "
    CREATE TABLE IF NOT EXISTS tickets_soporte (
        id $pk,
        user_id $int NULL,
        nombre {$str(120)} NOT NULL,
        email {$str(190)} NOT NULL,
        categoria {$str(30)} NOT NULL,
        asunto {$str(160)} NOT NULL,
        mensaje TEXT NOT NULL,
        estado {$str(20)} NOT NULL DEFAULT 'abierto',
        created_at $ts,
        FOREIGN KEY (user_id) REFERENCES usuarios(id) ON DELETE SET NULL
    )$tail";

$tablas['oauth_cuentas'] = "
    CREATE TABLE IF NOT EXISTS oauth_cuentas (
        id $pk,
        usuario_id $int NOT NULL,
        proveedor {$str(20)} NOT NULL,
        proveedor_id {$str(190)} NOT NULL,
        created_at $ts,
        UNIQUE (proveedor, proveedor_id),
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )$tail";


$tablas['clientes_perfil'] = "
    CREATE TABLE IF NOT EXISTS clientes_perfil (
        usuario_id $int PRIMARY KEY,
        tienda_texto TEXT,
        tienda_imagen {$str(255)},
        redes TEXT,
        updated_at $ts,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )$tail";


$tablas['clientes_proyectos'] = "
    CREATE TABLE IF NOT EXISTS clientes_proyectos (
        id $pk,
        usuario_id $int NOT NULL,
        nombre_original {$str(255)} NOT NULL,
        archivo {$str(255)} NOT NULL,
        mime {$str(100)} NOT NULL,
        tamano $int NOT NULL DEFAULT 0,
        created_at $ts,
        FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
    )$tail";

try {
    echo "Base de datos detectada: $driver\n\n";


    foreach ($tablas as $nombre => $sql) {
        $conexion->exec($sql);
        echo "Tabla $nombre: OK\n";
    }

    if ($sqlite) {
        $conexion->exec('CREATE INDEX IF NOT EXISTS idx_proyectos_usuario ON proyectos(usuario_id)');
        $conexion->exec('CREATE INDEX IF NOT EXISTS idx_propuestas_programador ON propuestas(programador_id)');
        $conexion->exec('CREATE INDEX IF NOT EXISTS idx_tickets_estado ON tickets_soporte(estado)');
    }

    $existentes = $sqlite
        ? $conexion->query('PRAGMA table_info(usuarios)')->fetchAll(PDO::FETCH_COLUMN, 1)
        : $conexion->query('SHOW COLUMNS FROM usuarios')->fetchAll(PDO::FETCH_COLUMN, 0);

    $opcionales = [
        'especialidad' => $str(60), 'nivel' => $str(30), 'portafolio' => $str(255), 'habilidades' => $str(255),
        'bio' => 'TEXT', 'ubicacion' => $str(120), 'educacion' => $str(255),
    ];
    foreach ($opcionales as $col => $tipo) {
        if (!in_array($col, $existentes, true)) {
            $conexion->exec("ALTER TABLE usuarios ADD COLUMN $col $tipo NULL");
            echo "Columna agregada a usuarios: $col\n";
        }
    }

   
    $insertar = $conexion->prepare(($sqlite ? 'INSERT OR IGNORE' : 'INSERT IGNORE') . ' INTO habilidades (nombre) VALUES (?)');
    $iniciales = [
        'HTML', 'CSS', 'JavaScript', 'PHP', 'Python', 'React', 'Node.js', 'MySQL', 'SQLite', 'PostgreSQL',
        'Django', 'Laravel', 'Tailwind', 'Bootstrap', 'Figma', 'Git', 'API', 'UI/UX', 'WordPress', 'Shopify',
        'React Native', 'Firebase', 'Pandas', 'Testing',
    ];
    foreach ($iniciales as $hab) {
        $insertar->execute([$hab]);
    }
    echo "Catálogo de habilidades: " . count($iniciales) . " valores iniciales\n";

    echo "\nListo. Ahora BORRA instalar_tablas.php del servidor.\n";
    if ($sqlite) {
        echo "Recuerda agregar en db.php, justo después de crear \$pdo:  \$pdo->exec('PRAGMA foreign_keys = ON');\n";
    }
} catch (PDOException $e) {
    error_log('instalar_tablas.php: ' . $e->getMessage());
    echo "Error al preparar la base de datos: " . $e->getMessage() . "\n";
}