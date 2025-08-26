<?php
session_start();

// Datos de prueba para el inicio de sesión (sin base de datos)
$usuario_prueba = "usuario@ejemplo.com";
$clave_prueba = "123456"; // Contraseña de prueba

if (isset($_POST['CORREO']) && isset($_POST['CLAVE'])) {
    function validate($data) {
        $data = trim($data);
        $data = stripslashes($data);
        $data = htmlspecialchars($data);
        return $data;
    }

    $CORREO = validate($_POST['CORREO']);
    $CLAVE = validate($_POST['CLAVE']);

    if (empty($CORREO)) {
        header("Location: INICIODESESION.PHP?error=El Correo es requerido");
        exit();
    } elseif (empty($CLAVE)) {
        header("Location: INICIODESESION.PHP?error=La clave es requerida");
        exit();
    } else {
        // Validamos con los datos de prueba
        if ($CORREO === $usuario_prueba && $CLAVE === $clave_prueba) {
            $_SESSION['CORREO'] = $usuario_prueba;
            $_SESSION['Nombre_Completo'] = "Usuario Demo"; // Nombre ficticio
            $_SESSION['Id'] = 1; // ID ficticio
            header("Location: Menu.php");
            exit();
        } else {
            header("Location: INICIODESESION.PHP?error=El usuario o la clave son incorrectos");
            exit();
        }
    }
} else {
    header("Location: INICIODESESION.PHP?error=Acceso denegado");
    exit();
}
?>
