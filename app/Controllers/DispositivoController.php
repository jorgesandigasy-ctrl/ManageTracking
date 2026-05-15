<?php
require_once dirname(__DIR__) . '/Models/Dispositivo.php';
require_once dirname(__DIR__) . '/Models/RegistroGPS.php';

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
        RegistroGPS::insert(
            $mac,
            floatval($d['latitud']),
            floatval($d['longitud']),
            isset($d['altitud'])   ? floatval($d['altitud'])  : null,
            isset($d['velocidad']) ? floatval($d['velocidad']): null,
            isset($d['satelites']) ? intval($d['satelites'])  : null,
            round((microtime(true) - $inicio) * 1000, 3)
        );
        Dispositivo::updateLastSeen($mac);
        echo json_encode(['ok' => true, 'mensaje' => 'Ubicación registrada']);
    }

    public static function historial(string $mac, int $limite): void {
        $mac = strtoupper($mac);
        $dispositivo = Dispositivo::getByMac($mac);
        if (!$dispositivo) { http_response_code(404); echo json_encode(['error' => 'Equipo no encontrado']); return; }
        $registros = RegistroGPS::getByMac($mac, $limite);
        echo json_encode(['dispositivo' => $dispositivo, 'total' => count($registros), 'registros' => $registros]);
    }
}
