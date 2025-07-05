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
$proveedor = null;

if (!$id) {
    header("Location: proveedores.php");
    exit();
}

// Obtener datos del proveedor para mostrar
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

// Procesar la eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    // Verificar si el proveedor tiene productos asociados
    $sql_check = "SELECT COUNT(*) as total FROM productos WHERE proveedor_id = ?";
    $stmt_check = mysqli_prepare($conexion, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "i", $id);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    $productos_asociados = mysqli_fetch_assoc($result_check)['total'];

    if ($productos_asociados > 0) {
        $mensaje = "No se puede eliminar el proveedor porque tiene $productos_asociados producto(s) asociado(s). Primero debe eliminar o reasignar los productos.";
        $tipo_mensaje = "error";
    } else {
        // Eliminar el proveedor
        $sql_delete = "DELETE FROM proveedores WHERE id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            header("Location: proveedores.php?mensaje=Proveedor eliminado exitosamente&tipo=success");
            exit();
        } else {
            $mensaje = "Error al eliminar el proveedor: " . mysqli_error($conexion);
            $tipo_mensaje = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Proveedor | MASS</title>
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
        
        .provider-info {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .provider-info h3 {
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .provider-detail {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .provider-detail strong {
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
        <h1>Eliminar Proveedor</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="warning-container">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>¿Está seguro que desea eliminar este proveedor?</h2>
                <p>Esta acción no se puede deshacer. Se eliminará permanentemente el proveedor y toda su información.</p>
            </div>

            <div class="provider-info">
                <h3>Información del Proveedor a Eliminar:</h3>
                
                <div class="provider-detail">
                    <strong>ID:</strong>
                    <span><?php echo htmlspecialchars($proveedor['id']); ?></span>
                </div>
                
                <div class="provider-detail">
                    <strong>Nombre:</strong>
                    <span><?php echo htmlspecialchars($proveedor['nombre']); ?></span>
                </div>
                
                <div class="provider-detail">
                    <strong>Contacto:</strong>
                    <span><?php echo htmlspecialchars($proveedor['contact_info']); ?></span>
                </div>
                
                <div class="provider-detail">
                    <strong>Estado:</strong>
                    <span>
                        <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $proveedor['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                            <?php echo ucfirst(htmlspecialchars($proveedor['estado'])); ?>
                        </span>
                    </span>
                </div>
                
                <div class="provider-detail">
                    <strong>Fecha de Creación:</strong>
                    <span><?php echo htmlspecialchars($proveedor['fecha_creacion']); ?></span>
                </div>
                
                
                <div class="provider-detail">
                    <strong>Nombre1:</strong>
                    <span>
                        <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $proveedor['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                            <?php echo ucfirst(htmlspecialchars($proveedor['estado'])); ?>
                        </span>
                    </span>
                </div>
                



                <?php if (!empty($proveedor['direccion'])): ?>
                <div class="provider-detail">
                    <strong>Dirección:</strong>
                    <span><?php echo htmlspecialchars($proveedor['direccion']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($proveedor['telefono'])): ?>
                <div class="provider-detail">
                    <strong>Teléfono:</strong>
                    <span><?php echo htmlspecialchars($proveedor['telefono']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($proveedor['email'])): ?>
                <div class="provider-detail">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($proveedor['email']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirmar_eliminar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿ESTÁ COMPLETAMENTE SEGURO? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Sí, Eliminar Definitivamente
                    </button>
                    <a href="ver_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="proveedores.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>