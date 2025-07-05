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
$personal = null;

if (!$id_personal) {
    header("Location: personal.php"); // Redirige si no hay ID
    exit();
}

// Obtener datos del personal para mostrar (siempre se necesita para cargar la página)
$sql_select = "SELECT id_personal, nombre_completo, email, rol, estado, fecha_creacion FROM personal WHERE id_personal = ?";
$stmt_select = mysqli_prepare($conexion, $sql_select);
mysqli_stmt_bind_param($stmt_select, "i", $id_personal);
mysqli_stmt_execute($stmt_select);
$resultado_select = mysqli_stmt_get_result($stmt_select);
$personal = mysqli_fetch_assoc($resultado_select);

if (!$personal) {
    header("Location: personal.php"); // Redirige si el personal no existe
    exit();
}

// Procesar la eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    // IMPORTANTE: Aquí deberías verificar si este personal tiene registros asociados
    // Por ejemplo, si es un usuario que ha realizado ventas, o si está asignado a tareas, etc.
    // Como en tu tabla 'personal' solo tienes los campos básicos, no hay FK que chequear por ahora.
    // Si en el futuro añades tablas que referencien 'id_personal', necesitarás una lógica similar a la de 'productos_asociados'

    // Eliminar el personal
    $sql_delete = "DELETE FROM personal WHERE id_personal = ?";
    $stmt_delete = mysqli_prepare($conexion, $sql_delete);
    mysqli_stmt_bind_param($stmt_delete, "i", $id_personal);
    
    if (mysqli_stmt_execute($stmt_delete)) {
        // Redirige al listado con un mensaje de éxito
        header("Location: personal.php?mensaje=Personal eliminado exitosamente&tipo=success");
        exit();
    } else {
        $mensaje = "Error al eliminar el personal: " . mysqli_error($conexion);
        $tipo_mensaje = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Personal | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css"> <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos específicos para esta página (similares a eliminar_proveedor) */
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
        
        .personal-info { /* Cambiado de .provider-info a .personal-info */
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .personal-info h3 { /* Cambiado */
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .personal-detail { /* Cambiado */
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .personal-detail strong { /* Cambiado */
            color: #374151;
        }
    </style>
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
        <h1>Eliminar Personal</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="warning-container">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>¿Está seguro que desea eliminar este miembro del personal?</h2>
                <p>Esta acción no se puede deshacer. Se eliminará permanentemente al personal y toda su información.</p>
            </div>

            <div class="personal-info">
                <h3>Información del Personal a Eliminar:</h3>
                
                <div class="personal-detail">
                    <strong>ID:</strong>
                    <span><?php echo htmlspecialchars($personal['id_personal']); ?></span>
                </div>
                
                <div class="personal-detail">
                    <strong>Nombre Completo:</strong>
                    <span><?php echo htmlspecialchars($personal['nombre_completo']); ?></span>
                </div>
                
                <div class="personal-detail">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($personal['email']); ?></span>
                </div>
                
                <div class="personal-detail">
                    <strong>Rol:</strong>
                    <span><?php echo htmlspecialchars($personal['rol']); ?></span>
                </div>
                
                <div class="personal-detail">
                    <strong>Estado:</strong>
                    <span>
                        <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $personal['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                            <?php echo ucfirst(htmlspecialchars($personal['estado'])); ?>
                        </span>
                    </span>
                </div>
                
                <div class="personal-detail">
                    <strong>Fecha de Creación:</strong>
                    <span><?php echo htmlspecialchars($personal['fecha_creacion']); ?></span>
                </div>
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirmar_eliminar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿ESTÁ COMPLETAMENTE SEGURO? Esta acción no se puede deshacer.');">
                        <i class="fas fa-trash"></i> Sí, Eliminar Definitivamente
                    </button>
                    <a href="ver_personal.php?id=<?php echo $personal['id_personal']; ?>" class="btn btn-secondary">
                        <i class="fas fa-eye"></i> Ver Detalles
                    </a>
                    <a href="personal.php" class="btn btn-primary">
                        <i class="fas fa-arrow-left"></i> Cancelar y Volver
                    </a>
                </div>
            </form>
        </div>
    </div>
</body>

</html>