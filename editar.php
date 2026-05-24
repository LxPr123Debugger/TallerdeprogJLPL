<?php
session_start();
include 'conexion.php';
include 'logs.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: dashboard.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$user) {
    header("Location: dashboard.php");
    exit();
}

if (isset($_POST['actualizar'])) {
    $nuevo_user = $_POST['usuario'];
    $nuevo_email = $_POST['email'];

    if (!empty($_POST['password'])) {
        $nuevo_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ?, password = ? WHERE id = ?");
        $stmt->execute([$nuevo_user, $nuevo_email, $nuevo_pass, $id]);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ? WHERE id = ?");
        $stmt->execute([$nuevo_user, $nuevo_email, $id]);
    }
    
    registrar_log("TRIGGER ➜ USER_UPDATED - ID: #$id por admin", 'success');
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

<div class="app-container">
    <div class="box-container">
        <h3 style="color: #ffffff; text-shadow: 0 0 5px #fff; text-align: center; letter-spacing: 1px; font-size:16px; margin-top:0;">
            <span class="pointer">▶</span>MODIFICAR ID #<?php echo htmlspecialchars($id); ?>
        </h3>
        <form method="POST" action="">
            <label style="color: #00f2fe; display: block; margin-bottom: 3px; font-size: 12px; margin-top: 10px;">Usuario:</label>
            <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>
            
            <label style="color: #00f2fe; display: block; margin-bottom: 3px; font-size: 12px; margin-top: 10px;">Correo electrónico:</label>
            <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            
            <label style="color: #ffffff; text-shadow: 0 0 3px #fff; display: block; margin-bottom: 3px; font-size: 12px; margin-top: 10px;">Nueva contraseña (opcional):</label>
            <input type="password" name="password" placeholder="Dejar vacío para mantener">
            
            <button type="submit" name="actualizar">ACTUALIZAR DATOS</button>
            <a href="dashboard.php" style="display: block; text-align: center; color: #64748b; margin-top: 15px; text-decoration: none; font-size: 13px;">Volver al panel</a>
        </form>
    </div>
</div>

<?php include 'logs.php'; ?>

</body>
</html>