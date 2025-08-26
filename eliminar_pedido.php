<?php
include('conexion.php'); // Usa solo la conexión que corresponde (mysqli)

if (isset($_GET['id']) && is_numeric($_GET['id'])) {
    $cita = $_GET['id'];

    $sql = "DELETE FROM pedidos WHERE cve_pedido = '$cita'";
    $resultado = mysqli_query($conexion, $sql);

    if ($resultado) {
        echo "<script>
            alert('Los datos se eliminaron correctamente de la BD');
            window.location.href = 'Pedidos.php';
        </script>";
    } else {
        echo "<script>
            alert('Los datos NO se eliminaron correctamente de la BD');
            window.location.href = 'Pedidos.php';
        </script>";
    }
} else {
    echo "<script>
        alert('ID no válido');
        window.location.href = 'Pedidos.php';
    </script>";
}
?>