<?php
    require 'Conexion.php';
    

    
?>

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
        <h2>Recuperar Contraseña</h2>
        
        <!-- Mensaje de error -->
        <?php


?>
        <form action="recuperar.php" method="post">
                
                <label>Correo</label>
                <input type="email" name="correo" required autocomplete="off"  onkeydown="return event.key !== ' '"/>
            
                <button type="submit">Enviar</button>

                <br><br>
            </form>
        
        <a href="Index.php" class="btn">Regresar</a>

</body>
</html>
