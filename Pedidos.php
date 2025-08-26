<?php
require 'Conexion.php';
session_start();


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
    </script>
</head>
<body>
<?php
echo "<pre>";
print_r($_GET);
echo "</pre>";
?>

<?php
if (isset($_GET['actualizado']) && $_GET['actualizado'] == '1') {
    echo "<div style='background-color: #d4edda; color: #155724; padding: 10px; border: 1px solid #c3e6cb; margin: 15px 0;'>
            ¡Pedido actualizado correctamente!
          </div>";
}
?>

    <!-- Encabezado -->
    <header class="header">
        <div class="logo-container">
            <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <!-- Menú de navegación -->
    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php">Agenda</a></li>
            <li><a href="Pedidos.php" class="active">Pedidos</a></li>
            <li><a href="Clientes.php">Clientes</a></li>
            <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
        </ul>
    </nav>

    <?php
    require 'Conexion2.php';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $tipo = strtoupper($_POST['piezas_cve_pieza']);
        $cliente = strtoupper($_POST['clientes_id_cte']);
        $piezas = $_POST['cantidad_piezas'];
        $fecharecibido = $_POST['fecha_recibido'];
        $fechaentrega = $_POST['fecha_entrega'];
        $Preciofinal = $_POST['Precio_final'];
    
        $hoy = date('Y-m-d');
    
        if ($fecharecibido >= $hoy && $fechaentrega >= $hoy) {
            // Verificar si ya existe el pedido
            $checkSql = "SELECT COUNT(*) FROM pedidos 
                         WHERE piezas_cve_pieza = ? 
                         AND clientes_id_cte = ? 
                         AND fecha_entrega = ?";
            $checkStmt = $pdo->prepare($checkSql);
            $checkStmt->execute([$tipo, $cliente, $fechaentrega]);
            $existe = $checkStmt->fetchColumn();
    
            if ($existe > 0) {
                echo "<script>
            alert('Ya existe un pedido con esta pieza, cliente y fecha de entrega.');
            window.location.href = 'Pedidos.php';
        </script>";
            } else {
                // Insertar si no existe
                $sql = "INSERT INTO pedidos 
                        (piezas_cve_pieza, clientes_id_cte, cantidad_piezas, fecha_recibido, fecha_entrega, Precio_final) 
                        VALUES (?, ?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$tipo, $cliente, $piezas, $fecharecibido, $fechaentrega, $Preciofinal]);
                echo "<script>
            alert('Pedido agregado correctamente.');
            window.location.href = 'Pedidos.php';
        </script>";
                
            }
        } else {
            echo "<script>
            alert('Las fechas deben ser a partir de hoy.');
            window.location.href = 'Pedidos.php';
        </script>";
           
        }
    }
    
?>

    <!-- Contenido de clientes -->
    <div class="container">
    <button onclick="toggleForm()" class="btn">+</button>

    <div id="cliente-form" style="display: none; margin-top: 20px;">
            <form action="Pedidos.php" method="POST" onsubmit="return validarFechas()">
                <h1>Agregar Pedido</h1>

                <label for="piezas_cve_pieza" >Tipo de pieza:</label>
            <select name="piezas_cve_pieza" id="piezas_cve_pieza" required
                oninvalid="this.setCustomValidity('Selecciona un tipo de pieza')"
                oninput="this.setCustomValidity('')" style="text-transform: uppercase;">
            <option value="" disabled selected>Selecciona un tipo de pieza</option>
            <option value="TAPÓN MANGUERA 3">TAPÓN MANGUERA 3</option>
            <option value="TAPÓN MANGUERA 2 1/4">TAPÓN MANGUERA 2' 1/4</option>
            <option value="CONDUIT 4">CONDUIT 4</option>
            <option value="CONDUIT 3">CONDUIT 3</option>
            </select>

                <label >Cliente:</label>
                <input type="text" 
       name="clientes_id_cte" 
       placeholder="CLIENTE"
       maxlength="20"
       required
       pattern="[A-Za-zÁÉÍÓÚáéíóúÑñ\s]+" 
       oninvalid="this.setCustomValidity('Rellena este campo')" 
       oninput="this.setCustomValidity('')" 
       onkeypress="return /^[A-Za-záéíóúÑñ\s]$/.test(event.key)"
       style="text-transform: uppercase;">
                <label >Cantidad de piezas:</label>
                <input type="text" name="cantidad_piezas" id="cantidad_piezas"
       placeholder="CANTIDAD DE PIEZAS"
       maxlength="10"
       pattern="\d+"
       required
       oninput="this.value = this.value.replace(/[^0-9]/g, '')"
       title="Solo se permiten números de hasta 10 dígitos">
                <script>
                // Obtener el año actual y crear una fecha máxima al 31 de diciembre del mismo
                const fechaMax = new Date().getFullYear() + "-12-31";
                    document.addEventListener("DOMContentLoaded", () => {
                    
                    document.getElementById("fecha_recibido").max = fechaMax;
                     });
                </script>

                <label for="fecha_recibido">Fecha de recibido:</label>
                <input type="date" id="fecha_recibido" name="fecha_recibido" required
                    oninvalid="this.setCustomValidity('Fecha Invalida')"
                    oninput="this.setCustomValidity('')" min="<?= date('Y-m-d'); ?>">

                <label for="fecha_entrega">Fecha de entrega:</label>
                <input type="date" id="fecha_entrega" name="fecha_entrega" required
                     min="<?= date('Y-m-d'); ?>" oninvalid="this.setCustomValidity('Fecha Invalida')" max="2100-12-31">

                    <div id="error-fechas" style="color: #ff4d4d; font-size: 0.9em; margin-top: 5px;"></div>
                    <label>Precio final:</label>
                    <input type="text" name="Precio_final" id="Precio_final" placeholder="PRECIO FINAL" maxlength="10" pattern="\d+" required oninput="this.value = this.value.replace(/[^0-9]/g, '')" title="Solo se permiten números de hasta 10 dígitos">
          

                <input class="btn"type="submit" value="Añadir Pedido">
                <a class="btn" href="Pedidos.php">Regresar a Pedidos</a>
                
            </form>
            <script>
  function validarFechas() {
    const recibidoInput = document.getElementById('fecha_recibido');
    const entregaInput = document.getElementById('fecha_entrega');
    const recibido = new Date(recibidoInput.value);
    const entrega = new Date(entregaInput.value);
    const errorDiv = document.getElementById('error-fechas');

    // Reset estilos y mensajes previos
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
        
include('conexion2.php');

$sql = "SELECT * FROM pedidos ORDER BY fecha_recibido, fecha_entrega";
$stmt = $pdo->query($sql);
$citas = $stmt->fetchAll();



foreach ($citas as $cita) {

    echo "<div>";
    echo "<strong>" . htmlspecialchars($cita['piezas_cve_pieza']) . "</strong><br>";
    echo "<strong>" . htmlspecialchars($cita['clientes_id_cte']) . "</strong><br>";
    echo "<strong>" . htmlspecialchars($cita['cantidad_piezas']) . "</strong><br>";
    echo "Fecha: " . htmlspecialchars($cita['fecha_recibido']) . "<br>";
    echo "Fecha: " . htmlspecialchars($cita['fecha_entrega']) . "<br>";
    echo "<strong>" . htmlspecialchars($cita['Precio_final']) . "</strong><br>";

    // Enlace con confirmación personalizada
    echo "<a href='eliminar_pedido.php?id=" . $cita['cve_pedido'] . "' 
        onclick=\"return confirm('¿Estás seguro de eliminar el pedido de " . 
        htmlspecialchars($cita['piezas_cve_pieza']) . 
        " para " . htmlspecialchars($cita['clientes_id_cte']) . "?');\">Eliminar</a> | ";

    echo "<a href='editarpedido.php?id=" . $cita['cve_pedido'] . "'>Editar</a>";
    echo "</div><hr>";
}

?>
        
        
      
</body>
</html>
