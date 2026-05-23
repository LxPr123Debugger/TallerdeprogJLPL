<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

// Verificar que venga un ID válido en la URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];

// Consulta adaptada a PDO para traer los datos del usuario actual
$stmt = $conn->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Si el usuario no existe en la base de datos, regresar al dashboard
if (!$user) {
    header("Location: dashboard.php");
    exit();
}

// Procesar la actualización cuando se envía el formulario
if (isset($_POST['actualizar'])) {
    $nuevo_user = $_POST['usuario'];
    $nuevo_email = $_POST['email'];

    if (!empty($_POST['password'])) {
        // Si el usuario cambió la contraseña
        $nuevo_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$nuevo_user, $nuevo_email, $nuevo_pass, $id]);
    } else {
        // Si la contraseña se dejó vacía (no se actualiza)
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ? WHERE id = ?");
        $stmt->execute([$nuevo_user, $nuevo_email, $id]);
    }

    header("Location: dashboard.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Registro</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>
<div class="box-container" style="margin-top: 50px; border: 2px solid #00ffff; box-shadow: 0 0 15px #00ffff;">
    <h3 style="color: #fff; text-align: center; letter-spacing: 2px;">MODIFICAR ID #<?php echo htmlspecialchars($id); ?></h3>
    <form method="POST" action="">
        <label style="color: #00ffff; display: block; margin-bottom: 5px; font-size: 14px;">Usuario:</label>
        <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>
        
        <label style="color: #00ffff; display: block; margin-bottom: 5px; font-size: 14px; margin-top: 15px;">Correo electrónico:</label>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        
        <label style="color: #ff0055; display: block; margin-bottom: 5px; font-size: 14px; margin-top: 15px;">Nueva contraseña (dejar vacío para no cambiar):</label>
        <input type="password" name="password" placeholder="Opcional">
        
        <button type="submit" name="actualizar" style="margin-top: 20px; background: #00ffff; color: #000; box-shadow: 0 0 10px #00ffff;">ACTUALIZAR</button>
        <a href="dashboard.php" style="display: block; text-align: center; color: #aaa; margin-top: 15px; text-decoration: none; font-size: 14px;">Volver al panel</a>
    </form>
</div>
</body>
</html>