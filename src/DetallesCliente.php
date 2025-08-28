<?php
require 'Conexion.php';
session_start();

if (!isset($_GET['id'])) {
    echo "ID de cliente no proporcionado.";
    exit;
}

$id_cliente = $_GET['id'];
$query = "SELECT * FROM clientes WHERE ID_CTE = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $id_cliente);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

if (!$cliente) {
    echo "Cliente no encontrado.";
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalles del Cliente</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>
    
    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php">Agenda</a></li>
            <li><a href="Pedidos.php">Pedidos</a></li>
            <li><a href="Clientes.php">Clientes</a></li>
            <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
        </ul>
    </nav>
    
    <div class="container">
        <h1>Detalles del Cliente</h1>
        <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente['NOMBRE_CTE']); ?></p>
        <p><strong>Apellido Paterno:</strong> <?php echo htmlspecialchars($cliente['APELLIDO_PATERNO_CTE']); ?></p>
        <p><strong>Apellido Materno:</strong> <?php echo htmlspecialchars($cliente['APELLIDO_MATERNO_CTE']); ?></p>
        <p><strong>Empresa:</strong> <?php echo htmlspecialchars($cliente['EMPRESA']); ?></p>
        <p><strong>RFC:</strong> <?php echo htmlspecialchars($cliente['RFC']); ?></p>
        <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente['TELEFONO']); ?></p>
        <p><strong>Correo:</strong> <?php echo htmlspecialchars($cliente['CORREO_CTE']); ?></p>
        
        <a href="EditarCliente.php?id=<?php echo $cliente['ID_CTE']; ?>" class="btn">Editar</a>
        <a href="EliminarCliente.php?id=<?php echo $cliente['ID_CTE']; ?>" class="btn" onclick="return confirm('¿Seguro que deseas eliminar este cliente?');">Eliminar</a>
    </div>
</body>
</html>