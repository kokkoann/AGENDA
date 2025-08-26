<?php
require 'Conexion.php';
session_start();

// Obtener el ID del cliente desde la URL
$clienteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Verificar si el ID es válido
if ($clienteId <= 0) {
    die("ID de cliente inválido.");
}

// Obtener los datos del cliente desde la base de datos
$query = "SELECT * FROM clientes WHERE ID_CTE = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $clienteId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

// Si no se encuentra el cliente
if (!$cliente) {
    die("Cliente no encontrado.");
}

// Si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre = mysqli_real_escape_string($conexion, $_POST['nombre']);
    $apellidoPaterno = mysqli_real_escape_string($conexion, $_POST['apellido_paterno']);
    $apellidoMaterno = mysqli_real_escape_string($conexion, $_POST['apellido_materno']);
    $empresa = mysqli_real_escape_string($conexion, $_POST['empresa']);
    $rfc = mysqli_real_escape_string($conexion, $_POST['rfc']);
    $telefono = mysqli_real_escape_string($conexion, $_POST['telefono']);
    $correo = mysqli_real_escape_string($conexion, $_POST['correo']);

    // Actualizar la información del cliente en la base de datos
    $updateQuery = "UPDATE clientes SET 
                    NOMBRE_CTE = ?, 
                    APELLIDO_PATERNO_CTE = ?, 
                    APELLIDO_MATERNO_CTE = ?, 
                    EMPRESA = ?, 
                    RFC = ?, 
                    TELEFONO = ?, 
                    CORREO_CTE = ? 
                    WHERE ID_CTE = ?";

    $stmt = mysqli_prepare($conexion, $updateQuery);
    mysqli_stmt_bind_param($stmt, "sssssssi", $nombre, $apellidoPaterno, $apellidoMaterno, $empresa, $rfc, $telefono, $correo, $clienteId);

    if (mysqli_stmt_execute($stmt)) {
        header("Location: DetallesCliente.php?id=$clienteId");
        exit();
    } else {
        echo "Error al actualizar la información: " . mysqli_error($conexion);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cliente</title>
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
        <h1>Editar Cliente</h1>
        <form action="EditarCliente.php?id=<?php echo $clienteId; ?>" method="POST">
            <label for="nombre">Nombre:</label>
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['NOMBRE_CTE']); ?>" required>

            <label for="apellido_paterno">Apellido Paterno:</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($cliente['APELLIDO_PATERNO_CTE']); ?>" required>

            <label for="apellido_materno">Apellido Materno:</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($cliente['APELLIDO_MATERNO_CTE']); ?>" required>

            <label for="empresa">Empresa:</label>
            <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($cliente['EMPRESA']); ?>">

            <label for="rfc">RFC:</label>
            <input type="text" id="rfc" name="rfc" value="<?php echo htmlspecialchars($cliente['RFC']); ?>">

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['TELEFONO']); ?>" required>

            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($cliente['CORREO_CTE']); ?>" required>

            <button type="submit" class="btn">Actualizar Cliente</button>
        </form>
    </div>
</body>
</html>