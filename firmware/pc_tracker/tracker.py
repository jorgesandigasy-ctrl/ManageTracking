import requests
import subprocess
import socket
import uuid
import platform
import time
import logging
import os
import sys

# ===================== CONFIGURACIÓN =====================
SERVIDOR      = "http://192.168.18.14/managetracking"
NOMBRE_USUARIO   = ""
APELLIDO_USUARIO = ""
TELEFONO_USUARIO = ""
SEDE_ID          = None  # Pon el ID de sede cuando lo tengas
INTERVALO        = 3600  # segundos entre envíos de ubicación
# =========================================================

LOG_PATH = os.path.join(
    os.path.dirname(sys.executable if getattr(sys, 'frozen', False) else __file__),
    "tracker.log"
)
logging.basicConfig(
    filename=LOG_PATH, level=logging.INFO,
    format="%(asctime)s %(levelname)s %(message)s",
    datefmt="%Y-%m-%d %H:%M:%S"
)

def ps(command):
    """Ejecuta un comando PowerShell y devuelve la salida limpia."""
    out = subprocess.run(
        ["powershell", "-NoProfile", "-Command", command],
        capture_output=True, text=True, errors="ignore"
    ).stdout.strip()
    return out

def get_mac():
    mac_int = uuid.getnode()
    return ':'.join(('%012X' % mac_int)[i:i+2] for i in range(0, 12, 2))

def get_specs():
    serie     = ps("Get-CimInstance Win32_BIOS | Select-Object -ExpandProperty SerialNumber")
    procesador= ps("Get-CimInstance Win32_Processor | Select-Object -ExpandProperty Name")
    ram_bytes = ps("Get-CimInstance Win32_ComputerSystem | Select-Object -ExpandProperty TotalPhysicalMemory")
    disco_bytes= ps("Get-CimInstance Win32_DiskDrive | Select-Object -First 1 -ExpandProperty Size")

    try: ram_gb = round(int(ram_bytes) / 1024**3)
    except: ram_gb = None

    try: disco_gb = round(int(disco_bytes) / 1024**3)
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
    specs = get_specs()
    r = requests.post(f"{SERVIDOR}/api/registrar_dispositivo.php", json=specs, timeout=10)
    if r.status_code == 200:
        data = r.json()
        logging.info(f"Dispositivo registrado — MAC: {data['mac_address']}")
        return data["mac_address"], data["api_key"]
    raise RuntimeError(f"Registro falló {r.status_code}: {r.text}")

def obtener_ubicacion():
    ps = """
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
    out = subprocess.run(["powershell", "-Command", ps],
                         capture_output=True, text=True, timeout=30).stdout.strip()
    if not out or out == "UNKNOWN":
        raise RuntimeError("Ubicación no disponible")
    lat, lon = out.split(",")
    return float(lat), float(lon)

def enviar_ubicacion(mac, api_key):
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
        logging.info(f"Ubicación enviada — Lat: {lat}, Lon: {lon}")
    else:
        logging.error(f"Error servidor {r.status_code}: {r.text}")

def main():
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
