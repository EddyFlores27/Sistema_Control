<?php
include '../Sistema_Control/php/conexion.php';

$tipo = $_GET['tipo'];

switch ($tipo) {
    case 'dia':
        $fecha = $_GET['fecha'];
        $query = "SELECT * FROM productos WHERE DATE(fecha_hora) = '$fecha'";
        break;

    case 'semana':
        $semana = $_GET['semana']; // formato: YYYY-W##
        list($año, $sem) = explode("-W", $semana);
        $query = "SELECT * FROM productos 
                  WHERE YEARWEEK(fecha_hora, 1) = YEARWEEK(STR_TO_DATE('$año-$sem Monday', '%X-%V %W'), 1)";
        break;

    case 'mes':
        $mes = $_GET['mes']; // formato: YYYY-MM
        $query = "SELECT * FROM productos WHERE DATE_FORMAT(fecha_hora, '%Y-%m') = '$mes'";
        break;

    default:
        echo "<p>Tipo de reporte no válido.</p>";
        exit;
}

// Ejecutar la consulta
$resultado = mysqli_query($conexion, $query);

if (!$resultado) {
    echo "<p>Error en la consulta: " . mysqli_error($conexion) . "</p>";
    exit;
}

// Mostrar los resultados
echo "<table border='1' cellpadding='5'><tr><th>ID</th><th>Nombre</th><th>Cantidad</th><th>Precio</th><th>Fecha</th></tr>";
while ($fila = mysqli_fetch_assoc($resultado)) {
    echo "<tr>
            <td>{$fila['id']}</td>
            <td>{$fila['nombre']}</td>
            <td>{$fila['cantidad']}</td>
            <td>S/ {$fila['precio']}</td>
            <td>{$fila['fecha_hora']}</td>
          </tr>";
}
echo "</table>";
?>
