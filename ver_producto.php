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
        <h1>Detalle del Producto</h1>
        
        <div class="form-container">
            <div class="form-group">
                <label><strong>ID del Producto:</strong></label>
                <p><?php echo htmlspecialchars($producto['id']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Nombre del Producto:</strong></label>
                <p><?php echo htmlspecialchars($producto['nombre']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Cantidad en Stock:</strong></label>
                <p><?php echo htmlspecialchars($producto['cantidad']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Precio de Referencia:</strong></label>
                <p>S/ <?php echo number_format(htmlspecialchars($producto['precio']), 2); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Fecha de Registro:</strong></label>
                <p><?php echo htmlspecialchars($producto['fecha_hora']); ?></p>
            </div>

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