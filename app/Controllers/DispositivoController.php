<?php
require_once dirname(__DIR__) . '/Models/Dispositivo.php';
require_once dirname(__DIR__) . '/Models/RegistroGPS.php';
require_once dirname(__DIR__) . '/Models/Sede.php';
require_once dirname(__DIR__) . '/Models/Alerta.php';

class DispositivoController {
    public static function getAll(): void {
        echo json_encode(['dispositivos' => Dispositivo::getAll()]);
    }

    public static function upsert(array $d): void {
        if (empty($d['mac_address'])) { http_response_code(400); echo json_encode(['error' => 'mac_address requerido']); return; }
        $api_key = Dispositivo::upsert($d);
        echo json_encode(['ok' => true, 'api_key' => $api_key, 'mac_address' => strtoupper(trim($d['mac_address']))]);
    }

    public static function update(array $d): void {
        $mac = strtoupper(trim($d['mac_address'] ?? ''));
        if (!$mac) { http_response_code(400); echo json_encode(['error' => 'mac_address requerido']); return; }
        Dispositivo::update($mac, $d);
        echo json_encode(['ok' => true]);
    }

    public static function delete(array $d): void {
        $mac = strtoupper(trim($d['mac_address'] ?? ''));
        if (!$mac) { http_response_code(400); echo json_encode(['error' => 'mac_address requerido']); return; }
        Dispositivo::delete($mac);
        echo json_encode(['ok' => true]);
    }

    public static function registrarUbicacion(array $d): void {
        $inicio = microtime(true);

        foreach (['mac_address', 'api_key', 'latitud', 'longitud'] as $campo) {
            if (empty($d[$campo])) { http_response_code(400); echo json_encode(['error' => "Campo requerido: $campo"]); return; }
        }
        $mac = strtoupper(trim($d['mac_address']));
        $key = trim($d['api_key']);
        if (!Dispositivo::validateApiKey($mac, $key)) {
            http_response_code(401);
            echo json_encode(['error' => 'Dispositivo no autorizado']);
            return;
        }

        $lat = floatval($d['latitud']);
        $lng = floatval($d['longitud']);

        RegistroGPS::insert(
            $mac, $lat, $lng,
            isset($d['altitud'])   ? floatval($d['altitud'])   : null,
            isset($d['velocidad']) ? floatval($d['velocidad'])  : null,
            isset($d['satelites']) ? intval($d['satelites'])    : null,
            round((microtime(true) - $inicio) * 1000, 3)
        );
        Dispositivo::updateLastSeen($mac);
        self::procesarGeofencing($mac, $lat, $lng);

        echo json_encode(['ok' => true, 'mensaje' => 'Ubicación registrada']);
    }

    public static function historial(string $mac, int $limite): void {
        $mac = strtoupper($mac);
        $dispositivo = Dispositivo::getByMac($mac);
        if (!$dispositivo) { http_response_code(404); echo json_encode(['error' => 'Equipo no encontrado']); return; }
        $registros = RegistroGPS::getByMac($mac, $limite);
        echo json_encode(['dispositivo' => $dispositivo, 'total' => count($registros), 'registros' => $registros]);
    }

    // ─── Geofencing ──────────────────────────────────────────────────────────
    // Compara la nueva posición contra todas las sedes con coordenadas.
    //
    // Reglas:
    //   - Fuera de todas las sedes → alerta 'fuera_de_sedes'
    //     (anti-spam: máximo 1 alerta no leída cada 24h por equipo)
    //   - Dentro de una sede diferente a la última detectada → alerta 'cambio_de_sede'
    //     (anti-spam: máximo 1 alerta no leída cada 24h por equipo)
    //
    // El primer check-in ya puede generar alerta aunque no haya historial previo,
    // porque lo que importa es la posición actual, no la transición.
    private static function procesarGeofencing(string $mac, float $lat, float $lng): void {
        $sedes   = Sede::getWithCoords();
        $cercana = null;
        $distMin = PHP_FLOAT_MAX;

        foreach ($sedes as $sede) {
            $dist = self::haversine($lat, $lng, (float)$sede['latitud'], (float)$sede['longitud']);
            if ($dist <= (int)$sede['radio_metros'] && $dist < $distMin) {
                $cercana = $sede;
                $distMin = $dist;
            }
        }

        $disp         = Dispositivo::getByMac($mac);
        $ultimaSedeId = $disp && $disp['ultima_sede_detectada_id'] ? (int)$disp['ultima_sede_detectada_id'] : null;

        if ($cercana === null) {
            // Fuera de todas las sedes — alerta si no hay una reciente sin leer
            if (!Alerta::tieneAlertaReciente($mac, 'fuera_de_sedes', 24)) {
                Alerta::insert($mac, $lat, $lng, 'fuera_de_sedes', null);
            }
        } else {
            $cercanaId = (int)$cercana['id'];
            // Cambio de sede — solo si cambió respecto a la última detectada
            if ($ultimaSedeId !== $cercanaId && !Alerta::tieneAlertaReciente($mac, 'cambio_de_sede', 24)) {
                Alerta::insert($mac, $lat, $lng, 'cambio_de_sede', $cercanaId);
            }
        }

        Dispositivo::updateUltimaSede($mac, $cercana ? (int)$cercana['id'] : null);
    }

    private static function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float {
        $R    = 6371000;
        $phi1 = deg2rad($lat1); $phi2 = deg2rad($lat2);
        $dphi = deg2rad($lat2 - $lat1);
        $dlam = deg2rad($lng2 - $lng1);
        $a    = sin($dphi / 2) ** 2 + cos($phi1) * cos($phi2) * sin($dlam / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
