<?php
include __DIR__ . '/conexion.php';

// 1. Validar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(["Error: Método no permitido"]));
}

// 2. Sanitizar datos
$nombre = trim($conexion->real_escape_string($_POST['nombre_completo'] ?? ''));
$email = trim($conexion->real_escape_string($_POST['email'] ?? ''));
$usuario = trim($conexion->real_escape_string($_POST['usuario'] ?? ''));
$password = $_POST['password'] ?? '';
$confirm_password = $_POST['confirm_password'] ?? '';
$rol = trim($conexion->real_escape_string($_POST['rol'] ?? 'user')); // Valor por defecto 'user'

// 3. Validaciones básicas
$errores = [];

if (empty($nombre)) $errores[] = "Nombre completo es requerido";
if (empty($email)) $errores[] = "Email es requerido";
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) $errores[] = "Email inválido";
if (empty($usuario)) $errores[] = "Usuario es requerido";
if (strlen($password) < 8) $errores[] = "La contraseña debe tener al menos 8 caracteres";
if ($password !== $confirm_password) $errores[] = "Las contraseñas no coinciden";
if (!in_array($rol, ['user', 'admin', 'gerente'])) $errores[] = "Rol no válido";

// 4. Si hay errores, mostrarlos
if (!empty($errores)) {
    header("Location: ../register.html?errores=" . urlencode(json_encode($errores)));
    exit();
}

// 5. Verificar si usuario/email ya existen
$sql_check = "SELECT id FROM usuarios WHERE usuario = '$usuario' OR email = '$email'";
$resultado = $conexion->query($sql_check);

if ($resultado->num_rows > 0) {
    header("Location: ../register.html?errores=" . urlencode(json_encode(["El usuario o email ya existen"])));
    exit();
}

// 6. Hash de contraseña
$password_hash = password_hash($password, PASSWORD_BCRYPT);

// 7. Insertar nuevo usuario con rol
$sql = "INSERT INTO usuarios (nombre, email, usuario, password, rol) 
        VALUES ('$nombre', '$email', '$usuario', '$password_hash', '$rol')";

if ($conexion->query($sql) === TRUE) {
    header("Location: ../Login.html?registro=exitoso");
} else {
    header("Location: ../register.html?errores=" . urlencode(json_encode(["Error al registrar: " . $conexion->error])));
}

$conexion->close();
?>