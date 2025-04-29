<?php
session_start();
include __DIR__ . '/conexion.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("Método no permitido");
}

$usuario = trim($_POST['usuario'] ?? '');
$password = $_POST['password'] ?? '';
$rol_solicitado = $_POST['profile'] ?? 'user'; // Rol seleccionado en el selector

if (empty($usuario) || empty($password)) {
    die("Usuario y contraseña son requeridos");
}

$stmt = $conexion->prepare("SELECT id, password, rol FROM usuarios WHERE usuario = ?");
$stmt->bind_param("s", $usuario);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows === 1) {
    $fila = $resultado->fetch_assoc();
    if (password_verify($password, $fila['password'])) {
        // Verificar que el rol del usuario coincida con el rol seleccionado
        if ($fila['rol'] !== $rol_solicitado) {
            die("No tienes permisos para acceder como " . ucfirst($rol_solicitado) . ". <a href='../index.html'>Volver</a>");
        }
        
        $_SESSION['user_id'] = $fila['id'];
        $_SESSION['user_name'] = $usuario;
        $_SESSION['user_role'] = $fila['rol'];
        
        header("Location: ../dashboard.php");
        exit();
    }
}

// Si llega aquí es porque falló
die("Usuario o contraseña incorrectos. <a href='Login.html?profile=" . urlencode($rol_solicitado) . "'>Volver</a>");
?>