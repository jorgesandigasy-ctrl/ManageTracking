<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class Cliente {
    public static function getAll(): array {
        return Database::get()->query("
            SELECT c.id, c.nombre, c.ruc, c.creado_en, COUNT(s.id) AS total_sedes
            FROM clientes c LEFT JOIN sedes s ON s.cliente_id = c.id
            GROUP BY c.id ORDER BY c.nombre
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public static function create(string $nombre, string $ruc): int {
        $conn = Database::get();
        $stmt = $conn->prepare("INSERT INTO clientes (nombre, ruc) VALUES (?, ?)");
        $stmt->bind_param('ss', $nombre, $ruc);
        $stmt->execute();
        return $conn->insert_id;
    }

    public static function update(int $id, string $nombre, string $ruc): void {
        $stmt = Database::get()->prepare("UPDATE clientes SET nombre=?, ruc=? WHERE id=?");
        $stmt->bind_param('ssi', $nombre, $ruc, $id);
        $stmt->execute();
    }

    public static function delete(int $id): void {
        $stmt = Database::get()->prepare("DELETE FROM clientes WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }
}
