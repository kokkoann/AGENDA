<?php
require '../database/Conexion.php';
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

// Función para validar el horario laboral (8:00 am a 10:00 pm)
function validarHorarioLaboral($hora) {
    $horaMin = strtotime('08:00:00');
    $horaMax = strtotime('22:00:00');
    $horaEvento = strtotime($hora);
    
    return ($horaEvento >= $horaMin && $horaEvento <= $horaMax);
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
    
    if (!validarHorarioLaboral($hora)) {
        $errores[] = "EL HORARIO LABORAL ES DE 8:00 AM A 10:00 PM";
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
    
    if (!validarHorarioLaboral($hora)) {
        $errores[] = "EL HORARIO LABORAL ES DE 8:00 AM A 10:00 PM";
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

$stmt = $conexion->prepare("SELECT * FROM EVENTOS WHERE USUARIO_ID = ? AND FECHA BETWEEN ? AND ? ORDER BY FECHA, HORA");
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
    <link rel="stylesheet" href="agenda.css">
</head>
<body>
    <header class="header">
        <div class="logo-container">
            <a href="../session/Menu.php">
                <img src="../../public/IMAGENES/LogoPSA.jpg" alt="Logo" class="logo">
            </a>
            <span class="company-name">Plásticos San Ángel</span>
        </div>
    </header>

    <nav class="menu-container">
        <ul>
            <li><a href="Agenda.php" class="active">Agenda</a></li>
            <li><a href="../pedidos/Pedidos.php">Pedidos</a></li>
            <li><a href="../clientes/Clientes.php">Clientes</a></li>
            <li><a href="../CerrarSesion.php" class="logout-btn">Cerrar Sesión</a></li>
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
                <input type="date" id="fecha" name="fecha" required 
                       min="<?php echo date('Y-m-d'); ?>" 
                       max="2100-12-31">
                <small style="color: #FFD700;">Rango permitido: <?php echo date('d/m/Y'); ?> - 31/12/2100</small>

                <label for="hora">HORA:</label>
                <input type="time" id="hora" name="hora" required min="08:00" max="22:00">

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
                       value="<?php echo $evento['FECHA']; ?>" 
                       min="<?php echo date('Y-m-d'); ?>" 
                       max="2100-12-31">
                <small style="color: #FFD700;">Rango permitido: <?php echo date('d/m/Y'); ?> - 31/12/2100</small>
                
                <label for="hora-editar">HORA:</label>
                <input type="time" id="hora-editar" name="hora" required
                       value="<?php echo $evento['HORA']; ?>" min="08:00" max="22:00">
                
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

        // Validación del formulario (ACTUALIZADA)
        function validarFormulario() {
            const fechaInput = document.querySelector('input[type="date"]');
            const horaInput = document.querySelector('input[type="time"]');
            
            // Validación de año máximo (2100)
            if (fechaInput) {
                const anioEvento = new Date(fechaInput.value).getFullYear();
                if (anioEvento > 2100) {
                    alert('EL AÑO MÁXIMO PERMITIDO ES 2100');
                    return false;
                }
            }
            
            // Validación de horario laboral
            if (horaInput) {
                const hora = horaInput.value;
                const horaParts = hora.split(':');
                const horas = parseInt(horaParts[0]);
                const minutos = parseInt(horaParts[1]);
                
                if (horas < 8 || horas >= 22) {
                    alert('EL HORARIO LABORAL ES DE 8:00 AM A 10:00 PM');
                    return false;
                }
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