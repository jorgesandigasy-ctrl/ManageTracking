<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/config/Database.php';

$conn = Database::get();

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 1 — Nivel de Trazabilidad (NT)
// Fórmula: NT = (dispositivos con señal en últimas 24h / total dispositivos) × 100
// Fuente: campo `ultima_vez` de la tabla `dispositivos`
// ══════════════════════════════════════════════════════════════════════════════
$rowNT = $conn->query("
    SELECT
        COUNT(*)                                              AS total,
        SUM(ultima_vez >= NOW() - INTERVAL 24 HOUR)          AS con_senal_24h
    FROM dispositivos
")->fetch_assoc();

$NT_total      = (int)$rowNT['total'];
$NT_conSenal   = (int)$rowNT['con_senal_24h'];
$NT_porcentaje = $NT_total > 0 ? round($NT_conSenal / $NT_total * 100, 1) : 0;

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 2 — Porcentaje de Incidencias Detectadas (PID)
// Fórmula: PID = (incidencias detectadas automáticamente / total dispositivos) × 100
// Incidencia automática: estado = 'perdido'  O  sin señal en las últimas 24h
// ══════════════════════════════════════════════════════════════════════════════
$rowPID = $conn->query("
    SELECT
        COUNT(*)                                                              AS total,
        SUM(estado = 'perdido')                                               AS perdidos,
        SUM(estado != 'perdido' AND ultima_vez < NOW() - INTERVAL 24 HOUR)   AS sin_senal,
        SUM(estado = 'activo'   AND ultima_vez >= NOW() - INTERVAL 24 HOUR)  AS sin_incidencia
    FROM dispositivos
")->fetch_assoc();

$PID_total         = (int)$rowPID['total'];
$PID_detectadas    = (int)$rowPID['perdidos'] + (int)$rowPID['sin_senal'];
$PID_sinIncidencia = (int)$rowPID['sin_incidencia'];
$PID_porcentaje    = $PID_total > 0 ? round($PID_detectadas / $PID_total * 100, 1) : 0;

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 3 — Tiempo Promedio de Ubicación (TPU)
// Fórmula: TPU = Σ(tiempo_respuesta_ms) / N peticiones
// Fuente: campo `tiempo_respuesta_ms` de la tabla `registros_gps`
// Se devuelve también el desglose por equipo para el gráfico de barras
// ══════════════════════════════════════════════════════════════════════════════
$rowTPU = $conn->query("
    SELECT ROUND(AVG(tiempo_respuesta_ms), 2) AS promedio_global
    FROM registros_gps
    WHERE tiempo_respuesta_ms IS NOT NULL
")->fetch_assoc();

$resultTPU_porEquipo = $conn->query("
    SELECT
        COALESCE(d.hostname, d.mac_address) AS nombre,
        ROUND(AVG(r.tiempo_respuesta_ms), 2) AS promedio_ms,
        COUNT(r.id)                          AS total_peticiones
    FROM dispositivos d
    JOIN registros_gps r ON d.mac_address = r.mac_address
    WHERE r.tiempo_respuesta_ms IS NOT NULL
    GROUP BY d.mac_address, d.hostname
    ORDER BY promedio_ms ASC
    LIMIT 10
");

$TPU_porEquipo = [];
while ($row = $resultTPU_porEquipo->fetch_assoc()) {
    $TPU_porEquipo[] = [
        'nombre'           => $row['nombre'],
        'promedio_ms'      => (float)$row['promedio_ms'],
        'total_peticiones' => (int)$row['total_peticiones'],
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 4 — Nivel de Satisfacción del Personal (NSP)
// Fórmula: NSP = SUM(p1+p2+p3+p4+p5) / (25 × total_encuestas) × 100
// 25 = puntaje máximo por encuesta (5 preguntas × 5 puntos máximo cada una)
// Fuente: tabla `encuestas`
// ══════════════════════════════════════════════════════════════════════════════
$rowNSP = $conn->query("
    SELECT
        COUNT(*)                        AS total,
        SUM(p1 + p2 + p3 + p4 + p5)    AS suma_total,
        AVG(p1) AS avg_p1,
        AVG(p2) AS avg_p2,
        AVG(p3) AS avg_p3,
        AVG(p4) AS avg_p4,
        AVG(p5) AS avg_p5
    FROM encuestas
")->fetch_assoc();

$NSP_total      = (int)$rowNSP['total'];
$NSP_porcentaje = $NSP_total > 0
    ? round((float)$rowNSP['suma_total'] / (25 * $NSP_total) * 100, 1)
    : null;

echo json_encode([
    'trazabilidad' => [
        'porcentaje'  => $NT_porcentaje,
        'con_senal'   => $NT_conSenal,
        'total'       => $NT_total,
    ],
    'incidencias' => [
        'porcentaje'     => $PID_porcentaje,
        'detectadas'     => $PID_detectadas,
        'sin_incidencia' => $PID_sinIncidencia,
        'perdidos'       => (int)$rowPID['perdidos'],
        'sin_senal'      => (int)$rowPID['sin_senal'],
        'total'          => $PID_total,
    ],
    'tiempo_ubicacion' => [
        'promedio_ms' => $rowTPU['promedio_global'] !== null ? (float)$rowTPU['promedio_global'] : null,
        'por_equipo'  => $TPU_porEquipo,
    ],
    'satisfaccion' => [
        'porcentaje'   => $NSP_porcentaje,
        'total'        => $NSP_total,
        'por_pregunta' => [
            round((float)($rowNSP['avg_p1'] ?? 0), 2),
            round((float)($rowNSP['avg_p2'] ?? 0), 2),
            round((float)($rowNSP['avg_p3'] ?? 0), 2),
            round((float)($rowNSP['avg_p4'] ?? 0), 2),
            round((float)($rowNSP['avg_p5'] ?? 0), 2),
        ],
    ],
]);
