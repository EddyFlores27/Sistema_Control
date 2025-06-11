<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

// Obtener el ID del usuario
$id = $_GET['id'] ?? 0;
$mensaje = '';
$tipo_mensaje = '';
$usuario = null;

if (!$id) {
    header("Location: personal.php");
    exit();
}

// Verificar que no se trate de eliminar al usuario actual
if ($id == $_SESSION['user_id']) {
    header("Location: personal.php?mensaje=No puedes eliminar tu propia cuenta&tipo=error");
    exit();
}

// Obtener datos del usuario para mostrar
$sql = "SELECT * FROM usuarios WHERE id = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($resultado);

if (!$usuario) {
    header("Location: personal.php");
    exit();
}

// Procesar la eliminación si se confirma
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirmar_eliminar'])) {
    // Verificar si es el último administrador
    if ($usuario['rol'] === 'admin') {
        $sql_check_admin = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'admin' AND id != ?";
        $stmt_check_admin = mysqli_prepare($conexion, $sql_check_admin);
        mysqli_stmt_bind_param($stmt_check_admin, "i", $id);
        mysqli_stmt_execute($stmt_check_admin);
        $result_check_admin = mysqli_stmt_get_result($stmt_check_admin);
        $otros_admins = mysqli_fetch_assoc($result_check_admin)['total'];

        if ($otros_admins == 0) {
            $mensaje = "No se puede eliminar el último administrador del sistema. Debe haber al menos un administrador activo.";
            $tipo_mensaje = "error";
        } else {
            // Eliminar el usuario
            $sql_delete = "DELETE FROM usuarios WHERE id = ?";
            $stmt_delete = mysqli_prepare($conexion, $sql_delete);
            mysqli_stmt_bind_param($stmt_delete, "i", $id);
            
            if (mysqli_stmt_execute($stmt_delete)) {
                header("Location: personal.php?mensaje=Usuario eliminado exitosamente&tipo=success");
                exit();
            } else {
                $mensaje = "Error al eliminar el usuario: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    } else {
        // Eliminar el usuario (no es admin)
        $sql_delete = "DELETE FROM usuarios WHERE id = ?";
        $stmt_delete = mysqli_prepare($conexion, $sql_delete);
        mysqli_stmt_bind_param($stmt_delete, "i", $id);
        
        if (mysqli_stmt_execute($stmt_delete)) {
            header("Location: personal.php?mensaje=Usuario eliminado exitosamente&tipo=success");
            exit();
        } else {
            $mensaje = "Error al eliminar el usuario: " . mysqli_error($conexion);
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
    <title>Eliminar Usuario | MASS</title>
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
        
        .user-info {
            background: #f3f4f6;
            padding: 1.5rem;
            border-radius: 8px;
            margin: 1.5rem 0;
        }
        
        .user-info h3 {
            margin-bottom: 1rem;
            color: #1f2937;
        }
        
        .user-detail {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .user-detail strong {
            color: #374151;
        }
        
        .role-badge {
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
            font-weight: 500;
        }
        
        .role-admin {
            background-color: #fee2e2;
            color: #991b1b;
        }
        
        .role-gerente {
            background-color: #fef3c7;
            color: #92400e;
        }
        
        .role-user {
            background-color: #dbeafe;
            color: #1e40af;
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
        <h1>Eliminar Usuario</h1>
        
        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <div class="warning-container">
                <i class="fas fa-exclamation-triangle"></i>
                <h2>¿Está seguro que desea eliminar este usuario?</h2>
                <p>Esta acción no se puede deshacer. Se eliminará permanentemente el usuario y perderá acceso al sistema.</p>
                <?php if ($usuario['rol'] === 'admin'): ?>
                    <p><strong>ADVERTENCIA:</strong> Está a punto de eliminar un usuario administrador. Asegúrese de que existan otros administradores en el sistema.</p>
                <?php endif; ?>
            </div>

            <div class="user-info">
                <h3>Información del Usuario a Eliminar:</h3>
                
                <div class="user-detail">
                    <strong>ID:</strong>
                    <span><?php echo htmlspecialchars($usuario['id']); ?></span>
                </div>
                
                <div class="user-detail">
                    <strong>Nombre:</strong>
                    <span><?php echo htmlspecialchars($usuario['nombre']); ?></span>
                </div>
                
                <div class="user-detail">
                    <strong>Email:</strong>
                    <span><?php echo htmlspecialchars($usuario['email']); ?></span>
                </div>
                
                <div class="user-detail">
                    <strong>Rol:</strong>
                    <span>
                        <span class="role-badge role-<?php echo $usuario['rol']; ?>">
                            <?php echo ucfirst(htmlspecialchars($usuario['rol'])); ?>
                        </span>
                    </span>
                </div>
                
                <?php if (isset($usuario['fecha_creacion'])): ?>
                <div class="user-detail">
                    <strong>Fecha de Creación:</strong>
                    <span><?php echo htmlspecialchars($usuario['fecha_creacion']); ?></span>
                </div>
                <?php endif; ?>
                
                <?php if (isset($usuario['ultimo_acceso']) && !empty($usuario['ultimo_acceso'])): ?>
                <div class="user-detail">
                    <strong>Último Acceso:</strong>
                    <span><?php echo htmlspecialchars($usuario['ultimo_acceso']); ?></span>
                </div>
                <?php endif; ?>
            </div>

            <form method="POST">
                <div class="form-actions">
                    <button type="submit" name="confirmar_eliminar" value="1" class="btn btn-danger" 
                            onclick="return confirm('¿ESTÁ COMPLETAMENTE SEGURO? Esta acción no se puede deshacer y el usuario perderá acceso al sistema.');">
                        <i class="fas fa-trash"></i> Sí, Eliminar Definitivamente
                    </button>
                    <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-secondary">
                        <i class="fas fa-edit"></i> Editar Usuario
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