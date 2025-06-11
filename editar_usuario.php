<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

// Obtener el ID del usuario
$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

if (!$id) {
    header("Location: personal.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_usuario = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $rol_usuario = $_POST['rol'];
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');

    // Validaciones básicas
    if (empty($nombre_usuario) || empty($email) || empty($rol_usuario)) {
        $mensaje = "El nombre, email y rol son obligatorios.";
        $tipo_mensaje = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del email no es válido.";
        $tipo_mensaje = "error";
    } elseif (!empty($password) && strlen($password) < 6) {
        $mensaje = "La contraseña debe tener al menos 6 caracteres.";
        $tipo_mensaje = "error";
    } elseif (!empty($password) && $password !== $confirm_password) {
        $mensaje = "Las contraseñas no coinciden.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el email ya existe (excluyendo el usuario actual)
        $check_sql = "SELECT id FROM usuarios WHERE email = ? AND id != ?";
        $stmt_check = mysqli_prepare($conexion, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "si", $email, $id);
        mysqli_stmt_execute($stmt_check);
        $result_check = mysqli_stmt_get_result($stmt_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $mensaje = "Ya existe otro usuario con ese email.";
            $tipo_mensaje = "error";
        } else {
            // Actualizar usuario
            if (!empty($password)) {
                // Actualizar con nueva contraseña
                $password_hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ?, password = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "ssssi", $nombre_usuario, $email, $rol_usuario, $password_hash, $id);
            } else {
                // Actualizar sin cambiar contraseña
                $sql = "UPDATE usuarios SET nombre = ?, email = ?, rol = ? WHERE id = ?";
                $stmt = mysqli_prepare($conexion, $sql);
                mysqli_stmt_bind_param($stmt, "sssi", $nombre_usuario, $email, $rol_usuario, $id);
            }
            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Usuario actualizado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar el usuario: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    }
}

// Obtener datos actuales del usuario
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

if (!$usuario) {
    header("Location: personal.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario | MASS</title>
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
            Editar Usuario
        </h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-user"></i>
                        Nombre del Usuario *
                    </label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($usuario['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Email *
                    </label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-user-tag"></i>
                        Rol *
                    </label>
                    <select id="rol" name="rol" required>
                        <option value="admin" <?php echo $usuario['rol'] === 'admin' ? 'selected' : ''; ?>>Administrador</option>
                        <option value="gerente" <?php echo $usuario['rol'] === 'gerente' ? 'selected' : ''; ?>>Gerente</option>
                        <option value="empleado" <?php echo $usuario['rol'] === 'empleado' ? 'selected' : ''; ?>>Empleado</option>
                        <option value="user" <?php echo $usuario['rol'] === 'user' ? 'selected' : ''; ?>>Usuario</option>
                    </select>
                </div>

                <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; margin: 1rem 0; border-left: 4px solid #fbbf24;">
                    <h4 style="color: #1f2937; margin-bottom: 0.5rem;">
                        <i class="fas fa-key"></i>
                        Cambiar Contraseña (Opcional)
                    </h4>
                    <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1rem;">
                        Deja estos campos vacíos si no deseas cambiar la contraseña
                    </p>

                    <div class="form-group">
                        <label for="password">
                            <i class="fas fa-lock"></i>
                            Nueva Contraseña
                        </label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            placeholder="Mínimo 8 caracteres (dejar vacío para no cambiar)"
                        >
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">
                            <i class="fas fa-lock"></i>
                            Confirmar Nueva Contraseña
                        </label>
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            placeholder="Repetir nueva contraseña"
                        >
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="personal.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1f2937; margin-bottom: 0.5rem;">
                <i class="fas fa-info-circle"></i>
                Información del Usuario
            </h3>
            <div style="color: #6b7280; margin-left: 1rem;">
                <p><strong>ID:</strong> <?php echo $usuario['id']; ?></p>
                <p><strong>Fecha de Registro:</strong> <?php echo $usuario['fecha_registro'] ?? 'No disponible'; ?></p>
                <p><strong>Último Acceso:</strong> <?php echo $usuario['ultimo_acceso'] ?? 'Nunca'; ?></p>
            </div>
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

        // Validar que si se llena una contraseña, se llenen ambas
        document.getElementById('password').addEventListener('input', function() {
            const password = this.value;
            const confirmPassword = document.getElementById('confirm_password');
            
            if (password !== '') {
                confirmPassword.required = true;
            } else {
                confirmPassword.required = false;
                confirmPassword.setCustomValidity('');
            }
        });
    </script>
</body>

</html>