<?php
/**
 * Página de cronómetro PHP
 * @author Sergio Fernandez-Miranda Longo
 */

 // Incluir la clase Cronometro
require_once 'cronometro.php';

// Iniciar sesión para mantener el estado del cronómetro
session_start();



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
			<a href="clasificaciones.php" title="Información de la clasificacion">Clasificaciones</a>
			<a href="juegos.html" title="Información de los juegos">Juegos</a>
			<a href="ayuda.html" title="Ayuda">Ayuda</a>
		</nav>
	</header>

	<nav>
		<p>Estás en: 
			<a href="index.html">Inicio</a> &gt;&gt;
			<a href="juegos.html">Juegos</a> &gt;&gt;
			<span>Cronómetro PHP</span>
		</p>
	</nav>

	<h2>Cronómetro PHP</h2>

	<main>
		<section>
			<h3>Control del Cronómetro</h3>
			
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