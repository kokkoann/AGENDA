<?php
require 'Conexion.php';
session_start();

if (isset($_GET['id'])) {
    $clienteId = $_GET['id'];

    // Consulta para eliminar el cliente
    $query = "DELETE FROM clientes WHERE ID_CTE = ?";
    $stmt = mysqli_prepare($conexion, $query);
    mysqli_stmt_bind_param($stmt, "i", $clienteId);

    if (mysqli_stmt_execute($stmt)) {
        // Redirigir a la lista de clientes después de eliminar
        header("Location: Clientes.php");
        exit();
    } else {
        echo "Error al eliminar el cliente: " . mysqli_error($conexion);
    }

    mysqli_stmt_close($stmt);
} else {
    echo "ID de cliente no proporcionado.";
}
?>
