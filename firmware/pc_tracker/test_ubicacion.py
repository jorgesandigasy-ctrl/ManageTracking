import subprocess

print("Obteniendo ubicacion via Windows Location API (PowerShell)...")
print("Asegurate de tener activado: Configuracion > Privacidad > Ubicacion\n")

ps = """
Add-Type -AssemblyName System.Device
$w = New-Object System.Device.Location.GeoCoordinateWatcher('High')
$w.Start()
$t = 0
while ($w.Status -ne 'Ready' -and $t -lt 20) {
    Start-Sleep -Milliseconds 500
    $t += 0.5
}
if ($w.Position.Location.IsUnknown) {
    Write-Output "UNKNOWN"
} else {
    $loc = $w.Position.Location
    Write-Output "$($loc.Latitude),$($loc.Longitude),$($loc.Altitude),$($loc.HorizontalAccuracy)"
}
$w.Stop()
"""

result = subprocess.run(
    ["powershell", "-Command", ps],
    capture_output=True, text=True, timeout=30
)

out = result.stdout.strip()
print(f"Respuesta raw: '{out}'")

if out == "UNKNOWN" or not out:
    print("\nNo se pudo obtener ubicacion.")
    print("Verifica: Configuracion > Privacidad y seguridad > Ubicacion > Activar")
else:
    partes = out.split(",")
    lat, lon = float(partes[0]), float(partes[1])
    precision = partes[3] if len(partes) > 3 else "?"
    print(f"\nLatitud:   {lat}")
    print(f"Longitud:  {lon}")
    print(f"Precision: {precision} metros")
    print(f"\nGoogle Maps: https://maps.google.com/?q={lat},{lon}")
