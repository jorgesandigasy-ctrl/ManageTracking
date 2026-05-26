<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class Alerta {
    public static function insert(string $mac, float $lat, float $lng, string $tipo, ?int $sedeDetectadaId): void {
        $stmt = Database::get()->prepare("
            INSERT INTO alertas (mac_address, latitud, longitud, tipo, sede_detectada_id)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sddsi', $mac, $lat, $lng, $tipo, $sedeDetectadaId);
        $stmt->execute();
    }

    public static function getRecientes(int $limite = 30): array {
        $stmt = Database::get()->prepare("
            SELECT a.id, a.mac_address, a.latitud, a.longitud, a.tipo, a.leida, a.creada_en,
                   COALESCE(d.hostname, a.mac_address) AS equipo,
                   TRIM(CONCAT(COALESCE(d.nombre_usuario,''), ' ', COALESCE(d.apellido_usuario,''))) AS usuario,
                   s.nombre AS sede_detectada
            FROM alertas a
            LEFT JOIN dispositivos d ON d.mac_address = a.mac_address
            LEFT JOIN sedes s ON s.id = a.sede_detectada_id
            ORDER BY a.creada_en DESC
            LIMIT ?
        ");
        $stmt->bind_param('i', $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function marcarLeida(int $id): void {
        $stmt = Database::get()->prepare("UPDATE alertas SET leida=1 WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
    }

    public static function marcarTodasLeidas(): void {
        Database::get()->query("UPDATE alertas SET leida=1 WHERE leida=0");
    }

    public static function countNoLeidas(): int {
        $row = Database::get()->query("SELECT COUNT(*) FROM alertas WHERE leida=0")->fetch_row();
        return (int)$row[0];
    }

    // Evita spam: devuelve true si ya existe una alerta no leída del mismo tipo
    // para este equipo en las últimas $horas horas.
    public static function tieneAlertaReciente(string $mac, string $tipo, int $horas = 24): bool {
        $stmt = Database::get()->prepare("
            SELECT 1 FROM alertas
            WHERE mac_address=? AND tipo=? AND leida=0
              AND creada_en >= NOW() - INTERVAL ? HOUR
            LIMIT 1
        ");
        $stmt->bind_param('ssi', $mac, $tipo, $horas);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }
}
