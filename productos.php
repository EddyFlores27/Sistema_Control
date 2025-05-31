<?php
session_start(); 
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

$buscar = $_GET['buscar'] ?? '';
$sql = "SELECT * FROM productos 
        WHERE nombre LIKE '%$buscar%' 
        ORDER BY fecha_hora DESC";
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Productos | MASS</title>
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
        <h1>Gestión de Productos</h1>

        <div class="top-bar" style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
            <form method="GET" style="flex-grow: 1; margin-right: 1rem;">
                <input type="text" name="buscar" class="buscar" placeholder="Buscar por nombre..." value="<?php echo htmlspecialchars($buscar); ?>" style="padding: 0.5rem; width: 100%; max-width: 300px;">
            </form>
            <a href="nuevo_producto.php" class="nuevo-personal" style="background: #3b82f6; color: white; padding: 0.5rem 1rem; border-radius: 8px; text-decoration: none;">Nuevo Producto</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Movimiento</th>
                        <th>Fecha y Hora</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $contador = 1;
                    while ($fila = mysqli_fetch_assoc($resultado)): ?>
                        <tr>
                            <td><?php echo $contador++; ?></td>
                            <td><?php echo htmlspecialchars($fila['movimiento']); ?></td>
                            <td><?php echo htmlspecialchars($fila['fecha_hora']); ?></td>
                            <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                            <td>S/ <?php echo number_format($fila['precio'], 2); ?></td>
                            <td><?php echo htmlspecialchars($fila['cantidad']); ?></td>
                            <td>
                                <div class="action-dropdown">
                                    <button class="dropdown-btn">Acciones</button>
                                    <div class="dropdown-content">
                                        <a href="ver_producto.php?id=<?php echo $fila['id']; ?>">Ver</a>
                                        <a href="editar_producto.php?id=<?php echo $fila['id']; ?>">Editar</a>
                                        <a href="eliminar_producto.php?id=<?php echo $fila['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este producto?');">Eliminar</a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>
