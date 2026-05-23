<?php
session_start();
include 'conexion.php';

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}

$msg = "";

if (isset($_POST['crear'])) {
    $nuevo_user = $_POST['nuevo_usuario'];
    $nuevo_pass = password_hash($_POST['nuevo_password'], PASSWORD_DEFAULT);
    $nuevo_email = $_POST['nuevo_email'];

    try {
        $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
        $stmt->execute([$nuevo_user, $nuevo_pass, $nuevo_email]);
        $msg = "<p class='msg-success' style='color: #00ffaa; text-align: center;'>Usuario creado con éxito.</p>";
    } catch (Exception $e) {
        $msg = "<p class='error' style='color: #ff0055; text-align: center;'>Error: El usuario ya existe o los datos son inválidos.</p>";
    }
}

if (isset($_GET['eliminar'])) {
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$_GET['eliminar']]);
    header("Location: dashboard.php");
    exit();
}

// Aquí traemos los usuarios usando PDO correctamente
$resultado_usuarios = $conn->query("SELECT id, usuario, email, created_at FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8"><title>Dashboard</title><link rel="stylesheet" href="estilos.css"></head>
<body>
<div class="dashboard-layout">
    <div class="header-panel">
        <div class="neon-title" style="font-size: 28px; margin: 0;">Panel Control</div>
        <div style="color: #fff;">
            <span>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span> | 
            <a href="logout.php" style="color: #ff0055;">Cerrar Sesión</a>
        </div>
    </div>
    <?php echo $msg; ?>
    <div class="main-content" style="display: flex; gap: 20px; margin-top: 20px;">
        <div class="box-container" style="height: fit-content; flex: 1;">
            <h3>NUEVO REGISTRO</h3>
            <form method="POST" action="">
                <input type="text" name="nuevo_usuario" placeholder="Nombre de usuario" required>
                <input type="email" name="nuevo_email" placeholder="Correo electrónico" required>
                <input type="password" name="nuevo_password" placeholder="Contraseña" required>
                <button type="submit" name="crear">GUARDAR USUARIO</button>
            </form>
        </div>
        <div class="table-box" style="flex: 2; background: #000; padding: 20px; border: 2px solid #ff00aa; border-radius: 10px; box-shadow: 0 0 15px #ff00aa;">
            <h3 style="color: #fff; text-align: center; letter-spacing: 2px;">USUARIOS EN LA BASE DE DATOS</h3>
            <table style="width: 100%; color: #fff; border-collapse: collapse; margin-top: 15px;">
                <thead>
                    <tr style="border-bottom: 2px solid #00ffff; color: #00ffff;">
                        <th style="padding: 10px; text-align: left;">ID</th>
                        <th style="padding: 10px; text-align: left;">USUARIO</th>
                        <th style="padding: 10px; text-align: left;">EMAIL</th>
                        <th style="padding: 10px; text-align: left;">CREADO EL</th>
                        <th style="padding: 10px; text-align: left;">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($resultado_usuarios as $row): ?>
                    <tr style="border-bottom: 1px solid #333;">
                        <td style="padding: 10px;"><?php echo $row['id']; ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td style="padding: 10px;"><?php echo htmlspecialchars($row['email']); ?></td>
                        <td style="padding: 10px;"><?php echo $row['created_at']; ?></td>
                        <td style="padding: 10px;">
                            <a href="editar.php?id=<?php echo $row['id']; ?>" style="color: #00ffff; text-decoration: none; margin-right: 10px;">Editar</a>
                            <a href="dashboard.php?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar registro?');" style="color: #ff0055; text-decoration: none;">Eliminar</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body></html>