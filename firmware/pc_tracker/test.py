import requests
import asyncio

SERVIDOR_URL   = "http://192.168.18.14/managetracking/api/registrar_ubicacion.php"
DISPOSITIVO_ID = "ASUS-260903"
API_KEY        = "77ed4efb0d4282c9874f0d63b341da0b1fde736d50d3c5bba790decfc610110e"

print("1. Obteniendo ubicacion via Windows Location API...")
lat, lon = None, None
try:
    from winrt.windows.devices.geolocation import Geolocator
    async def _get():
        gl = Geolocator()
        pos = await gl.get_geoposition_async()
        return pos.coordinate.latitude, pos.coordinate.longitude
    lat, lon = asyncio.run(_get())
    print(f"   OK — Lat: {lat}, Lon: {lon}")
except Exception as e:
    print(f"   Windows API falló: {e}")
    print("   Intentando por IP...")
    try:
        r = requests.get("http://ip-api.com/json/?fields=lat,lon,status,message", timeout=10)
        data = r.json()
        lat, lon = data["lat"], data["lon"]
        print(f"   IP fallback — Lat: {lat}, Lon: {lon}")
    except Exception as e2:
        print(f"   ERROR IP: {e2}")
        exit()

print(f"\n2. Enviando al servidor...")
try:
    payload = {
        "dispositivo_id": DISPOSITIVO_ID,
        "api_key":        API_KEY,
        "latitud":        lat,
        "longitud":       lon,
        "altitud":        0,
        "velocidad":      0,
        "satelites":      0
    }
    print(f"   Payload: {payload}")
    r = requests.post(SERVIDOR_URL, json=payload, timeout=10)
    print(f"   HTTP {r.status_code}: {r.text}")
except Exception as e:
    print(f"   ERROR: {e}")
