<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

// Obtener el ID del producto
$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';

if (!$id) {
    header("Location: productos.php");
    exit();
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_producto = trim($_POST['nombre']);
    $movimiento = $_POST['movimiento'];
    $precio = floatval($_POST['precio']);
    $cantidad = intval($_POST['cantidad']);
    $categoria = trim($_POST['categoria'] ?? '');
    $proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : null;

    // Validaciones básicas
    if (empty($nombre_producto) || empty($movimiento) || $precio <= 0 || $cantidad < 0) {
        $mensaje = "El nombre, movimiento, precio (mayor a 0) y cantidad (mayor o igual a 0) son obligatorios.";
        $tipo_mensaje = "error";
    } else {
        // Actualizar producto
        $sql = "UPDATE productos SET nombre = ?, movimiento = ?, precio = ?, cantidad = ?, categoria = ?, proveedor_id = ? WHERE id = ?";
        $stmt = mysqli_prepare($conexion, $sql);
        mysqli_stmt_bind_param($stmt, "ssdiiii", $nombre_producto, $movimiento, $precio, $cantidad, $categoria, $proveedor_id, $id);
        
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Producto actualizado exitosamente.";
            $tipo_mensaje = "success";
        } else {
            $mensaje = "Error al actualizar el producto: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
    }
}

// Obtener datos actuales del producto
$sql = "SELECT * FROM productos WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    header("Location: productos.php");
    exit();
}

// Obtener lista de proveedores para el select
$sql_proveedores = "SELECT id, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre";
$resultado_proveedores = mysqli_query($conexion, $sql_proveedores);
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
                    <label for="movimiento">Tipo de Movimiento *</label>
                    <select id="movimiento" name="movimiento" required>
                        <option value="entrada" <?php echo $producto['movimiento'] === 'entrada' ? 'selected' : ''; ?>>Entrada</option>
                        <option value="salida" <?php echo $producto['movimiento'] === 'salida' ? 'selected' : ''; ?>>Salida</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="precio">Precio *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0.01" value="<?php echo $producto['precio']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="cantidad">Cantidad *</label>
                    <input type="number" id="cantidad" name="cantidad" min="0" value="<?php echo $producto['cantidad']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="categoria">Categoría</label>
                    <input type="text" id="categoria" name="categoria" value="<?php echo htmlspecialchars($producto['categoria'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="proveedor_id">Proveedor</label>
                    <select id="proveedor_id" name="proveedor_id">
                        <option value="">Seleccionar proveedor (opcional)</option>
                        <?php while ($proveedor = mysqli_fetch_assoc($resultado_proveedores)): ?>
                            <option value="<?php echo $proveedor['id']; ?>" <?php echo $producto['proveedor_id'] == $proveedor['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($proveedor['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
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
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>