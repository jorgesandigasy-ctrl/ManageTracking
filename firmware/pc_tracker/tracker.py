"""
ManageTracking — Agente de rastreo para PC/Laptop con Windows
=============================================================
Funcionamiento:
  1. Al iniciarse, recopila las especificaciones del equipo (MAC, hostname,
     procesador, RAM, etc.) y las registra en el servidor via HTTP POST.
     El servidor devuelve una api_key única que identifica al equipo.
  2. Cada INTERVALO segundos obtiene la ubicación del equipo usando la
     Windows Location API (la misma que usan las apps de mapas de Windows).
  3. Envía las coordenadas al servidor junto con la api_key para autenticarse.
  4. Todo queda registrado en tracker.log junto al ejecutable.

Requisito en la PC destino:
  - Windows 10/11 con Ubicación activada:
    Configuración → Privacidad → Ubicación → Activar
"""

import requests
import subprocess
import socket
import uuid
import platform
import time
import logging
import os
import sys

# ─────────────────────────────────────────────────────────────────────────────
# CONFIGURACIÓN — Editar antes de compilar el .exe
# ─────────────────────────────────────────────────────────────────────────────
SERVIDOR         = "https://lively-reprieve-production-3227.up.railway.app"  # URL del servidor ManageTracking
NOMBRE_USUARIO   = ""      # El admin lo asigna desde el panel web
APELLIDO_USUARIO = ""
TELEFONO_USUARIO = ""
SEDE_ID          = None    # El admin lo asigna desde el panel web
INTERVALO        = 3600    # Segundos entre envíos de ubicación (3600 = 1 hora)
# ─────────────────────────────────────────────────────────────────────────────

# El log se guarda en la misma carpeta que el .exe (o el .py si no está compilado)
LOG_PATH = os.path.join(
    os.path.dirname(sys.executable if getattr(sys, 'frozen', False) else __file__),
    "tracker.log"
)
logging.basicConfig(
    filename=LOG_PATH, level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S"
)


def ejecutar_powershell(command):
    """Ejecuta un comando PowerShell y devuelve la salida como texto limpio."""
    out = subprocess.run(
        ["powershell", "-NoProfile", "-Command", command],
        capture_output=True, text=True, errors="ignore"
    ).stdout.strip()
    return out


def get_mac():
    """
    Obtiene la dirección MAC del adaptador de red principal del equipo.
    Se usa como identificador único del dispositivo en la base de datos.
    uuid.getnode() devuelve el MAC del primer adaptador activo.
    """
    mac_int = uuid.getnode()
    return ':'.join(('%012X' % mac_int)[i:i+2] for i in range(0, 12, 2))


def get_specs():
    """
    Recopila las especificaciones de hardware del equipo usando WMI (PowerShell).
    Estos datos se envían al registrar el dispositivo por primera vez.
    El servidor hace un UPSERT: si el MAC ya existe, actualiza los datos.
    """
    serie        = ejecutar_powershell("Get-CimInstance Win32_BIOS | Select-Object -ExpandProperty SerialNumber")
    procesador   = ejecutar_powershell("Get-CimInstance Win32_Processor | Select-Object -ExpandProperty Name")
    ram_bytes    = ejecutar_powershell("Get-CimInstance Win32_ComputerSystem | Select-Object -ExpandProperty TotalPhysicalMemory")
    disco_bytes  = ejecutar_powershell("Get-CimInstance Win32_DiskDrive | Select-Object -First 1 -ExpandProperty Size")

    try:    ram_gb   = round(int(ram_bytes)   / 1024**3)
    except: ram_gb   = None

    try:    disco_gb = round(int(disco_bytes) / 1024**3)
    except: disco_gb = None

    return {
        "mac_address":       get_mac(),
        "hostname":          socket.gethostname(),
        "tipo":              "laptop",
        "windows_version":   platform.version(),
        "serie_equipo":      serie,
        "procesador":        procesador,
        "ram_gb":            ram_gb,
        "almacenamiento_gb": disco_gb,
        "nombre_usuario":    NOMBRE_USUARIO,
        "apellido_usuario":  APELLIDO_USUARIO,
        "telefono_usuario":  TELEFONO_USUARIO,
        "sede_id":           SEDE_ID,
    }


def registrar_dispositivo():
    """
    Registra el equipo en el servidor y obtiene la api_key.
    - Si el MAC ya existe en la BD: el servidor devuelve la api_key existente.
    - Si es un equipo nuevo: el servidor crea el registro y devuelve una api_key nueva.
    La api_key se usa en cada envío de ubicación para autenticar el equipo.
    """
    specs = get_specs()
    r = requests.post(f"{SERVIDOR}/api/registrar_dispositivo.php", json=specs, timeout=10)
    if r.status_code == 200:
        data = r.json()
        logging.info(f"Dispositivo registrado — MAC: {data['mac_address']}")
        return data["mac_address"], data["api_key"]
    raise RuntimeError(f"Registro falló {r.status_code}: {r.text}")


def obtener_ubicacion():
    """
    Obtiene las coordenadas GPS del equipo usando la Windows Location API.
    Internamente Windows usa WiFi positioning, GPS (si existe) o IP geolocation.
    Espera hasta 20 segundos para que el sensor esté listo.
    Lanza RuntimeError si la ubicación no está disponible (servicio desactivado).
    """
    script_ps = """
Add-Type -AssemblyName System.Device
$w = New-Object System.Device.Location.GeoCoordinateWatcher('High')
$w.Start()
$t = 0
while ($w.Status -ne 'Ready' -and $t -lt 20) {
    Start-Sleep -Milliseconds 500
    $t += 0.5
}
if ($w.Position.Location.IsUnknown) { Write-Output "UNKNOWN" }
else {
    $loc = $w.Position.Location
    Write-Output "$($loc.Latitude),$($loc.Longitude)"
}
$w.Stop()
"""
    out = subprocess.run(
        ["powershell", "-Command", script_ps],
        capture_output=True, text=True, timeout=30
    ).stdout.strip()

    if not out or out == "UNKNOWN":
        raise RuntimeError("Ubicación no disponible — activa el servicio en Configuración > Privacidad > Ubicación")

    lat, lon = out.split(",")
    return float(lat), float(lon)


def enviar_ubicacion(mac, api_key):
    """
    Obtiene la ubicación actual y la envía al servidor.
    El servidor valida la api_key, guarda las coordenadas en registros_gps
    y actualiza el campo ultima_vez del dispositivo.
    """
    lat, lon = obtener_ubicacion()
    r = requests.post(f"{SERVIDOR}/api/registrar_ubicacion.php", json={
        "mac_address": mac,
        "api_key":     api_key,
        "latitud":     lat,
        "longitud":    lon,
        "altitud":     0,
        "velocidad":   0,
        "satelites":   0,
    }, timeout=10)
    if r.status_code == 200:
        logging.info(f"OK — Lat: {lat}, Lng: {lon}")
    else:
        logging.error(f"Error servidor {r.status_code}: {r.text}")


def main():
    """
    Flujo principal:
      1. Registra el dispositivo y obtiene mac + api_key
      2. Bucle infinito: cada INTERVALO segundos envía la ubicación
    Si el registro inicial falla (sin conexión, servidor caído), el tracker
    se detiene y registra el error en el log.
    """
    logging.info("Tracker iniciado")
    try:
        mac, api_key = registrar_dispositivo()
    except Exception as e:
        logging.error(f"No se pudo registrar dispositivo: {e}")
        return

    while True:
        try:
            enviar_ubicacion(mac, api_key)
        except Exception as e:
            logging.error(f"Error ubicación: {e}")
        time.sleep(INTERVALO)


if __name__ == "__main__":
    main()
