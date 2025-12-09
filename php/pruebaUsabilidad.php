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
    
    // Si no hay errores, detener el cronómetro y procesar
    if (!$errorFormulario && isset($_SESSION['cronometro'])) {
        $_SESSION['cronometro']->detener();
        $tiempoTranscurrido = $_SESSION['cronometro']->obtenerTiempo();
        $_SESSION['tiempoTotal'] = $tiempoTranscurrido;
        $pruebaCompletada = true;
    }
}

// Guardar observaciones del facilitador
if (isset($_POST['accion']) && $_POST['accion'] == 'guardarObservaciones') {
    $observaciones = $_POST['observaciones'];
    $tiempoTotal = $_SESSION['tiempoTotal'];
    $mensajeFinal = true;
    
    // TODO: Guardar en base de datos
    // - Respuestas de las preguntas
    // - Tiempo total
    // - Observaciones del facilitador
    
    // Limpiar la sesión
    session_destroy();
    header("Location: pruebaUsabilidad.php");
    exit();
    
}

// Verificar si la prueba está en curso
if (isset($_SESSION['cronometro'])) {
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
    <?php if ($mensajeFinal): ?>
        <section>
            <h2> Fin de la prueba de usabilidad </h2>
            <p> Muchas gracias por participar, puedes cerrar esta pestaña </p>
        </section>

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
                    <button type="submit">Terminar Prueba</button>
                </section>
            </form>
        
        <?php elseif ($pruebaCompletada): ?>
            <section>
                <h2>Prueba Completada</h2>
                <p>El usuario ha completado la prueba de usabilidad.</p>
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