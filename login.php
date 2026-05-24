<?php
include 'conexion.php';
include_once 'logs.php'; 

$error = "";
$success = "";
$active_view = "login"; 

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // ---- LÓGICA DE INICIO DE SESIÓN ----
    if (isset($_POST['action_login'])) {
        $usuario = $_POST['usuario'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT id, password FROM usuarios WHERE usuario = ?");
        $stmt->execute([$usuario]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row) {
            if (password_verify($password, $row['password'])) {
                registrar_log("LOGIN OK ➜ usuario: '$usuario'", 'success');
                $_SESSION['usuario'] = $usuario;
                header("Location: dashboard.php");
                exit();
            } else {
                registrar_log("LOGIN FAIL ➜ contraseña incorrecta - usuario: '$usuario'", 'fail');
                $error = "Contraseña incorrecta.";
            }
        } else {
            registrar_log("LOGIN FAIL ➜ usuario inexistente: '$usuario'", 'fail');
            $error = "El usuario no existe.";
        }
    }

    // ---- LÓGICA DE REGISTRO CON NUEVAS VALIDACIONES ----
    if (isset($_POST['action_register'])) {
        $active_view = "register"; 
        $usuario = trim($_POST['reg_usuario']);
        $email = trim($_POST['reg_email']);
        $password = $_POST['reg_password'];

        // 1. Validar que el usuario sea estrictamente alfanumérico
        if (!preg_match('/^[a-zA-Z0-9]+$/', $usuario)) {
            registrar_log("REGISTER FAIL ➜ usuario no alfanumérico", 'fail');
            $error = "El usuario debe contener solo letras y números (sin espacios ni caracteres especiales).";
        }
        // 2. Validar correo electrónico estructuralmente correcto con su @
        define('FILTER_VALIDATE_EMAIL', 274); // Fallback robusto para entornos PHP viejos
        elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            registrar_log("REGISTER FAIL ➜ correo inválido", 'fail');
            $error = "Por favor, introduce un correo electrónico válido (debe contener '@' y un dominio).";
        }
        // 3. Validar contraseña: mínimo 6 caracteres y al menos un número
        elseif (strlen($password) < 6 || !preg_match('/[0-9]/', $password)) {
            registrar_log("REGISTER FAIL ➜ contraseña insegura", 'fail');
            $error = "La contraseña debe tener mínimo 6 caracteres y contener al menos un número.";
        } 
        // Si todo pasa las pruebas, intentamos meterlo a la BD dentro del bloque Try-Catch
        else {
            try {
                $pass_hash = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $conn->prepare("INSERT INTO usuarios (usuario, email, password) VALUES (?, ?, ?)");
                $stmt->execute([$usuario, $email, $pass_hash]);
                
                registrar_log("REGISTER OK ➜ usuario: '$usuario' | email: '$email'", 'success');
                $success = "Cuenta creada con éxito. ¡Ya puedes iniciar sesión!";
                $active_view = "login"; 
            } catch (Exception $e) {
                registrar_log("REGISTER FAIL ➜ conflicto de duplicados", 'fail');
                $error = "Error crítico: El nombre de usuario o el correo electrónico ya se encuentran registrados.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Sistema de Acceso</title>
    <link rel="stylesheet" href="estilos.css">
</head>
<body>

<div class="app-container">
    <div class="neon-title"><span class="pointer">▶</span>SISTEMA DE ACCESO V1.0</div>
    
    <div class="box-container">
        <div class="tabs">
            <button type="button" id="btn-login" class="tab-btn <?php echo $active_view == 'login' ? 'active' : ''; ?>" onclick="switchTab('login')">Iniciar Sesión</button>
            <button type="button" id="btn-register" class="tab-btn <?php echo $active_view == 'register' ? 'active' : ''; ?>" onclick="switchTab('register')">Crear Cuenta</button>
        </div>
        
        <?php 
            if(!empty($error)) echo "<p class='error'>$error</p>"; 
            if(!empty($success)) echo "<p class='msg-success'>$success</p>"; 
        ?>
        
        <div id="wrapper" class="forms-wrapper <?php echo $active_view == 'register' ? 'show-register' : ''; ?>">
            
            <div class="form-block">
                <form method="POST" action="">
                    <input type="hidden" name="action_login" value="1">
                    <input type="text" name="usuario" placeholder="Usuario" required>
                    <input type="password" name="password" placeholder="Contraseña" required>
                    <button type="submit">INGRESAR</button>
                </form>
            </div>
            
            <div class="form-block">
                <form method="POST" action="">
                    <input type="hidden" name="action_register" value="1">
                    <input type="text" name="reg_usuario" placeholder="Usuario (Letras y números)" required>
                    <input type="email" name="reg_email" placeholder="Correo (@dominio.com)" required>
                    <input type="password" name="reg_password" placeholder="Mínimo 6 caracteres y 1 número" required>
                    <button type="submit">CREAR CUENTA</button>
                </form>
            </div>

        </div>
    </div>
</div>

<?php include_once 'logs.php'; ?>

<script>
function switchTab(type) {
    const wrapper = document.getElementById('wrapper');
    const btnLogin = document.getElementById('btn-login');
    const btnRegister = document.getElementById('btn-register');

    if (type === 'register') {
        wrapper.classList.add('show-register');
        btnRegister.classList.add('active');
        btnLogin.classList.remove('active');
    } else {
        wrapper.classList.remove('show-register');
        btnLogin.classList.add('active');
        btnRegister.classList.remove('active');
    }
}
</script>

</body>
</html>