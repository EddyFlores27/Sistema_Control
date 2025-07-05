<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html"); // Ajusta la ruta si es necesario
    exit();
}

$rol_sesion = $_SESSION['user_role'] ?? 'user';
$nombre_sesion = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php'; // Asegúrate de que esta ruta sea correcta

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_personal'])) {
    $nombre_completo = mysqli_real_escape_string($conexion, trim($_POST['nombre_completo']));
    $email = mysqli_real_escape_string($conexion, trim($_POST['email']));
    $rol = mysqli_real_escape_string($conexion, $_POST['rol']);
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);

    // Validaciones
    if (empty($nombre_completo)) {
        $mensaje = "El nombre completo del personal es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (empty($email)) {
        $mensaje = "El correo electrónico es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $mensaje = "El formato del correo electrónico no es válido.";
        $tipo_mensaje = "error";
    } elseif (empty($rol)) {
        $mensaje = "Debe seleccionar un rol para el personal.";
        $tipo_mensaje = "error";
    } elseif (empty($estado)) {
        $mensaje = "Debe seleccionar un estado para el personal.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el correo electrónico ya existe
        $check_sql = "SELECT id_personal FROM personal WHERE email = '$email'";
        $check_result = mysqli_query($conexion, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $mensaje = "Ya existe un miembro del personal con ese correo electrónico.";
            $tipo_mensaje = "error";
        } else {
            // Insertar el nuevo personal
            $insert_sql = "INSERT INTO personal (nombre_completo, email, rol, estado) 
                           VALUES ('$nombre_completo', '$email', '$rol', '$estado')";
            
            if (mysqli_query($conexion, $insert_sql)) {
                $mensaje = "Miembro del personal registrado exitosamente.";
                $tipo_mensaje = "success";
                // Limpiar los campos después del registro exitoso
                $_POST = array(); // Esto reseteará el formulario
            } else {
                $mensaje = "Error al registrar el personal: " . mysqli_error($conexion);
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
    <title>Nuevo Personal | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="avatar"></div>
        <div class="admin-label">
            <i class="fas <?php echo $rol_sesion === 'gerente' ? 'fa-user-tie' : ($rol_sesion === 'admin' ? 'fa-user-cog' : 'fa-user'); ?>"></i>
            <?php echo ucfirst($rol_sesion); ?><br>
            <small><?php echo htmlspecialchars($nombre_sesion); ?></small>
        </div>

        <form action="../Sistema_Control/logout.php" method="post"> <button type="submit" class="logout">Cerrar sesión</button>
        </form>

        <div class="nav-buttons">
            <a href="dashboard.php">Inicio</a>
            <a href="proveedores.php">Proveedores</a>
            <a href="productos.php">Productos</a>
            <a href="personal.php">Personal</a> <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="main">
        <h1>
            <i class="fas fa-arrow-left" style="margin-right: 1rem; cursor: pointer;" onclick="window.location.href='personal.php'"></i>
            Registrar Nuevo Personal
        </h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $mensaje; ?>
                <?php if ($tipo_mensaje === 'success'): ?>
                    <br><br>
                    <a href="personal.php" class="btn btn-primary">Ver lista de personal</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre_completo">
                        <i class="fas fa-user"></i>
                        Nombre Completo *
                    </label>
                    <input 
                        type="text" 
                        id="nombre_completo" 
                        name="nombre_completo" 
                        placeholder="Ej: Juan Pérez Gómez"
                        value="<?php echo isset($_POST['nombre_completo']) ? htmlspecialchars($_POST['nombre_completo']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="email">
                        <i class="fas fa-envelope"></i>
                        Correo Electrónico *
                    </label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        placeholder="Ej: juan.perez@mass.com"
                        value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="rol">
                        <i class="fas fa-briefcase"></i>
                        Rol *
                    </label>
                    <select id="rol" name="rol" required>
                        <option value="">Selecciona el rol</option>
                        <option value="Admin" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Admin') ? 'selected' : ''; ?>>Admin</option>
                        <option value="Gerente" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Gerente') ? 'selected' : ''; ?>>Gerente</option>
                        <option value="Empleado" <?php echo (isset($_POST['rol']) && $_POST['rol'] === 'Empleado') ? 'selected' : ''; ?>>Empleado</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="estado">
                        <i class="fas fa-toggle-on"></i>
                        Estado *
                    </label>
                    <select id="estado" name="estado" required>
                        <option value="">Selecciona el estado</option>
                        <option value="activo" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'activo') ? 'selected' : ''; ?>>
                            Activo
                        </option>
                        <option value="inactivo" <?php echo (isset($_POST['estado']) && $_POST['estado'] === 'inactivo') ? 'selected' : ''; ?>>
                            Inactivo
                        </option>
                    </select>
                </div>

                <div class="form-actions">
                    <a href="personal.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" name="registrar_personal" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Registrar Personal
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
                <li>El correo electrónico debe ser único en el sistema</li>
                <li>Asegúrate de asignar el rol correcto al nuevo personal</li>
                <li>Puedes cambiar el estado del personal posteriormente desde la lista</li>
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
                // Excluir el botón de submit del chequeo de datos
                if (value.trim() !== '' && key !== 'registrar_personal') {
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
    </script>
</body>

</html>