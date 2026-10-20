<?php
try {
    $db_path = __DIR__ . '/devweb.db';
    
   
    $pdo = new PDO("sqlite:" . $db_path);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

    
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS usuarios (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            tipo TEXT NOT NULL,
            nombre TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            especialidad TEXT,
            nivel TEXT,
            portafolio TEXT,
            habilidades TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

} catch (PDOException $e) {
    die("Error de conexión con SQLite: " . $e->getMessage());
}


