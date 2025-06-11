<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_usuario'])) {
    $nombre_completo = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $nombre_usuario = mysqli_real_escape_string($conexion, trim($_POST['usuario']));
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $password = mysqli_real_escape_string($conexion, $_POST['password']);
    $confirm_password = mysqli_real_escape_string($conexion, $_POST['confirm_password']);
    $rol_usuario = mysqli_real_escape_string($conexion, $_POST['rol']);
    $fecha = date('Y-m-d H:i:s');

    // Validaciones
    if (empty($nombre_completo)) {
        $mensaje = "El nombre completo es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (empty($nombre_usuario)) {
        $mensaje = "El nombre de usuario es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (empty($email)) {
        $mensaje = "El email es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del email no es válido.";
        $tipo_mensaje = "error";
    } elseif (empty($password)) {
        $mensaje = "La contraseña es obligatoria.";
        $tipo_mensaje = "error";
    } elseif (strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } elseif ($password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } elseif (empty($rol_usuario)) {
        $mensaje = "Debe seleccionar un rol.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el email o nombre de usuario ya existen
        $check_sql = "SELECT id FROM usuarios WHERE email = '$email' OR usuario = '$nombre_usuario'";
        $check_result = mysqli_query($conexion, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $existing_user = mysqli_fetch_assoc($check_result);
            $check_sql2 = "SELECT email, usuario FROM usuarios WHERE id = ".$existing_user['id'];
            $check_result2 = mysqli_query($conexion, $check_sql2);
            $user_data = mysqli_fetch_assoc($check_result2);
            
            if ($user_data['email'] === $email) {
                $mensaje = "Ya existe un usuario con ese email.";
            } else {
                $mensaje = "El nombre de usuario ya está en uso.";
            }
            $tipo_mensaje = "error";
        } else {
            // Encriptar la contraseña
            $password_hash = password_hash($password, PASSWORD_DEFAULT);
            
            // Insertar el nuevo usuario
            $insert_sql = "INSERT INTO usuarios (nombre, usuario, email, password, rol, fecha_registro) 
                           VALUES ('$nombre_completo', '$nombre_usuario', '$email', '$password_hash', '$rol_usuario', '$fecha')";
            
            if (mysqli_query($conexion, $insert_sql)) {
                $mensaje = "Usuario registrado exitosamente.";
                $tipo_mensaje = "success";
                // Limpiar los campos después del registro exitoso
                $_POST = array();
            } else {
                $error_msg = mysqli_error($conexion);
                error_log("Error al registrar usuario: " . $error_msg);
                $mensaje = "Error al registrar el usuario. Detalles técnicos: " . $error_msg;
                $tipo_mensaje = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="avatar"></div>
        <div class="admin-label">
            <i class="fas <?php echo $rol === 'gerente' ? 'fa-user-tie' : ($rol === 'admin' ? 'fa-user-cog' : 'fa-user'); ?>"></i>
            <?php echo ucfirst($rol); ?><br>
            <small><?php echo htmlspecialchars($nombre); ?></small>
        </div>

        <form action="../Sistema_Control/logout.php" method="post">
            <button type="submit" class="logout">Cerrar sesión</button>
        </form>

        <div class="nav-buttons">
            <a href="dashboard.php">Inicio</a>
            <a href="proveedores.php">Proveedores</a>
            <a href="productos.php">Productos</a>
            <a href="personal.php">Usuarios</a>
            <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="main">
        <h1>
            <i class="fas fa-arrow-left" style="margin-right: 1rem; cursor: pointer;" onclick="window.location.href='personal.php'"></i>
            Registrar Nuevo Usuario
        </h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $mensaje; ?>
                <?php if ($tipo_mensaje === 'success'): ?>
                    <br><br>
                    <a href="personal.php" class="btn btn-primary">Ver lista de usuarios</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-user"></i>
                        Nombre Completo *
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        placeholder="Ej: Juan Pérez"
                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="usuario">
                        <i class="fas fa-user-circle"></i>
                        Nombre de Usuario *
                    </label>
                    <input 
                        type="text" 
                        id="usuario" 
                        name="usuario" 
                        placeholder="Ej: jperez"
                        value="<?php echo isset($_POST['usuario']) ? htmlspecialchars($_POST['usuario']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Ej: usuario@empresa.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="password">
                        <i class="fas fa-lock"></i>
                        Contraseña *
                    </label>
                    <input 
                        type="password" 
                        id="password" 
                        name="password" 
                        placeholder="Mínimo 6 caracteres"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="confirm_password">
                        <i class="fas fa-lock"></i>
                        Confirmar Contraseña *
                    </label>
                    <input 
                        type="password" 
                        id="confirm_password" 
                        name="confirm_password" 
                        placeholder="Repetir contraseña"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-user-tag"></i>
                        Rol *
                    </label>
                    <select id="rol" name="rol" required>
                        <option value="">Selecciona el rol</option>
                        <option value="admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'admin') ? 'selected' : ''; ?>>
                            Administrador
                        </option>
                        <option value="gerente" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'gerente') ? 'selected' : ''; ?>>
                            Gerente
                        </option>
                        <option value="user" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'user') ? 'selected' : ''; ?>>
                            Usuario
                        </option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="personal.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" name="registrar_usuario" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Registrar Usuario
                    </button>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1f2937; margin-bottom: 0.5rem;">
                <i class="fas fa-info-circle"></i>
                Información importante
            </h3>
            <ul style="color: #6b7280; margin-left: 1rem;">
                <li>Los campos marcados con (*) son obligatorios</li>
                <li>El email y nombre de usuario deben ser únicos en el sistema</li>
                <li>La contraseña debe tener al menos 6 caracteres</li>
                <li>Los roles determinan los permisos de acceso del usuario</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-ocultar mensaje de éxito después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            }
        });

        // Confirmar antes de cancelar si hay datos en el formulario
        document.querySelector('.btn-secondary').addEventListener('click', function(e) {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            let hasData = false;
            
            for (let [key, value] of formData.entries()) {
                if (value.trim() !== '' && key !== 'registrar_usuario') {
                    hasData = true;
                    break;
                }
            }
            
            if (hasData) {
                if (!confirm('¿Estás seguro de que deseas cancelar? Se perderán los datos ingresados.')) {
                    e.preventDefault();
                }
            }
        });

        // Validación de contraseñas en tiempo real
        document.getElementById('confirm_password').addEventListener('input', function() {
            const password = document.getElementById('password').value;
            const confirmPassword = this.value;
            
            if (confirmPassword !== '' && password !== confirmPassword) {
                this.setCustomValidity('Las contraseñas no coinciden');
            } else {
                this.setCustomValidity('');
            }
        });
    </script>
</body>

</html>