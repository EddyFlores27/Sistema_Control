<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Definir permisos basados en rol
$permisos = [
    'user' => ['ver_produccion'],
    'admin' => ['ver_produccion', 'gestion_usuarios', 'reportes'],
    'gerente' => ['ver_produccion', 'gestion_usuarios', 'reportes', 'configuracion']
];

$rol = $_SESSION['user_role'] ?? 'user';
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dashboard | MASS</title>
    <link rel="stylesheet" href="../css/styles_log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <h1>¡Bienvenido, <?php echo $_SESSION['user_name'] ?? 'Usuario'; ?>!</h1>
        <p class="role-badge">
            <i class="fas <?php 
                echo $rol === 'gerente' ? 'fa-user-tie' : 
                     ($rol === 'admin' ? 'fa-user-cog' : 'fa-user'); 
            ?>"></i>
            <?php echo ucfirst($rol); ?>
        </p>
        
        <div class="menu">
            <?php if(in_array('ver_produccion', $permisos[$rol])): ?>
                <a href="produccion.php" class="btn primary">
                    <i class="fas fa-chart-line"></i> Ver Producción
                </a>
            <?php endif; ?>
            
            <?php if(in_array('reportes', $permisos[$rol])): ?>
                <a href="reportes.php" class="btn primary">
                    <i class="fas fa-file-alt"></i> Reportes
                </a>
            <?php endif; ?>
            
            <?php if(in_array('gestion_usuarios', $permisos[$rol])): ?>
                <a href="usuarios.php" class="btn primary">
                    <i class="fas fa-users-cog"></i> Gestión de Usuarios
                </a>
            <?php endif; ?>
        </div>
        
        <a href="../php/logout.php" class="btn secondary">
            <i class="fas fa-sign-out-alt"></i> Cerrar sesión
        </a>
    </div>
</body>
</html>