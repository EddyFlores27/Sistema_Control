<?php
// Configuración de la base de datos
$host = "localhost";      // Servidor (generalmente "localhost" en XAMPP)
$user = "root";           // Usuario por defecto en XAMPP
$password = "";           // Contraseña (vacía por defecto en XAMPP)
$database = "Sistema_control"; // Nombre de tu base de datos

// Intentar conexión
$conn = new mysqli($host, $user, $password, $database);

// Verificar si hay errores
if ($conn->connect_error) {
    die("🔴 Error de conexión: " . $conn->connect_error);
} else {
    echo "🟢 ¡Conexión exitosa a la base de datos!<br>";
    echo "Base de datos seleccionada: <strong>$database</strong>";
}

// Cerrar conexión (opcional)
$conn->close();
?>