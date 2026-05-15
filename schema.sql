-- Base de datos ManageTracking
CREATE DATABASE IF NOT EXISTS managetracking CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE managetracking;

-- Empresas cliente
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    ruc VARCHAR(20),
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Sedes por cliente
CREATE TABLE IF NOT EXISTS sedes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    direccion TEXT,
    ciudad VARCHAR(100),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
);

-- Dispositivos (PC / Laptop / ESP32+SIM800L / etc.)
CREATE TABLE IF NOT EXISTS dispositivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(20) NOT NULL UNIQUE COMMENT 'MAC para PCs, IMEI para dispositivos GSM',
    hostname VARCHAR(100),
    nombre_usuario VARCHAR(100),
    apellido_usuario VARCHAR(100),
    telefono_usuario VARCHAR(20),
    sede_id INT DEFAULT NULL,
    tipo ENUM('laptop', 'pc', 'esp8266', 'gps', 'otro') DEFAULT 'laptop',
    windows_version VARCHAR(100) DEFAULT NULL,
    serie_equipo VARCHAR(100) DEFAULT NULL,
    ram_gb INT DEFAULT NULL,
    almacenamiento_gb INT DEFAULT NULL,
    procesador VARCHAR(200) DEFAULT NULL,
    api_key VARCHAR(64) NOT NULL,
    estado ENUM('activo', 'inactivo', 'perdido') DEFAULT 'activo',
    ultima_vez TIMESTAMP NULL,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sede_id) REFERENCES sedes(id) ON SET NULL
);

CREATE TABLE IF NOT EXISTS registros_gps (
    id INT AUTO_INCREMENT PRIMARY KEY,
    mac_address VARCHAR(20) NOT NULL,
    latitud DECIMAL(10, 8) NOT NULL,
    longitud DECIMAL(11, 8) NOT NULL,
    altitud DECIMAL(8, 2) DEFAULT NULL,
    velocidad DECIMAL(6, 2) DEFAULT NULL,
    satelites INT DEFAULT NULL,
    tiempo_respuesta_ms DECIMAL(10, 3) DEFAULT NULL COMMENT 'ms que tardó el API en procesar y responder',
    registrado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mac_address) REFERENCES dispositivos(mac_address) ON DELETE CASCADE,
    INDEX idx_mac_fecha (mac_address, registrado_en DESC)
);

CREATE TABLE IF NOT EXISTS encuestas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    p1 TINYINT NOT NULL COMMENT 'Utilidad del sistema (1-5)',
    p2 TINYINT NOT NULL COMMENT 'Facilidad de uso (1-5)',
    p3 TINYINT NOT NULL COMMENT 'Reducción del tiempo de búsqueda (1-5)',
    p4 TINYINT NOT NULL COMMENT 'Mejora en el control de equipos (1-5)',
    p5 TINYINT NOT NULL COMMENT 'Recomendaría el sistema (1-5)',
    respondido_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ── Migración para instalaciones existentes ──────────────────────────────────
-- Ejecuta esto si ya tienes la tabla creada:
--
-- ALTER TABLE dispositivos
--   MODIFY COLUMN mac_address VARCHAR(20) NOT NULL,
--   MODIFY COLUMN tipo ENUM('laptop','pc','esp8266','gps','otro') DEFAULT 'laptop';
