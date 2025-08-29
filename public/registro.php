<?php
require_once '../src/database/Conexion.php';

$registro_exitoso = false;

if (isset($_POST['registro'])) {
    $correo = $_POST['CORREO'];
    $clave = $_POST['CLAVE'];
    $Rclave = $_POST['RCLAVE'];
    $nombre = $_POST['NOMBRE'];
    $apellido_paterno = $_POST['APELLIDO_PATERNO'];
    $apellido_materno = $_POST['APELLIDO_MATERNO'];
    $puesto = $_POST['PUESTO'];

    // Validación PHP para mayúsculas y sin números
    if (
        preg_match("/\d/", $nombre) || 
        preg_match("/\d/", $apellido_paterno) || 
        preg_match("/\d/", $apellido_materno) ||
        !ctype_upper($nombre) || 
        !ctype_upper($apellido_paterno) || 
        !ctype_upper($apellido_materno)
    ) {
        echo "<script>alert('Los campos de nombre y apellidos deben estar en MAYÚSCULAS y no contener números.');</script>";
    } else {
        // Verificar si el correo ya está registrado
        $checkCorreo = "SELECT * FROM USUARIO WHERE CORREO = '$correo'";
        $result = mysqli_query($conexion, $checkCorreo);

        if (mysqli_num_rows($result) > 0) {
            echo "<script>alert('Correo inválido. Ya está registrado. Ingrese uno nuevo.');</script>";
        } else {
            // Validar las contraseñas
            if ($clave == $Rclave && strlen($clave) >= 5 && strlen($clave) <= 20) {
                // Insertar los datos en la base de datos
                $insertarDatos = "INSERT INTO USUARIO (correo, clave, nombre, apellido_paterno, apellido_materno, puesto) 
                                VALUES ('$correo', '$clave', '$nombre', '$apellido_paterno', '$apellido_materno', '$puesto')";
                $ejecutarInsertar = mysqli_query($conexion, $insertarDatos);

                if ($ejecutarInsertar) {
                    $registro_exitoso = true;
                } else {
                    echo "<script>alert('Error al registrar. Intente nuevamente.');</script>";
                }
            } else {
                echo "<script>alert('Contraseñas no coinciden o no cumplen con los requisitos (5-20 caracteres).');</script>";
            }
        }
    }

    if ($registro_exitoso) {
        header("Location: index.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE-edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro</title>
    <link rel="stylesheet" href="style.css">

    <style>
        .error-message {
            color: #FF4C4C;
            font-size: 12px;
            position: absolute;
            top: 100%;
            left: 0;
            width: 100%;
            background-color: #FFF3F3;
            padding: 5px;
            margin-top: 5px;
            border: 1px solid #FF4C4C;
            border-radius: 5px;
            display: none;
        }
    </style>
</head>
<body class="login-body">
    <header class="header">
        <div class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>
    
    <div class="login-container">
        <h2>Registro</h2>
        <form action="" method="POST">
            <label>Correo</label>
            <input type="email" name="CORREO" onkeydown="return event.key !== ' '" required>
            <span class="error-message" id="correo-error"></span>

            <label>Contraseña</label>
            <input type="password" name="CLAVE" onkeydown="return event.key !== ' '" required>
            <span class="error-message" id="clave-error"></span>

            <label>Repita Contraseña</label>
            <input type="password" name="RCLAVE" onkeydown="return event.key !== ' '" required>
            <span class="error-message" id="rclave-error"></span>

            <label>Nombre</label>
            <input type="text" name="NOMBRE" maxlength="20" style="text-transform:uppercase;" 
                   oninput="this.value = this.value.toUpperCase()" 
                   onkeypress="return !/\d/.test(event.key)" required>
            <span class="error-message" id="nombre-error"></span>

            <label>Apellido Paterno</label>
            <input type="text" name="APELLIDO_PATERNO" maxlength="20" style="text-transform:uppercase;" 
                   oninput="this.value = this.value.toUpperCase()" 
                   onkeypress="return !/\d/.test(event.key)" required>
            <span class="error-message" id="apellido-paterno-error"></span>

            <label>Apellido Materno</label>
            <input type="text" name="APELLIDO_MATERNO" maxlength="20" style="text-transform:uppercase;" 
                   oninput="this.value = this.value.toUpperCase()" 
                   onkeypress="return !/\d/.test(event.key)" required>
            <span class="error-message" id="apellido-materno-error"></span>

            <label>Puesto</label>
            <select name="PUESTO" required>
                <option value="Dueño">Dueño</option>
                <option value="Encargado de administracion">Encargado de administración</option>
                <option value="Encargado de inyeccion de Plastico">Encargado de inyección de plástico</option>
            </select>

            <button type="submit" name="registro">Registrarse</button>
        </form>

        <p><a href="index.php">Regresar</a></p>
    </div>

    <script>
        var correoInput = document.querySelector('input[name="CORREO"]');
        var correoError = document.getElementById('correo-error');
        correoInput.addEventListener('blur', function() {
            var emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,6}$/;
            if (!emailPattern.test(correoInput.value)) {
                correoError.textContent = "Correo inválido. Ingrese un correo válido.";
                correoError.style.display = "block";
            } else {
                correoError.textContent = "";
                correoError.style.display = "none";
            }
        });

        function validarMayusculasSinNumeros(input, errorElement, campoNombre) {
            if (/\d/.test(input.value)) {
                errorElement.textContent = `El ${campoNombre} no debe contener números.`;
                errorElement.style.display = "block";
            } else if (input.value !== input.value.toUpperCase()) {
                errorElement.textContent = `El ${campoNombre} debe estar en MAYÚSCULAS.`;
                errorElement.style.display = "block";
            } else if (input.value.length > 20) {
                errorElement.textContent = `El ${campoNombre} no debe tener más de 20 caracteres.`;
                errorElement.style.display = "block";
            } else {
                errorElement.textContent = "";
                errorElement.style.display = "none";
            }
        }

        document.querySelector('input[name="NOMBRE"]').addEventListener('blur', function() {
            validarMayusculasSinNumeros(this, document.getElementById('nombre-error'), "nombre");
        });

        document.querySelector('input[name="APELLIDO_PATERNO"]').addEventListener('blur', function() {
            validarMayusculasSinNumeros(this, document.getElementById('apellido-paterno-error'), "apellido paterno");
        });

        document.querySelector('input[name="APELLIDO_MATERNO"]').addEventListener('blur', function() {
            validarMayusculasSinNumeros(this, document.getElementById('apellido-materno-error'), "apellido materno");
        });
    </script>
</body>
</html>
