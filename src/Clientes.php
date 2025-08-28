<?php
require 'Conexion.php';
session_start();

// Consultar todos los clientes ordenados por el nombre
$query = "SELECT * FROM clientes ORDER BY NOMBRE_CTE ASC";
$result = mysqli_query($conexion, $query);

// Organizar clientes por letra inicial
$clientesPorLetra = [];
while ($row = mysqli_fetch_assoc($result)) {
    $letraInicial = strtoupper(substr($row['NOMBRE_CTE'], 0, 1)); // Primera letra del nombre
    $clientesPorLetra[$letraInicial][] = $row;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Clientes</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleForm() {
            var form = document.getElementById('cliente-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }
        
        // Función para contar espacios y limitarlos
        function validarEspacios(input) {
            var texto = input.value;
            var espacios = (texto.match(/ /g) || []).length;
            
            if (espacios > 5) {
                alert('Máximo 5 espacios permitidos');
                input.value = texto.substring(0, texto.lastIndexOf(' '));
            }
        }
    </script>
</head>
<body>
    <!-- Encabezado -->
    <header class="header">
        <a href="Menu.php" class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </a>
    </header>

    <!-- Menú de navegación -->
    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php">Agenda</a></li>
            <li><a href="Pedidos.php">Pedidos</a></li>
            <li><a href="Clientes.php" class="active">Clientes</a></li>
            <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <!-- Contenido de clientes -->
    <div class="container">
        <h1>Clientes</h1>
        <button onclick="toggleForm()" class="btn">+</button>
        
        <div id="cliente-form" style="display: none; margin-top: 20px;">
            <form action="AgregarCliente.php" method="POST" autocomplete="off">
                <label for="nombre">Nombre:</label>
                <input type="text" id="nombre" name="nombre" required
                    pattern="[A-Z ]{1,22}"
                    maxlength="22"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                    title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

                <label for="apellido_paterno">Apellido Paterno:</label>
                <input type="text" id="apellido_paterno" name="apellido_paterno" required
                    pattern="[A-Z ]{1,22}"
                    maxlength="22"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                    title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

                <label for="apellido_materno">Apellido Materno:</label>
                <input type="text" id="apellido_materno" name="apellido_materno" required
                    pattern="[A-Z ]{1,22}"
                    maxlength="22"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z ]/g, ''); validarEspacios(this)"
                    title="Solo letras mayúsculas sin acentos, máximo 5 espacios">

                <label for="empresa">Empresa:</label>
                <input type="text" id="empresa" name="empresa"
                    pattern="[A-Z0-9 ]{1,22}"
                    maxlength="22"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9 ]/g, ''); validarEspacios(this)"
                    title="Solo letras mayúsculas y números sin acentos, máximo 5 espacios">

                <label for="rfc">RFC:</label>
                <input type="text" id="rfc" name="rfc"
                    pattern="[A-Z0-9]{1,13}"
                    maxlength="13"
                    oninput="this.value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '')"
                    title="Solo letras mayúsculas y números, sin espacios">

                <label for="telefono">Teléfono:</label>
                <input type="text" id="telefono" name="telefono" required
                    pattern="[0-9]{1,15}"
                    maxlength="15"
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    title="Solo números, sin espacios">

                <label for="correo">Correo:</label>
                <input type="email" id="correo" name="correo" required
                    maxlength="30"
                    title="Máximo 30 caracteres">

                <button type="submit" class="btn">Agregar Cliente</button>
            </form>
        </div>

        <!-- Mostrar lista de clientes por letra -->
        <div class="clientes-lista">
            <?php foreach ($clientesPorLetra as $letra => $clientes): ?>
                <h2><?php echo $letra; ?></h2>
                <ul>
                    <?php foreach ($clientes as $cliente): ?>
                        <li>
                            <a href="DetallesCliente.php?id=<?php echo $cliente['ID_CTE']; ?>">
                                <?php echo $cliente['NOMBRE_CTE'] . ' ' . $cliente['APELLIDO_PATERNO_CTE']; ?>
                            </a>
                        </li>
                    <?php endforeach; ?>
                </ul>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>
