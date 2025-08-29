<?php
session_start();
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Menu.php"); 
    exit();
}
?>