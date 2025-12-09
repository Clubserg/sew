<?php
/**
 * Interfaz de configuración para la base de datos de pruebas de usabilidad
 * @author Sergio Fernandez-Miranda Longo
 */

// Incluir la clase Configuracion
require_once 'Configuracion.php';

$mensaje = "";
$estadisticas = [];

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $config = new Configuracion();
    
    if (isset($_POST['accion'])) {
        switch ($_POST['accion']) {
            case 'reiniciar':
                $mensaje = $config->reiniciarBaseDatos();
                break;
            case 'eliminar':
                $mensaje = $config->eliminarBaseDatos();
                break;
            case 'exportar':
                $mensaje = $config->exportarDatosCSV();
                break;
            case 'importar':
                $mensaje = $config->importarBaseDatos();
                break;
        }
    }
}

// Obtener estadísticas si la BD existe
try {
    $config = new Configuracion();
    $estadisticas = $config->obtenerEstadisticas();
} catch (Exception $e) {
    // La base de datos no existe o hay error
}
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta name="author" content="Sergio Fernandez-Miranda Longo" />
    <meta name="description" content="Configuración de la base de datos de pruebas de usabilidad" />
    <meta name="keywords" content="MotoGP, configuración, test, usabilidad" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <meta charset="UTF-8" />
    <title>MotoGP - Configuración Test Usabilidad</title>
    <link rel="icon" type="image/x-icon" href="multimedia/favicon.ico" />
    <link rel="stylesheet" type="text/css" href="../estilo/estilo.css" />
    <link rel="stylesheet" type="text/css" href="../estilo/layout.css" />
</head>

<body>
    <header>
        <h1><a href="index.html">MotoGP Desktop</a></h1>
        <nav>
            <a href="../index.html" title="Inicio de la pagina de MotoGP">Inicio</a>
            <a href="../piloto.html" title="Información del piloto">Piloto</a>
            <a href="../circuito.html" title="Información de los circuitos">Circuito</a>
            <a href="../meteorologia.html" title="Información del tiempo">Meteorologia</a>
            <a href="../clasificaciones.php" title="Información de la clasificacion">Clasificaciones</a>
            <a href="../juegos.html" title="Información de los juegos">Juegos</a>
            <a href="../ayuda.html" title="Ayuda">Ayuda</a>
        </nav>
    </header>

    <nav>
        <p>Estás en: 
            <a href="index.html">Inicio</a> &gt;&gt;
            <a href="juegos.html">Juegos</a> &gt;&gt;
            <span>Configuración Test</span>
        </p>
    </nav>

    <main>
        <h2>Configuración de Test de Usabilidad</h2>
        
        <?php if ($mensaje): ?>
            <section>
                <p><?php echo htmlspecialchars($mensaje); ?></p>
            </section>
        <?php endif; ?>
        
        <?php if (!empty($estadisticas) && !isset($estadisticas['error'])): ?>
            <section>
                <h3>Estadísticas de la Base de Datos</h3>
                <?php foreach ($estadisticas as $label => $valor): ?>
                    <article>
                        <p><?php echo $valor; ?></p>
                        <p><?php echo htmlspecialchars($label); ?></p>
                    </article>
                <?php endforeach; ?>
            </section>
        <?php endif; ?>
        
        <section>
            <h3>Gestión de Base de Datos</h3>
            <form method="post" action="configuracionTest.php">
                <input type="hidden" name="accion" value="importar" />
                <button type="submit">Importar Base de Datos (usability_db.sql)</button>
            </form>
        </section>
        
        <section>
            <h3>Reiniciar Datos</h3>
            <form method="post" action="configuracionTest.php">
                <input type="hidden" name="accion" value="reiniciar" />
                <button type="submit">Reiniciar Base de Datos</button>
            </form>
        </section>
        
        <section>
            <h3>Exportar Datos</h3>
            <form method="post" action="configuracionTest.php">
                <input type="hidden" name="accion" value="exportar" />
                <button type="submit">Exportar a CSV</button>
            </form>
        </section>
        
        <section>
            <h3>Eliminar Base de Datos</h3>
            <form method="post" action="configuracionTest.php">
                <input type="hidden" name="accion" value="eliminar" />
                <button type="submit">Eliminar Base de Datos</button>
            </form>
        </section>

        
    </main>
</body>
</html>