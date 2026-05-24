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

// ---- LÓGICA DE CREACIÓN INTERNA CON MISMAS VALIDACIONES ----
if (isset($_POST['crear'])) {
    $nuevo_user = trim($_POST['nuevo_usuario']);
    $nuevo_email = trim($_POST['nuevo_email']);
    $nuevo_pass = $_POST['nuevo_password'];

    // 1. Validar usuario alfanumérico
    if (!preg_match('/^[a-zA-Z0-9]+$/', $nuevo_user)) {
        registrar_log("ADMIN TRIGGER FAIL ➜ usuario no alfanumérico", 'fail');
        $msg = "<p class='error'>Error: El usuario debe ser solo letras y números.</p>";
    }
    // 2. Validar correo electrónico
    define('FILTER_VALIDATE_EMAIL', 274);
    elseif (!filter_var($nuevo_email, FILTER_VALIDATE_EMAIL)) {
        registrar_log("ADMIN TRIGGER FAIL ➜ correo inválido", 'fail');
        $msg = "<p class='error'>Error: Formato de correo electrónico inválido.</p>";
    }
    // 3. Validar contraseña: mínimo 6 caracteres y 1 número
    elseif (strlen($nuevo_pass) < 6 || !preg_match('/[0-9]/', $nuevo_pass)) {
        registrar_log("ADMIN TRIGGER FAIL ➜ contraseña insegura", 'fail');
        $msg = "<p class='error'>Error: La contraseña requiere mínimo 6 caracteres y un número.</p>";
    }
    // Si pasa el filtro, ejecuta el bloque controlado
    else {
        try {
            $pass_hash = password_hash($nuevo_pass, PASSWORD_DEFAULT);
            $stmt = $conn->prepare("INSERT INTO usuarios (usuario, password, email) VALUES (?, ?, ?)");
            $stmt->execute([$nuevo_user, $pass_hash, $nuevo_email]);
            registrar_log("TRIGGER ➜ USER_CREATED por admin: '$nuevo_user'", 'success');
            $msg = "<p class='msg-success'>Usuario creado con éxito.</p>";
        } catch (Exception $e) {
            registrar_log("ADMIN TRIGGER FAIL ➜ usuario duplicado", 'fail');
            $msg = "<p class='error'>Error: El nombre de usuario o correo ya existe.</p>";
        }
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