<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.html");
    exit();
}

// Definir permisos basados en rol
$permisos = [
    'user' => ['ver_productos'],
    'admin' => ['ver_productos', 'gestion_usuarios', 'reportes'],
    'gerente' => ['ver_productos', 'gestion_usuarios', 'reportes', 'configuracion']
];

$rol = $_SESSION['user_role'] ?? 'user';
$nombre = $_SESSION['user_name'] ?? 'Usuario';
?>
<!DOCTYPE html>
<html>

<head>
    <title>Dashboard | MASS</title>
    <link rel="stylesheet" href="../css/styles_log.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="../Sistema_Control/css/styles_dash.css">
</head>

<body>
    <div class="sidebar">
        <div class="avatar"></div>
        <div class="admin-label">
            <i class="fas <?php
                echo $rol === 'gerente' ? 'fa-user-tie' : ($rol === 'admin' ? 'fa-user-cog' : 'fa-user');
            ?>"></i>
            <?php echo ucfirst($rol); ?><br>
            <small><?php echo htmlspecialchars($nombre); ?></small>
        </div>
        <form action="../Sistema_Control/logout.php" method="post">
            <button type="submit" class="logout">Cerrar sesión</button>
        </form>
    </div>

    <div class="main">
        <h1>Dashboard</h1>
        <div class="card-grid">
            <?php if (in_array('ver_productos', $permisos[$rol])): ?>
                <a href="productos.php" class="card">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M20 13V6a2 2 0 00-2-2h-6a2 2 0 00-2 2v7m10 0h-4m4 0v5a2 2 0 01-2 2h-6a2 2 0 01-2-2v-5m10 0h-4" />
                    </svg>
                    <p>Productos</p>
                </a>
            <?php endif; ?>

            <a href="proveedores.php" class="card">
                <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M5.121 17.804A13.937 13.937 0 0112 15c2.21 0 4.28.535 6.121 1.48M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
                <p>Proveedores</p>
            </a>

            <?php if (in_array('reportes', $permisos[$rol])): ?>
                <a href="reportes.php" class="card">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 17v-6a2 2 0 012-2h6" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M17 9l4 4m0 0l-4 4m4-4H3" />
                    </svg>
                    <p>Reportes</p>
                </a>
            <?php endif; ?>

            <?php if (in_array('gestion_usuarios', $permisos[$rol])): ?>
                <a href="usuarios.php" class="card">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5.121 17.804A13.937 13.937 0 0112 15c2.21 0 4.28.535 6.121 1.48M15 11a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p>Usuarios</p>
                </a>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>
