<?php
// Iniciar la sesión
session_start();

// Verificar si hay una sesión activa antes de destruirla
if (session_status() == PHP_SESSION_ACTIVE) {
    // Destruir todas las variables de sesión
    $_SESSION = array();

    // Destruir la sesión
    session_destroy();
}

// Redirigir al inicio
header("Location: ../../public/index.php");
exit();
?>
