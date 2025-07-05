<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre_usuario = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php'; // Asegúrate de que esta ruta es correcta

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_producto'])) {
    $nombre_producto = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $cantidad = mysqli_real_escape_string($conexion, trim($_POST['cantidad']));
    $precio = mysqli_real_escape_string($conexion, trim($_POST['precio'])); // Opcional, puedes quitarlo si no lo usas
    $fecha_registro = date('Y-m-d H:i:s'); // Usamos fecha y hora actual

    // Validaciones
    if (empty($nombre_producto)) {
        $mensaje = "El nombre del producto es obligatorio.";
        $tipo_mensaje = "error";
    } elseif (!is_numeric($cantidad) || $cantidad < 0) {
        $mensaje = "La cantidad debe ser un número positivo.";
        $tipo_mensaje = "error";
    } elseif (!empty($precio) && (!is_numeric($precio) || $precio < 0)) { // Validar precio si se ingresa
        $mensaje = "El precio debe ser un número positivo (opcional).";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el producto ya existe por nombre
        $check_sql = "SELECT id FROM productos WHERE nombre = '$nombre_producto'";
        $check_result = mysqli_query($conexion, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $mensaje = "Ya existe un producto con ese nombre.";
            $tipo_mensaje = "error";
        } else {
            // Insertar el nuevo producto
            $insert_sql = "INSERT INTO productos (nombre, cantidad, precio, fecha_hora) 
                           VALUES ('$nombre_producto', '$cantidad', '$precio', '$fecha_registro')";
            
            if (mysqli_query($conexion, $insert_sql)) {
                $mensaje = "Producto registrado exitosamente.";
                $tipo_mensaje = "success";
                // Limpiar los campos después del registro exitoso
                $_POST = array(); // Esto vacía los campos del formulario
            } else {
                $mensaje = "Error al registrar el producto: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Producto | MASS</title>
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>

<body>
    <div class="sidebar">
        <div class="avatar"></div>
        <div class="admin-label">
            <i class="fas <?php echo $rol === 'gerente' ? 'fa-user-tie' : ($rol === 'admin' ? 'fa-user-cog' : 'fa-user'); ?>"></i>
            <?php echo ucfirst($rol); ?><br>
            <small><?php echo htmlspecialchars($nombre_usuario); ?></small>
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
        <h1>
            <i class="fas fa-arrow-left" style="margin-right: 1rem; cursor: pointer;" onclick="window.location.href='productos.php'"></i>
            Registrar Nuevo Producto
        </h1>

        <?php if (!empty($mensaje)): ?>
            <div class="alert alert-<?php echo $tipo_mensaje; ?>">
                <i class="fas <?php echo $tipo_mensaje === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle'; ?>"></i>
                <?php echo $mensaje; ?>
                <?php if ($tipo_mensaje === 'success'): ?>
                    <br><br>
                    <a href="productos.php" class="btn btn-primary">Ver lista de productos</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="nombre">
                        <i class="fas fa-box"></i>
                        Nombre del Producto *
                    </label>
                    <input 
                        type="text" 
                        id="nombre" 
                        name="nombre" 
                        placeholder="Ej: Agua embotellada 500ml"
                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="cantidad">
                        <i class="fas fa-sort-numeric-up"></i>
                        Cantidad en Stock *
                    </label>
                    <input 
                        type="number" 
                        id="cantidad" 
                        name="cantidad" 
                        placeholder="Ej: 100"
                        value="<?php echo isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : ''; ?>"
                        min="0"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="precio">
                        <i class="fas fa-money-bill-alt"></i>
                        Precio de Referencia (Opcional)
                    </label>
                    <input 
                        type="number" 
                        id="precio" 
                        name="precio" 
                        step="0.01" 
                        placeholder="Ej: 1.50"
                        value="<?php echo isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : ''; ?>"
                        min="0"
                    >
                </div>
                
                <div class="form-actions">
                    <a href="productos.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        Cancelar
                    </a>
                    <button type="submit" name="registrar_producto" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        Registrar Producto
                    </button>
                </div>
            </form>
        </div>

        <div style="margin-top: 2rem; padding: 1rem; background: #f8fafc; border-radius: 8px; border-left: 4px solid #3b82f6;">
            <h3 style="color: #1f2937; margin-bottom: 0.5rem;">
                <i class="fas fa-info-circle"></i>
                Información importante
            </h3>
            <ul style="color: #6b7280; margin-left: 1rem;">
                <li>Los campos marcados con (*) son obligatorios</li>
                <li>El nombre del producto debe ser único en el sistema</li>
                <li>La cantidad en stock debe ser un número no negativo</li>
                <li>El precio de referencia es opcional y puede incluir decimales</li>
            </ul>
        </div>
    </div>

    <script>
        // Auto-ocultar mensaje de éxito después de 5 segundos
        document.addEventListener('DOMContentLoaded', function() {
            const alert = document.querySelector('.alert-success');
            if (alert) {
                setTimeout(function() {
                    alert.style.transition = 'opacity 0.5s';
                    alert.style.opacity = '0';
                    setTimeout(function() {
                        alert.remove();
                    }, 500);
                }, 5000);
            }
        });

        // Confirmar antes de cancelar si hay datos en el formulario
        document.querySelector('.btn-secondary').addEventListener('click', function(e) {
            const form = document.querySelector('form');
            const formData = new FormData(form);
            let hasData = false;
            
            for (let [key, value] of formData.entries()) {
                if (value.trim() !== '' && key !== 'registrar_producto') {
                    hasData = true;
                    break;
                }
            }
            
            if (hasData) {
                if (!confirm('¿Estás seguro de que deseas cancelar? Se perderán los datos ingresados.')) {
                    e.preventDefault();
                }
            }
        });
    </script>
</body>

</html>