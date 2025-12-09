<?php

// Iniciar sesión para mantener el estado del cronómetro
session_start();
/**
 * Clase Cronometro
 * Permite medir el tiempo transcurrido entre el arranque y la parada
 */
class Cronometro {
    
    private $inicio;
    private $tiempo;
    
    /**
     * Constructor - Inicializa el tiempo a cero
     */
    public function __construct() {
        $this->tiempo = 0;
    }
    
    /**
     * Marca el momento temporal en el que se inicia
     */
    public function arrancar() {
        $this->inicio = microtime(true);
    }
    
    /**
     * Calcula el tiempo transcurrido desde el arranque
     */
    public function parar() {
        if (isset($this->inicio)) {
            $fin = microtime(true);
            $this->tiempo = $fin - $this->inicio;
        }
    }
    
    /**
     * Retorna el tiempo en formato mm:ss.s
     * @return string Tiempo formateado
     */
    public function mostrar() {
        $minutos = floor($this->tiempo / 60);
        $segundos = floor($this->tiempo % 60);
        $decimas = floor(($this->tiempo - floor($this->tiempo)) * 10);
        
        return sprintf("%02d:%02d.%d", $minutos, $segundos, $decimas);
    }
}



// Crear o recuperar el cronómetro de la sesión
if (!isset($_SESSION['cronometro'])) {
    $_SESSION['cronometro'] = new Cronometro();
}

$cronometro = $_SESSION['cronometro'];
$mensaje = "";

// Procesar las acciones de los botones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['arrancar'])) {
        $cronometro->arrancar();
        $mensaje = "Cronómetro arrancado";
    } elseif (isset($_POST['parar'])) {
        $cronometro->parar();
        $mensaje = "Cronómetro parado";
    } elseif (isset($_POST['mostrar'])) {
        $mensaje = "Tiempo transcurrido: " . $cronometro->mostrar();
    }
    
    // Guardar el cronómetro actualizado en la sesión
    $_SESSION['cronometro'] = $cronometro;
}
?>
<!DOCTYPE HTML>

<html lang="es">
<head>
	<meta name="author" content="Sergio Fernandez-Miranda Longo" />
	<meta name="description" content="Cronómetro para MotoGP" />
	<meta name="keywords" content="MotoGP, cronometro, tiempo" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />

	<meta charset="UTF-8" />
	<title>MotoGP - Cronómetro</title>
	<link rel="icon" type="image/x-icon" href="multimedia/favicon.ico">
	<link rel="stylesheet" type="text/css" href="estilo/estilo.css" />
	<link rel="stylesheet" type="text/css" href="estilo/layout.css"/>
</head>

<body>
	<!-- Tarea 6: Estructura general del documento -->
	<header>
		<h1><a href="index.html">MotoGP Desktop</a></h1>
		
		<nav>
			<a href="index.html" title="Inicio de la pagina de MotoGP">Inicio</a>
			<a href="piloto.html" title="Información del piloto">Piloto</a>
			<a href="circuito.html" title="Información de los circuitos">Circuito</a>
			<a href="meteorologia.html" title="Información del tiempo">Meteorologia</a>
			<a href="clasificaciones.php" title="Información de la clasificacion">Clasificaciones</a>
			<a class="highlight" href="juegos.html" title="Información de los juegos">Juegos</a>
			<a href="ayuda.html" title="Ayuda">Ayuda</a>
		</nav>
	</header>

	<!-- Migas de pan -->
	<nav>
		<p>Estás en: 
			<a href="index.html">Inicio</a> &gt;&gt;
			<a href="juegos.html">Juegos</a> &gt;&gt;
			<span><strong>Cronómetro PHP</strong></span>
		</p>
	</nav>

	<h2>Cronómetro PHP</h2>

	<main>
		<section>
			<h3>Control del Cronómetro</h3>
			
			<!-- Tarea 6: Interfaz con botones -->
			<form method="post" action="">
				<button type="submit" name="arrancar">Arrancar</button>
				<button type="submit" name="parar">Parar</button>
				<button type="submit" name="mostrar">Mostrar Tiempo</button>
			</form>
			
			<?php if ($mensaje !== ""): ?>
				<p><?php echo htmlspecialchars($mensaje); ?></p>
			<?php endif; ?>
		</section>
	</main>

</body>
</html>