<?php
/**
 * Clase Cronometro
 * Permite medir el tiempo transcurrido entre el arranque y la parada
 * @author Sergio Fernandez-Miranda Longo
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
    
    /**
     * Obtiene el tiempo transcurrido en segundos
     * @return float Tiempo en segundos
     */
    public function obtenerTiempo() {
        return $this->tiempo;
    }
    
    /**
     * Inicia el cronómetro (alias de arrancar para compatibilidad)
     */
    public function iniciar() {
        $this->arrancar();
    }
    
    /**
     * Detiene el cronómetro (alias de parar para compatibilidad)
     */
    public function detener() {
        $this->parar();
    }
}
?>