<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php'; // Asegúrate de que esta ruta es correcta

$buscar = $_GET['buscar'] ?? '';
// Adaptar la consulta SQL para la tabla 'productos'
$sql = "SELECT id, nombre, cantidad, precio, fecha_hora, movimiento FROM productos 
        WHERE nombre LIKE '%$buscar%' OR movimiento LIKE '%$buscar%'
        ORDER BY fecha_hora DESC";
$resultado = mysqli_query($conexion, $sql);

if (!$resultado) {
    die("Error en la consulta: " . mysqli_error($conexion));
}

// Mensajes de éxito o error que vienen de otras páginas (ej. nuevo_producto.php, editar_producto.php, eliminar_producto.php)
$mensaje = $_GET['mensaje'] ?? '';
$tipo_mensaje = $_GET['tipo'] ?? '';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Productos | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        /* Estilos adicionales para los estados de cantidad si quieres resaltarlos */
        .cantidad-baja {
            background-color: #fef3c7; /* amarillo claro */
            color: #92400e; /* naranja oscuro */
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
        }
        .cantidad-ok {
            background-color: #dcfce7; /* verde claro */
            color: #166534; /* verde oscuro */
            padding: 0.25rem 0.5rem;
            border-radius: 12px;
            font-size: 0.875rem;
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
        <h1>Gestión de Productos</h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje === 'success' ? 'success' : 'error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="top-bar" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
            <form method="GET" style="flex-grow: 1; margin-right: 1rem;">
                <input type="text" name="buscar" class="buscar" placeholder="Buscar por nombre de producto..." value="<?php echo htmlspecialchars($buscar); ?>">
            </form>
            <a href="nuevo_producto.php" class="nuevo-personal">Nuevo Producto</a>
        </div>

        <div class="table-container">
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>ID Producto</th>
                        <th>Fecha Registro</th>
                        <th>Nombre</th>
                        <th>Cantidad</th>
                        <th>Precio Ref.</th>
                        <th>Movimiento</th>
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
                                <td><?php echo htmlspecialchars($fila['id']); ?></td>
                                <td><?php echo htmlspecialchars($fila['fecha_hora']); ?></td>
                                <td><?php echo htmlspecialchars($fila['nombre']); ?></td>
                                <td>
                                    <?php 
                                        $cantidad_clase = ($fila['cantidad'] < 10) ? 'cantidad-baja' : 'cantidad-ok';
                                    ?>
                                    <span class="<?php echo $cantidad_clase; ?>">
                                        <?php echo htmlspecialchars($fila['cantidad']); ?>
                                    </span>
                                </td>
                                <td>S/ <?php echo number_format(htmlspecialchars($fila['precio']), 2); ?></td>
                                <td><?php echo ucfirst($fila['movimiento']); ?></td>
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
                        <?php endwhile;
                    } else { ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                <?php echo empty($buscar) ? 'No hay productos registrados' : 'No se encontraron productos que coincidan con la búsqueda'; ?>
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

    // Cerrar el dropdown si haces clic fuera
    document.addEventListener('click', function () {
        document.querySelectorAll('.dropdown-content').forEach(function (menu) {
            menu.classList.remove('show');
        });
    });
});
</script>


</body>

</html>