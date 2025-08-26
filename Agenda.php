<?php
require 'Conexion.php';
session_start();

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit();
}

// Función para obtener el nombre del mes en mayúsculas
function obtenerNombreMes($mes) {
    $meses = [
        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
    ];
    return $meses[$mes] ?? 'DESCONOCIDO';
}

// Normaliza texto (mayúsculas y espacios)
function normalizarTexto($texto) {
    $texto = mb_strtoupper(trim($texto), 'UTF-8');
    return preg_replace('/\s{4,}/', '   ', $texto);
}

// Valida contenido según reglas
function validarContenido($texto, $esTitulo = true) {
    $max = $esTitulo ? 15 : 30;
    if (mb_strlen($texto) > $max) return false;
    
    $patron = '/^[A-ZÁÉÍÓÚÜÑ0-9 .,!?\-()\/"\'#@:\n\r]+$/u';
    return preg_match($patron, $texto);
}

// Verifica eventos duplicados
function existeDuplicado($conexion, $usuario_id, $titulo, $fecha, $hora, $excluir_id = null) {
    $sql = "SELECT COUNT(*) AS total FROM EVENTOS 
            WHERE USUARIO_ID = ? AND TITULO = ? AND FECHA = ? AND HORA = ?";
    if ($excluir_id) $sql .= " AND CVE_EVENTO != ?";
    
    $stmt = $conexion->prepare($sql);
    $params = $excluir_id 
        ? [$usuario_id, $titulo, $fecha, $hora, $excluir_id]
        : [$usuario_id, $titulo, $fecha, $hora];
    
    $stmt->bind_param(str_repeat("s", count($params)), ...$params);
    $stmt->execute();
    $total = $stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();
    
    return $total > 0;
}

// --- AGREGAR EVENTO ---
if (isset($_POST['guardarEvento'])) {
    $titulo = normalizarTexto($_POST['titulo']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $descripcion = normalizarTexto($_POST['descripcion']);
    $usuario_id = $_SESSION['usuario_id'];

    $errores = [];
    
    if (!validarContenido($titulo, true)) {
        $errores[] = "TÍTULO: Máx. 15 caracteres. Caracteres permitidos: letras, números, . , ! ? - ( ) / \" ' # @ :";
    }
    
    if (!validarContenido($descripcion, false)) {
        $errores[] = "DESCRIPCIÓN: Máx. 30 caracteres. Se permiten saltos de línea";
    }
    
    if (existeDuplicado($conexion, $usuario_id, $titulo, $fecha, $hora)) {
        $errores[] = "YA EXISTE UN EVENTO CON ESTOS DATOS";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("INSERT INTO EVENTOS (USUARIO_ID, TITULO, FECHA, HORA, DESCRIPCION) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $usuario_id, $titulo, $fecha, $hora, $descripcion);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = "Evento creado correctamente";
        header("Location: Agenda.php");
        exit();
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['form_data'] = $_POST;
        header("Location: Agenda.php");
        exit();
    }
}

// --- ELIMINAR EVENTO ---
if (isset($_GET['eliminar'])) {
    $evento_id = (int)$_GET['eliminar'];
    $usuario_id = $_SESSION['usuario_id'];

    $stmt = $conexion->prepare("DELETE FROM EVENTOS WHERE CVE_EVENTO = ? AND USUARIO_ID = ?");
    $stmt->bind_param("ii", $evento_id, $usuario_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['mensaje'] = "Evento eliminado correctamente";
    header("Location: Agenda.php");
    exit();
}

// --- EDITAR EVENTO ---
if (isset($_POST['editarEvento'])) {
    $evento_id = (int)$_POST['evento_id'];
    $titulo = normalizarTexto($_POST['titulo']);
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $descripcion = normalizarTexto($_POST['descripcion']);

    $errores = [];
    
    if (!validarContenido($titulo, true)) {
        $errores[] = "TÍTULO: Máx. 15 caracteres. Caracteres permitidos: letras, números, . , ! ? - ( ) / \" ' # @ :";
    }
    
    if (!validarContenido($descripcion, false)) {
        $errores[] = "DESCRIPCIÓN: Máx. 30 caracteres. Se permiten saltos de línea";
    }
    
    if (existeDuplicado($conexion, $_SESSION['usuario_id'], $titulo, $fecha, $hora, $evento_id)) {
        $errores[] = "YA EXISTE OTRO EVENTO CON ESTOS DATOS";
    }

    if (empty($errores)) {
        $stmt = $conexion->prepare("UPDATE EVENTOS SET TITULO=?, FECHA=?, HORA=?, DESCRIPCION=? WHERE CVE_EVENTO=? AND USUARIO_ID=?");
        $stmt->bind_param("ssssii", $titulo, $fecha, $hora, $descripcion, $evento_id, $_SESSION['usuario_id']);
        $stmt->execute();
        $stmt->close();
        $_SESSION['mensaje'] = "Evento actualizado correctamente";
        header("Location: Agenda.php");
        exit();
    } else {
        $_SESSION['errores'] = $errores;
        $_SESSION['form_data'] = $_POST;
        header("Location: Agenda.php?editar=".$evento_id);
        exit();
    }
}

// Obtener eventos del mes actual
$mes = isset($_GET['mes']) ? (int)$_GET['mes'] : date('n');
$anio = isset($_GET['anio']) ? (int)$_GET['anio'] : date('Y');
$primerDia = mktime(0, 0, 0, $mes, 1, $anio);
$diasMes = date('t', $primerDia);
$diaSemana = date('w', $primerDia);

$eventos = [];
$inicioMes = date('Y-m-01', $primerDia);
$finMes = date('Y-m-'.$diasMes, $primerDia);

$stmt = $conexion->prepare("SELECT * FROM EVENTOS WHERE USUARIO_ID = ? AND FECHA BETWEEN ? AND ?");
$stmt->bind_param("iss", $_SESSION['usuario_id'], $inicioMes, $finMes);
$stmt->execute();
$resultado = $stmt->get_result();

while ($fila = $resultado->fetch_assoc()) {
    $dia = date('j', strtotime($fila['FECHA']));
    $eventos[$dia][] = $fila;
}
$stmt->close();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agenda - Plásticos San Ángel</title>
    <style>
        /* Estilos generales */
        body {
            font-family: 'Arial', sans-serif;
            margin: 0;
            padding: 0;
            background-color: #121212;
            color: #EAEAEA;
        }

        /* Encabezado */
        .header {
            background-color: #FFD700;
            padding: 15px;
            text-align: center;
            display: flex;
            align-items: center;
            justify-content: center;
            width: 100%;
            position: fixed;
            top: 0;
            left: 0;
            z-index: 1000;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .company-name {
            font-size: 24px;
            font-weight: bold;
            color: #111;
        }

        .logo {
            width: 50px;
            height: auto;
        }

        /* Menú de navegación */
        nav {
            background-color: #111;
            padding: 15px 0;
            margin-top: 70px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .menu-container {
            display: flex;
            justify-content: center;
            gap: 20px;
        }

        nav ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
        }

        nav ul li {
            margin: 0 15px;
        }

        nav ul li a {
            color: #FFD700;
            text-decoration: none;
            font-weight: bold;
            padding: 10px 20px;
            background-color: #222;
            border-radius: 5px;
            transition: background-color 0.3s;
        }

        nav ul li a:hover {
            background-color: #333;
        }

        nav ul li a.active {
            background-color: #FFD700;
            color: #111;
        }

        /* Contenedor principal */
        .container {
            max-width: 1200px;
            margin: 140px auto 30px;
            padding: 20px;
            background-color: #1E1E1E;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(255, 215, 0, 0.2);
        }

        h1 {
            color: #FFD700;
            text-align: center;
            margin-bottom: 30px;
        }

        /* Calendario */
        .calendar-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        .calendar-table th, .calendar-table td {
            padding: 10px;
            text-align: center;
            border: 1px solid #444;
            vertical-align: top;
        }

        .calendar-table th {
            background-color: #FFD700;
            color: #111;
        }

        .calendar-table td {
            background-color: #333;
            color: #EAEAEA;
        }

        .day-button {
            width: 40px;
            height: 40px;
            display: inline-block;
            background-color: #444;
            color: white;
            border: none;
            font-size: 16px;
            font-weight: bold;
            border-radius: 50%;
            margin-bottom: 10px;
            cursor: pointer;
            transition: background-color 0.3s;
        }

        .day-button:hover {
            background-color: #555;
        }

        .day-button.hoy {
            background-color: #FFD700;
            color: #111;
        }

        /* Eventos */
        .event-list {
            margin-top: 20px;
        }

        .event {
            background-color: #2B2B2B;
            border-left: 3px solid #FFD700;
            padding: 10px;
            margin: 10px 0;
            border-radius: 5px;
        }

        .event a {
            color: #FFD700;
            margin-left: 10px;
            font-weight: bold;
            text-decoration: none;
        }

        .event a:hover {
            text-decoration: underline;
        }

        /* Botones */
        .add-event-button {
            background-color: #FFD700;
            color: #111;
            padding: 12px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: bold;
            display: block;
            margin: 20px auto;
            width: 200px;
            text-align: center;
            transition: background-color 0.3s;
        }

        .add-event-button:hover {
            background-color: #C9A600;
        }

        /* Formularios */
        .event-form {
            background-color: #2B2B2B;
            padding: 20px;
            border-radius: 5px;
            margin-top: 20px;
            border: 1px solid #FFD700;
        }

        .event-form label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: #FFD700;
        }

        .event-form input, 
        .event-form textarea, 
        .event-form select {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            background-color: #333;
            border: 1px solid #FFD700;
            color: white;
            border-radius: 4px;
            font-size: 16px;
        }

        .event-form textarea {
            min-height: 100px;
            resize: vertical;
        }

        .event-form button {
            background-color: #FFD700;
            color: #111;
            padding: 12px 20px;
            border: none;
            border-radius: 4px;
            margin-top: 20px;
            cursor: pointer;
            font-weight: bold;
            font-size: 16px;
            width: 100%;
            transition: background-color 0.3s;
        }

        .event-form button:hover {
            background-color: #C9A600;
        }

        /* Navegación del calendario */
        .calendar-nav {
            text-align: center;
            margin: 20px 0;
        }

        .calendar-nav .btn {
            background-color: #444;
            color: #FFD700;
            padding: 10px 20px;
            margin: 0 10px;
            text-decoration: none;
            border-radius: 4px;
            font-weight: bold;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .calendar-nav .btn:hover {
            background-color: #555;
        }

        /* Mensajes */
        .error-message {
            color: #FF6B6B;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            background-color: #2B2B2B;
            border-left: 4px solid #FF6B6B;
            border-radius: 5px;
        }

        .success-message {
            color: #6BFF6B;
            font-weight: bold;
            margin: 20px 0;
            padding: 15px;
            background-color: #2B2B2B;
            border-left: 4px solid #6BFF6B;
            border-radius: 5px;
        }

        .contador-caracteres {
            font-size: 12px;
            color: #FFD700;
            text-align: right;
            display: block;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<!-- Encabezado -->
    <header class="header">
        <div class="logo-container">
            <a href="Menu.php">
                <img src="IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            </a>
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

<nav class="menu-container">
    <ul>
        <li><a href="Agenda.php" class="active">Agenda</a></li>
        <li><a href="Pedidos.php">Pedidos</a></li>
        <li><a href="Clientes.php">Clientes</a></li>
        <li><a href="Cerrarsesion.php" class="logout-btn">Cerrar Sesión</a></li>
    </ul>
</nav>

<div class="container">
    <h1>AGENDA - <?php echo obtenerNombreMes($mes) . " " . $anio; ?></h1>

    <?php if (isset($_SESSION['mensaje'])): ?>
        <div class="success-message">
            <?php echo $_SESSION['mensaje']; ?>
        </div>
        <?php unset($_SESSION['mensaje']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['errores'])): ?>
        <div class="error-message">
            <h3>ERRORES:</h3>
            <ul>
                <?php foreach ($_SESSION['errores'] as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php unset($_SESSION['errores']); ?>
    <?php endif; ?>

    <button class="add-event-button" onclick="mostrarFormulario()">AGREGAR EVENTO</button>

    <div class="calendar-nav">
        <a class="btn" href="?mes=<?php echo ($mes == 1) ? 12 : $mes - 1; ?>&anio=<?php echo ($mes == 1) ? $anio - 1 : $anio; ?>">
            &lt; MES ANTERIOR
        </a>
        <a class="btn" href="?mes=<?php echo date('n'); ?>&anio=<?php echo date('Y'); ?>">
            MES ACTUAL
        </a>
        <a class="btn" href="?mes=<?php echo ($mes == 12) ? 1 : $mes + 1; ?>&anio=<?php echo ($mes == 12) ? $anio + 1 : $anio; ?>">
            MES SIGUIENTE &gt;
        </a>
    </div>

    <table class="calendar-table">
        <thead>
            <tr>
                <th>DOM</th><th>LUN</th><th>MAR</th><th>MIÉ</th><th>JUE</th><th>VIE</th><th>SÁB</th>
            </tr>
        </thead>
        <tbody>
            <?php
            $diaActual = 1;
            $hoy = date('j');
            
            for ($i = 0; $i < 6; $i++) {
                echo "<tr>";
                for ($j = 0; $j < 7; $j++) {
                    if (($i == 0 && $j < $diaSemana) || $diaActual > $diasMes) {
                        echo "<td></td>";
                    } else {
                        $esHoy = ($diaActual == $hoy && $mes == date('n') && $anio == date('Y'));
                        echo "<td>";
                        echo "<button class='day-button " . ($esHoy ? "hoy" : "") . "' onclick='mostrarEventosDia($diaActual)'>$diaActual</button>";
                        
                        if (isset($eventos[$diaActual])) {
                            echo "<div style='font-size:10px; color:#FFD700;'>" . count($eventos[$diaActual]) . " EVENTO(S)</div>";
                        }
                        
                        echo "</td>";
                        $diaActual++;
                    }
                }
                echo "</tr>";
                if ($diaActual > $diasMes) break;
            }
            ?>
        </tbody>
    </table>

    <div id="eventos-dia" class="event-list"></div>

    <!-- Formulario para nuevo evento -->
    <div id="formulario-evento" style="display:none;" class="event-form">
        <h2>NUEVO EVENTO</h2>
        <form action="Agenda.php" method="post" onsubmit="return validarFormulario()">
            <label for="titulo">TÍTULO (MÁX. 15 CARACTERES):</label>
            <input type="text" id="titulo" name="titulo" maxlength="15" required
                   oninput="this.value = this.value.toUpperCase(); actualizarContador('titulo', 15)">
            <span id="contador-titulo" class="contador-caracteres">0/15</span>

            <label for="fecha">FECHA:</label>
            <input type="date" id="fecha" name="fecha" required>

            <label for="hora">HORA:</label>
            <input type="time" id="hora" name="hora" required>

            <label for="descripcion">DESCRIPCIÓN (MÁX. 30 CARACTERES):</label>
            <textarea id="descripcion" name="descripcion" rows="4" maxlength="30" required
                      oninput="this.value = this.value.toUpperCase(); actualizarContador('descripcion', 30)"></textarea>
            <span id="contador-descripcion" class="contador-caracteres">0/30</span>

            <button type="submit" name="guardarEvento">GUARDAR EVENTO</button>
        </form>
    </div>

    <!-- Formulario para editar evento -->
    <?php if (isset($_GET['editar'])): 
        $evento_id = (int)$_GET['editar'];
        $stmt = $conexion->prepare("SELECT * FROM EVENTOS WHERE CVE_EVENTO = ? AND USUARIO_ID = ?");
        $stmt->bind_param("ii", $evento_id, $_SESSION['usuario_id']);
        $stmt->execute();
        $evento = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        
        if ($evento):
    ?>
    <div id="editar-evento" class="event-form">
        <h2>EDITAR EVENTO</h2>
        <form action="Agenda.php" method="post" onsubmit="return validarFormulario()">
            <input type="hidden" name="evento_id" value="<?php echo $evento['CVE_EVENTO']; ?>">
            
            <label for="titulo-editar">TÍTULO (MÁX. 15 CARACTERES):</label>
            <input type="text" id="titulo-editar" name="titulo" maxlength="15" required
                   value="<?php echo htmlspecialchars($evento['TITULO']); ?>"
                   oninput="this.value = this.value.toUpperCase(); actualizarContador('titulo-editar', 15)">
            <span id="contador-titulo-editar" class="contador-caracteres"><?php echo mb_strlen($evento['TITULO']); ?>/15</span>
            
            <label for="fecha-editar">FECHA:</label>
            <input type="date" id="fecha-editar" name="fecha" required 
                   value="<?php echo $evento['FECHA']; ?>">
            
            <label for="hora-editar">HORA:</label>
            <input type="time" id="hora-editar" name="hora" required
                   value="<?php echo $evento['HORA']; ?>">
            
            <label for="descripcion-editar">DESCRIPCIÓN (MÁX. 30 CARACTERES):</label>
            <textarea id="descripcion-editar" name="descripcion" rows="4" maxlength="30" required
                      oninput="this.value = this.value.toUpperCase(); actualizarContador('descripcion-editar', 30)"><?php 
                      echo htmlspecialchars($evento['DESCRIPCION']); ?></textarea>
            <span id="contador-descripcion-editar" class="contador-caracteres"><?php echo mb_strlen($evento['DESCRIPCION']); ?>/30</span>
            
            <button type="submit" name="editarEvento">ACTUALIZAR EVENTO</button>
        </form>
    </div>
    <?php endif; endif; ?>
</div>

<script>
    // Mostrar/ocultar formulario
    function mostrarFormulario() {
        const formulario = document.getElementById('formulario-evento');
        formulario.style.display = formulario.style.display === 'none' ? 'block' : 'none';
    }

    // Mostrar eventos de un día específico
    function mostrarEventosDia(dia) {
        const eventos = <?php echo json_encode($eventos); ?>;
        const eventosDia = eventos[dia] || [];
        const contenedor = document.getElementById('eventos-dia');
        
        let html = '<h3>EVENTOS DEL DÍA ' + dia + '</h3>';
        
        if (eventosDia.length > 0) {
            eventosDia.forEach(evento => {
                html += `
                    <div class="event">
                        <strong>${evento.TITULO}</strong><br>
                        <small>${evento.HORA}</small><br>
                        ${evento.DESCRIPCION}<br>
                        <a href="Agenda.php?editar=${evento.CVE_EVENTO}">EDITAR</a> | 
                        <a href="Agenda.php?eliminar=${evento.CVE_EVENTO}" onclick="return confirm('¿ESTÁS SEGURO DE ELIMINAR ESTE EVENTO?')">ELIMINAR</a>
                    </div>
                `;
            });
        } else {
            html += '<p>NO HAY EVENTOS PARA ESTE DÍA</p>';
        }
        
        contenedor.innerHTML = html;
    }

    // Actualizar contadores de caracteres
    function actualizarContador(elementoId, maximo) {
        const elemento = document.getElementById(elementoId);
        const contadorId = 'contador-' + elementoId;
        const contador = document.getElementById(contadorId);
        
        if (elemento && contador) {
            contador.textContent = elemento.value.length + '/' + maximo;
        }
    }

    // Validación del formulario
    function validarFormulario() {
        // Validación de título
        const titulo = document.querySelector('input[name="titulo"]');
        if (titulo && titulo.value.length > 15) {
            alert('EL TÍTULO NO PUEDE TENER MÁS DE 15 CARACTERES');
            return false;
        }
        
        // Validación de descripción
        const descripcion = document.querySelector('textarea[name="descripcion"]');
        if (descripcion && descripcion.value.length > 30) {
            alert('LA DESCRIPCIÓN NO PUEDE TENER MÁS DE 30 CARACTERES');
            return false;
        }
        
        return true;
    }

    // Inicializar contadores al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        const tituloEditar = document.getElementById('titulo-editar');
        if (tituloEditar) {
            actualizarContador('titulo-editar', 15);
        }
        
        const descripcionEditar = document.getElementById('descripcion-editar');
        if (descripcionEditar) {
            actualizarContador('descripcion-editar', 30);
        }
    });
</script>
</body>
</html>