<?php
error_reporting(E_ALL);
ini_set("display_errors", 1);
session_start();
require_once '../database/Conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $correo = trim($_POST['correo'] ?? '');
    $clave  = trim($_POST['clave'] ?? '');

    // Validaciones básicas
    if (empty($correo) || empty($clave)) {
        mostrarError("Ambos campos son obligatorios");
    }

    // Consulta preparada
    $stmt = $conexion->prepare("SELECT ID, CORREO, CLAVE, NOMBRE FROM USUARIO WHERE CORREO = ?");
    if (!$stmt) {
        mostrarError("Error en la consulta: " . htmlspecialchars($conexion->error));
    }

    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();
        if ($clave === $usuario['CLAVE']) { 
            $_SESSION['usuario_id'] = $usuario['ID'];
            $_SESSION['correo']     = $usuario['CORREO'];
            $_SESSION['nombre']     = $usuario['NOMBRE'];
            header("Location: Menu.php");
            exit();
        }
    }
    mostrarError("Credenciales incorrectas");
}

// Función para mostrar error de manera uniforme
function mostrarError($mensaje) {
    ?>
    <!DOCTYPE html>
    <html lang="es">
    <head>
        <meta charset="UTF-8">
        <title>Error de inicio de sesión</title>
        <link rel="stylesheet" href="../public/style.css">
    </head>
    <body>
        <div class="container">
            <h2>Error</h2>
            <p class="error-message"><?= htmlspecialchars($mensaje) ?></p>
            <a href="../public/iniciosesion.php" class="btn">Regresar</a>
        </div>
    </body>
    </html>
    <?php
    exit();
}
