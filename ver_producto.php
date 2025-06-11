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

if (!$id) {
    header("Location: productos.php");
    exit();
}

// Consultar datos del producto
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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Producto | MASS</title>
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
        <h1>Detalle del Producto</h1>
        
        <div class="form-container">
            <div class="form-group">
                <label><strong>ID:</strong></label>
                <p><?php echo htmlspecialchars($producto['id']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Nombre:</strong></label>
                <p><?php echo htmlspecialchars($producto['nombre']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Movimiento:</strong></label>
                <p><?php echo htmlspecialchars($producto['movimiento']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Precio:</strong></label>
                <p>S/ <?php echo number_format($producto['precio'], 2); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Cantidad:</strong></label>
                <p><?php echo htmlspecialchars($producto['cantidad']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Fecha y Hora:</strong></label>
                <p><?php echo htmlspecialchars($producto['fecha_hora']); ?></p>
            </div>

            <?php if (!empty($producto['categoria'])): ?>
            <div class="form-group">
                <label><strong>Categoría:</strong></label>
                <p><?php echo htmlspecialchars($producto['categoria']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($producto['proveedor_id'])): ?>
            <div class="form-group">
                <label><strong>Proveedor:</strong></label>
                <p>
                    <?php
                    // Obtener nombre del proveedor
                    $sql_proveedor = "SELECT nombre FROM proveedores WHERE id = ?";
                    $stmt_proveedor = mysqli_prepare($conexion, $sql_proveedor);
                    mysqli_stmt_bind_param($stmt_proveedor, "i", $producto['proveedor_id']);
                    mysqli_stmt_execute($stmt_proveedor);
                    $resultado_proveedor = mysqli_stmt_get_result($stmt_proveedor);
                    $proveedor = mysqli_fetch_assoc($resultado_proveedor);
                    echo $proveedor ? htmlspecialchars($proveedor['nombre']) : 'Proveedor no encontrado';
                    ?>
                </p>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="productos.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</body>

</html>