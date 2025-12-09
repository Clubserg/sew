<?php
/**
 * Formulario de prueba de usabilidad
 * @author Sergio Fernandez-Miranda Longo
 */

// Incluir la clase Cronometro
require_once '../cronometro.php';

session_start();

$errorFormulario = false;
$errores = array();
$formularioPOST = "";
$pruebaIniciada = false;
$pruebaCompletada = false;
$mensajeFinal = false;
$mostrarFormularioInicial = false;

// Configuración de la base de datos
define('DB_HOST', 'localhost');
define('DB_USER', 'DBUSER2025');
define('DB_PASS', 'DBPSWD2025');
define('DB_NAME', 'uo302282_db');

// Función para conectar a la base de datos
function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    $conn->set_charset("utf8");
    return $conn;
}

// Mostrar formulario inicial de datos del usuario
if (!isset($_SESSION['usuario_registrado'])) {
    $mostrarFormularioInicial = true;
}

// Registrar usuario
if (isset($_POST['accion']) && $_POST['accion'] == 'registrarUsuario') {
    // Validar datos del usuario
    if (empty($_POST['profesion'])) {
        $errores['profesion'] = " * La profesión es obligatoria";
        $errorFormulario = true;
    }
    if (empty($_POST['edad']) || $_POST['edad'] < 1 || $_POST['edad'] > 150) {
        $errores['edad'] = " * La edad debe estar entre 1 y 150 años";
        $errorFormulario = true;
    }
    if (empty($_POST['genero'])) {
        $errores['genero'] = " * El género es obligatorio";
        $errorFormulario = true;
    }
    if (empty($_POST['pericia'])) {
        $errores['pericia'] = " * El nivel de pericia es obligatorio";
        $errorFormulario = true;
    }
    if (empty($_POST['dispositivo'])) {
        $errores['dispositivo'] = " * El tipo de dispositivo es obligatorio";
        $errorFormulario = true;
    }
    
    if (!$errorFormulario) {
        // Insertar usuario en la base de datos
        $conn = conectarDB();
        
        $profesion = $conn->real_escape_string($_POST['profesion']);
        $edad = intval($_POST['edad']);
        $genero = intval($_POST['genero']);
        $pericia = intval($_POST['pericia']);
        $dispositivo = intval($_POST['dispositivo']);
        
        $sql = "INSERT INTO Usuario (profesion, edad, id_genero, id_pericia) 
                VALUES ('$profesion', $edad, $genero, $pericia)";
        
        if ($conn->query($sql) === TRUE) {
            $codigo_usuario = $conn->insert_id;
            
            // Guardar datos del usuario en sesión
            $_SESSION['usuario'] = array(
                'codigo_usuario' => $codigo_usuario,
                'profesion' => $_POST['profesion'],
                'edad' => $_POST['edad'],
                'genero' => $_POST['genero'],
                'pericia' => $_POST['pericia'],
                'dispositivo' => $dispositivo
            );
            $_SESSION['usuario_registrado'] = true;
            $mostrarFormularioInicial = false;
        } else {
            $errores['general'] = " * Error al registrar usuario: " . $conn->error;
            $errorFormulario = true;
        }
        
        $conn->close();
    }
}

// Iniciar la prueba
if (isset($_POST['accion']) && $_POST['accion'] == 'iniciar') {
    $_SESSION['cronometro'] = new Cronometro();
    $_SESSION['cronometro']->iniciar();
    $pruebaIniciada = true;
    $pruebaCompletada = false;
}

// Terminar la prueba
if (isset($_POST['accion']) && $_POST['accion'] == 'terminar') {
    $formularioPOST = $_POST;
    
    // Validar que todas las preguntas han sido contestadas
    for ($i = 1; $i <= 10; $i++) {
        $nombreCampo = "pregunta" . $i;
        if (!isset($_POST[$nombreCampo]) || trim($_POST[$nombreCampo]) == "") {
            $errores[$nombreCampo] = " * Esta pregunta es obligatoria";
            $errorFormulario = true;
        }
    }
    
    // Validar comentarios del usuario
    if (empty($_POST['comentarios_usuario'])) {
        $errores['comentarios_usuario'] = " * Los comentarios son obligatorios";
        $errorFormulario = true;
    }
    
    // Validar propuestas de mejora
    if (empty($_POST['propuestas_mejora'])) {
        $errores['propuestas_mejora'] = " * Las propuestas de mejora son obligatorias";
        $errorFormulario = true;
    }
    
    // Validar valoración
    if (empty($_POST['valoracion_usuario']) || $_POST['valoracion_usuario'] < 0 || $_POST['valoracion_usuario'] > 10) {
        $errores['valoracion_usuario'] = " * La valoración debe estar entre 0 y 10";
        $errorFormulario = true;
    }
    
    // Si no hay errores, detener el cronómetro y procesar
    if (!$errorFormulario && isset($_SESSION['cronometro'])) {
        $_SESSION['cronometro']->detener();
        $tiempoTranscurrido = $_SESSION['cronometro']->obtenerTiempo();
        $_SESSION['tiempoTotal'] = $tiempoTranscurrido;
        
        // Guardar respuestas en sesión
        $_SESSION['respuestas'] = array();
        for ($i = 1; $i <= 10; $i++) {
            $_SESSION['respuestas']['pregunta' . $i] = $_POST['pregunta' . $i];
        }
        $_SESSION['comentarios_usuario'] = $_POST['comentarios_usuario'];
        $_SESSION['propuestas_mejora'] = $_POST['propuestas_mejora'];
        $_SESSION['valoracion_usuario'] = $_POST['valoracion_usuario'];
        
        $pruebaCompletada = true;
    }
}

// Guardar observaciones del facilitador y todo en la BD
if (isset($_POST['accion']) && $_POST['accion'] == 'guardarObservaciones') {
    $observaciones = $_POST['observaciones'];
    $tiempoTotal = $_SESSION['tiempoTotal'];
    
    $conn = conectarDB();
    
    // Iniciar transacción
    $conn->begin_transaction();
    
    try {
        // 1. Insertar en tabla ResultadoTestUsabilidad
        $codigo_usuario = $_SESSION['usuario']['codigo_usuario'];
        $id_dispositivo = $_SESSION['usuario']['dispositivo'];
        $comentarios_usuario = $conn->real_escape_string($_SESSION['comentarios_usuario']);
        $propuestas_mejora = $conn->real_escape_string($_SESSION['propuestas_mejora']);
        $valoracion_usuario = floatval($_SESSION['valoracion_usuario']);
        
        // Convertir tiempo a formato TIME de MySQL (HH:MM:SS)
        // Asumiendo que $tiempoTotal viene en formato string como "00:05:32"
        
        $sql = "INSERT INTO ResultadoTestUsabilidad 
                (codigo_usuario, id_dispositivo, tiempo_completado, tarea_completada, 
                 comentarios_usuario, propuestas_mejora, valoracion_usuario) 
                VALUES ($codigo_usuario, $id_dispositivo, '$tiempoTotal', TRUE, 
                        '$comentarios_usuario', '$propuestas_mejora', $valoracion_usuario)";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error al insertar resultado: " . $conn->error);
        }
        
        // 2. Insertar en tabla ObservacionFacilitador
        $observaciones_escaped = $conn->real_escape_string($observaciones);
        
        $sql = "INSERT INTO ObservacionFacilitador (codigo_usuario, comentarios_facilitador) 
                VALUES ($codigo_usuario, '$observaciones_escaped')";
        
        if (!$conn->query($sql)) {
            throw new Exception("Error al insertar observaciones: " . $conn->error);
        }
        
        // Confirmar transacción
        $conn->commit();
        $mensajeFinal = true;
        
        // Limpiar la sesión
        session_destroy();
        
        $conn->close();
        
        // Redirigir después de guardar exitosamente
        header("Location: pruebaUsabilidad.php");
        exit();
        
    } catch (Exception $e) {
        // Revertir cambios en caso de error
        $conn->rollback();
        $errores['general'] = " * Error al guardar datos: " . $e->getMessage();
        $conn->close();
    }
}

// Verificar si la prueba está en curso
if (isset($_SESSION['cronometro']) && !$mostrarFormularioInicial) {
    $pruebaIniciada = true;
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta name="author" content="Sergio Fernandez-Miranda Longo" />
    <meta name="description" content="Prueba de usabilidad del sitio web MotoGP" />
    <meta name="keywords" content="MotoGP, prueba, usabilidad, test" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <title>MotoGP - Prueba de Usabilidad</title>
    <link rel="icon" type="image/x-icon" href="../multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>

<body>
    <header>
        <h1>Prueba de Usabilidad - MotoGP Desktop</h1>
    </header>

    <main>
    <?php if (isset($errores['general'])): ?>
        <section>
            <p style="color: red;"><?php echo $errores['general']; ?></p>
        </section>
    <?php endif; ?>

    <?php if ($mensajeFinal): ?>
        <section>
            <h2>Fin de la prueba de usabilidad</h2>
            <p>Muchas gracias por participar, puedes cerrar esta pestaña</p>
        </section>

    <?php elseif ($mostrarFormularioInicial): ?>
        <section>
            <h2>Información del Participante</h2>
            <p>Antes de comenzar, necesitamos algunos datos básicos:</p>
        </section>
        
        <form action="#" method="post">
            <input type="hidden" name="accion" value="registrarUsuario" />
            
            <section>
                <label for="profesion">Profesión:</label>
                <input type="text" id="profesion" name="profesion" value="<?php echo isset($_POST['profesion']) ? htmlspecialchars($_POST['profesion']) : ''; ?>" />
                <?php if (isset($errores["profesion"])): ?>
                    <span><?php echo $errores["profesion"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <label for="edad">Edad:</label>
                <input type="number" id="edad" name="edad" min="1" max="150" value="<?php echo isset($_POST['edad']) ? htmlspecialchars($_POST['edad']) : ''; ?>" />
                <?php if (isset($errores["edad"])): ?>
                    <span><?php echo $errores["edad"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <label for="genero">Género:</label>
                <select id="genero" name="genero">
                    <option value="">Seleccione...</option>
                    <option value="1" <?php echo (isset($_POST['genero']) && $_POST['genero'] == '1') ? 'selected' : ''; ?>>Masculino</option>
                    <option value="2" <?php echo (isset($_POST['genero']) && $_POST['genero'] == '2') ? 'selected' : ''; ?>>Femenino</option>
                    <option value="3" <?php echo (isset($_POST['genero']) && $_POST['genero'] == '3') ? 'selected' : ''; ?>>No binario</option>
                    <option value="4" <?php echo (isset($_POST['genero']) && $_POST['genero'] == '4') ? 'selected' : ''; ?>>Prefiero no decir</option>
                </select>
                <?php if (isset($errores["genero"])): ?>
                    <span><?php echo $errores["genero"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <label for="pericia">Nivel de Pericia Informática:</label>
                <select id="pericia" name="pericia">
                    <option value="">Seleccione...</option>
                    <option value="1" <?php echo (isset($_POST['pericia']) && $_POST['pericia'] == '1') ? 'selected' : ''; ?>>Básico - Uso ocasional de ordenador y aplicaciones simples</option>
                    <option value="2" <?php echo (isset($_POST['pericia']) && $_POST['pericia'] == '2') ? 'selected' : ''; ?>>Intermedio - Uso regular de múltiples aplicaciones</option>
                    <option value="3" <?php echo (isset($_POST['pericia']) && $_POST['pericia'] == '3') ? 'selected' : ''; ?>>Avanzado - Usuario experto con conocimientos técnicos</option>
                    <option value="4" <?php echo (isset($_POST['pericia']) && $_POST['pericia'] == '4') ? 'selected' : ''; ?>>Experto - Profesional de IT o desarrollador</option>
                </select>
                <?php if (isset($errores["pericia"])): ?>
                    <span><?php echo $errores["pericia"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <label for="dispositivo">Dispositivo que está usando:</label>
                <select id="dispositivo" name="dispositivo">
                    <option value="">Seleccione...</option>
                    <option value="1" <?php echo (isset($_POST['dispositivo']) && $_POST['dispositivo'] == '1') ? 'selected' : ''; ?>>Ordenador</option>
                    <option value="2" <?php echo (isset($_POST['dispositivo']) && $_POST['dispositivo'] == '2') ? 'selected' : ''; ?>>Tableta</option>
                    <option value="3" <?php echo (isset($_POST['dispositivo']) && $_POST['dispositivo'] == '3') ? 'selected' : ''; ?>>Teléfono</option>
                </select>
                <?php if (isset($errores["dispositivo"])): ?>
                    <span><?php echo $errores["dispositivo"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <button type="submit">Continuar</button>
            </section>
        </form>

    <?php elseif (!$pruebaIniciada): ?>
        <section>
            <h2>Bienvenido a la Prueba de Usabilidad</h2>
            <p>Esta prueba consiste en responder 10 preguntas sobre el sitio web MotoGP Desktop.</p>
            <p>Al iniciar la prueba, se comenzará a contabilizar el tiempo empleado.</p>
            <p>Puedes navegar por el sitio web en otra pestaña mientras realizas la prueba.</p>
            
            <form action="#" method="post">
                <input type="hidden" name="accion" value="iniciar" />
                <button type="submit">Iniciar Prueba</button>
            </form>
        </section>
    
    <?php elseif ($pruebaIniciada && !$pruebaCompletada): ?>
        <section>
            <h2>Test de Usabilidad</h2>
            <p>Por favor, responde a las siguientes preguntas sobre el sitio web MotoGP Desktop.</p>
            <p>Todas las preguntas son obligatorias.</p>
        </section>

        <form action="#" method="post">
            <input type="hidden" name="accion" value="terminar" />
            
            <section>
                <h3>Pregunta 1</h3>
                <p>¿Cuántos juegos aparecen en el listado de juegos?</p>
                <input type="text" name="pregunta1" value="<?php echo isset($_POST['pregunta1']) ? htmlspecialchars($_POST['pregunta1']) : ''; ?>" />
                <?php if (isset($errores["pregunta1"])): ?>
                    <span><?php echo $errores["pregunta1"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 2</h3>
                <p>¿Cuántas cartas aparecen en el juego de memoria?</p>
                <input type="text" name="pregunta2" value="<?php echo isset($_POST['pregunta2']) ? htmlspecialchars($_POST['pregunta2']) : ''; ?>" />
                <?php if (isset($errores["pregunta2"])): ?>
                    <span><?php echo $errores["pregunta2"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 3</h3>
                <p>Cita alguno de los conceptos claves de MotoGP</p>
                <input type="text" name="pregunta3" value="<?php echo isset($_POST['pregunta3']) ? htmlspecialchars($_POST['pregunta3']) : ''; ?>" />
                <?php if (isset($errores["pregunta3"])): ?>
                    <span><?php echo $errores["pregunta3"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 4</h3>
                <p>¿Para qué equipo compite Francesco Bagnaia?</p>
                <input type="text" name="pregunta4" value="<?php echo isset($_POST['pregunta4']) ? htmlspecialchars($_POST['pregunta4']) : ''; ?>" />
                <?php if (isset($errores["pregunta4"])): ?>
                    <span><?php echo $errores["pregunta4"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 5</h3>
                <p>¿Qué día es la carrera?</p>
                <input type="text" name="pregunta5" value="<?php echo isset($_POST['pregunta5']) ? htmlspecialchars($_POST['pregunta5']) : ''; ?>" />
                <?php if (isset($errores["pregunta5"])): ?>
                    <span><?php echo $errores["pregunta5"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 6</h3>
                <p>¿Llueve en los días de entrenamientos?</p>
                <input type="radio" name="pregunta6" value="Si" <?php echo (isset($_POST['pregunta6']) && $_POST['pregunta6'] == 'Si') ? 'checked' : ''; ?> />Sí
                <input type="radio" name="pregunta6" value="No" <?php echo (isset($_POST['pregunta6']) && $_POST['pregunta6'] == 'No') ? 'checked' : ''; ?> />No
                <?php if (isset($errores["pregunta6"])): ?>
                    <span><?php echo $errores["pregunta6"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 7</h3>
                <p>¿Qué circuito aparece en la página?</p>
                <input type="text" name="pregunta7" value="<?php echo isset($_POST['pregunta7']) ? htmlspecialchars($_POST['pregunta7']) : ''; ?>" />
                <?php if (isset($errores["pregunta7"])): ?>
                    <span><?php echo $errores["pregunta7"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 8</h3>
                <p>¿Quién fue el ganador de la carrera en el circuito?</p>
                <input type="text" name="pregunta8" value="<?php echo isset($_POST['pregunta8']) ? htmlspecialchars($_POST['pregunta8']) : ''; ?>" />
                <?php if (isset($errores["pregunta8"])): ?>
                    <span><?php echo $errores["pregunta8"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 9</h3>
                <p>¿Cuánto tiempo tardó en ganar la carrera?</p>
                <input type="text" name="pregunta9" value="<?php echo isset($_POST['pregunta9']) ? htmlspecialchars($_POST['pregunta9']) : ''; ?>" />
                <?php if (isset($errores["pregunta9"])): ?>
                    <span><?php echo $errores["pregunta9"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Pregunta 10</h3>
                <p>¿Cuántos botones tiene el juego de Cronómetro?</p>
                <input type="text" name="pregunta10" value="<?php echo isset($_POST['pregunta10']) ? htmlspecialchars($_POST['pregunta10']) : ''; ?>" />
                <?php if (isset($errores["pregunta10"])): ?>
                    <span><?php echo $errores["pregunta10"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Comentarios sobre la Experiencia</h3>
                <p>Por favor, comparte tus comentarios sobre la experiencia de uso del sitio web:</p>
                <textarea name="comentarios_usuario" rows="5" cols="50"><?php echo isset($_POST['comentarios_usuario']) ? htmlspecialchars($_POST['comentarios_usuario']) : ''; ?></textarea>
                <?php if (isset($errores["comentarios_usuario"])): ?>
                    <span><?php echo $errores["comentarios_usuario"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Propuestas de Mejora</h3>
                <p>¿Qué mejorarías del sitio web? (funcionalidades, diseño, navegación, etc.):</p>
                <textarea name="propuestas_mejora" rows="5" cols="50"><?php echo isset($_POST['propuestas_mejora']) ? htmlspecialchars($_POST['propuestas_mejora']) : ''; ?></textarea>
                <?php if (isset($errores["propuestas_mejora"])): ?>
                    <span><?php echo $errores["propuestas_mejora"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <h3>Valoración General</h3>
                <p>Del 0 al 10, ¿cómo valorarías tu experiencia con el sitio web?</p>
                <input type="number" name="valoracion_usuario" min="0" max="10" step="0.1" value="<?php echo isset($_POST['valoracion_usuario']) ? htmlspecialchars($_POST['valoracion_usuario']) : ''; ?>" />
                <?php if (isset($errores["valoracion_usuario"])): ?>
                    <span><?php echo $errores["valoracion_usuario"]; ?></span>
                <?php endif; ?>
            </section>

            <section>
                <button type="submit">Terminar Prueba</button>
            </section>
        </form>
    
    <?php elseif ($pruebaCompletada): ?>
        <section>
            <h2>Prueba Completada</h2>
            <p>El usuario ha completado la prueba de usabilidad.</p>
            <p><strong>Tiempo total:</strong> <?php echo $_SESSION['tiempoTotal']; ?></p>
            <p><strong>Valoración:</strong> <?php echo $_SESSION['valoracion_usuario']; ?>/10</p>
        </section>
        
        <section>
            <h3>Observaciones del Facilitador</h3>
            <p>Por favor, añade comentarios adicionales sobre la prueba:</p>
            
            <form action="#" method="post">
                <input type="hidden" name="accion" value="guardarObservaciones" />
                <textarea name="observaciones" rows="10" cols="50"></textarea>
                <button type="submit">Guardar y Finalizar</button>
            </form>
        </section>
    <?php endif; ?>
    </main>
</body>
</html>