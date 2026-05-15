<?php
require_once dirname(__DIR__, 2) . '/config/Database.php';

class Encuesta {
    public static function guardar(int $p1, int $p2, int $p3, int $p4, int $p5): int {
        $conn = Database::get();
        $stmt = $conn->prepare("INSERT INTO encuestas (p1, p2, p3, p4, p5) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param('iiiii', $p1, $p2, $p3, $p4, $p5);
        $stmt->execute();
        return $conn->insert_id;
    }

    public static function resumen(): array {
        $row = Database::get()->query("
            SELECT
                COUNT(*) AS total,
                AVG((p1 + p2 + p3 + p4 + p5) / 5.0) AS promedio,
                AVG(p1) AS avg_p1,
                AVG(p2) AS avg_p2,
                AVG(p3) AS avg_p3,
                AVG(p4) AS avg_p4,
                AVG(p5) AS avg_p5
            FROM encuestas
        ")->fetch_assoc();
        return [
            'total'      => (int)$row['total'],
            'promedio'   => $row['promedio'] !== null ? round((float)$row['promedio'], 2) : null,
            'porcentaje' => $row['promedio'] !== null ? round((float)$row['promedio'] / 5 * 100, 1) : null,
            'por_pregunta' => [
                round((float)($row['avg_p1'] ?? 0), 2),
                round((float)($row['avg_p2'] ?? 0), 2),
                round((float)($row['avg_p3'] ?? 0), 2),
                round((float)($row['avg_p4'] ?? 0), 2),
                round((float)($row['avg_p5'] ?? 0), 2),
            ],
        ];
    }
}
