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

if (!$id) {
    header("Location: proveedores.php");
    exit();
}

// Consultar datos del proveedor
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
    <title>Ver Proveedor | MASS</title>
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
        <h1>Detalle del Proveedor</h1>
        
        <div class="form-container">
            <div class="form-group">
                <label><strong>ID:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['id']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Nombre:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['nombre']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Información de Contacto:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['contact_info']); ?></p>
            </div>

             <div class="form-group">
                <label><strong>LINEA1:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['contact_info']); ?></p>
            </div>

             <div class="form-group">
                <label><strong>LINEA2:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['contact_info']); ?></p>
            </div>

             <div class="form-group">
                <label><strong>LINEA3:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['contact_info']); ?></p>
            </div>

            <div class="form-group">
                <label><strong>Estado:</strong></label>
                <p>
                    <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $proveedor['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                        <?php echo ucfirst(htmlspecialchars($proveedor['estado'])); ?>
                    </span>
                </p>
            </div>

            <div class="form-group">
                <label><strong>Fecha de Creación:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['fecha_creacion']); ?></p>


            

            <?php if (!empty($proveedor['direccion'])): ?>
            <div class="form-group">
                <label><strong>Dirección:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['direccion']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($proveedor['telefono'])): ?>
            <div class="form-group">
                <label><strong>Teléfono:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['telefono']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($proveedor['email'])): ?>
            <div class="form-group">
                <label><strong>Email:</strong></label>
                <p><?php echo htmlspecialchars($proveedor['email']); ?></p>
            </div>
            <?php endif; ?>

            <?php if (!empty($proveedor['descripcion'])): ?>
            <div class="form-group">
                <label><strong>Descripción:</strong></label>
                <p><?php echo nl2br(htmlspecialchars($proveedor['descripcion'])); ?></p>
            </div>
            <?php endif; ?>

            <div class="form-actions">
                <a href="editar_proveedor.php?id=<?php echo $proveedor['id']; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar
                </a>

                <button style= "width:50px;
height 100px;
background: lightgreen; "> hola1</buttom>
            <button style= "width:50px;
            height 100px;
            background: yellow; "> hola2</buttom>

            </div>
                <a href="proveedores.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
            
            </div>
            </div>
        </div>
    </div>
</body>

</html>