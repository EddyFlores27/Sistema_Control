<?php
$host = "localhost";
$user = "root";
$password = "";  // Deja vacío si no tienes contraseña
$database = "usuarios_app";  // Nombre de tu BD

$conexion = new mysqli($host, $user, $password, $database);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

$conexion->set_charset("utf8mb4");  // Para caracteres especiales
?>