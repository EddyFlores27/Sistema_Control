<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html"); // Ajusta la ruta si es necesario
    exit();
}

$rol_sesion = $_SESSION['user_role'] ?? 'user';
$nombre_sesion = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php'; // Asegúrate de que esta ruta sea correcta

// Obtener el ID del personal
$id_personal = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

if (!$id_personal) {
    header("Location: personal.php"); // Redirige si no hay ID
    exit();
}

// Obtener datos actuales del personal (siempre se necesita para cargar el formulario)
$sql_select = "SELECT id_personal, nombre_completo, email, rol, estado FROM personal WHERE id_personal = ?";
$stmt_select = mysqli_prepare($conexion, $sql_select);
mysqli_stmt_bind_param($stmt_select, "i", $id_personal);
mysqli_stmt_execute($stmt_select);
$resultado_select = mysqli_stmt_get_result($stmt_select);
$personal = mysqli_fetch_assoc($resultado_select);

if (!$personal) {
    header("Location: personal.php"); // Redirige si el personal no existe
    exit();
}

// Procesar el formulario cuando se envía (después de obtener los datos actuales)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_completo = trim($_POST['nombre_completo']);
    $email = trim($_POST['email']);
    $rol = $_POST['rol'];
    $estado = $_POST['estado'];

    // Validaciones básicas
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
        // Verificar si el nuevo email ya existe en otro registro
        $check_email_sql = "SELECT id_personal FROM personal WHERE email = ? AND id_personal != ?";
        $stmt_check_email = mysqli_prepare($conexion, $check_email_sql);
        mysqli_stmt_bind_param($stmt_check_email, "si", $email, $id_personal);
        mysqli_stmt_execute($stmt_check_email);
        $check_email_result = mysqli_stmt_get_result($stmt_check_email);

        if (mysqli_num_rows($check_email_result) > 0) {
            $mensaje = "El correo electrónico ya está siendo usado por otro miembro del personal.";
            $tipo_mensaje = "error";
        } else {
            // Actualizar personal
            $sql_update = "UPDATE personal SET nombre_completo = ?, email = ?, rol = ?, estado = ? WHERE id_personal = ?";
            $stmt_update = mysqli_prepare($conexion, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "ssssi", $nombre_completo, $email, $rol, $estado, $id_personal);
            
            if (mysqli_stmt_execute($stmt_update)) {
                $mensaje = "Personal actualizado exitosamente.";
                $tipo_mensaje = "success";
                // Volver a cargar los datos para que el formulario muestre los cambios
                $sql_select = "SELECT id_personal, nombre_completo, email, rol, estado FROM personal WHERE id_personal = ?";
                $stmt_select = mysqli_prepare($conexion, $sql_select);
                mysqli_stmt_bind_param($stmt_select, "i", $id_personal);
                mysqli_stmt_execute($stmt_select);
                $resultado_select = mysqli_stmt_get_result($stmt_select);
                $personal = mysqli_fetch_assoc($resultado_select);
            } else {
                $mensaje = "Error al actualizar el personal: " . mysqli_error($conexion);
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
    <title>Editar Personal | MASS</title>
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
            <a href="personal.php">Personal</a>
            <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="main">
        <h1>Editar Personal</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nombre_completo">Nombre Completo *</label>
                    <input type="text" id="nombre_completo" name="nombre_completo" value="<?php echo htmlspecialchars($personal['nombre_completo']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Correo Electrónico *</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($personal['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="rol">Rol *</label>
                    <select id="rol" name="rol" required>
                        <option value="">Selecciona el rol</option>
                        <option value="Admin" <?php echo $personal['rol'] === 'Admin' ? 'selected' : ''; ?>>Admin</option>
                        <option value="Gerente" <?php echo $personal['rol'] === 'Gerente' ? 'selected' : ''; ?>>Gerente</option>
                        <option value="Empleado" <?php echo $personal['rol'] === 'Empleado' ? 'selected' : ''; ?>>Empleado</option>
                        </select>
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?php echo $personal['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $personal['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="ver_personal.php?id=<?php echo $personal['id_personal']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="personal.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>