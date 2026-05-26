<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class Dispositivo {
    public static function getAll(): array {
        // El estado se calcula dinámicamente según ultima_vez:
        //   - 'perdido'  → marcado manualmente por el admin (no se sobreescribe)
        //   - 'activo'   → ultima_vez en las últimas 2 horas (tracker corriendo)
        //   - 'inactivo' → sin señal hace más de 2 horas (PC apagada)
        // 2h = intervalo de envío (1h) + margen de tolerancia (1h)
        return Database::get()->query("
            SELECT d.mac_address, d.hostname, d.nombre_usuario, d.apellido_usuario,
                   d.telefono_usuario, d.tipo, d.sede_id,
                   s.nombre  AS sede_nombre,
                   sd.nombre AS sede_detectada_nombre,
                   d.ultima_sede_detectada_id,
                   d.windows_version, d.procesador, d.ram_gb, d.almacenamiento_gb,
                   d.serie_equipo, d.api_key, d.ultima_vez,
                   CASE
                       WHEN d.estado = 'perdido'                              THEN 'perdido'
                       WHEN d.ultima_vez >= NOW() - INTERVAL 2 HOUR          THEN 'activo'
                       ELSE 'inactivo'
                   END AS estado,
                   g.latitud, g.longitud, g.registrado_en AS ultima_ubicacion
            FROM dispositivos d
            LEFT JOIN sedes s  ON s.id  = d.sede_id
            LEFT JOIN sedes sd ON sd.id = d.ultima_sede_detectada_id
            LEFT JOIN registros_gps g ON g.id = (
                SELECT id FROM registros_gps
                WHERE mac_address = d.mac_address
                ORDER BY registrado_en DESC LIMIT 1
            )
            ORDER BY d.ultima_vez DESC
        ")->fetch_all(MYSQLI_ASSOC);
    }

    public static function getByMac(string $mac): ?array {
        $stmt = Database::get()->prepare("
            SELECT d.*, s.nombre AS sede_nombre
            FROM dispositivos d
            LEFT JOIN sedes s ON s.id = d.sede_id
            WHERE d.mac_address = ?
        ");
        $stmt->bind_param('s', $mac);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc() ?: null;
    }

    public static function upsert(array $d): string {
        $conn = Database::get();
        $mac  = strtoupper(trim($d['mac_address']));

        $stmt = $conn->prepare("SELECT api_key FROM dispositivos WHERE mac_address = ?");
        $stmt->bind_param('s', $mac);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $hostname = trim($d['hostname']         ?? '');
        $nomb     = trim($d['nombre_usuario']   ?? '');
        $apel     = trim($d['apellido_usuario'] ?? '');
        $tel      = trim($d['telefono_usuario'] ?? '');
        $sede     = !empty($d['sede_id'])            ? intval($d['sede_id'])           : null;
        $tipo     = $d['tipo']                       ?? 'laptop';
        $winver   = trim($d['windows_version']  ?? '');
        $serie    = trim($d['serie_equipo']     ?? '');
        $ram      = !empty($d['ram_gb'])             ? intval($d['ram_gb'])            : null;
        $disco    = !empty($d['almacenamiento_gb'])  ? intval($d['almacenamiento_gb']) : null;
        $proc     = trim($d['procesador']       ?? '');

        if ($row) {
            // UPDATE: solo toca datos de hardware. Nombre, teléfono y sede
            // los gestiona el admin desde el panel y no deben ser sobreescritos.
            $api_key = $row['api_key'];
            $stmt = $conn->prepare("
                UPDATE dispositivos SET
                    hostname=?, tipo=?, windows_version=?, serie_equipo=?,
                    ram_gb=?, almacenamiento_gb=?, procesador=?, ultima_vez=NOW()
                WHERE mac_address=?
            ");
            $stmt->bind_param('ssssiiss', $hostname, $tipo, $winver, $serie, $ram, $disco, $proc, $mac);
        } else {
            $api_key = bin2hex(random_bytes(32));
            $stmt = $conn->prepare("
                INSERT INTO dispositivos
                    (mac_address, hostname, nombre_usuario, apellido_usuario, telefono_usuario,
                     sede_id, tipo, windows_version, serie_equipo, ram_gb, almacenamiento_gb, procesador, api_key)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->bind_param('sssssisssiiss', $mac, $hostname, $nomb, $apel, $tel, $sede, $tipo, $winver, $serie, $ram, $disco, $proc, $api_key);
        }
        $stmt->execute();
        $stmt->close();
        return $api_key;
    }

    public static function update(string $mac, array $d): void {
        $nomb  = trim($d['nombre_usuario']   ?? '');
        $apel  = trim($d['apellido_usuario'] ?? '');
        $tel   = trim($d['telefono_usuario'] ?? '');
        $sede  = !empty($d['sede_id']) ? intval($d['sede_id']) : null;
        $tipo  = $d['tipo']   ?? 'laptop';
        $est   = $d['estado'] ?? 'activo';
        $stmt  = Database::get()->prepare("
            UPDATE dispositivos
            SET nombre_usuario=?, apellido_usuario=?, telefono_usuario=?, sede_id=?, tipo=?, estado=?
            WHERE mac_address=?
        ");
        $stmt->bind_param('sssisss', $nomb, $apel, $tel, $sede, $tipo, $est, $mac);
        $stmt->execute();
    }

    public static function delete(string $mac): void {
        $stmt = Database::get()->prepare("DELETE FROM dispositivos WHERE mac_address=?");
        $stmt->bind_param('s', $mac);
        $stmt->execute();
    }

    public static function validateApiKey(string $mac, string $key): bool {
        // Solo bloquea dispositivos marcados manualmente como 'perdido'
        $stmt = Database::get()->prepare("SELECT 1 FROM dispositivos WHERE mac_address=? AND api_key=? AND estado!='perdido'");
        $stmt->bind_param('ss', $mac, $key);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public static function updateLastSeen(string $mac): void {
        $stmt = Database::get()->prepare("UPDATE dispositivos SET ultima_vez=NOW() WHERE mac_address=?");
        $stmt->bind_param('s', $mac);
        $stmt->execute();
    }

    public static function updateUltimaSede(string $mac, ?int $sedeId): void {
        $stmt = Database::get()->prepare("UPDATE dispositivos SET ultima_sede_detectada_id=? WHERE mac_address=?");
        $stmt->bind_param('is', $sedeId, $mac);
        $stmt->execute();
    }
}
