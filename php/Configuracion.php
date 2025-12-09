<?php
/**
 * Clase Configuracion
 * Gestiona las operaciones de configuración de la base de datos UO302282_DB
 * para las pruebas de usabilidad
 * 
 * @author Sergio Fernandez-Miranda Longo
 */
class Configuracion {
    
    private $servidor;
    private $usuario;
    private $password;
    private $baseDatos;
    private $conexion;
    
    /**
     * Constructor de la clase
     * Inicializa los parámetros de conexión a la base de datos
     */
    public function __construct() {
        $this->servidor = "localhost";
        $this->usuario = "SMFL_user";
        $this->password = "SMFL_password";
        $this->baseDatos = "UO302282_DB";
    }
    
    /**
     * Establece conexión con la base de datos
     * @return bool True si la conexión es exitosa, False en caso contrario
     */
    private function conectar() {
        $this->conexion = new mysqli($this->servidor, $this->usuario, $this->password, $this->baseDatos);
        
        if ($this->conexion->connect_error) {
            return false;
        }
        
        $this->conexion->set_charset("utf8");
        return true;
    }
    
    /**
     * Cierra la conexión con la base de datos
     */
    private function cerrarConexion() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
    
    /**
     * Importa la base de datos desde el archivo SQL
     * @return string Mensaje con el resultado de la operación
     */
    public function importarBaseDatos() {
        // Ruta al archivo SQL (un nivel arriba del directorio php)
        $archivoSQL = "../usability_db.sql";
        
        // Verificar que el archivo existe
        if (!file_exists($archivoSQL)) {
            return "Error: No se encontró el archivo usability_db.sql en la ruta esperada.";
        }
        
        // Conectar al servidor sin especificar base de datos
        $conexionServidor = new mysqli($this->servidor, $this->usuario, $this->password);
        
        if ($conexionServidor->connect_error) {
            return "Error: No se pudo conectar al servidor de base de datos.";
        }
        
        // Leer el contenido del archivo SQL
        $sql = file_get_contents($archivoSQL);
        
        if ($sql === false) {
            $conexionServidor->close();
            return "Error: No se pudo leer el archivo SQL.";
        }
        
        // Ejecutar el script SQL
        if ($conexionServidor->multi_query($sql)) {
            // Esperar a que se completen todas las consultas
            do {
                if ($resultado = $conexionServidor->store_result()) {
                    $resultado->free();
                }
            } while ($conexionServidor->more_results() && $conexionServidor->next_result());
            
            $conexionServidor->close();
            return "Base de datos importada correctamente desde usability_db.sql";
        } else {
            $error = $conexionServidor->error;
            $conexionServidor->close();
            return "Error al importar la base de datos: " . $error;
        }
    }
    
    /**
     * Reinicia la base de datos eliminando todos los datos de las tablas
     * pero manteniendo la estructura
     * @return string Mensaje con el resultado de la operación
     */
    public function reiniciarBaseDatos() {
        if (!$this->conectar()) {
            return "Error: No se pudo conectar a la base de datos. Asegúrese de que existe.";
        }
        
        // Desactivar comprobación de claves foráneas temporalmente
        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 0");
        
        $tablas = [
            "ObservacionFacilitador",
            "ResultadoTestUsabilidad",
            "Usuario",
            "TipoDispositivo",
            "PericiaInformatica",
            "Genero"
        ];
        
        $errores = [];
        $exitosos = 0;
        
        foreach ($tablas as $tabla) {
            $consulta = "TRUNCATE TABLE " . $tabla;
            if ($this->conexion->query($consulta)) {
                $exitosos++;
            } else {
                $errores[] = "Error al truncar tabla $tabla: " . $this->conexion->error;
            }
        }
        
        // Reinsertar datos de catálogo
        $this->insertarDatosCatalogo();
        
        // Reactivar comprobación de claves foráneas
        $this->conexion->query("SET FOREIGN_KEY_CHECKS = 1");
        
        $this->cerrarConexion();
        
        if (count($errores) > 0) {
            return "Base de datos reiniciada parcialmente. Errores: " . implode(", ", $errores);
        }
        
        return "Base de datos reiniciada correctamente. Se eliminaron los datos de $exitosos tablas.";
    }
    
    /**
     * Inserta los datos iniciales en las tablas de catálogo
     */
    private function insertarDatosCatalogo() {
        // Insertar géneros
        $generos = [
            "Masculino", "Femenino", "No binario", "Prefiero no decir"
        ];
        
        foreach ($generos as $genero) {
            $stmt = $this->conexion->prepare("INSERT INTO Genero (nombre_genero) VALUES (?)");
            $stmt->bind_param("s", $genero);
            $stmt->execute();
            $stmt->close();
        }
        
        // Insertar niveles de pericia
        $pericias = [
            ["Básico", "Uso ocasional de ordenador y aplicaciones simples"],
            ["Intermedio", "Uso regular de múltiples aplicaciones"],
            ["Avanzado", "Usuario experto con conocimientos técnicos"],
            ["Experto", "Profesional de IT o desarrollador"]
        ];
        
        foreach ($pericias as $pericia) {
            $stmt = $this->conexion->prepare("INSERT INTO PericiaInformatica (nivel_pericia, descripcion) VALUES (?, ?)");
            $stmt->bind_param("ss", $pericia[0], $pericia[1]);
            $stmt->execute();
            $stmt->close();
        }
        
        // Insertar tipos de dispositivo
        $dispositivos = ["Ordenador", "Tableta", "Teléfono"];
        
        foreach ($dispositivos as $dispositivo) {
            $stmt = $this->conexion->prepare("INSERT INTO TipoDispositivo (nombre_dispositivo) VALUES (?)");
            $stmt->bind_param("s", $dispositivo);
            $stmt->execute();
            $stmt->close();
        }
    }
    
    /**
     * Elimina completamente la base de datos y toda su estructura
     * @return string Mensaje con el resultado de la operación
     */
    public function eliminarBaseDatos() {
        // Conectar al servidor sin especificar base de datos
        $conexionServidor = new mysqli($this->servidor, $this->usuario, $this->password);
        
        if ($conexionServidor->connect_error) {
            return "Error: No se pudo conectar al servidor de base de datos.";
        }
        
        $consulta = "DROP DATABASE IF EXISTS " . $this->baseDatos;
        
        if ($conexionServidor->query($consulta)) {
            $conexionServidor->close();
            return "Base de datos '" . $this->baseDatos . "' eliminada correctamente.";
        } else {
            $error = $conexionServidor->error;
            $conexionServidor->close();
            return "Error al eliminar la base de datos: " . $error;
        }
    }
    
    /**
     * Exporta los datos de todas las tablas a formato CSV
     * @return string Mensaje con el resultado de la operación
     */
    public function exportarDatosCSV() {
        if (!$this->conectar()) {
            return "Error: No se pudo conectar a la base de datos.";
        }
        
        $tablas = [
            "Genero",
            "PericiaInformatica",
            "TipoDispositivo",
            "Usuario",
            "ResultadoTestUsabilidad",
            "ObservacionFacilitador"
        ];
        
        $archivosGenerados = [];
        
        foreach ($tablas as $tabla) {
            $nombreArchivo = $tabla . "_" . date("Y-m-d_H-i-s") . ".csv";
            $rutaArchivo = "../exports/" . $nombreArchivo;
            
            // Crear directorio exports si no existe
            if (!file_exists("../exports")) {
                mkdir("../exports", 0777, true);
            }
            
            $resultado = $this->conexion->query("SELECT * FROM " . $tabla);
            
            if ($resultado && $resultado->num_rows > 0) {
                $archivo = fopen($rutaArchivo, "w");
                
                // Escribir encabezados
                $primerRegistro = $resultado->fetch_assoc();
                fputcsv($archivo, array_keys($primerRegistro));
                
                // Escribir primer registro
                fputcsv($archivo, $primerRegistro);
                
                // Escribir resto de registros
                while ($fila = $resultado->fetch_assoc()) {
                    fputcsv($archivo, $fila);
                }
                
                fclose($archivo);
                $archivosGenerados[] = $nombreArchivo;
            }
        }
        
        $this->cerrarConexion();
        
        if (count($archivosGenerados) > 0) {
            return "Exportación completada. Archivos generados: " . implode(", ", $archivosGenerados);
        } else {
            return "No se encontraron datos para exportar.";
        }
    }
    
    /**
     * Verifica si la base de datos existe
     * @return bool True si existe, False en caso contrario
     */
    public function existeBaseDatos() {
        $conexionServidor = new mysqli($this->servidor, $this->usuario, $this->password);
        
        if ($conexionServidor->connect_error) {
            return false;
        }
        
        $resultado = $conexionServidor->query("SHOW DATABASES LIKE '" . $this->baseDatos . "'");
        $existe = ($resultado && $resultado->num_rows > 0);
        
        $conexionServidor->close();
        return $existe;
    }
    
    /**
     * Obtiene estadísticas de la base de datos
     * @return array Array con información estadística
     */
    public function obtenerEstadisticas() {
        if (!$this->conectar()) {
            return ["error" => "No se pudo conectar a la base de datos"];
        }
        
        $stats = [];
        
        $tablas = [
            "Usuario" => "Usuarios registrados",
            "ResultadoTestUsabilidad" => "Tests realizados",
            "ObservacionFacilitador" => "Observaciones registradas"
        ];
        
        foreach ($tablas as $tabla => $descripcion) {
            $resultado = $this->conexion->query("SELECT COUNT(*) as total FROM " . $tabla);
            if ($resultado) {
                $fila = $resultado->fetch_assoc();
                $stats[$descripcion] = $fila['total'];
            }
        }
        
        $this->cerrarConexion();
        return $stats;
    }
}
?>