<?php
session_start();
session_destroy(); // Cierra la sesión actual
header("Location: index.php"); // Redirige a index.php
exit();
?>
