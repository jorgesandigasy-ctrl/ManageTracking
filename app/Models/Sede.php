<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class Sede {
    public static function getAll(?int $clienteId = null): array {
        $conn = Database::get();
        if ($clienteId) {
            $stmt = $conn->prepare("
                SELECT s.id, s.cliente_id, s.nombre, s.ciudad, s.direccion, c.nombre AS cliente
                FROM sedes s JOIN clientes c ON c.id = s.cliente_id
                WHERE s.cliente_id = ? ORDER BY s.nombre
            ");
            $stmt->bind_param('i', $clienteId);
            $stmt->execute();
            return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        }
        return $conn->query("
            SELECT s.id, s.cliente_id, s.nombre, s.ciudad, s.direccion, c.nombre AS cliente
            FROM sedes s JOIN clientes c ON c.id = s.cliente_id
            ORDER BY c.nombre, s.nombre
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public static function create(int $clienteId, string $nombre, string $ciudad, string $direccion): int {
        $conn = Database::get();
        $stmt = $conn->prepare("INSERT INTO sedes (cliente_id, nombre, ciudad, direccion) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $clienteId, $nombre, $ciudad, $direccion);
        $stmt->execute();
        return $conn->insert_id;
    }

    public static function update(int $id, string $nombre, string $ciudad, string $direccion): void {
        $stmt = Database::get()->prepare("UPDATE sedes SET nombre=?, ciudad=?, direccion=? WHERE id=?");
        $stmt->bind_param('sssi', $nombre, $ciudad, $direccion, $id);
        $stmt->execute();
    }

    public static function delete(int $id): void {
        $stmt = Database::get()->prepare("DELETE FROM sedes WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
