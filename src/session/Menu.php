<?php
require __DIR__ . '/../database/Conexion.php';
require __DIR__ . '/FechaUtils.php';
session_start();

// Verificación de sesión
if (!isset($_SESSION['correo'])) {
    header("Location: ../../public/iniciosesion.php");
    exit();
}

// Obtener datos del usuario
$query = "SELECT NOMBRE, APELLIDO_PATERNO FROM USUARIO WHERE CORREO = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "s", $_SESSION['correo']);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$usuario = mysqli_fetch_assoc($result);
mysqli_stmt_close($stmt);

// Obtener últimos 5 clientes registrados
$queryClientes = "SELECT ID_CTE, NOMBRE_CTE, APELLIDO_PATERNO_CTE, EMPRESA 
                  FROM CLIENTES
                  WHERE USUARIO_ID = ?
                  ORDER BY ID_CTE DESC 
                  LIMIT 5";
$stmtClientes = mysqli_prepare($conexion, $queryClientes);
mysqli_stmt_bind_param($stmtClientes, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmtClientes);
$clientesRecientes = mysqli_stmt_get_result($stmtClientes);

// Obtener próximos 5 eventos (desde hoy en adelante)
$queryEventos = "SELECT CVE_EVENTO, TITULO, FECHA, HORA 
                 FROM EVENTOS 
                 WHERE USUARIO_ID = ? AND FECHA >= CURDATE()
                 ORDER BY FECHA ASC, HORA ASC
                 LIMIT 5";
$stmtEventos = mysqli_prepare($conexion, $queryEventos);
mysqli_stmt_bind_param($stmtEventos, "i", $_SESSION['usuario_id']);
mysqli_stmt_execute($stmtEventos);
$eventosProximos = mysqli_stmt_get_result($stmtEventos);

// Función para formatear la fecha en español
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Plásticos San Ángel</title>
    <link rel="stylesheet" href="../style.css">
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="logo-container">
            <a href="Menu.php">
                <img src="../../public/IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            </a>
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <!-- Menú de navegación -->
    <nav class="menu-container">
        <ul>
            <li><a href="../agenda/Agenda.php">Agenda</a></li>
            <li><a href="../pedidos/Pedidos.php">Pedidos</a></li>
            <li><a href="../clientes/Clientes.php">Clientes</a></li>
            <li><a href="CerrarSesion.php" class="logout-btn">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido principal -->
    <div class="container">
        <!-- Mensaje de bienvenida -->
        <div class="welcome-message">
            <h1>Bienvenido, <?php echo htmlspecialchars($usuario['NOMBRE'] ?? explode('@', $_SESSION['correo'])[0]); ?></h1>
            <p>Sistema de Gestión de Plásticos San Ángel</p>
        </div>
        
        <!-- Paneles de información -->
        <div class="dashboard">
            <!-- Panel de clientes recientes -->
            <div class="panel">
                <h2>Clientes Recientes</h2>
                
                <?php if (mysqli_num_rows($clientesRecientes) > 0): ?>
                    <ul class="list">
                        <?php while ($cliente = mysqli_fetch_assoc($clientesRecientes)): ?>
                            <li>
                                <a href="../clientes/DetallesCliente.php?id=<?php echo $cliente['ID_CTE']; ?>">
                                    <span class="client-name">
                                        <?php echo htmlspecialchars($cliente['NOMBRE_CTE'] . ' ' . $cliente['APELLIDO_PATERNO_CTE']); ?>
                                    </span>
                                    <?php if (!empty($cliente['EMPRESA'])): ?>
                                        <span class="client-company"><?php echo htmlspecialchars($cliente['EMPRESA']); ?></span>
                                    <?php endif; ?>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    
                    <a href="../clientes/Clientes.php" class="view-all">Ver todos los clientes →</a>
                <?php else: ?>
                    <p class="empty-message">No hay clientes registrados aún.</p>
                    <a href="../clientes/Clientes.php" class="btn">Agregar primer cliente</a>
                <?php endif; ?>
            </div>
            
            <!-- Panel de eventos próximos -->
            <div class="panel">
                <h2>Eventos Próximos</h2>
                
                <?php if (mysqli_num_rows($eventosProximos) > 0): ?>
                    <ul class="list">
                        <?php while ($evento = mysqli_fetch_assoc($eventosProximos)): ?>
                            <li>
                                <a href="../agenda/Agenda.php">
                                    <span class="event-title"><?php echo htmlspecialchars($evento['TITULO']); ?></span>
                                    <span class="event-date"><?php echo formatFecha($evento['FECHA']); ?></span>
                                    <span class="event-time">Hora: <?php echo date('H:i', strtotime($evento['HORA'])); ?></span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>

                    <a href="../agenda/Agenda.php" class="view-all">Ver todos los eventos →</a>
                <?php else: ?>
                    <p class="empty-message">No hay eventos próximos programados.</p>
                    <a href="../agenda/Agenda.php" class="btn">Agregar nuevo evento</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>