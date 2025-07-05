<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

// Obtener parámetros del formulario
$rango = $_GET['rango'] ?? '';
$fecha_inicio = $_GET['fecha_inicio'] ?? '';
$fecha_fin = $_GET['fecha_fin'] ?? '';

$where = "";

// Prioridad: Si se seleccionan fechas personalizadas, se usan esas
if ($fecha_inicio && $fecha_fin) {
    $where = "DATE(fecha_hora) BETWEEN '$fecha_inicio' AND '$fecha_fin'";
} elseif ($rango) {
    switch ($rango) {
        case 'dia':
            $where = "DATE(fecha_hora) = CURDATE()";
            break;
        case 'semana':
            $where = "YEARWEEK(fecha_hora, 1) = YEARWEEK(CURDATE(), 1)";
            break;
        case 'mes':
            $where = "MONTH(fecha_hora) = MONTH(CURDATE()) AND YEAR(fecha_hora) = YEAR(CURDATE())";
            break;
    }
}

$sql = "SELECT * FROM productos";
if ($where) {
    $sql .= " WHERE $where";
}
$sql .= " ORDER BY fecha_hora DESC";

$result = mysqli_query($conexion, $sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        th, td {
            padding: 10px;
            border: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #f0f0f0;
        }
        .form-select, input[type="date"] {
            padding: 0.5rem;
            font-size: 1rem;
            border-radius: 6px;
            border: 1px solid #ccc;
            margin-right: 1rem;
        }
        .btn-primary {
            background-color: #5d20d3;
            color: white;
            border: none;
            padding: 0.6rem 1.2rem;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
        }
        .btn-primary:hover {
            background-color: #470eaa;
        }
        .report-header {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 1rem;
            margin-bottom: 1rem;
        }
    </style>
</head>

<body>
<div class="sidebar">
    <div class="avatar"></div>
    <div class="admin-label">
        <i class="fas <?php echo $rol === 'gerente' ? 'fa-user-tie' : ($rol === 'admin' ? 'fa-user-cog' : 'fa-user'); ?>"></i>
        <?php echo ucfirst($rol); ?><br />
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
        <a href="reportes.php" class="active">Reportes</a>
    </div>
</div>

<div class="main">
    <h1>Reportes de Productos</h1>

    <!-- Filtros -->
    <form method="GET" action="reportes.php" class="report-header">
        <select name="rango" class="form-select">
            <option value="">-- Día/Semana/Mes --</option>
            <option value="dia" <?php if ($rango == 'dia') echo 'selected'; ?>>Día</option>
            <option value="semana" <?php if ($rango == 'semana') echo 'selected'; ?>>Semana</option>
            <option value="mes" <?php if ($rango == 'mes') echo 'selected'; ?>>Mes</option>
        </select>

        <label>O elige un rango personalizado:</label>

        <input type="date" name="fecha_inicio" value="<?php echo $fecha_inicio; ?>">
        <input type="date" name="fecha_fin" value="<?php echo $fecha_fin; ?>">

        <button type="submit" class="btn-primary">Generar Reporte</button>
    </form>

    <!-- Título dinámico -->
    <?php if ($fecha_inicio && $fecha_fin): ?>
        <h2>Mostrando productos desde <em><?php echo $fecha_inicio; ?></em> hasta <em><?php echo $fecha_fin; ?></em></h2>
    <?php elseif ($rango): ?>
        <h2>Mostrando productos por: <em><?php echo ucfirst($rango); ?></em></h2>
    <?php endif; ?>

    <!-- Tabla -->
    <table>
        <thead>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Cantidad</th>
            <th>Precio</th>
            <th>Movimiento</th>
            <th>Fecha y Hora</th>
        </tr>
        </thead>
        <tbody>
        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                    <td><?php echo $row['cantidad']; ?></td>
                    <td>S/ <?php echo number_format($row['precio'], 2); ?></td>
                    <td><?php echo ucfirst($row['movimiento']); ?></td>
                    <td><?php echo $row['fecha_hora']; ?></td>
                </tr>
            <?php endwhile; ?>
        <?php else: ?>
            <tr>
                <td colspan="6" style="text-align:center; padding: 1rem;">
                    No se encontraron registros para el filtro seleccionado.
                </td>
            </tr>
        <?php endif; ?>
        </tbody>
    </table>
</div>
</body>
</html>
