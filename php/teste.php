<?php
include 'C:\xampp\htdocs\Sistema_Control\php\conexion.php';
$result = $conexion->query("SHOW TABLES");
echo "Tablas en la base de datos:<br>";
while ($row = $result->fetch_array()) {
    echo $row[0] . "<br>";
}

$conexion->close();
?>