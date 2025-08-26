<?php
require 'Conexion.php';
session_start();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pedidos</title>
    <link rel="stylesheet" href="style.css">
    <script>
        function toggleForm() {
            var form = document.getElementById('cliente-form');
            form.style.display = (form.style.display === 'none' || form.style.display === '') ? 'block' : 'none';
        }
    </script>
</head>
<body>
<?php
if (isset($_GET['actualizado']) && $_GET['actualizado'] == '1') {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin: 15px 0;'>
            ¡Pedido actualizado correctamente!
          </div>";
}
?>

    <header class="header">
        <div class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php">Agenda</a></li>
            <li><a href="Pedidos.php" class="active">Pedidos</a></li>
            <li><a href="Clientes.php">Clientes</a></li>
            <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <?php
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Obtener ID del cliente
        $nombre_cliente = strtoupper(mysqli_real_escape_string($conexion, $_POST['clientes_id_cte']));
        $query_cliente = "SELECT ID_CTE FROM CLIENTES WHERE NOMBRE_CTE = '$nombre_cliente' LIMIT 1";
        $result_cliente = mysqli_query($conexion, $query_cliente);
        
        if(mysqli_num_rows($result_cliente) > 0) {
            $row_cliente = mysqli_fetch_assoc($result_cliente);
            $id_cliente = $row_cliente['ID_CTE'];
            
            // Obtener ID de la pieza
            $nombre_pieza = strtoupper(mysqli_real_escape_string($conexion, $_POST['piezas_cve_pieza']));
            $query_pieza = "SELECT CVE_PIEZA FROM PIEZAS WHERE NOMBRE_PIEZA = '$nombre_pieza' LIMIT 1";
            $result_pieza = mysqli_query($conexion, $query_pieza);
            
            if(mysqli_num_rows($result_pieza) > 0) {
                $row_pieza = mysqli_fetch_assoc($result_pieza);
                $id_pieza = $row_pieza['CVE_PIEZA'];
                
                $piezas = mysqli_real_escape_string($conexion, $_POST['cantidad_piezas']);
                $fecharecibido = mysqli_real_escape_string($conexion, $_POST['fecha_recibido']);
                $fechaentrega = mysqli_real_escape_string($conexion, $_POST['fecha_entrega']);
                $Preciofinal = mysqli_real_escape_string($conexion, $_POST['Precio_final']);

                $hoy = date('Y-m-d');

                if ($fecharecibido >= $hoy && $fechaentrega >= $hoy) {
                    $sql = "INSERT INTO PEDIDOS 
                            (PIEZAS_CVE_PIEZA, CLIENTES_ID_CTE, CANTIDAD_PIEZAS, FECHA_RECIBIDO, FECHA_ENTREGA, PRECIO_FINAL) 
                            VALUES ('$id_pieza', '$id_cliente', '$piezas', '$fecharecibido', '$fechaentrega', '$Preciofinal')";
                    
                    if(mysqli_query($conexion, $sql)) {
                        echo "<script>
                            alert('Pedido agregado correctamente.');
                            window.location.href = 'Pedidos.php';
                        </script>";
                    } else {
                        echo "<script>
                            alert('Error al agregar pedido: ".mysqli_error($conexion)."');
                            window.location.href = 'Pedidos.php';
                        </script>";
                    }
                } else {
                    echo "<script>
                        alert('Las fechas deben ser a partir de hoy.');
                        window.location.href = 'Pedidos.php';
                    </script>";
                }
            } else {
                echo "<script>
                    alert('La pieza seleccionada no existe.');
                    window.location.href = 'Pedidos.php';
                </script>";
            }
        } else {
            echo "<script>
                alert('El cliente no existe. Regístrelo primero.');
                window.location.href = 'Clientes.php';
            </script>";
        }
    }
?>

    <div class="container">
        <button onclick="toggleForm()" class="btn">+</button>

        <div id="cliente-form" style="display: none; margin-top: 20px;">
            <form action="Pedidos.php" method="POST" onsubmit="return validarFechas()">
                <h1>Agregar Pedido</h1>

                <label for="piezas_cve_pieza">Tipo de pieza:</label>
                <select name="piezas_cve_pieza" id="piezas_cve_pieza" required
                    oninvalid="this.setCustomValidity('Selecciona un tipo de pieza')"
                    oninput="this.setCustomValidity('')" style="text-transform: uppercase;">
                    <option value="" disabled selected>Selecciona un tipo de pieza</option>
                    <?php
                    $query_piezas = "SELECT NOMBRE_PIEZA FROM PIEZAS";
                    $result_piezas = mysqli_query($conexion, $query_piezas);
                    while($pieza = mysqli_fetch_assoc($result_piezas)) {
                        echo '<option value="'.$pieza['NOMBRE_PIEZA'].'">'.$pieza['NOMBRE_PIEZA'].'</option>';
                    }
                    ?>
                </select>

                <label for="clientes_id_cte">Cliente:</label>
                <select name="clientes_id_cte" id="clientes_id_cte" required
                    oninvalid="this.setCustomValidity('Selecciona un cliente')"
                    oninput="this.setCustomValidity('')">
                    <option value="" disabled selected>Selecciona un cliente</option>
                    <?php
                    $query_clientes = "SELECT NOMBRE_CTE FROM CLIENTES";
                    $result_clientes = mysqli_query($conexion, $query_clientes);
                    while($cliente = mysqli_fetch_assoc($result_clientes)) {
                        echo '<option value="'.$cliente['NOMBRE_CTE'].'">'.$cliente['NOMBRE_CTE'].'</option>';
                    }
                    ?>
                </select>

                <label for="cantidad_piezas">Cantidad de piezas:</label>
                <input type="text" name="cantidad_piezas" id="cantidad_piezas"
                    placeholder="CANTIDAD DE PIEZAS"
                    maxlength="10"
                    pattern="\d+"
                    required
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                    title="Solo se permiten números de hasta 10 dígitos">

                <label for="fecha_recibido">Fecha de recibido:</label>
                <input type="date" id="fecha_recibido" name="fecha_recibido" required
                    oninvalid="this.setCustomValidity('Fecha Invalida')"
                    oninput="this.setCustomValidity('')" min="<?= date('Y-m-d'); ?>">

                <label for="fecha_entrega">Fecha de entrega:</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega" required
                    min="<?= date('Y-m-d'); ?>" oninvalid="this.setCustomValidity('Fecha Invalida')" max="2100-12-31">

                <div id="error-fechas" style="color: #ff4d4d; font-size: 0.9em; margin-top: 5px;"></div>
                
                <label for="Precio_final">Precio final:</label>
                <input type="text" name="Precio_final" id="Precio_final" 
                    placeholder="PRECIO FINAL" 
                    maxlength="10" 
                    pattern="\d+" 
                    required 
                    oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
                    title="Solo se permiten números de hasta 10 dígitos">

                <input class="btn" type="submit" value="Añadir Pedido">
                <a class="btn" href="Pedidos.php">Regresar a Pedidos</a>
            </form>
            <script>
                function validarFechas() {
                    const recibidoInput = document.getElementById('fecha_recibido');
                    const entregaInput = document.getElementById('fecha_entrega');
                    const recibido = new Date(recibidoInput.value);
                    const entrega = new Date(entregaInput.value);
                    const errorDiv = document.getElementById('error-fechas');

                    recibidoInput.style.border = '';
                    entregaInput.style.border = '';
                    errorDiv.textContent = '';

                    if (recibido >= entrega) {
                        errorDiv.textContent = 'La fecha de recibido debe ser anterior a la fecha de entrega.';
                        recibidoInput.style.border = '2px solid #ff4d4d';
                        entregaInput.style.border = '2px solid #ff4d4d';
                        return false;
                    }

                    return true;
                }
            </script>
        </div>

        <h1>Pedidos</h1>
        <?php
        $sql = "SELECT p.*, 
                (SELECT NOMBRE_PIEZA FROM PIEZAS WHERE CVE_PIEZA = p.PIEZAS_CVE_PIEZA) AS NOMBRE_PIEZA,
                (SELECT NOMBRE_CTE FROM CLIENTES WHERE ID_CTE = p.CLIENTES_ID_CTE) AS NOMBRE_CLIENTE
                FROM PEDIDOS p
                ORDER BY p.FECHA_RECIBIDO, p.FECHA_ENTREGA";
        $result = mysqli_query($conexion, $sql);
        
        if(mysqli_num_rows($result) > 0) {
            while($pedido = mysqli_fetch_assoc($result)) {
                echo "<div class='pedido-item'>";
                echo "<strong>Pieza: " . htmlspecialchars($pedido['NOMBRE_PIEZA']) . "</strong><br>";
                echo "<strong>Cliente: " . htmlspecialchars($pedido['NOMBRE_CLIENTE']) . "</strong><br>";
                echo "<strong>Cantidad: " . htmlspecialchars($pedido['CANTIDAD_PIEZAS']) . "</strong><br>";
                echo "Fecha recibido: " . htmlspecialchars($pedido['FECHA_RECIBIDO']) . "<br>";
                echo "Fecha entrega: " . htmlspecialchars($pedido['FECHA_ENTREGA']) . "<br>";
                echo "<strong>Precio: $" . htmlspecialchars($pedido['PRECIO_FINAL']) . "</strong><br>";

                echo "<div class='acciones-pedido'>";
                echo "<a href='eliminar_pedido.php?id=" . $pedido['CVE_PEDIDO'] . "' 
                    onclick=\"return confirm('¿Estás seguro de eliminar este pedido?');\">Eliminar</a> | ";
                echo "<a href='editarpedido.php?id=" . $pedido['CVE_PEDIDO'] . "'>Editar</a>";
                echo "</div></div><hr>";
            }
        } else {
            echo "<p>No hay pedidos registrados</p>";
        }
        ?>
    </div>
</body>
</html>