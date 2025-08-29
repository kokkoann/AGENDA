<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8"> 
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio de Sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body class="login-body">
    <header class="header">
        <div class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo de la empresa" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <div class="container">
        <h2>Inicio de Sesión</h2>
        
        <!-- Mensaje de error -->
        <?php
        require_once '../src/database/Conexion.php';
        error_reporting(E_ALL);
        ini_set("display_errors", 1);
        if (isset($_GET['error'])) {
            echo '<p class="error-message">' . htmlspecialchars($_GET['error']) . '</p>';
        }
        ?>

        <form action="../src/session/controlador.php" method="POST">
            <label for="CORREO">Correo electrónico</label>
            <input type="email" name="correo" id="CORREO" maxlength="35" onkeydown="return event.key !== ' '">

            <label for="CLAVE">Contraseña</label>
            <input type="password" name="clave" id="CLAVE" maxlength="20" onkeydown="return event.key !== ' '">

            <div class="remember">
                <a href="recovery.php">¿Olvidaste tu contraseña?</a>
            </div>
            
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <a href="index.php" class="btn">Regresar</a>
    </div>
</body>
</html>
