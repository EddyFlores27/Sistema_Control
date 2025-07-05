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
$sql = "SELECT * FROM proveedores 
        WHERE nombre LIKE '%$buscar%' OR contact_info LIKE '%$buscar%' 
        ORDER BY fecha_creacion DESC";
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
            <a href="personal.php">Usuarios</a>
            <a href="reportes.php">Reportes</a>
        </div>
    </div>

    <div class="main">
        <h1>Gestión de Proveedores</h1>

        <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <form method="GET" style="flex-grow: 1; margin-right: 1rem;">
                <input type="text" name="buscar" class="buscar" placeholder="Buscar por nombre o contacto..." value="<?php echo htmlspecialchars($buscar); ?>">
            </form>
            <a href="nuevo_proveedor.php" class="nuevo-personal">Nuevo Proveedor</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Fecha de Creación</th>
                        <th>Nombre</th>
                        <th>Contacto</th>
                        <th>Estado</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $contador = 1;
                    if (mysqli_num_rows($resultado) > 0) {
                        while ($fila = mysqli_fetch_assoc($resultado)): ?>
                            <tr>
                                <td><?php echo $contador++; ?></td>
                                <td><?php echo htmlspecialchars($fila['fecha_creacion']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                <td><?php echo htmlspecialchars($fila['contact_info']); ?></td>
                                <td>
                                    <span style="padding: 0.25rem 0.5rem; border-radius: 12px; font-size: 0.875rem; <?php echo $fila['estado'] === 'activo' ? 'background-color: #dcfce7; color: #166534;' : 'background-color: #fee2e2; color: #991b1b;'; ?>">
                                        <?php echo ucfirst(htmlspecialchars($fila['estado'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <div class="action-dropdown">
                                        <button class="dropdown-btn">Acciones</button>
                                        <div class="dropdown-content">
                                            <a href="ver_proveedor.php?id=<?php echo $fila['id']; ?>">Ver</a>
                                            <a href="editar_proveedor.php?id=<?php echo $fila['id']; ?>">Editar</a>
                                            <a href="eliminar_proveedor.php?id=<?php echo $fila['id']; ?>" onclick="return confirm('¿Seguro que deseas eliminar este proveedor?');">Eliminar</a>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile;
                    } else { ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <?php echo empty($buscar) ? 'No hay proveedores registrados' : 'No se encontraron proveedores que coincidan con la búsqueda'; ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>
    <script>
document.addEventListener('DOMContentLoaded', function () {
    const dropdownBtns = document.querySelectorAll('.dropdown-btn');

    dropdownBtns.forEach(function (btn) {
        const menu = btn.nextElementSibling;

        btn.addEventListener('click', function (e) {
            e.stopPropagation();

            // Cierra todos los menús abiertos
            document.querySelectorAll('.dropdown-content').forEach(function (dropdown) {
                if (dropdown !== menu) dropdown.classList.remove('show');
            });

            // Alterna el menú actual
            if (menu) {
                menu.classList.toggle('show');
            }
        });
    });

    // Cierra todos los dropdowns si se hace clic fuera
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-content').forEach(function (menu) {
            menu.classList.remove('show');
        });
    });
});
</script>

</body>

</html>