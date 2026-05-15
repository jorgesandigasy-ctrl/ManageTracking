<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class RegistroGPS {
    public static function insert(string $mac, float $lat, float $lng, ?float $alt, ?float $vel, ?int $sat, ?float $tiempoMs = null): void {
        $stmt = Database::get()->prepare("
            INSERT INTO registros_gps (mac_address, latitud, longitud, altitud, velocidad, satelites, tiempo_respuesta_ms)
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        $stmt->bind_param('sddddid', $mac, $lat, $lng, $alt, $vel, $sat, $tiempoMs);
        $stmt->execute();
    }

    public static function promedioTiempoRespuesta(): ?float {
        $row = Database::get()->query("SELECT AVG(tiempo_respuesta_ms) AS promedio FROM registros_gps WHERE tiempo_respuesta_ms IS NOT NULL")->fetch_assoc();
        return $row['promedio'] !== null ? round((float)$row['promedio'], 2) : null;
    }

    public static function getByMac(string $mac, int $limite = 100): array {
        $stmt = Database::get()->prepare("
            SELECT latitud, longitud, altitud, velocidad, satelites, registrado_en
            FROM registros_gps WHERE mac_address=?
            ORDER BY registrado_en DESC LIMIT ?
        ");
        $stmt->bind_param('si', $mac, $limite);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public static function countByMac(string $mac): int {
        $stmt = Database::get()->prepare("SELECT COUNT(*) FROM registros_gps WHERE mac_address=?");
        $stmt->bind_param('s', $mac);
        $stmt->execute();
        return (int)$stmt->get_result()->fetch_row()[0];
    }
}
