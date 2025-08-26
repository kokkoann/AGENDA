<?php
session_start();
include_once('Conexion.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    function validar($data) {
        return htmlspecialchars(trim($data));
    }

    $Correo = validar($_POST['CORREO']);
    $Nombre = validar($_POST['NOMBRE']);
    $Apellido_Paterno = validar($_POST['APELLIDO_PATERNO']);
    $Apellido_Materno = validar($_POST['APELLIDO_MATERNO']);
    $Puesto = validar($_POST['PUESTO']);
    $Clave = validar($_POST['CLAVE']);
    $Rclave = validar($_POST['RCLAVE']);

    $datosUsuarios = "CORREO=$Correo&NOMBRE=$Nombre&APELLIDO_PATERNO=$Apellido_Paterno&APELLIDO_MATERNO=$Apellido_Materno&PUESTO=$Puesto";

    if (empty($Correo) || empty($Nombre) || empty($Apellido_Paterno) || empty($Apellido_Materno) || empty($Puesto) || empty($Clave) || empty($Rclave)) {
        echo "<script>
                alert('Todos los campos son obligatorios');
                window.location.href = '../Registro.php?$datosUsuarios';
              </script>";
        exit();
    } elseif ($Clave !== $Rclave) {
        echo "<script>
                alert('Las contraseñas no coinciden');
                window.location.href = '../Registro.php?$datosUsuarios';
              </script>";
        exit();
    }

    $Clave = md5($Clave);
    $sql = "SELECT * FROM usuarios WHERE CORREO = '$Correo'";
    $query = $conexion->query($sql);

    if (mysqli_num_rows($query) > 0) {
        echo "<script>
                alert('El usuario ya existe');
                window.location.href = '../Registro.php';
              </script>";
        exit();
    } else {
        $sql2 = "INSERT INTO usuarios (CORREO, NOMBRE, APELLIDO_PATERNO, APELLIDO_MATERNO, CLAVE, PUESTO) 
                 VALUES ('$Correo', '$Nombre', '$Apellido_Paterno', '$Apellido_Materno', '$Clave', '$Puesto')";

        if ($conexion->query($sql2)) {
            echo "<script>
                    alert('Usuario registrado con éxito');
                    window.location.href = '../index.php';
                  </script>";
        } else {
            echo "<script>
                    alert('Error al registrar usuario');
                    window.location.href = '../Registro.php';
                  </script>";
        }
        exit();
    }
} else {
    header('location: ../Registro.php');
    exit();
}
?>
