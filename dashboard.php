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

    $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $nuevo_user, $nuevo_pass, $nuevo_email);
    
    if ($stmt->execute()) {
        $msg = "<p class='msg-success'>Usuario creado con éxito.</p>";
    } else {
        $msg = "<p class='error'>Error: El usuario ya existe o los datos son inválidos.</p>";
    }
    $stmt->close();
}

if (isset($_GET['eliminar'])) {
    $id_eliminar = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->bind_param("i", $id_eliminar);
    $stmt->execute();
    $stmt->close();
    header("Location: dashboard.php");
    exit();
}

$resultado_usuarios = $conn->query("SELECT id, usuario, email, created_at FROM usuarios");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Gestión de Usuarios</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="dashboard-layout">
    <div class="header-panel">
        <div class="neon-title" style="font-size: 28px; margin: 0;">Panel Control</div>
        <div>
            <span>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span> | 
            <a href="logout.php">Cerrar Sesión</a>
        </div>
    </div>

    <?php echo $msg; ?>

    <div class="main-content">
        <div class="box-container" style="height: fit-content;">
            <h3>Nuevo Registro</h3>
            <form method="POST" action="">
                <input type="text" name="nuevo_usuario" placeholder="Nombre de usuario" required>
                <input type="email" name="nuevo_email" placeholder="Correo electrónico" required>
                <input type="password" name="nuevo_password" placeholder="Contraseña" required>
                <input type="submit" name="crear" value="Guardar Usuario">
            </form>
        </div>

        <div class="table-box">
            <h3>Usuarios en la Base de Datos</h3>
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Usuario</th>
                        <th>Email</th>
                        <th>Creado el</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = $resultado_usuarios->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                        <td><?php echo htmlspecialchars($row['email']); ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td>
                            <a class="btn-edit" href="editar.php?id=<?php echo $row['id']; ?>">Editar</a>
                            <a class="btn-delete" href="dashboard.php?eliminar=<?php echo $row['id']; ?>" onclick="return confirm('¿Eliminar registro?');">Eliminar</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

</body>
</html>