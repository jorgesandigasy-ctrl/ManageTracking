<?php
require_once dirname(__DIR__) . '/Models/Cliente.php';

class ClienteController {
    public static function getAll(): void {
        echo json_encode(['clientes' => Cliente::getAll()]);
    }

    public static function create(array $d): void {
        $nombre = trim($d['nombre'] ?? '');
        if (!$nombre) { http_response_code(400); echo json_encode(['error' => 'Nombre requerido']); return; }
        $id = Cliente::create($nombre, trim($d['ruc'] ?? ''));
        echo json_encode(['ok' => true, 'id' => $id]);
    }

    public static function update(array $d): void {
        $id     = intval($d['id'] ?? 0);
        $nombre = trim($d['nombre'] ?? '');
        if (!$id || !$nombre) { http_response_code(400); echo json_encode(['error' => 'ID y nombre requeridos']); return; }
        Cliente::update($id, $nombre, trim($d['ruc'] ?? ''));
        echo json_encode(['ok' => true]);
    }

    public static function delete(array $d): void {
        $id = intval($d['id'] ?? 0);
        if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID requerido']); return; }
        Cliente::delete($id);
        echo json_encode(['ok' => true]);
    }
}
