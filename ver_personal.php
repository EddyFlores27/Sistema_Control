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

if (!$id_personal) {
    header("Location: personal.php"); // Redirige si no hay ID
    exit();
}

// Consultar datos del personal
$sql = "SELECT id_personal, nombre_completo, email, rol, estado, fecha_creacion FROM personal WHERE id_personal = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "i", $id_personal);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);
$personal = mysqli_fetch_assoc($resultado);

if (!$personal) {
    header("Location: personal.php"); // Redirige si el personal no existe
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ver Personal | MASS</title>
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
        <h1>Detalle del Personal</h1>
        
        <div class="form-container">
            <div class="form-group">
                <label><strong>ID:</strong></label>
                <p><?php echo htmlspecialchars($personal['id_personal']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Nombre Completo:</strong></label>
                <p><?php echo htmlspecialchars($personal['nombre_completo']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Correo Electrónico:</strong></label>
                <p><?php echo htmlspecialchars($personal['email']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Rol:</strong></label>
                <p><?php echo htmlspecialchars($personal['rol']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Estado:</strong></label>
                <p>
                    <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $personal['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                        <?php echo ucfirst(htmlspecialchars($personal['estado'])); ?>
                    </span>
                </p>
            </div>

            <div class="form-group">
                <label><strong>Fecha de Creación:</strong></label>
                <p><?php echo htmlspecialchars($personal['fecha_creacion']); ?></p>
            </div>

            <?php /*
            <?php if (!empty($personal['direccion'])): ?>
            <div class="form-group">
                <label><strong>Dirección:</strong></label>
                <p><?php echo htmlspecialchars($personal['direccion']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($personal['telefono'])): ?>
            <div class="form-group">
                <label><strong>Teléfono:</strong></label>
                <p><?php echo htmlspecialchars($personal['telefono']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($personal['descripcion'])): ?>
            <div class="form-group">
                <label><strong>Descripción:</strong></label>
                <p><?php echo nl2br(htmlspecialchars($personal['descripcion'])); ?></p>
            </div>
            <?php endif; ?>
            */ ?>

            <div class="form-actions">
                <a href="editar_personal.php?id=<?php echo $personal['id_personal']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>
                <a href="personal.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            </div>
        </div>
    </div>
</body>

</html>