<?php
class Clasificacion {
    private $documento;
    private $xml;

    public function __construct() {
        // Inicializa el atributo documento con la ruta al archivo XML
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {
        // Verifica que el archivo existe y lo carga
        if (file_exists($this->documento)) {
            $this->xml = simplexml_load_file($this->documento);
            if ($this->xml !== false) {
                return true;
            }
        }
        return false;
    }

    public function mostrarGanador() {
        if (isset($this->xml->resultado_carrera)) {
            $resultado = $this->xml->resultado_carrera;
            $nombrePiloto = (string)$resultado->nombre_piloto;
            $tiempo = (string)$resultado->tiempo;
            
            // Convertir el tiempo de formato ISO 8601 a formato legible
            $tiempoFormateado = $this->formatearTiempo($tiempo);
            
            echo "<section>\n";
            echo "<h3>Ganador de la Carrera</h3>\n";
            echo "<article>\n";
            echo "<h4>" . htmlspecialchars($nombrePiloto) . "</h4>\n";
            echo "<p><strong>Tiempo:</strong> " . htmlspecialchars($tiempoFormateado) . "</p>\n";
            echo "</article>\n";
            echo "</section>\n";
        }
    }


    private function formatearTiempo($tiempoISO) {
        // Convierte PT39M0.191S a formato legible: 39:00.191
        $tiempo = $tiempoISO;
        $tiempo = str_replace('PT', '', $tiempo);
        $tiempo = str_replace('M', ':', $tiempo);
        $tiempo = str_replace('S', '', $tiempo);
        
        // Si no tiene minutos, añadir 0:
        if (strpos($tiempo, ':') === false) {
            $tiempo = '0:' . $tiempo;
        }
        
        // Asegurar formato MM:SS.sss
        $partes = explode(':', $tiempo);
        if (count($partes) === 2) {
            $minutos = $partes[0];
            $segundos = $partes[1];
            
            // Formatear segundos con dos dígitos antes del punto decimal
            if (strpos($segundos, '.') !== false) {
                $partesSegundos = explode('.', $segundos);
                $segundos = str_pad($partesSegundos[0], 2, '0', STR_PAD_LEFT) . '.' . $partesSegundos[1];
            } else {
                $segundos = str_pad($segundos, 2, '0', STR_PAD_LEFT);
            }
            
            $tiempo = $minutos . ':' . $segundos;
        }
        
        return $tiempo;
    }
}

// Crear instancia de la clase
$clasificacion = new Clasificacion();
?>
<!DOCTYPE HTML>

<html lang="es">
<head>
	<meta name="author" content="Sergio Fernandez-Miranda Longo" />
	<meta name="description" content="Clasificaciones de la pagina de MotoGP" />
	<meta name="keywords" content="MotoGP, clasificaciones, rankings" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<meta charset="UTF-8" />
	<title>MotoGP-Clasificaciones</title>
	<link rel="icon" type="image/x-icon" href="multimedia/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
	<link rel="stylesheet" type="text/css" href="estilo/layout.css" />
</head>

<body>
	<header>
		<h1><a href="index.html">MotoGP Desktop</a></h1>
	
		<nav>
			<a href="index.html" title="Inicio de la pagina de MotoGP">Inicio</a>
			<a href="piloto.html" title="Información del piloto">Piloto</a>
			<a href="circuito.html" title="Información de los circuitos">Circuito</a>
			<a href="meteorologia.html" title="Información del tiempo">Meteorologia</a>
			<a class="highlight" href="clasificaciones.php" title="Información de la clasificacion">Clasificaciones</a>
			<a href="juegos.html" title="Información de los juegos">Juegos</a>
			<a href="ayuda.html" title="Ayuda">Ayuda</a>
		</nav>
	</header>

	<nav>
		<p>Estás en: 
		<a href="index.html">Inicio</a> &gt;&gt;
		<strong>Clasificaciones</strong>
		</p>
	</nav>

	<h2>Clasificaciones de MotoGP Desktop</h2>
	
	<?php
	// Consultar el documento XML y mostrar la información
	if ($clasificacion->consultar()) {
		$clasificacion->mostrarGanador();
	}
	?>

</body>
</html>