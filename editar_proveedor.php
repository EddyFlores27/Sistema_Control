<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

// Obtener el ID del proveedor
$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

if (!$id) {
    header("Location: proveedores.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_proveedor = trim($_POST['nombre']);
    $contact_info = trim($_POST['contact_info']);
    $estado = $_POST['estado'];
    $direccion = trim($_POST['direccion'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $descripcion = trim($_POST['descripcion'] ?? '');

    // Validaciones básicas
    if (empty($nombre_proveedor) || empty($contact_info)) {
        $mensaje = "El nombre y la información de contacto son obligatorios.";
        $tipo_mensaje = "error";
    } else {
        // Actualizar proveedor
        $sql = "UPDATE proveedores SET nombre = ?, contact_info = ?, estado = ?, direccion = ?, telefono = ?, email = ?, descripcion = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "sssssssi", $nombre_proveedor, $contact_info, $estado, $direccion, $telefono, $email, $descripcion, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Proveedor actualizado exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar el proveedor: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
    }
}

// Obtener datos actuales del proveedor
$sql = "SELECT * FROM proveedores WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$proveedor = mysqli_fetch_assoc($resultado);

if (!$proveedor) {
    header("Location: proveedores.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Proveedor | MASS</title>
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
        <h1>Editar Proveedor</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre del Proveedor *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($proveedor['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="contact_info">Información de Contacto *</label>
                    <input type="text" id="contact_info" name="contact_info" value="<?php echo htmlspecialchars($proveedor['contact_info']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="direccion">Dirección</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($proveedor['direccion'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" value="<?php echo htmlspecialchars($proveedor['telefono'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($proveedor['email'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="estado">Estado</label>
                    <select id="estado" name="estado" required>
                        <option value="activo" <?php echo $proveedor['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                        <option value="inactivo" <?php echo $proveedor['estado'] === 'inactivo' ? 'selected' : ''; ?>>Inactivo</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="descripcion">Descripción</label>
                    <textarea id="descripcion" name="descripcion" rows="4"><?php echo htmlspecialchars($proveedor['descripcion'] ?? ''); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="ver_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="proveedores.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>