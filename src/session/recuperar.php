<?php
require '../database/Conexion.php';

// Verificar si se envió el formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $correo = $conn->real_escape_string($_POST['correo']);

    // Buscar el correo en la base de datos
    $sql = "SELECT clave FROM usuario WHERE correo = '$correo'";
    $resultado = $conn->query($sql);

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        $clave = $fila['clave']; // Si la clave está encriptada, no la puedes recuperar directamente

        // Enviar correo con la contraseña (solo si no está encriptada)
        $asunto = "Recuperación de contraseña";
        $mensaje = "Tu contraseña es: $clave";
        $cabeceras = "From: no-reply@tudominio.com";

        if (mail($correo, $asunto, $mensaje, $cabeceras)) {
            echo "Se ha enviado un correo con tu contraseña.";
        } else {
            echo "Error al enviar el correo.";
        }
    } else {
        echo "El correo no está registrado.";
    }
}

// Cerrar conexión
$conn->close();
