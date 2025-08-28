<?php
require 'Conexion.php';
session_start();

// Verificar sesión
if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Error: Sesión no iniciada.'); window.location.href='index.html';</script>";
    exit();
}

// Obtener el ID del cliente desde la URL
$clienteId = isset($_GET['id']) ? (int) $_GET['id'] : 0;

// Verificar si el ID es válido
if ($clienteId <= 0) {
    die("ID de cliente inválido.");
}

// Obtener los datos del cliente actual
$query = "SELECT * FROM clientes WHERE ID_CTE = ?";
$stmt = mysqli_prepare($conexion, $query);
mysqli_stmt_bind_param($stmt, "i", $clienteId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$cliente = mysqli_fetch_assoc($result);

if (!$cliente) {
    die("Cliente no encontrado.");
}

// Funciones de validación mejoradas
function validarTexto($texto) {
    // Validar sin acentos, máximo 5 espacios y 22 caracteres
    return preg_match('/^[A-Z ]{1,22}$/', $texto) && 
           (substr_count($texto, ' ') <= 5);
}

function validarRFC($rfc) {
    return preg_match('/^[A-Z0-9]{1,13}$/', $rfc);
}

function validarTelefono($tel) {
    return preg_match('/^[0-9]{1,15}$/', $tel);
}

function validarCorreo($correo) {
    // Validar formato de correo y máximo 30 caracteres
    return filter_var($correo, FILTER_VALIDATE_EMAIL) && 
           strlen($correo) <= 30;
}

// Función para eliminar acentos
function eliminarAcentos($string) {
    $string = str_replace(
        array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'á', 'é', 'í', 'ó', 'ú', 'ñ'),
        array('A', 'E', 'I', 'O', 'U', 'N', 'a', 'e', 'i', 'o', 'u', 'n'),
        $string
    );
    return $string;
}

// Si el formulario ha sido enviado
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Recoger y sanitizar los datos del formulario
    $nombre = isset($_POST['nombre']) ? strtoupper(trim($_POST['nombre'])) : '';
    $apellidoPaterno = isset($_POST['apellido_paterno']) ? strtoupper(trim($_POST['apellido_paterno'])) : '';
    $apellidoMaterno = isset($_POST['apellido_materno']) ? strtoupper(trim($_POST['apellido_materno'])) : '';
    $empresa = isset($_POST['empresa']) ? strtoupper(trim($_POST['empresa'])) : '';
    $rfc = isset($_POST['rfc']) ? strtoupper(trim($_POST['rfc'])) : '';
    $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
    $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';

    // Eliminar acentos de los campos de texto
    $nombre = eliminarAcentos($nombre);
    $apellidoPaterno = eliminarAcentos($apellidoPaterno);
    $apellidoMaterno = eliminarAcentos($apellidoMaterno);
    $empresa = eliminarAcentos($empresa);

    // Validar datos obligatorios y su formato
    if (
        validarTexto($nombre) &&
        validarTexto($apellidoPaterno) &&
        validarTexto($apellidoMaterno) &&
        ($empresa === '' || validarTexto($empresa)) &&
        ($rfc === '' || validarRFC($rfc)) &&
        validarTelefono($telefono) &&
        validarCorreo($correo)
    ) {
        // Verificar duplicado por nombre completo (nombre + apellidos)
        $verificarNombreCompleto = "SELECT * FROM CLIENTES 
                                  WHERE CONCAT(NOMBRE_CTE, ' ', APELLIDO_PATERNO_CTE, ' ', APELLIDO_MATERNO_CTE) = ?
                                  AND ID_CTE != ?";
        $nombreCompleto = $nombre.' '.$apellidoPaterno.' '.$apellidoMaterno;
        $stmtNombre = mysqli_prepare($conexion, $verificarNombreCompleto);
        mysqli_stmt_bind_param($stmtNombre, "si", $nombreCompleto, $clienteId);
        mysqli_stmt_execute($stmtNombre);
        $resultadoNombre = mysqli_stmt_get_result($stmtNombre);

        if (mysqli_num_rows($resultadoNombre) > 0) {
            echo "<script>alert('Error: Ya existe un cliente con ese nombre completo.'); window.history.back();</script>";
            exit();
        }
        mysqli_stmt_close($stmtNombre);

        // Verificar duplicado por correo electrónico
        $verificarCorreo = "SELECT * FROM CLIENTES 
                          WHERE CORREO_CTE = ?
                          AND ID_CTE != ?";
        $stmtCorreo = mysqli_prepare($conexion, $verificarCorreo);
        mysqli_stmt_bind_param($stmtCorreo, "si", $correo, $clienteId);
        mysqli_stmt_execute($stmtCorreo);
        $resultadoCorreo = mysqli_stmt_get_result($stmtCorreo);

        if (mysqli_num_rows($resultadoCorreo) > 0) {
            echo "<script>alert('Error: El correo electrónico ya está registrado para otro cliente.'); window.history.back();</script>";
            exit();
        }
        mysqli_stmt_close($stmtCorreo);

        // Verificar duplicado por RFC (si se proporcionó)
        if (!empty($rfc)) {
            $verificarRFC = "SELECT * FROM CLIENTES WHERE RFC = ? AND ID_CTE != ?";
            $stmtRFC = mysqli_prepare($conexion, $verificarRFC);
            mysqli_stmt_bind_param($stmtRFC, "si", $rfc, $clienteId);
            mysqli_stmt_execute($stmtRFC);
            $resultadoRFC = mysqli_stmt_get_result($stmtRFC);

            if (mysqli_num_rows($resultadoRFC) > 0) {
                echo "<script>alert('Error: El RFC ya está registrado para otro cliente.'); window.history.back();</script>";
                exit();
            }
            mysqli_stmt_close($stmtRFC);
        }

        // Si no hay duplicados, actualizar el cliente
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
            echo "<script>alert('Cliente actualizado exitosamente.'); window.location.href='DetallesCliente.php?id=$clienteId';</script>";
            exit();
        } else {
            echo "<script>alert('Error al actualizar la información: " . mysqli_error($conexion) . "');</script>";
        }
    } else {
        echo "<script>alert('Datos inválidos. Verifica los campos e intenta de nuevo. Recuerda: sin acentos, máximo 5 espacios y correo máximo 30 caracteres.'); window.history.back();</script>";
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
    <script>
        // Función para contar espacios y limitarlos
        function validarEspacios(input) {
            var texto = input.value;
            var espacios = (texto.match(/ /g) || []).length;
            
            if (espacios > 5) {
                alert('Máximo 5 espacios permitidos');
                input.value = texto.substring(0, texto.lastIndexOf(' '));
            }
        }
    </script>
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
            <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['NOMBRE_CTE']); ?>" required
                pattern="[A-Z ]{1,22}"
                maxlength="22"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

            <label for="apellido_paterno">Apellido Paterno:</label>
            <input type="text" id="apellido_paterno" name="apellido_paterno" value="<?php echo htmlspecialchars($cliente['APELLIDO_PATERNO_CTE']); ?>" required
                pattern="[A-Z ]{1,22}"
                maxlength="22"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

            <label for="apellido_materno">Apellido Materno:</label>
            <input type="text" id="apellido_materno" name="apellido_materno" value="<?php echo htmlspecialchars($cliente['APELLIDO_MATERNO_CTE']); ?>" required
                pattern="[A-Z ]{1,22}"
                maxlength="22"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

            <label for="empresa">Empresa:</label>
            <input type="text" id="empresa" name="empresa" value="<?php echo htmlspecialchars($cliente['EMPRESA']); ?>"
                pattern="[A-Z0-9 ]{1,22}"
                maxlength="22"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9 ]/g, ''); validarEspacios(this)"
                title="Solo letras mayúsculas y números sin acentos, máximo 5 espacios">

            <label for="rfc">RFC:</label>
            <input type="text" id="rfc" name="rfc" value="<?php echo htmlspecialchars($cliente['RFC']); ?>"
                pattern="[A-Z0-9]{1,13}"
                maxlength="13"
                oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                title="Solo letras mayúsculas y números, sin espacios">

            <label for="telefono">Teléfono:</label>
            <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['TELEFONO']); ?>" required
                pattern="[0-9]{1,15}"
                maxlength="15"
                oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                title="Solo números, sin espacios">

            <label for="correo">Correo:</label>
            <input type="email" id="correo" name="correo" value="<?php echo htmlspecialchars($cliente['CORREO_CTE']); ?>" required
                maxlength="30"
                title="Máximo 30 caracteres">

            <button type="submit" class="btn">Actualizar Cliente</button>
        </form>
    </div>
</body>
</html>