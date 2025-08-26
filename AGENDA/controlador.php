<?php
session_start();

// Configuración de la base de datos
$host = "localhost";
$user = "root";
$pass = "";
$db = "iniciosesiondb";

// Conectar a la base de datos
$conexion = new mysqli($host, $user, $pass, $db);

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// Verificar si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Obtener y limpiar datos
    $correo = trim($_POST['correo'] ?? '');
    $clave = trim($_POST['clave'] ?? '');

    // Validaciones básicas
    if (empty($correo) || empty($clave)) {
        $_SESSION['error_login'] = "Ambos campos son obligatorios";
        header("Location: Iniciosesion.php");
        exit();
    }

    // Consulta preparada más segura
    $stmt = $conexion->prepare("SELECT ID, CORREO, CLAVE, NOMBRE FROM usuario WHERE CORREO = ?");
    if (!$stmt) {
        die("Error en la consulta: " . $conexion->error);
    }
    
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        
        // Verificación de contraseña (2 opciones)
        
        // Opción 1: Si las contraseñas están hasheadas
        if (password_verify($clave, $usuario['CLAVE'])) {
            // Login exitoso
            $_SESSION['usuario_id'] = $usuario['ID'];
            $_SESSION['correo'] = $usuario['CORREO'];
            $_SESSION['nombre'] = $usuario['NOMBRE'];
            
            header("Location: Menu.php");
            exit();
        }
        // Opción 2: Si las contraseñas están en texto plano (eliminar la opción 1 si usas esta)
        elseif ($clave === $usuario['CLAVE']) {
            // Login exitoso
            $_SESSION['usuario_id'] = $usuario['ID'];
            $_SESSION['correo'] = $usuario['CORREO'];
            $_SESSION['nombre'] = $usuario['NOMBRE'];
            
            header("Location: Menu.php");
            exit();
        }
    }
    
    // Si llega aquí es porque falló la autenticación
    $_SESSION['error_login'] = "Credenciales incorrectas";
    header("Location: Iniciosesion.php");
    exit();
}

// Si se accede directamente al archivo
header("Location: Iniciosesion.php");
exit();