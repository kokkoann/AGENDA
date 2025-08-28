<?php
require 'Conexion.php';
session_start();

if (!isset($_SESSION['correo'])) {
    echo "<script>alert('Error: Sesión no iniciada.'); window.location.href='index.html';</script>";
    exit();
}

$correoUsuario = $_SESSION['correo'];

$queryUsuario = "SELECT ID FROM USUARIO WHERE CORREO = ?";
$stmtUsuario = mysqli_prepare($conexion, $queryUsuario);
mysqli_stmt_bind_param($stmtUsuario, "s", $correoUsuario);
mysqli_stmt_execute($stmtUsuario);
$resultUsuario = mysqli_stmt_get_result($stmtUsuario);
$usuario = mysqli_fetch_assoc($resultUsuario);

if (!$usuario) {
    echo "<script>alert('Error: No se encontró el usuario en la base de datos.');</script>";
    exit();
}

$usuarioId = $usuario['ID'];

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

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Obtener y sanitizar datos
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
        // Verificar duplicado por nombre completo y correo
        $verificarDuplicado = "SELECT * FROM CLIENTES WHERE NOMBRE_CTE = ? AND APELLIDO_PATERNO_CTE = ? AND APELLIDO_MATERNO_CTE = ? AND CORREO_CTE = ?";
        $stmtVerificar = mysqli_prepare($conexion, $verificarDuplicado);
        mysqli_stmt_bind_param($stmtVerificar, "ssss", $nombre, $apellidoPaterno, $apellidoMaterno, $correo);
        mysqli_stmt_execute($stmtVerificar);
        $resultadoVerificacion = mysqli_stmt_get_result($stmtVerificar);

        if (mysqli_num_rows($resultadoVerificacion) > 0) {
            echo "<script>alert('Ya existe un cliente con ese nombre y correo.'); window.history.back();</script>";
            exit();
        }
        mysqli_stmt_close($stmtVerificar);

        // Verificar duplicado por RFC (si se proporcionó)
        if (!empty($rfc)) {
            $verificarRFC = "SELECT * FROM CLIENTES WHERE RFC = ?";
            $stmtRFC = mysqli_prepare($conexion, $verificarRFC);
            mysqli_stmt_bind_param($stmtRFC, "s", $rfc);
            mysqli_stmt_execute($stmtRFC);
            $resultadoRFC = mysqli_stmt_get_result($stmtRFC);

            if (mysqli_num_rows($resultadoRFC) > 0) {
                echo "<script>alert('Ya existe un cliente con ese RFC.'); window.history.back();</script>";
                exit();
            }
            mysqli_stmt_close($stmtRFC);
        }

        // Si no hay duplicados, insertar el cliente
        $query = "INSERT INTO CLIENTES (USUARIO_ID, NOMBRE_CTE, APELLIDO_PATERNO_CTE, APELLIDO_MATERNO_CTE, EMPRESA, RFC, TELEFONO, CORREO_CTE) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

        $stmt = mysqli_prepare($conexion, $query);
        mysqli_stmt_bind_param($stmt, "isssssss", $usuarioId, $nombre, $apellidoPaterno, $apellidoMaterno, $empresa, $rfc, $telefono, $correo);

        if (mysqli_stmt_execute($stmt)) {
            echo "<script>alert('Cliente agregado exitosamente.'); window.location.href='Clientes.php';</script>";
        } else {
            echo "<script>alert('Error al agregar el cliente: " . mysqli_error($conexion) . "');</script>";
        }

        mysqli_stmt_close($stmt);
    } else {
        echo "<script>alert('Datos inválidos. Verifica los campos e intenta de nuevo. Recuerda: sin acentos, máximo 5 espacios y correo máximo 30 caracteres.'); window.history.back();</script>";
    }
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

mysqli_stmt_close($stmtUsuario);
?>