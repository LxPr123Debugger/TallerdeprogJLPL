<?php
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
include 'conexion.php';
include_once 'logs.php'; 

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
        registrar_log("TRIGGER ➜ USER_CREATED por admin: '$nuevo_user'", 'success');
        $msg = "<p class='msg-success'>Usuario creado con éxito.</p>";
    } catch (Exception $e) {
        $msg = "<p class='error'>Error: El usuario ya existe.</p>";
    }
}

if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $conn->prepare("DELETE FROM usuarios WHERE id = ?");
    $stmt->execute([$id]);
    registrar_log("TRIGGER ➜ USER_DELETED - ID: #$id", 'fail');
    header("Location: dashboard.php");
    exit();
}

$resultado_usuarios = $conn->query("SELECT id, usuario, email FROM usuarios")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control</title>
    <link rel="stylesheet" href="estilos.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

<div class="app-container">
    <div class="dashboard-layout">
        <div class="header-panel">
            <div class="neon-title" style="font-size: 24px; margin: 0;"><span class="pointer">▶</span>PANEL CONTROL</div>
            <div>
                <span>Bienvenido, <strong style="color: #ff003c;"><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></span> | 
                <a href="logout.php" style="color: #ffffff; text-shadow: 0 0 5px #fff; text-decoration: none; font-weight: bold; transition: all 0.2s;" onmouseover="this.style.color='#ff003c'; this.style.textShadow='0 0 8px #ff003c'" onmouseout="this.style.color='#ffffff'; this.style.textShadow='0 0 5px #fff'">Cerrar Sesión</a>
            </div>
        </div>

        <?php echo $msg; ?>

        <div class="main-content">
            <div class="box-container" style="height: fit-content; flex: 1; min-height: auto;">
                <h3 style="color: #fff; text-shadow: 0 0 5px #fff; font-size: 16px; margin-top:0;">NUEVO REGISTRO</h3>
                <form method="POST" action="">
                    <input type="text" name="nuevo_usuario" placeholder="Nombre de usuario" required>
                    <input type="email" name="nuevo_email" placeholder="Correo electrónico" required>
                    <input type="password" name="nuevo_password" placeholder="Contraseña" required>
                    <button type="submit" name="crear">GUARDAR USUARIO</button>
                </form>
            </div>

            <div class="table-box">
                <h3 style="color: #ff003c; text-shadow: 0 0 5px rgba(255,0,60,0.4); font-size: 16px; margin-top:0;">USUARIOS EN LA BASE DE DATOS</h3>
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>USUARIO</th>
                            <th>EMAIL</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($resultado_usuarios as $row): ?>
                        <tr>
                            <td><?php echo $row['id']; ?></td>
                            <td><?php echo htmlspecialchars($row['usuario']); ?></td>
                            <td><?php echo htmlspecialchars($row['email']); ?></td>
                            <td>
                                <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-editar" style="color: #ff003c; text-decoration: none; margin-right: 15px;">Editar</a>
                                <a href="#" class="btn-eliminar" onclick="confirmarEliminar(event, <?php echo $row['id']; ?>)" style="color: #ffffff; text-shadow: 0 0 3px #fff; text-decoration: none;">Eliminar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include_once 'logs.php'; ?>

<script>
function confirmarEliminar(event, id) {
    event.preventDefault(); 
    
    Swal.fire({
        title: '¿ELIMINAR REGISTRO?',
        text: "Esta acción destruirá el ID #" + id + " en la base de datos.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ff003c',
        cancelButtonColor: '#222',
        confirmButtonText: 'SÍ, BORRAR',
        cancelButtonText: 'CANCELAR',
        customClass: {
            popup: 'dark-swal',
            title: 'dark-swal-title',
            htmlContainer: 'dark-swal-text'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'dashboard.php?eliminar=' + id;
        }
    });
}
</script>

</body>
</html>