<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';

include '../Sistema_Control/php/conexion.php';

$mensaje = '';
$tipo_mensaje = '';

// Procesar el formulario cuando se envía
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_producto'])) {
    $nombre_producto = mysqli_real_escape_string($conexion, trim($_POST['nombre']));
    $precio = floatval($_POST['precio']);
    $cantidad = intval($_POST['cantidad']);
    $movimiento = mysqli_real_escape_string($conexion, $_POST['movimiento']);
    $categoria = mysqli_real_escape_string($conexion, trim($_POST['categoria'] ?? ''));
    $proveedor_id = !empty($_POST['proveedor_id']) ? intval($_POST['proveedor_id']) : null;
    $fecha_hora = date('Y-m-d H:i:s');

    // Validaciones
    if (empty($nombre_producto)) {
        $mensaje = "El nombre del producto es obligatorio.";
        $tipo_mensaje = "error";
    } elseif ($precio <= 0) {
        $mensaje = "El precio debe ser un número válido mayor a 0.";
        $tipo_mensaje = "error";
    } elseif ($cantidad < 0) {
        $mensaje = "La cantidad debe ser un número válido mayor o igual a 0.";
        $tipo_mensaje = "error";
    } elseif (empty($movimiento)) {
        $mensaje = "Debe seleccionar un tipo de movimiento.";
        $tipo_mensaje = "error";
    } else {
        // Verificar si el producto ya existe
        $check_sql = "SELECT id FROM productos WHERE nombre = '$nombre_producto'";
        $check_result = mysqli_query($conexion, $check_sql);
        
        if (mysqli_num_rows($check_result) > 0) {
            $mensaje = "Ya existe un producto con ese nombre.";
            $tipo_mensaje = "error";
        } else {
            // Insertar el nuevo producto
            $insert_sql = "INSERT INTO productos (nombre, precio, cantidad, movimiento, categoria, proveedor_id, fecha_hora) 
                           VALUES ('$nombre_producto', '$precio', '$cantidad', '$movimiento', '$categoria', " . ($proveedor_id ? "'$proveedor_id'" : "NULL") . ", '$fecha_hora')";
            
            if (mysqli_query($conexion, $insert_sql)) {
                $mensaje = "Producto registrado exitosamente.";
                $tipo_mensaje = "success";
                // Limpiar los campos después del registro exitoso
                $_POST = array();
            } else {
                $mensaje = "Error al registrar el producto: " . mysqli_error($conexion);
                $tipo_mensaje = "error";
            }
        }
    }
}

// Obtener lista de proveedores para el select
$sql_proveedores = "SELECT id, nombre FROM proveedores WHERE estado = 'activo' ORDER BY nombre";
$resultado_proveedores = mysqli_query($conexion, $sql_proveedores);
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
                        placeholder="Ej: Laptop HP Pavilion 15"
                        value="<?php echo isset($_POST['nombre']) ? htmlspecialchars($_POST['nombre']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="precio">
                        <i class="fas fa-dollar-sign"></i>
                        Precio (S/) *
                    </label>
                    <input 
                        type="number" 
                        id="precio" 
                        name="precio" 
                        placeholder="Ej: 2500.00"
                        step="0.01"
                        min="0.01"
                        value="<?php echo isset($_POST['precio']) ? htmlspecialchars($_POST['precio']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="cantidad">
                        <i class="fas fa-cubes"></i>
                        Cantidad *
                    </label>
                    <input 
                        type="number" 
                        id="cantidad" 
                        name="cantidad" 
                        placeholder="Ej: 10"
                        min="0"
                        value="<?php echo isset($_POST['cantidad']) ? htmlspecialchars($_POST['cantidad']) : ''; ?>"
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="movimiento">
                        <i class="fas fa-exchange-alt"></i>
                        Tipo de Movimiento *
                    </label>
                    <select id="movimiento" name="movimiento" required>
                        <option value="">Selecciona el tipo de movimiento</option>
                        <option value="entrada" <?php echo (isset($_POST['movimiento']) && $_POST['movimiento'] === 'entrada') ? 'selected' : ''; ?>>
                            Entrada
                        </option>
                        <option value="salida" <?php echo (isset($_POST['movimiento']) && $_POST['movimiento'] === 'salida') ? 'selected' : ''; ?>>
                            Salida
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="categoria">
                        <i class="fas fa-tags"></i>
                        Categoría
                    </label>
                    <input 
                        type="text" 
                        id="categoria" 
                        name="categoria" 
                        placeholder="Ej: Electrónicos, Oficina, etc."
                        value="<?php echo isset($_POST['categoria']) ? htmlspecialchars($_POST['categoria']) : ''; ?>"
                    >
                </div>

                <div class="form-group">
                    <label for="proveedor_id">
                        <i class="fas fa-truck"></i>
                        Proveedor
                    </label>
                    <select id="proveedor_id" name="proveedor_id">
                        <option value="">Seleccionar proveedor (opcional)</option>
                        <?php 
                        mysqli_data_seek($resultado_proveedores, 0); // Reiniciar el puntero del resultado
                        while ($proveedor = mysqli_fetch_assoc($resultado_proveedores)): ?>
                            <option value="<?php echo $proveedor['id']; ?>" <?php echo (isset($_POST['proveedor_id']) && $_POST['proveedor_id'] == $proveedor['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($proveedor['nombre']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
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
                <li>El precio debe ser un valor numérico positivo</li>
                <li>La cantidad debe ser un número entero mayor o igual a 0</li>
                <li>Selecciona "Entrada" para productos que ingresan al inventario o "Salida" para productos que salen</li>
                <li>La categoría es opcional pero recomendada para mejor organización</li>
                <li>Puedes asociar el producto con un proveedor activo del sistema</li>
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

        // Validación en tiempo real del precio
        document.getElementById('precio').addEventListener('input', function(e) {
            const value = parseFloat(e.target.value);
            if (value <= 0) {
                e.target.setCustomValidity('El precio debe ser mayor a 0');
            } else {
                e.target.setCustomValidity('');
            }
        });

        // Validación en tiempo real de la cantidad
        document.getElementById('cantidad').addEventListener('input', function(e) {
            const value = parseInt(e.target.value);
            if (value < 0) {
                e.target.setCustomValidity('La cantidad no puede ser negativa');
            } else {
                e.target.setCustomValidity('');
            }
        });

        // Validación del formulario antes del envío
        document.querySelector('form').addEventListener('submit', function(e) {
            const nombre = document.getElementById('nombre').value.trim();
            const precio = parseFloat(document.getElementById('precio').value);
            const cantidad = parseInt(document.getElementById('cantidad').value);
            const movimiento = document.getElementById('movimiento').value;

            if (!nombre) {
                alert('El nombre del producto es obligatorio');
                e.preventDefault();
                return;
            }

            if (precio <= 0) {
                alert('El precio debe ser mayor a 0');
                e.preventDefault();
                return;
            }

            if (cantidad < 0) {
                alert('La cantidad no puede ser negativa');
                e.preventDefault();
                return;
            }

            if (!movimiento) {
                alert('Debe seleccionar un tipo de movimiento');
                e.preventDefault();
                return;
            }
        });
    </script>
</body>

</html>