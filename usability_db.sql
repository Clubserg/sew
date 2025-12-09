-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS UO302282_DB;
USE UO302282_DB;

-- Tabla de catálogo para Género (Tercera Forma Normal)
CREATE TABLE Genero (
    id_genero INT PRIMARY KEY AUTO_INCREMENT,
    nombre_genero VARCHAR(20) NOT NULL UNIQUE
);

-- Tabla de catálogo para Nivel de Pericia Informática (Tercera Forma Normal)
CREATE TABLE PericiaInformatica (
    id_pericia INT PRIMARY KEY AUTO_INCREMENT,
    nivel_pericia VARCHAR(20) NOT NULL UNIQUE,
    descripcion VARCHAR(100)
);

-- Tabla de catálogo para Tipo de Dispositivo (Tercera Forma Normal)
CREATE TABLE TipoDispositivo (
    id_dispositivo INT PRIMARY KEY AUTO_INCREMENT,
    nombre_dispositivo VARCHAR(20) NOT NULL UNIQUE
);

-- Tabla principal de Usuarios
CREATE TABLE Usuario (
    codigo_usuario INT PRIMARY KEY AUTO_INCREMENT,
    profesion VARCHAR(100) NOT NULL,
    edad INT NOT NULL CHECK (edad > 0 AND edad < 150),
    id_genero INT NOT NULL,
    id_pericia INT NOT NULL,
    FOREIGN KEY (id_genero) REFERENCES Genero(id_genero),
    FOREIGN KEY (id_pericia) REFERENCES PericiaInformatica(id_pericia)
);

-- Tabla de Resultados de Test de Usabilidad
CREATE TABLE ResultadoTestUsabilidad (
    id_resultado INT PRIMARY KEY AUTO_INCREMENT,
    codigo_usuario INT NOT NULL,
    id_dispositivo INT NOT NULL,
    tiempo_completado TIME NOT NULL,
    tarea_completada BOOLEAN NOT NULL,
    comentarios_usuario TEXT,
    propuestas_mejora TEXT,
    valoracion_usuario DECIMAL(3,1) NOT NULL CHECK (valoracion_usuario >= 0 AND valoracion_usuario <= 10),
    fecha_prueba TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (codigo_usuario) REFERENCES Usuario(codigo_usuario),
    FOREIGN KEY (id_dispositivo) REFERENCES TipoDispositivo(id_dispositivo)
);

-- Tabla de Observaciones del Facilitador
CREATE TABLE ObservacionFacilitador (
    id_observacion INT PRIMARY KEY AUTO_INCREMENT,
    codigo_usuario INT NOT NULL,
    comentarios_facilitador TEXT NOT NULL,
    fecha_observacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (codigo_usuario) REFERENCES Usuario(codigo_usuario)
);

-- Insertar datos iniciales en tablas de catálogo

-- Géneros
INSERT INTO Genero (nombre_genero) VALUES 
    ('Masculino'),
    ('Femenino'),
    ('No binario'),
    ('Prefiero no decir');

-- Niveles de Pericia Informática
INSERT INTO PericiaInformatica (nivel_pericia, descripcion) VALUES 
    ('Básico', 'Uso ocasional de ordenador y aplicaciones simples'),
    ('Intermedio', 'Uso regular de múltiples aplicaciones'),
    ('Avanzado', 'Usuario experto con conocimientos técnicos'),
    ('Experto', 'Profesional de IT, desarrollador o estudiante de software');

-- Tipos de Dispositivo
INSERT INTO TipoDispositivo (nombre_dispositivo) VALUES 
    ('Ordenador'),
    ('Tableta'),
    ('Teléfono');

-- Índices para mejorar el rendimiento de consultas frecuentes
CREATE INDEX idx_usuario_edad ON Usuario(edad);
CREATE INDEX idx_resultado_fecha ON ResultadoTestUsabilidad(fecha_prueba);
CREATE INDEX idx_resultado_valoracion ON ResultadoTestUsabilidad(valoracion_usuario);
CREATE INDEX idx_resultado_completada ON ResultadoTestUsabilidad(tarea_completada);