-- Script de inicialización de la Base de Datos

CREATE DATABASE IF NOT EXISTS sistema_usuarios;
USE sistema_usuarios;

-- Estructura de la tabla para los usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Inserción de usuario administrador inicial de prueba
-- Contraseña encriptada para el login: admin123
INSERT INTO usuarios (usuario, password, email) 
SELECT 'admin', '$2y$10$7rKzCcbWpZkW5Wp7m8YxXeK1w.d1v5J.uVbW5M1Oa7m3G2r1A.Ki2', 'admin@correo.com'
WHERE NOT EXISTS (SELECT 1 FROM usuarios WHERE usuario = 'admin');