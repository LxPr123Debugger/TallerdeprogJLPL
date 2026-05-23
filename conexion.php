<?php
// Configuración dinámica usando variables de entorno o valores por defecto para local
$host = getenv('DB_HOST') ?: "localhost";
$user = getenv('DB_USER') ?: "root";
$pass = getenv('DB_PASS') !== false ? getenv('DB_PASS') : "";
$db   = getenv('DB_NAME') ?: "sistema_usuarios";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Error crítico: No se pudo conectar a la base de datos.");
}
?>