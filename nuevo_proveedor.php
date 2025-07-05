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
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_proveedor'])) {
    $nombre_proveedor = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $contacto = mysqli_real_escape_string($conexion, trim($_POST['contacto']));
    $estado = mysqli_real_escape_string($conexion, $_POST['estado']);
    $fecha = date('Y-m-d');

    // Validaciones
    if (empty($nombre_proveedor)) {
        $mensaje = "El nombre del proveedor es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (empty($contacto)) {
        $mensaje = "La información de contacto es obligatoria.";
        $tipo_mensaje = "error";
    } elseif (empty($estado)) {
        $mensaje = "Debe seleccionar un estado.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el proveedor ya existe
        $check_sql = "SELECT id FROM proveedores WHERE nombre = '$nombre_proveedor'";
        $check_result = mysqli_query($conexion, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $mensaje = "Ya existe un proveedor con ese nombre.";
            $tipo_mensaje = "error";
        } else {
            // Insertar el nuevo proveedor
            $insert_sql = "INSERT INTO proveedores (nombre, contact_info, estado, fecha_creacion) 
                           VALUES ('$nombre_proveedor', '$contacto', '$estado', '$fecha')";
            
            if (mysqli_query($conexion, $insert_sql)) {
                $mensaje = "Proveedor registrado exitosamente.";
                $tipo_mensaje = "success";
                // Limpiar los campos después del registro exitoso
                $_POST = array();
            } else {
                $mensaje = "Error al registrar el proveedor: " . mysqli_error($conexion);
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
    <title>Nuevo Proveedor | MASS</title>
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
            <i class="fas fa-arrow-left" style="margin-right: 1rem; cursor: pointer;" onclick="window.location.href='proveedores.php'"></i>
            Registrar Nuevo Proveedor
        </h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $mensaje; ?>
                <?php if ($tipo_mensaje === 'success'): ?>
                    <br><br>
                    <a href="proveedores.php" class="btn btn-primary">Ver lista de proveedores</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-building"></i>
                        Nombre del Proveedor *
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        placeholder="Ej: Distribuidora ABC S.A.C."
                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="contacto">
                        <i class="fas fa-phone"></i>
                        Información de Contacto *
                    </label>
                    <textarea 
                        id="contacto" 
                        name="contacto" 
                        placeholder="Ej: Teléfono: +51 123 456 789&#10;Email: contacto@distribuidoraabc.com&#10;Dirección: Av. Principal 123, Lima"
                        required
                    ><?php echo isset($_POST['contacto']) ? htmlspecialchars($_POST['contacto']) : ''; ?></textarea>
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
                    <a href="proveedores.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" name="registrar_proveedor" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Registrar Proveedor
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
                <li>El nombre del proveedor debe ser único en el sistema</li>
                <li>Incluye información completa de contacto para facilitar la comunicación</li>
                <li>Puedes cambiar el estado del proveedor posteriormente desde la lista</li>
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
                if (value.trim() !== '' && key !== 'registrar_proveedor') {
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