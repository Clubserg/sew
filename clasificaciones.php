<?php
class Clasificacion {
    private $documento;
    private $xml;

    public function __construct() {
        $this->documento = "xml/circuitoEsquema.xml";
    }

    public function consultar() {
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
            $tiempoFormateado = $this->formatearTiempo($tiempo);

            echo "<section>\n";
            echo "<h3>Ganador de la Carrera en el Circuito de las Américas</h3>\n";
            echo "<article>\n";
            echo "<p><strong>" . htmlspecialchars($nombrePiloto) . "</strong></p>\n";
            echo "<p>Tiempo: " . htmlspecialchars($tiempoFormateado) . "</p>\n";
            echo "</article>\n";
            echo "</section>\n";
        }
    }

    public function mostrarClasificacionMundial() {
        if (isset($this->xml->clasificacion_mundial)) {
            echo "<section>\n";
            echo "<h3>Clasificación Mundial de Pilotos</h3>\n";
            echo "<table>\n";
            echo "<thead>\n";
            echo "<tr>\n";
            echo "<th>Posición</th>\n";
            echo "<th>Piloto</th>\n";
            echo "<th>Puntos</th>\n";
            echo "</tr>\n";
            echo "</thead>\n";
            echo "<tbody>\n";

            foreach ($this->xml->clasificacion_mundial->piloto as $piloto) {
                $nombre = (string)$piloto->nombre_piloto;
                $puntos = (string)$piloto->puntos;
                $posicion = (string)$piloto->posicion;

                echo "<tr>\n";
                echo "<td>" . htmlspecialchars($posicion) . "</td>\n";
                echo "<td>" . htmlspecialchars($nombre) . "</td>\n";
                echo "<td>" . htmlspecialchars($puntos) . "</td>\n";
                echo "</tr>\n";
            }

            echo "</tbody>\n";
            echo "</table>\n";
            echo "</section>\n";
        }
    }

    private function formatearTiempo($tiempoISO) {
        $tiempo = $tiempoISO;
        $tiempo = str_replace('PT', '', $tiempo);
        $tiempo = str_replace('M', ':', $tiempo);
        $tiempo = str_replace('S', '', $tiempo);

        if (strpos($tiempo, ':') === false) {
            $tiempo = '0:' . $tiempo;
        }

        $partes = explode(':', $tiempo);
        if (count($partes) === 2) {
            $minutos = $partes[0];
            $segundos = $partes[1];

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

$clasificacion = new Clasificacion();
?>

<!DOCTYPE HTML>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="author" content="Tu Nombre">
    <meta name="description" content="Clasificaciones de MotoGP Desktop">
    <meta name="keywords" content="MotoGP, clasificaciones, carrera, mundial">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MotoGP Desktop</title>
    <link rel="stylesheet" type="text/css" href="estilo/estilo.css">
    <link rel="stylesheet" type="text/css" href="estilo/layout.css">
</head>
<body>
    <header>
        <h1>MotoGP Desktop</h1>
        <nav>
            <a href="index.html">Inicio</a>
            <a href="piloto.html">Piloto</a>
            <a href="circuito.html">Circuito</a>
            <a href="meteorologia.html">Meteorologia</a>
            <a href="clasificaciones.php">Clasificaciones</a>
            <a href="juegos.html">Juegos</a>
            <a href="ayuda.html">Ayuda</a>
        </nav>
    </header>

    <p>Estás en: <a href="index.html">Inicio</a> >> Clasificaciones</p>

    <main>
        <h2>Clasificaciones de MotoGP Desktop</h2>
        
        <?php
        if ($clasificacion->consultar()) {
            $clasificacion->mostrarGanador();
            $clasificacion->mostrarClasificacionMundial();
        } else {
            echo "<p>Error: No se pudo cargar el archivo XML de clasificaciones.</p>";
        }
        ?>
    </main>
</body>
</html>