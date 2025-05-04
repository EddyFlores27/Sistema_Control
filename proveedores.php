<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Proveedores | MASS</title>
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
            <a href="usuarios.php">Usuarios</a>
            <a href="reportes.php">Reportes</a>
        </div>

    </div>

    <div class="main">
        <h1>Gestión de Proveedores</h1>
        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Movimiento</th>
                        <th>Fecha-Hora</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Cantidad</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Ejemplo de fila -->
                    <tr>
                        <td>1</td>
                        <td>Entrada</td>
                        <td>2025-05-03 14:32</td>
                        <td>Proveedor S.A.C.</td>
                        <td>S/ 250.00</td>
                        <td>10</td>
                        <td>
                            <div class="action-dropdown">
                                <button class="dropdown-btn">Acciones</button>
                                <div class="dropdown-content">
                                    <a href="#">Ver</a>
                                    <a href="#">Editar</a>
                                    <a href="#">Eliminar</a>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <!-- Puedes duplicar esta fila como plantilla para más registros -->
                </tbody>
            </table>
        </div>
    </div>
</body>

</html>