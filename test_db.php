<?php
include 'php/conexion.php';

$result = $conexion->query("SHOW TABLES");

if (!$result) {
    die("Error en la consulta: " . $conexion->error);
}

echo "Tablas en la BD:<br>";

while ($row = $result->fetch_array()) {
    echo $row[0] . "<br>";
}
?>
