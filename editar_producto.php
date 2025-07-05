<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre_usuario = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php'; // Asegúrate de que esta ruta es correcta

// Obtener el ID del producto
$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

if (!$id) {
    header("Location: productos.php"); // Redirigir si no hay ID
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = trim($_POST['nombre']);
    $cantidad = trim($_POST['cantidad']);
    $movimiento = $_POST['movimiento'] ?? 'entrada';
    $precio = trim($_POST['precio'] ?? ''); // Puede ser opcional, por eso '??'

    // Validaciones básicas
    if (empty($nombre_producto)) {
        $mensaje = "El nombre del producto es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (!is_numeric($cantidad) || $cantidad < 0) {
        $mensaje = "La cantidad debe ser un número positivo.";
        $tipo_mensaje = "error";
    } elseif (!empty($precio) && (!is_numeric($precio) || $precio < 0)) {
        $mensaje = "El precio debe ser un número positivo (opcional).";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el nombre ya existe para otro producto (evitar duplicados)
        $check_sql = "SELECT id FROM productos WHERE nombre = ? AND id != ?";
        $stmt_check = mysqli_prepare($conexion, $check_sql);
        mysqli_stmt_bind_param($stmt_check, "si", $nombre_producto, $id);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_store_result($stmt_check);
        
        if (mysqli_stmt_num_rows($stmt_check) > 0) {
            $mensaje = "Ya existe otro producto con ese nombre.";
            $tipo_mensaje = "error";
        } else {
            $sql = "UPDATE productos SET nombre = ?, cantidad = ?, precio = ?, movimiento = ? WHERE id = ?";
            $stmt = mysqli_prepare($conexion, $sql);
            mysqli_stmt_bind_param($stmt, "sidss", $nombre_producto, $cantidad, $precio, $movimiento, $id);

            
            if (mysqli_stmt_execute($stmt)) {
                $mensaje = "Producto actualizado exitosamente.";
                $tipo_mensaje = "success";
            } else {
                $mensaje = "Error al actualizar el producto: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    }
}

// Obtener datos actuales del producto para mostrar en el formulario
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    header("Location: productos.php"); // Redirigir si el producto no existe
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="avatar"></div>
        <div class="admin-label">
            <i class="fas <?php echo $rol === 'gerente' ? 'fa-user-tie' : ($rol === 'admin' ? 'fa-user-cog' : 'fa-user'); ?>"></i>
            <?php echo ucfirst($rol); ?><br>
            <small><?php echo htmlspecialchars($nombre_usuario); ?></small>
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
        <h1>Editar Producto</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST">
                <div class="form-group">
                    <label for="nombre">Nombre del Producto *</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad en Stock *</label>
                    <input type="number" id="cantidad" name="cantidad" value="<?php echo htmlspecialchars($producto['cantidad']); ?>" min="0" required>
                </div>

                <div class="form-group">
                    <label for="precio">Precio de Referencia (Opcional)</label>
                    <input type="number" id="precio" name="precio" step="0.01" value="<?php echo htmlspecialchars($producto['precio'] ?? ''); ?>" min="0">
                </div>
                <div class="form-group">
                    <label for="movimiento"><strong>Movimiento:</strong></label>
                    <select name="movimiento" id="movimiento" required>
                    <option value="entrada" <?php if ($producto['movimiento'] === 'entrada') echo 'selected'; ?>>Entrada</option>
                    <option value="salida" <?php if ($producto['movimiento'] === 'salida') echo 'selected'; ?>>Salida</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="ver_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="productos.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Regresar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>