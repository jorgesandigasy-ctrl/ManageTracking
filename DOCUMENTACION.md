# ManageTracking — Documentación del Sistema

**Versión:** 1.0  
**Plataforma:** PHP 8.2 + MySQL 8 + Python 3  
**Entorno de producción:** Railway (PaaS)  
**Entorno de desarrollo:** XAMPP (Windows)

---

## Índice

1. [¿Qué es ManageTracking?](#1-qué-es-managetracking)
2. [Arquitectura general](#2-arquitectura-general)
3. [Estructura de carpetas](#3-estructura-de-carpetas)
4. [Base de datos](#4-base-de-datos)
5. [Panel de administración](#5-panel-de-administración)
6. [API REST](#6-api-rest)
7. [Agente de rastreo para PC (tracker.py)](#7-agente-de-rastreo-para-pc-trackerpy)
8. [Firmware ESP32 + SIM800L](#8-firmware-esp32--sim800l)
9. [Indicadores de tesis](#9-indicadores-de-tesis)
10. [Estados de los dispositivos](#10-estados-de-los-dispositivos)
11. [Despliegue en Railway](#11-despliegue-en-railway)
12. [Instalación local (XAMPP)](#12-instalación-local-xampp)

---

## 1. ¿Qué es ManageTracking?

ManageTracking es un sistema web de rastreo y gestión de equipos informáticos (laptops, PCs, dispositivos IoT). Permite:

- **Registrar** equipos con sus especificaciones de hardware.
- **Localizar** equipos en tiempo real mediante coordenadas GPS mostradas en un mapa.
- **Monitorear** el estado de cada equipo (activo, inactivo, perdido).
- **Organizar** los equipos por clientes y sedes.
- **Medir** indicadores clave para evaluar la efectividad del sistema (tesis).
- **Encuestar** al personal sobre su satisfacción con el sistema.

---

## 2. Arquitectura general

```
┌─────────────────────────────────────────────────────────────┐
│                        CLIENTE                              │
│                                                             │
│  Navegador Web          tracker.exe         ESP32+SIM800L   │
│  (Panel Admin)          (PC/Laptop)         (dispositivo IoT)│
└────────┬────────────────────┬───────────────────┬───────────┘
         │ HTTPS              │ HTTP POST          │ HTTP POST
         ▼                    ▼                    ▼
┌─────────────────────────────────────────────────────────────┐
│                    SERVIDOR (Railway)                        │
│                                                             │
│  PHP 8.2 + Apache                                           │
│  ┌─────────────────────────────────────────────────────┐   │
│  │  Vistas PHP      API REST        Controladores       │   │
│  │  /app/Views/     /api/*.php      /app/Controllers/   │   │
│  └─────────────────────────────────────────────────────┘   │
│                          │                                  │
│                          ▼                                  │
│  ┌─────────────────────────────────────────────────────┐   │
│  │              MySQL 8 (Railway)                       │   │
│  │  clientes / sedes / dispositivos /                   │   │
│  │  registros_gps / encuestas                           │   │
│  └─────────────────────────────────────────────────────┘   │
└─────────────────────────────────────────────────────────────┘
```

**Flujo básico:**
1. El `tracker.exe` (o firmware ESP32) envía la ubicación del equipo al servidor.
2. El servidor valida la `api_key`, guarda las coordenadas en `registros_gps` y actualiza `ultima_vez`.
3. El panel de administración consulta la API cada 15 segundos y actualiza el mapa y la tabla en tiempo real.

---

## 3. Estructura de carpetas

```
managetracking/
│
├── index.php                 ← Dashboard (requiere login)
├── dispositivos.php          ← Gestión de equipos
├── clientes.php              ← Gestión de clientes y sedes
├── detalle.php               ← Historial GPS de un equipo
├── encuesta.php              ← Encuesta pública (sin login)
├── login.php                 ← Formulario de autenticación
├── logout.php                ← Cierre de sesión
├── auth.php                  ← Verificación de sesión activa
├── config.php                ← Credenciales de BD y admin (usa env vars)
├── schema.sql                ← Script SQL para crear la base de datos
├── Dockerfile                ← Configuración de contenedor para Railway
│
├── config/
│   └── Database.php          ← Singleton de conexión MySQL
│
├── app/
│   ├── Models/               ← Acceso directo a la base de datos
│   │   ├── Cliente.php
│   │   ├── Sede.php
│   │   ├── Dispositivo.php
│   │   ├── RegistroGPS.php
│   │   └── Encuesta.php
│   │
│   ├── Controllers/          ← Lógica de negocio + respuestas JSON
│   │   ├── ClienteController.php
│   │   ├── SedeController.php
│   │   ├── DispositivoController.php
│   │   └── EncuestaController.php
│   │
│   └── Views/                ← Páginas HTML generadas con PHP
│       ├── dashboard.php
│       ├── dispositivos.php
│       ├── clientes.php
│       ├── detalle.php
│       ├── encuesta.php
│       └── layouts/
│           ├── head.php      ← <head> HTML compartido
│           └── sidebar.php   ← Barra lateral de navegación
│
├── api/                      ← Endpoints REST (thin wrappers)
│   ├── obtener_dispositivos.php
│   ├── registrar_dispositivo.php
│   ├── editar_dispositivo.php
│   ├── eliminar_dispositivo.php
│   ├── registrar_ubicacion.php
│   ├── historial_dispositivo.php
│   ├── obtener_clientes.php
│   ├── crear_cliente.php
│   ├── editar_cliente.php
│   ├── eliminar_cliente.php
│   ├── obtener_sedes.php
│   ├── crear_sede.php
│   ├── editar_sede.php
│   ├── eliminar_sede.php
│   ├── guardar_encuesta.php
│   └── indicadores.php
│
└── firmware/
    ├── pc_tracker/
    │   ├── tracker.py        ← Agente Python para PC/Laptop Windows
    │   ├── instalar.bat      ← Instalador automático (compila + registra tarea)
    │   └── test_ubicacion.py ← Script de prueba de ubicación
    └── esp32_sim800l_tracker/
        └── esp32_sim800l_tracker.ino ← Firmware Arduino para IoT
```

---

## 4. Base de datos

### Tabla `clientes`
Empresas o instituciones que poseen equipos.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `nombre` | VARCHAR(100) | Nombre de la empresa |
| `ruc` | VARCHAR(20) | RUC / número de identificación fiscal |
| `creado_en` | TIMESTAMP | Fecha de registro |

### Tabla `sedes`
Ubicaciones físicas de cada cliente.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `cliente_id` | INT FK | Cliente al que pertenece |
| `nombre` | VARCHAR(100) | Nombre de la sede |
| `direccion` | TEXT | Dirección física |
| `ciudad` | VARCHAR(100) | Ciudad |

### Tabla `dispositivos`
Equipos registrados en el sistema.

| Campo | Tipo | Descripción |
|---|---|---|
| `mac_address` | VARCHAR(20) UNIQUE | MAC address (PCs) o IMEI (dispositivos GSM) |
| `hostname` | VARCHAR(100) | Nombre del equipo en la red |
| `nombre_usuario` | VARCHAR(100) | Nombre del responsable del equipo |
| `apellido_usuario` | VARCHAR(100) | Apellido del responsable |
| `telefono_usuario` | VARCHAR(20) | Teléfono de contacto |
| `sede_id` | INT FK | Sede a la que está asignado |
| `tipo` | ENUM | `laptop`, `pc`, `esp8266`, `gps`, `otro` |
| `windows_version` | VARCHAR(100) | Versión del SO (solo PCs) |
| `serie_equipo` | VARCHAR(100) | Número de serie del BIOS |
| `ram_gb` | INT | RAM en GB |
| `almacenamiento_gb` | INT | Disco duro en GB |
| `procesador` | VARCHAR(200) | Modelo del procesador |
| `api_key` | VARCHAR(64) | Clave de autenticación del dispositivo |
| `estado` | ENUM | `activo`, `inactivo`, `perdido` (ver sección 10) |
| `ultima_vez` | TIMESTAMP | Última vez que el tracker envió datos |

### Tabla `registros_gps`
Historial de ubicaciones de cada dispositivo.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `mac_address` | VARCHAR(20) FK | Dispositivo al que pertenece |
| `latitud` | DECIMAL(10,8) | Coordenada de latitud |
| `longitud` | DECIMAL(11,8) | Coordenada de longitud |
| `altitud` | DECIMAL(8,2) | Altitud en metros (opcional) |
| `velocidad` | DECIMAL(6,2) | Velocidad en km/h (opcional) |
| `satelites` | INT | Número de satélites (solo GPS real) |
| `tiempo_respuesta_ms` | DECIMAL(10,3) | Tiempo que tardó la API en procesar la petición |
| `registrado_en` | TIMESTAMP | Fecha y hora del registro |

### Tabla `encuestas`
Respuestas de la encuesta de satisfacción del personal.

| Campo | Tipo | Descripción |
|---|---|---|
| `id` | INT PK | Identificador único |
| `p1` | TINYINT | ¿Qué tan útil es el sistema? (1–5) |
| `p2` | TINYINT | ¿Es fácil de usar? (1–5) |
| `p3` | TINYINT | ¿Reduce el tiempo de búsqueda? (1–5) |
| `p4` | TINYINT | ¿Mejora el control de equipos? (1–5) |
| `p5` | TINYINT | ¿Lo recomendaría? (1–5) |
| `respondido_en` | TIMESTAMP | Fecha de la respuesta |

---

## 5. Panel de administración

### Acceso
- **URL:** `https://[dominio]/login.php`
- **Usuario y contraseña:** configurados en `config.php` (variables de entorno `AUTH_USER` y `AUTH_PASS`).
- La sesión se mantiene activa mientras el navegador esté abierto.

### Secciones

#### Métricas (Dashboard — `index.php`)
Vista principal del sistema. Se actualiza automáticamente cada 15 segundos.

- **Tarjetas de resumen:** total de equipos, activos, perdidos, inactivos.
- **Gráfico de dona:** distribución de estados en tiempo real.
- **Actividad reciente:** últimos 5 equipos que enviaron ubicación.
- **Tabla de equipos:** lista completa con estado, sede y última señal.
- **Mapa en vivo:** ubicación de cada equipo con marcador de color según estado (verde=activo, rojo=perdido, gris=inactivo).
- **Indicadores de tesis:** 4 gráficos con NT, PID, TPU y NSP (ver sección 9).

#### Equipos (`dispositivos.php`)
Gestión completa del inventario de dispositivos.

- **Agregar equipo:** formulario para registrar manualmente un equipo (MAC, tipo, sede, datos del usuario). Al guardar, el sistema genera una `api_key` única.
- **Editar equipo:** modificar nombre de usuario, sede, tipo y estado.
- **Eliminar equipo:** elimina el equipo y todo su historial GPS.
- **Ver detalle:** enlace al historial completo de ubicaciones del equipo.

> **Nota:** Los equipos también se registran automáticamente cuando el `tracker.exe` se ejecuta por primera vez en un PC. El admin solo necesita asignar la sede y los datos del usuario.

#### Clientes y Sedes (`clientes.php`)
Vista de dos paneles: clientes a la izquierda, sedes de cada cliente a la derecha.

- **Clientes:** agregar, editar y eliminar empresas (nombre + RUC).
- **Sedes:** al seleccionar un cliente, se muestran sus sedes. Se pueden agregar, editar y eliminar sedes con dirección y ciudad.

#### Detalle de Equipo (`detalle.php?mac=XX:XX:XX:XX:XX:XX`)
- Especificaciones completas del equipo (procesador, RAM, disco, serie, etc.).
- Mapa con el historial de rutas del equipo.
- Tabla con todos los registros GPS (fecha, coordenadas, tiempo de respuesta).

#### Encuesta de Satisfacción (`encuesta.php`)
- Formulario público (sin necesidad de login).
- 5 preguntas con escala Likert 1–5 (Muy malo → Excelente).
- Se puede compartir el enlace directamente con el personal técnico.
- Los resultados se ven en el indicador NSP del dashboard.

---

## 6. API REST

Todos los endpoints están en la carpeta `/api/` y responden en formato JSON.

### Autenticación del panel
Las páginas del panel requieren sesión PHP activa (login). Los endpoints API del panel no requieren autenticación adicional (protegidos por estar detrás del login de PHP).

### Endpoints de Dispositivos

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/obtener_dispositivos.php` | Lista todos los dispositivos con su estado calculado |
| POST | `/api/registrar_dispositivo.php` | Registra o actualiza un dispositivo (upsert por MAC) |
| POST | `/api/editar_dispositivo.php` | Edita datos de un dispositivo existente |
| POST | `/api/eliminar_dispositivo.php` | Elimina un dispositivo y su historial |
| POST | `/api/registrar_ubicacion.php` | **Endpoint principal del tracker** — recibe coordenadas GPS |
| GET | `/api/historial_dispositivo.php?mac=XX&limite=100` | Historial de ubicaciones de un equipo |

#### `POST /api/registrar_ubicacion.php`
Este es el endpoint que llaman los trackers (PC y ESP32) para enviar su posición.

**Body (JSON):**
```json
{
  "mac_address": "80:C5:F2:73:E2:57",
  "api_key": "a3f8b2c1...",
  "latitud": -8.1092,
  "longitud": -79.0219,
  "altitud": 34.0,
  "velocidad": 0,
  "satelites": 0
}
```

**Respuesta exitosa:**
```json
{ "ok": true, "mensaje": "Ubicación registrada" }
```

**Respuesta de error (api_key inválida):**
```json
{ "error": "Dispositivo no autorizado" }
```

#### `POST /api/registrar_dispositivo.php`
Llamado automáticamente por el `tracker.exe` al iniciarse.

**Body (JSON):**
```json
{
  "mac_address": "80:C5:F2:73:E2:57",
  "hostname": "DESKTOP-ABC123",
  "tipo": "laptop",
  "windows_version": "10.0.19045",
  "procesador": "Intel Core i5-10210U",
  "ram_gb": 8,
  "almacenamiento_gb": 512,
  "serie_equipo": "SN123456"
}
```

**Respuesta:**
```json
{ "ok": true, "mac_address": "80:C5:F2:73:E2:57", "api_key": "a3f8b2c1..." }
```

> Si el MAC ya existe, devuelve la `api_key` existente (upsert).

### Endpoints de Clientes y Sedes

| Método | Endpoint | Descripción |
|---|---|---|
| GET | `/api/obtener_clientes.php` | Lista todos los clientes |
| POST | `/api/crear_cliente.php` | Crea un cliente nuevo |
| POST | `/api/editar_cliente.php` | Edita un cliente |
| POST | `/api/eliminar_cliente.php` | Elimina un cliente y sus sedes |
| GET | `/api/obtener_sedes.php?cliente_id=1` | Sedes de un cliente (o todas si no se pasa ID) |
| POST | `/api/crear_sede.php` | Crea una sede |
| POST | `/api/editar_sede.php` | Edita una sede |
| POST | `/api/eliminar_sede.php` | Elimina una sede |

### Endpoints de Encuesta e Indicadores

| Método | Endpoint | Descripción |
|---|---|---|
| POST | `/api/guardar_encuesta.php` | Guarda una respuesta de encuesta |
| GET | `/api/indicadores.php` | Devuelve los 4 indicadores de tesis calculados |

---

## 7. Agente de rastreo para PC (`tracker.py`)

### ¿Qué hace?
Script Python que se ejecuta en segundo plano en laptops/PCs con Windows. Su función es:
1. Registrar el equipo en el servidor (primera ejecución).
2. Obtener la ubicación del equipo cada hora.
3. Enviar las coordenadas al servidor.

### Tecnología de ubicación
Usa la **Windows Location API** (`System.Device.Location`), la misma que usan las apps de mapas en Windows. Internamente puede usar:
- **GPS del dispositivo** (si tiene chip GPS).
- **WiFi positioning** (triangulación por redes WiFi cercanas) — precisión ~10–50m.
- **IP geolocation** como último recurso — precisión baja (ciudad).

**Requisito:** El servicio de ubicación debe estar activado en el equipo:  
`Configuración → Privacidad y seguridad → Ubicación → Activar`

### Configuración antes de compilar
Editar estas líneas en `tracker.py`:

```python
SERVIDOR  = "https://tu-dominio.up.railway.app"  # URL del servidor
INTERVALO = 3600  # Segundos entre envíos (3600 = 1 hora)
```

> Los campos `NOMBRE_USUARIO`, `APELLIDO_USUARIO`, `SEDE_ID` se dejan vacíos — el administrador los completa desde el panel web después de que el equipo se registre automáticamente.

### Instalación en un equipo nuevo

**Opción A — Instalación completa (recomendada):**
1. Copiar `tracker.py` e `instalar.bat` al equipo destino.
2. Ejecutar `instalar.bat` como administrador.
3. El instalador: verifica Python, instala dependencias, compila `tracker.exe` y registra una **tarea programada en Windows** que arranca el tracker automáticamente con cada inicio del sistema.

**Opción B — Ejecución manual:**
```powershell
pip install requests
python tracker.py
```

### Archivos generados
- `tracker.exe` — ejecutable compilado (sin consola visible).
- `tracker.log` — registro de actividad en la misma carpeta del exe.

### Ejemplo de log
```
2026-04-26 20:53:23 INFO Tracker iniciado
2026-04-26 20:53:25 INFO OK — Lat: -8.09883, Lng: -79.04542
2026-04-26 21:53:25 INFO OK — Lat: -8.09902, Lng: -79.04557
```

---

## 8. Firmware ESP32 + SIM800L

### ¿Para qué sirve?
Para rastrear equipos que no tienen Windows ni conexión WiFi, usando la red celular GSM. Ideal para vehículos, maquinaria o equipos en campo.

### Hardware necesario
- **ESP32** (microcontrolador con WiFi/BT)
- **SIM800L** (módulo GSM 2G)
- **SIM card** con datos (Claro, Movistar, Bitel, etc.)
- **Diodo 1N4007** en serie para reducir 5V → ~4.3V para el SIM800L
- **Fuente de 5V** con capacidad de al menos 2A

### Conexiones
| ESP32 | SIM800L |
|---|---|
| GPIO16 (RX2) | TX |
| GPIO17 (TX2) | RX |
| GND | GND |
| 5V → diodo → VCC | VCC |

### Método de localización
El SIM800L **no tiene GPS**. Usa **localización por torres celulares** (`AT+CIPGSMLOC`), con precisión aproximada de 300m–2km dependiendo de la densidad de antenas en la zona.

### Configuración del firmware
En el archivo `.ino`, editar:
```cpp
#define APN       "claro.pe"        // APN de tu operadora
#define SERVIDOR  "192.168.18.14"   // IP/dominio del servidor
#define INTERVALO_MS 3600000UL      // 1 hora en milisegundos
```

**APNs por operadora (Perú):**
| Operadora | APN |
|---|---|
| Claro | `claro.pe` |
| Movistar | `internet.movistar.pe` |
| Entel | `pe.entelpcs.pe` |
| Bitel | `bitel` |

### Identificador de dispositivo
El firmware usa el **IMEI** del SIM800L (15 dígitos, obtenido con `AT+CGSN`) como identificador único, almacenado en el campo `mac_address` de la BD (VARCHAR(20)).

---

## 9. Indicadores de tesis

Los indicadores se calculan en `api/indicadores.php` y se muestran como gráficos en el dashboard.

### NT — Nivel de Trazabilidad

$$NT = \frac{\text{Equipos con señal en las últimas 24h}}{\text{Total de equipos}} \times 100$$

- **Fuente:** campo `ultima_vez` de la tabla `dispositivos`.
- **Gráfico:** velocímetro semicircular (verde ≥75%, amarillo ≥50%, rojo <50%).

### PID — Porcentaje de Incidencias Detectadas

$$PID = \frac{\text{Dispositivos perdidos} + \text{Sin señal >24h}}{\text{Total de dispositivos}} \times 100$$

- **Fuente:** campo `estado` y `ultima_vez` de la tabla `dispositivos`.
- **Gráfico:** dona (rojo = incidencias detectadas, verde = sin incidencia).

### TPU — Tiempo Promedio de Ubicación

$$TPU = \frac{\sum \text{tiempo\_respuesta\_ms}}{\text{N peticiones}}$$

- **Fuente:** campo `tiempo_respuesta_ms` de la tabla `registros_gps`.
- El tiempo se mide en el servidor desde que recibe la petición hasta que responde.
- **Gráfico:** barras horizontales con el promedio por equipo.
- **Valor de referencia pre-test (búsqueda manual):** ~900 segundos. Post-test: ~45ms.

### NSP — Nivel de Satisfacción del Personal

$$NSP = \frac{\sum (p1 + p2 + p3 + p4 + p5)}{25 \times \text{Total encuestas}} \times 100$$

- 25 = puntaje máximo por encuesta (5 preguntas × 5 puntos).
- **Fuente:** tabla `encuestas`.
- **Gráfico:** barras horizontales con el promedio de cada pregunta (escala 1–5).

---

## 10. Estados de los dispositivos

El estado se calcula **dinámicamente** en cada consulta SQL. El campo `estado` en la base de datos solo almacena el estado `perdido` cuando es asignado manualmente.

| Estado | Color | Criterio | Quién lo define |
|---|---|---|---|
| **Activo** | Verde | `ultima_vez` hace menos de 2 horas | Automático |
| **Inactivo** | Gris | `ultima_vez` hace más de 2 horas | Automático |
| **Perdido** | Rojo | Marcado manualmente por el admin | Manual |

**Lógica SQL:**
```sql
CASE
    WHEN estado = 'perdido'                     THEN 'perdido'
    WHEN ultima_vez >= NOW() - INTERVAL 2 HOUR  THEN 'activo'
    ELSE 'inactivo'
END AS estado
```

**Cuándo marcar un equipo como "Perdido":**
- El equipo fue reportado como robado.
- El equipo fue llevado fuera de las instalaciones sin autorización.
- El equipo no aparece físicamente y lleva días sin señal.

> Un equipo marcado como "Perdido" **no puede enviar ubicaciones** — su `api_key` es rechazada por el servidor. Esto evita que un equipo robado continúe actualizando su posición si el ladrón conoce las credenciales.

---

## 11. Despliegue en Railway

### Variables de entorno requeridas (servicio PHP)

| Variable | Valor |
|---|---|
| `MYSQLHOST` | `mysql.railway.internal` |
| `MYSQLPORT` | `3306` |
| `MYSQLUSER` | `root` |
| `MYSQLPASSWORD` | _(generado por Railway)_ |
| `MYSQLDATABASE` | `railway` |
| `AUTH_USER` | Usuario del panel admin |
| `AUTH_PASS` | Contraseña del panel admin |

> Las variables `MYSQL*` son inyectadas automáticamente por Railway si enlazas el servicio MySQL al servicio PHP desde el dashboard.

### Pasos para desplegar desde cero
1. Crear proyecto en Railway.
2. Agregar servicio MySQL (plugin).
3. Agregar servicio desde repositorio GitHub.
4. Configurar las variables de entorno listadas arriba.
5. Conectarse a la BD con TablePlus y ejecutar `schema.sql` (sin las primeras 2 líneas si la BD ya existe).
6. Railway despliega automáticamente en cada `git push` a la rama `main`.

---

## 12. Instalación local (XAMPP)

### Requisitos
- XAMPP con PHP 8.x y MySQL
- Python 3.10+
- Git

### Pasos
1. Clonar el repositorio en `C:\xampp\htdocs\managetracking`.
2. Crear la base de datos ejecutando `schema.sql` en phpMyAdmin.
3. Editar `config.php` con las credenciales locales:
   ```php
   define('AUTH_USER', 'tu_usuario');
   define('AUTH_PASS', 'tu_contraseña');
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');
   define('DB_NAME', 'managetracking');
   ```
4. Iniciar Apache y MySQL desde el panel de XAMPP.
5. Acceder a `http://localhost/managetracking/`.

### Probar el tracker localmente
```powershell
cd C:\xampp\htdocs\managetracking\firmware\pc_tracker
pip install requests
python tracker.py
```
Cambiar `SERVIDOR` a `http://localhost/managetracking` y `INTERVALO` a `10` para pruebas rápidas.
