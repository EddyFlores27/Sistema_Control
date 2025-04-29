<?php
include 'php/conexion.php';
$result = $conexion->query("SHOW TABLES");
echo "Tablas en la BD: ";
while ($row = $result->fetch_array()) {
    print_r($row);
}
?>