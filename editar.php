<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$id = $_GET['id'];

$stmt = $conn->prepare("SELECT usuario, email FROM usuarios WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (isset($_POST['actualizar'])) {
    $nuevo_user = $_POST['usuario'];
    $nuevo_email = $_POST['email'];

    if (!empty($_POST['password'])) {
        $nuevo_pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ?, password = ? WHERE id = ?");
        $stmt->bind_param("sssi", $nuevo_user, $nuevo_email, $nuevo_pass, $id);
    } else {
        $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, email = ? WHERE id = ?");
        $stmt->bind_param("ssi", $nuevo_user, $nuevo_email, $id);
    }

    $stmt->execute();
    $stmt->close();
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

<div class="box-container">
    <h3>Modificar ID #<?php echo $id; ?></h3>
    <form method="POST" action="">
        <input type="text" name="usuario" value="<?php echo htmlspecialchars($user['usuario']); ?>" required>
        <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
        <input type="password" name="password" placeholder="Nueva contraseña (opcional)">
        
        <input type="submit" name="actualizar" value="Actualizar">
        <a href="dashboard.php" style="display: block; text-align: center; color: #aaa; margin-top: 15px; text-decoration: none; font-size: 14px;">Volver al panel</a>
    </form>
</div>

</body>
</html>