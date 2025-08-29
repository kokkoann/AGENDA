<?php
require '../database/Conexion.php';
session_start();

if (!isset($_GET['id'])) {
    header('Location: Pedidos.php');
    exit;
}

$id_pedido = (int)$_GET['id'];

// Obtener datos actuales del pedido
$sql = "SELECT p.*, 
        (SELECT NOMBRE_PIEZA FROM PIEZAS WHERE CVE_PIEZA = p.PIEZAS_CVE_PIEZA) AS NOMBRE_PIEZA,
        (SELECT NOMBRE_CTE FROM CLIENTES WHERE ID_CTE = p.CLIENTES_ID_CTE) AS NOMBRE_CLIENTE
        FROM PEDIDOS p
        WHERE CVE_PEDIDO = $id_pedido
        LIMIT 1";

$result = mysqli_query($conexion, $sql);
if (mysqli_num_rows($result) == 0) {
    echo "Pedido no encontrado.";
    exit;
}

$pedido = mysqli_fetch_assoc($result);

// Procesar envío del formulario para actualizar pedido
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nombre_cliente = strtoupper(mysqli_real_escape_string($conexion, $_POST['clientes_id_cte']));
    $query_cliente = "SELECT ID_CTE FROM CLIENTES WHERE NOMBRE_CTE = '$nombre_cliente' LIMIT 1";
    $result_cliente = mysqli_query($conexion, $query_cliente);

    if(mysqli_num_rows($result_cliente) > 0) {
        $row_cliente = mysqli_fetch_assoc($result_cliente);
        $id_cliente = $row_cliente['ID_CTE'];

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
                $sql_update = "UPDATE PEDIDOS SET 
                                PIEZAS_CVE_PIEZA = '$id_pieza',
                                CLIENTES_ID_CTE = '$id_cliente',
                                CANTIDAD_PIEZAS = '$piezas',
                                FECHA_RECIBIDO = '$fecharecibido',
                                FECHA_ENTREGA = '$fechaentrega',
                                PRECIO_FINAL = '$Preciofinal'
                               WHERE CVE_PEDIDO = $id_pedido";

                if(mysqli_query($conexion, $sql_update)) {
                    header("Location: Pedidos.php?actualizado=1");
                    exit;
                } else {
                    $error = "Error al actualizar pedido: " . mysqli_error($conexion);
                }
            } else {
                $error = "Las fechas deben ser a partir de hoy.";
            }
        } else {
            $error = "La pieza seleccionada no existe.";
        }
    } else {
        $error = "El cliente no existe. Regístrelo primero.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Editar Pedido</title>
    <link rel="stylesheet" href="style.css" />
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
</head>
<body>

<header class="header">
    <div class="logo-container">
        <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo" />
        <span class="company-name">Plásticos San Ángel</span>
    </div>
</header>

<nav class="menu-container">
    <ul>
        <li><a href="Agenda.php">Agenda</a></li>
        <li><a href="Pedidos.php">Pedidos</a></li>
        <li><a href="Clientes.php">Clientes</a></li>
        <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
    </ul>
</nav>

<div class="container">
    <h1>Editar Pedido</h1>

    <?php if(isset($error)): ?>
        <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border: 1px solid #f5c6cb;">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form action="editarpedido.php?id=<?= $id_pedido ?>" method="POST" onsubmit="return validarFechas()">
        <label for="piezas_cve_pieza">Tipo de pieza:</label>
        <select name="piezas_cve_pieza" id="piezas_cve_pieza" required
            oninvalid="this.setCustomValidity('Selecciona un tipo de pieza')"
            oninput="this.setCustomValidity('')" style="text-transform: uppercase;">
            <option value="" disabled>Selecciona un tipo de pieza</option>
            <?php
            $query_piezas = "SELECT NOMBRE_PIEZA FROM PIEZAS";
            $result_piezas = mysqli_query($conexion, $query_piezas);
            while($pieza = mysqli_fetch_assoc($result_piezas)) {
                $selected = ($pieza['NOMBRE_PIEZA'] == $pedido['NOMBRE_PIEZA']) ? 'selected' : '';
                echo '<option value="'.$pieza['NOMBRE_PIEZA'].'" '.$selected.'>'.$pieza['NOMBRE_PIEZA'].'</option>';
            }
            ?>
        </select>

        <label for="clientes_id_cte">Cliente:</label>
        <select name="clientes_id_cte" id="clientes_id_cte" required
            oninvalid="this.setCustomValidity('Selecciona un cliente')"
            oninput="this.setCustomValidity('')">
            <option value="" disabled>Selecciona un cliente</option>
            <?php
            $query_clientes = "SELECT NOMBRE_CTE FROM CLIENTES";
            $result_clientes = mysqli_query($conexion, $query_clientes);
            while($cliente = mysqli_fetch_assoc($result_clientes)) {
                $selected = ($cliente['NOMBRE_CTE'] == $pedido['NOMBRE_CLIENTE']) ? 'selected' : '';
                echo '<option value="'.$cliente['NOMBRE_CTE'].'" '.$selected.'>'.$cliente['NOMBRE_CTE'].'</option>';
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
            title="Solo se permiten números de hasta 10 dígitos"
            value="<?= htmlspecialchars($pedido['CANTIDAD_PIEZAS']) ?>">

        <label for="fecha_recibido">Fecha de recibido:</label>
        <input type="date" id="fecha_recibido" name="fecha_recibido" required
            oninvalid="this.setCustomValidity('Fecha Invalida')"
            oninput="this.setCustomValidity('')" min="<?= date('Y-m-d'); ?>"
            value="<?= htmlspecialchars($pedido['FECHA_RECIBIDO']) ?>">

        <label for="fecha_entrega">Fecha de entrega:</label>
        <input type="date" id="fecha_entrega" name="fecha_entrega" required
            min="<?= date('Y-m-d'); ?>" oninvalid="this.setCustomValidity('Fecha Invalida')" max="2100-12-31"
            value="<?= htmlspecialchars($pedido['FECHA_ENTREGA']) ?>">

        <div id="error-fechas" style="color: #ff4d4d; font-size: 0.9em; margin-top: 5px;"></div>
        
        <label for="Precio_final">Precio final:</label>
        <input type="text" name="Precio_final" id="Precio_final" 
            placeholder="PRECIO FINAL" 
            maxlength="10" 
            pattern="\d+" 
            required 
            oninput="this.value = this.value.replace(/[^0-9]/g, '')" 
            title="Solo se permiten números de hasta 10 dígitos"
            value="<?= htmlspecialchars($pedido['PRECIO_FINAL']) ?>">

        <input class="btn" type="submit" value="Actualizar Pedido">
        <a class="btn" href="Pedidos.php">Regresar a Pedidos</a>
    </form>
</div>

</body>
</html>
