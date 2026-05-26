<?php
require_once dirname(__DIR__) . '/Models/Sede.php';

class SedeController {
    public static function getAll(?int $clienteId = null): void {
        echo json_encode(['sedes' => Sede::getAll($clienteId)]);
    }

    public static function create(array $d): void {
        $clienteId = intval($d['cliente_id'] ?? 0);
        $nombre    = trim($d['nombre'] ?? '');
        if (!$clienteId || !$nombre) { http_response_code(400); echo json_encode(['error' => 'cliente_id y nombre requeridos']); return; }
        $lat   = isset($d['latitud'])  && $d['latitud']  !== '' ? floatval($d['latitud'])  : null;
        $lng   = isset($d['longitud']) && $d['longitud'] !== '' ? floatval($d['longitud']) : null;
        $radio = intval($d['radio_metros'] ?? 120) ?: 120;
        $id = Sede::create($clienteId, $nombre, trim($d['ciudad'] ?? ''), trim($d['direccion'] ?? ''), $lat, $lng, $radio);
        echo json_encode(['ok' => true, 'id' => $id]);
    }

    public static function update(array $d): void {
        $id     = intval($d['id'] ?? 0);
        $nombre = trim($d['nombre'] ?? '');
        if (!$id || !$nombre) { http_response_code(400); echo json_encode(['error' => 'ID y nombre requeridos']); return; }
        $lat   = isset($d['latitud'])  && $d['latitud']  !== '' ? floatval($d['latitud'])  : null;
        $lng   = isset($d['longitud']) && $d['longitud'] !== '' ? floatval($d['longitud']) : null;
        $radio = intval($d['radio_metros'] ?? 120) ?: 120;
        Sede::update($id, $nombre, trim($d['ciudad'] ?? ''), trim($d['direccion'] ?? ''), $lat, $lng, $radio);
        echo json_encode(['ok' => true]);
    }

    public static function delete(array $d): void {
        $id = intval($d['id'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID requerido']); return; }
        Sede::delete($id);
        echo json_encode(['ok' => true]);
    }
}
