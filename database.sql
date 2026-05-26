-- ManageTracking — Script completo de base de datos
-- Ejecutar en orden, respeta dependencias de FK

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS alertas;
DROP TABLE IF EXISTS registros_gps;
DROP TABLE IF EXISTS dispositivos;
DROP TABLE IF EXISTS sedes;
DROP TABLE IF EXISTS clientes;
DROP TABLE IF EXISTS encuestas;
SET FOREIGN_KEY_CHECKS = 1;

-- ─────────────────────────────────────────────────────────────
-- CLIENTES
-- ─────────────────────────────────────────────────────────────
CREATE TABLE clientes (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    nombre     VARCHAR(150) NOT NULL,
    ruc        VARCHAR(20)  NOT NULL,
    creado_en  TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- SEDES
-- latitud/longitud/radio_metros: para geofencing
-- ─────────────────────────────────────────────────────────────
CREATE TABLE sedes (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id    INT          NOT NULL,
    nombre        VARCHAR(150) NOT NULL,
    ciudad        VARCHAR(100) NOT NULL,
    direccion     VARCHAR(255) NOT NULL,
    latitud       DECIMAL(10,8) NULL,
    longitud      DECIMAL(11,8) NULL,
    radio_metros  INT          NOT NULL DEFAULT 120,
    creado_en     TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- DISPOSITIVOS
-- estado almacenado solo sirve para marcar 'perdido' manualmente.
-- el estado real (activo/inactivo) se calcula dinámicamente en queries.
-- ultima_sede_detectada_id: última sede confirmada por geofencing
-- ─────────────────────────────────────────────────────────────
CREATE TABLE dispositivos (
    mac_address              VARCHAR(17)  PRIMARY KEY,
    hostname                 VARCHAR(150) NOT NULL DEFAULT '',
    nombre_usuario           VARCHAR(100) NOT NULL DEFAULT '',
    apellido_usuario         VARCHAR(100) NOT NULL DEFAULT '',
    telefono_usuario         VARCHAR(20)  NOT NULL DEFAULT '',
    sede_id                  INT          NULL,
    ultima_sede_detectada_id INT          NULL,
    tipo                     ENUM('laptop','desktop','tablet','otro') NOT NULL DEFAULT 'laptop',
    estado                   ENUM('activo','inactivo','perdido')      NOT NULL DEFAULT 'activo',
    api_key                  VARCHAR(64)  NOT NULL UNIQUE,
    ultima_vez               DATETIME     NULL,
    windows_version          VARCHAR(100) NOT NULL DEFAULT '',
    serie_equipo             VARCHAR(100) NOT NULL DEFAULT '',
    procesador               VARCHAR(150) NOT NULL DEFAULT '',
    ram_gb                   INT          NULL,
    almacenamiento_gb        INT          NULL,
    FOREIGN KEY (sede_id)                  REFERENCES sedes(id) ON DELETE SET NULL,
    FOREIGN KEY (ultima_sede_detectada_id) REFERENCES sedes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- REGISTROS GPS
-- tipo 'ubicacion'     → check-in exitoso con coordenadas reales
-- tipo 'sin_ubicacion' → slot esperado no recibido (registrado por cron)
--                        latitud/longitud NULL en este caso
-- ─────────────────────────────────────────────────────────────
CREATE TABLE registros_gps (
    id                  INT AUTO_INCREMENT PRIMARY KEY,
    mac_address         VARCHAR(17)    NOT NULL,
    tipo                ENUM('ubicacion','sin_ubicacion') NOT NULL DEFAULT 'ubicacion',
    latitud             DECIMAL(10,8)  NULL,
    longitud            DECIMAL(11,8)  NULL,
    altitud             DECIMAL(10,2)  NULL,
    velocidad           DECIMAL(8,2)   NULL,
    satelites           INT            NULL,
    tiempo_respuesta_ms DECIMAL(10,3)  NULL,
    registrado_en       TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mac_address) REFERENCES dispositivos(mac_address) ON DELETE CASCADE,
    INDEX idx_mac_fecha (mac_address, registrado_en),
    INDEX idx_tipo      (tipo)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ─────────────────────────────────────────────────────────────
-- ALERTAS
-- tipo 'fuera_de_sedes'  → equipo fuera del radio de todas las sedes
-- tipo 'cambio_de_sede'  → equipo detectado en una sede diferente a la anterior
-- sede_detectada_id      → sede donde fue detectado (solo en cambio_de_sede)
-- ─────────────────────────────────────────────────────────────
CREATE TABLE alertas (
    id                INT AUTO_INCREMENT PRIMARY KEY,
    mac_address       VARCHAR(17)   NOT NULL,
    latitud           DECIMAL(10,8) NOT NULL,
    longitud          DECIMAL(11,8) NOT NULL,
    tipo              ENUM('fuera_de_sedes','cambio_de_sede') NOT NULL,
    sede_detectada_id INT           NULL,
    leida             TINYINT(1)    NOT NULL DEFAULT 0,
    creada_en         TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (mac_address)        REFERENCES dispositivos(mac_address) ON DELETE CASCADE,
    FOREIGN KEY (sede_detectada_id)  REFERENCES sedes(id) ON DELETE SET NULL,
    INDEX idx_leida     (leida),
    INDEX idx_mac_fecha (mac_address, creada_en)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
