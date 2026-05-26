"""
ManageTracking — Test end-to-end del tracker
=============================================
Prueba el flujo completo:
  1. Registrar dispositivo  → POST /api/registrar_dispositivo.php
  2. Obtener ubicación      → Windows Location API (PowerShell)
  3. Enviar ubicación       → POST /api/registrar_ubicacion.php
  4. Verificar indicadores  → GET  /api/indicadores.php
  5. Simular sin_ubicacion  → php cron/verificar_checkins.php (CLI)
"""

import requests
import subprocess
import socket
import uuid
import platform
import json
import os

# ─────────────────────────────────────────────────────────────────────────────
SERVIDOR    = "http://localhost/managetracking"   # local Laragon
# SERVIDOR  = "https://lively-reprieve-production-3227.up.railway.app"
CRON_PATH   = r"C:\laragon\www\managetracking\cron\verificar_checkins.php"
# ─────────────────────────────────────────────────────────────────────────────

SEP  = "─" * 55
OK   = "  ✓"
FAIL = "  ✗"


def get_mac():
    mac_int = uuid.getnode()
    return ':'.join(('%012X' % mac_int)[i:i+2] for i in range(0, 12, 2))


def ejecutar_powershell(cmd):
    return subprocess.run(
        ["powershell", "-NoProfile", "-Command", cmd],
        capture_output=True, text=True, errors="ignore"
    ).stdout.strip()


def obtener_ubicacion():
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
    Write-Output "$($loc.Latitude),$($loc.Longitude),$($loc.HorizontalAccuracy)"
}
$w.Stop()
"""
    out = subprocess.run(
        ["powershell", "-Command", script_ps],
        capture_output=True, text=True, timeout=30
    ).stdout.strip()
    return out


# ─── PASO 1: Registrar dispositivo ───────────────────────────────────────────
print(SEP)
print("PASO 1 — Registrar dispositivo")
print(SEP)

mac        = get_mac()
serie      = ejecutar_powershell("Get-CimInstance Win32_BIOS | Select-Object -ExpandProperty SerialNumber")
procesador = ejecutar_powershell("Get-CimInstance Win32_Processor | Select-Object -ExpandProperty Name")
ram_bytes  = ejecutar_powershell("Get-CimInstance Win32_ComputerSystem | Select-Object -ExpandProperty TotalPhysicalMemory")
try:    ram_gb = round(int(ram_bytes) / 1024**3)
except: ram_gb = None

payload_reg = {
    "mac_address":      mac,
    "hostname":         socket.gethostname(),
    "tipo":             "laptop",
    "windows_version":  platform.version(),
    "procesador":       procesador,
    "ram_gb":           ram_gb,
    "serie_equipo":     serie,
}

print(f"  MAC Address : {mac}")
print(f"  Hostname    : {payload_reg['hostname']}")
print(f"  Procesador  : {procesador[:50]}..." if len(procesador) > 50 else f"  Procesador  : {procesador}")
print(f"  RAM         : {ram_gb} GB")
print(f"  Servidor    : {SERVIDOR}")
print()

try:
    r = requests.post(f"{SERVIDOR}/api/registrar_dispositivo.php", json=payload_reg, timeout=10)
    print(f"  HTTP {r.status_code}: {r.text[:200]}")
    if r.status_code == 200:
        data    = r.json()
        api_key = data.get("api_key")
        print(f"{OK} API Key   : {api_key[:16]}...")
    else:
        print(f"{FAIL} Fallo en registro")
        exit(1)
except Exception as e:
    print(f"{FAIL} Error de conexión: {e}")
    print("  ¿Está corriendo Laragon? ¿URL correcta?")
    exit(1)


# ─── PASO 2: Obtener ubicación ────────────────────────────────────────────────
print()
print(SEP)
print("PASO 2 — Obtener ubicación (Windows Location API)")
print(SEP)
print("  Esperando hasta 20s para que el sensor responda...")
print()

out = obtener_ubicacion()
print(f"  Respuesta raw: '{out}'")

if not out or out == "UNKNOWN":
    print(f"\n{FAIL} Ubicación no disponible.")
    print("  Verifica: Configuración → Privacidad → Ubicación → Activar")
    exit(1)

partes    = out.split(",")
lat, lon  = float(partes[0]), float(partes[1])
precision = partes[2] if len(partes) > 2 else "?"

print(f"\n{OK} Latitud   : {lat}")
print(f"{OK} Longitud  : {lon}")
print(f"  Precisión  : {precision} metros")
print(f"  Maps       : https://maps.google.com/?q={lat},{lon}")


# ─── PASO 3: Enviar ubicación ─────────────────────────────────────────────────
print()
print(SEP)
print("PASO 3 — Enviar ubicación al servidor")
print(SEP)

payload_gps = {
    "mac_address": mac,
    "api_key":     api_key,
    "latitud":     lat,
    "longitud":    lon,
    "altitud":     0,
    "velocidad":   0,
    "satelites":   0,
}

try:
    r = requests.post(f"{SERVIDOR}/api/registrar_ubicacion.php", json=payload_gps, timeout=10)
    print(f"  HTTP {r.status_code}: {r.text}")
    if r.status_code == 200:
        print(f"{OK} Ubicación registrada (tipo='ubicacion' en registros_gps)")
    else:
        print(f"{FAIL} El servidor rechazó la ubicación")
except Exception as e:
    print(f"{FAIL} Error de conexión: {e}")


# ─── PASO 4: Verificar indicadores ───────────────────────────────────────────
print()
print(SEP)
print("PASO 4 — Verificar indicadores (NT / PID / TMC)")
print(SEP)

try:
    r = requests.get(f"{SERVIDOR}/api/indicadores.php", timeout=10)
    if r.status_code == 200:
        ind = r.json()

        nt  = ind.get("trazabilidad", {})
        pid = ind.get("incidencias", {})
        tmc = ind.get("monitoreo_continuo", {})

        print(f"  NT  — Nivel de Trazabilidad      : {nt.get('porcentaje')}%")
        print(f"        {nt.get('con_senal')} de {nt.get('total')} equipos con señal en 24h")

        print(f"\n  PID — Incidencias Detectadas      : {pid.get('porcentaje')}%")
        print(f"        {pid.get('detectadas')} equipo(s) con incidencia de {pid.get('total')} total")

        print(f"\n  TMC — Monitoreo Continuo          : {tmc.get('porcentaje')}%")
        print(f"        {tmc.get('total')} equipo(s) monitoreados")

        por_equipo = tmc.get("por_equipo", [])
        if por_equipo:
            print("\n  TMC por equipo (peores primero):")
            for eq in por_equipo:
                slots = eq.get('total_slots', '?')
                rec   = eq.get('recibidos', 0)
                print(f"    · {eq['nombre']:<30} {eq['tmc']}%  ({rec}/{slots} slots)")

        print(f"\n{OK} Indicadores respondieron correctamente")
    else:
        print(f"{FAIL} HTTP {r.status_code}: {r.text[:100]}")
except Exception as e:
    print(f"{FAIL} Error al consultar indicadores: {e}")


# ─── PASO 5: Simular cron verificar_checkins ─────────────────────────────────
print()
print(SEP)
print("PASO 5 — Simular cron (verificar_checkins.php)")
print(SEP)

if not os.path.exists(CRON_PATH):
    print(f"  Archivo no encontrado: {CRON_PATH}")
    print("  Ajusta CRON_PATH al inicio del script si la ruta es diferente")
else:
    try:
        resultado = subprocess.run(
            ["php", CRON_PATH],
            capture_output=True, text=True, timeout=15
        )
        salida = resultado.stdout.strip() or resultado.stderr.strip()
        print(f"  {salida}")
        if "sin_ubicacion insertados" in salida:
            print(f"{OK} Cron ejecutado correctamente")
        else:
            print(f"  (sin salida reconocida — revisa que php esté en el PATH)")
    except FileNotFoundError:
        print(f"{FAIL} 'php' no está en el PATH. Agrega C:\\laragon\\bin\\php\\phpX.X a las variables de entorno")
    except Exception as e:
        print(f"{FAIL} Error al ejecutar cron: {e}")


print()
print(SEP)
print("Test completado")
print(SEP)
