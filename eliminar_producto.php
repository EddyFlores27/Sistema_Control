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
$producto = null;

if (!$id) {
    header("Location: productos.php");
    exit();
}

// Obtener datos del producto para mostrar
$sql = "SELECT p.*, pr.nombre as proveedor_nombre FROM productos p 
        LEFT JOIN proveedores pr ON p.proveedor_id = pr.id 
        WHERE p.id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$producto = mysqli_fetch_assoc($resultado);

if (!$producto) {
    header("Location: productos.php");
    exit();
}

// Procesar la eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    // Eliminar el producto
    $sql_delete = "DELETE FROM productos WHERE id = ?";
    $stmt_delete = mysqli_prepare($conexion, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        header("Location: productos.php?mensaje=Producto eliminado exitosamente&tipo=success");
        exit();
    } else {
        $mensaje = "Error al eliminar el producto: " . mysqli_error($conexion);
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Producto | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .warning-container {
            background: #fef3c7;
            border: 1px solid #fde68a;
            color: #92400e;
            padding: 2rem;
            border-radius: 12px;
            margin-bottom: 2rem;
            text-align: center;
        }
        
        .warning-container i {
            font-size: 3rem;
            margin-bottom: 1rem;
            display: block;
        }
        
        .product-info {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .product-info h3 {
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .product-detail {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .product-detail strong {
            color: #374151;
        }
    </style>
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
        <h1>Eliminar Producto</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="warning-container">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>¿Está seguro que desea eliminar este producto?</h2>
                <p>Esta acción no se puede deshacer. Se eliminará permanentemente el producto y toda su información.</p>
            </div>

            <div class="product-info">
                <h3>Información del Producto a Eliminar:</h3>
                
                <div class="product-detail">
                    <strong>ID:</strong>
                    <span><?php echo htmlspecialchars($producto['id']); ?></span>
                </div>
                
                <div class="product-detail">
                    <strong>Nombre:</strong>
                    <span><?php echo htmlspecialchars($producto['nombre']); ?></span>
                </div>
                
                <div class="product-detail">
                    <strong>Movimiento:</strong>
                    <span>
                        <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $producto['movimiento'] === 'entrada' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                            <?php echo ucfirst(htmlspecialchars($producto['movimiento'])); ?>
                        </span>
                    </span>
                </div>
                
                <div class="product-detail">
                    <strong>Precio:</strong>
                    <span>S/ <?php echo number_format($producto['precio'], 2); ?></span>
                </div>
                
                <div class="product-detail">
                    <strong>Cantidad:</strong>
                    <span><?php echo htmlspecialchars($producto['cantidad']); ?></span>
                </div>
                
                <div class="product-detail">
                    <strong>Fecha y Hora:</strong>
                    <span><?php echo htmlspecialchars($producto['fecha_hora']); ?></span>
                </div>
                
                <?php if (!empty($producto['categoria'])): ?>
                <div class="product-detail">
                    <strong>Categoría:</strong>
                    <span><?php echo htmlspecialchars($producto['categoria']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($producto['proveedor_nombre'])): ?>
                <div class="product-detail">
                    <strong>Proveedor:</strong>
                    <span><?php echo htmlspecialchars($producto['proveedor_nombre']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirmar_eliminar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿ESTÁ COMPLETAMENTE SEGURO? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Sí, Eliminar Definitivamente
                    </button>
                    <a href="ver_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="productos.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>