<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Conexión usando SQLite (PDO) para evitar depender de servidores externos en Codespaces
try {
    $conn = new PDO("sqlite:" . __DIR__ . "/sistema_usuarios.db");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear la tabla automáticamente si no existe
    $conn->exec("CREATE TABLE IF NOT EXISTS usuarios (
        id INTEGER PRIMARY KEY AUTOINCREMENT,
        usuario TEXT NOT NULL UNIQUE,
        password TEXT NOT NULL,
        email TEXT NOT NULL,
        created_at TEXT DEFAULT CURRENT_TIMESTAMP
    )");

    // Insertar el usuario administrador por defecto si la tabla está vacía
    $stmt = $conn->query("SELECT COUNT(*) FROM usuarios WHERE usuario = 'admin'");
    if ($stmt->fetchColumn() == 0) {
        $admin_pass = password_hash('admin123', PASSWORD_DEFAULT);
        $insert = $conn->prepare("INSERT INTO usuarios (usuario, password, email) VALUES ('admin', ?, 'admin@correo.com')");
        $insert->execute([$admin_pass]);
    }

} catch (PDOException $e) {
    die("Error crítico de conexión: " . $e->getMessage());
}
?>