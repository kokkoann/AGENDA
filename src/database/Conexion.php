<?php
require_once __DIR__ . '/../../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../../');
$dotenv->load();

$host = $_ENV['DB_HOST'];
$user = $_ENV['DB_USERNAME'];
$pass = $_ENV['DB_PASSWORD'];
$port = $_ENV['DB_PORT'];
$db   = $_ENV['DB_DATABASE'];

$conexion = mysqli_connect($host, $user, $pass, $db, $port);

if (mysqli_connect_errno()) {
    error_log("Error de conexión: " . mysqli_connect_error());
    die("Error de conexión: " . mysqli_connect_error());
} 