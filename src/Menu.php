<?php
require_once '../src/database/Conexion.php';
session_start();

// Verificación de sesión
if (!isset($_SESSION['correo'])) {
    header("Location: iniciosesion.php");
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
function formatFecha($fecha) {
    $dias = ['DOMINGO', 'LUNES', 'MARTES', 'MIÉRCOLES', 'JUEVES', 'VIERNES', 'SÁBADO'];
    $meses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
    
    $timestamp = strtotime($fecha);
    $diaSemana = $dias[date('w', $timestamp)];
    $dia = date('d', $timestamp);
    $mes = $meses[date('n', $timestamp) - 1];
    $anio = date('Y', $timestamp);
    
    return "$diaSemana $dia DE $mes DE $anio";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú Principal - Plásticos San Ángel</title>
    <link rel="stylesheet" href="style.css">
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #000000; /* Fondo negro */
            color: #FFFFFF; /* Texto blanco */
        }
        
        .container {
            max-width: 1200px;
            margin: 140px auto 30px;
            padding: 20px;
        }
        
        /* Encabezado */
        .header {
            background-color: #FFD700; /* Amarillo dorado */
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #111;
        }
        
        .logo {
            width: 50px;
            height: auto;
        }
        
        /* Menú de navegación */
        nav {
            background-color: #000000; /* Negro */
            padding: 15px 0;
            margin-top: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 999;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
        }
        
        .menu-container ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 20px;
        }
        
        .menu-container li a {
            color: #FFD700; /* Amarillo dorado */
            text-decoration: none;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #222222; /* Gris oscuro */
            border-radius: 5px;
            transition: all 0.3s;
        }
        
        .menu-container li a:hover {
            background-color: #333333; /* Gris más claro */
            transform: translateY(-2px);
        }
        
        /* Contenido principal */
        .welcome-message {
            text-align: center;
            margin-bottom: 30px;
            padding-top: 20px;
        }
        
        .welcome-message h1 {
            color: #FFD700; /* Amarillo dorado */
            margin-bottom: 10px;
        }
        
        .welcome-message p {
            color: #FFFFFF; /* Blanco */
            font-size: 1.1em;
        }
        
        .dashboard {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-top: 20px;
        }
        
        .panel {
            background-color: #FFD700; /* Amarillo dorado */
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            padding: 20px;
            border: 1px solid #FFC107;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .panel:hover {
            transform: translateY(-5px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }
        
        .panel h2 {
            color: #000000; /* Negro */
            border-bottom: 2px solid #FFC107;
            padding-bottom: 10px;
            margin-bottom: 15px;
            font-size: 1.3em;
        }
        
        .list {
            list-style-type: none;
            padding: 0;
        }
        
        .list li {
            padding: 12px 15px;
            border-bottom: 1px solid #FFC107;
            transition: all 0.3s;
            background-color: #FFE082; /* Amarillo claro */
            margin-bottom: 8px;
            border-radius: 4px;
        }
        
        .list li:hover {
            background-color: #FFD54F; /* Amarillo medio */
            transform: translateX(5px);
        }
        
        .list a {
            text-decoration: none;
            color: #000000; /* Negro */
            display: block;
        }
        
        .client-name, .event-title {
            font-weight: 600;
            color: #000000;
        }
        
        .client-company, .event-date {
            color: #333333; /* Gris oscuro */
            font-size: 0.9em;
            display: block;
            margin-top: 5px;
        }
        
        .event-time {
            color: #000000;
            font-weight: bold;
            font-size: 0.9em;
        }
        
        .view-all {
            display: block;
            text-align: right;
            margin-top: 15px;
            color: #000000;
            font-weight: bold;
            text-decoration: none;
            transition: color 0.3s;
        }
        
        .view-all:hover {
            color: #333333;
            text-decoration: underline;
        }
        
        .empty-message {
            color: #333333;
            font-style: italic;
            text-align: center;
            padding: 20px 0;
        }
        
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #FFC107; /* Amarillo anaranjado */
            color: #000000;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            margin-top: 10px;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
            text-align: center;
        }
        
        .btn:hover {
            background-color: #FFA000; /* Amarillo más oscuro */
            transform: translateY(-2px);
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }
        
        /* Efectos adicionales */
        .logout-btn:hover {
            background-color: #D32F2F !important; /* Rojo para el botón de cerrar sesión */
            color: white !important;
        }
    </style>
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <div class="logo-container">
            <a href="Menu.php">
                <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            </a>
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <!-- Menú de navegación -->
    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php">Agenda</a></li>
            <li><a href="Pedidos.php">Pedidos</a></li>
            <li><a href="Clientes.php">Clientes</a></li>
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
                                <a href="DetallesCliente.php?id=<?php echo $cliente['ID_CTE']; ?>">
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
                    
                    <a href="Clientes.php" class="view-all">Ver todos los clientes →</a>
                <?php else: ?>
                    <p class="empty-message">No hay clientes registrados aún.</p>
                    <a href="Clientes.php" class="btn">Agregar primer cliente</a>
                <?php endif; ?>
            </div>
            
            <!-- Panel de eventos próximos -->
            <div class="panel">
                <h2>Eventos Próximos</h2>
                
                <?php if (mysqli_num_rows($eventosProximos) > 0): ?>
                    <ul class="list">
                        <?php while ($evento = mysqli_fetch_assoc($eventosProximos)): ?>
                            <li>
                                <a href="Agenda.php">
                                    <span class="event-title"><?php echo htmlspecialchars($evento['TITULO']); ?></span>
                                    <span class="event-date"><?php echo formatFecha($evento['FECHA']); ?></span>
                                    <span class="event-time">Hora: <?php echo date('H:i', strtotime($evento['HORA'])); ?></span>
                                </a>
                            </li>
                        <?php endwhile; ?>
                    </ul>
                    
                    <a href="Agenda.php" class="view-all">Ver todos los eventos →</a>
                <?php else: ?>
                    <p class="empty-message">No hay eventos próximos programados.</p>
                    <a href="Agenda.php" class="btn">Agregar nuevo evento</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>