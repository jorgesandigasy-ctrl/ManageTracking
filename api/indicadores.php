<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/config/Database.php';

$conn = Database::get();

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 1 — Nivel de Trazabilidad (NT)
// Fórmula: NT = (dispositivos con señal en últimas 24h / total) × 100
// ══════════════════════════════════════════════════════════════════════════════
$rowNT = $conn->query("
    SELECT
        COUNT(*)                                     AS total,
        SUM(ultima_vez >= NOW() - INTERVAL 24 HOUR)  AS con_senal_24h
    FROM dispositivos
")->fetch_assoc();

$NT_total      = (int)$rowNT['total'];
$NT_conSenal   = (int)$rowNT['con_senal_24h'];
$NT_porcentaje = $NT_total > 0 ? round($NT_conSenal / $NT_total * 100, 1) : 0;

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 2 — Porcentaje de Incidencias Detectadas (PID)
// Fórmula: PID = (equipos con estado='perdido' O con alerta 'fuera_de_sedes'
//                en los últimos 7 días) / total × 100
// ══════════════════════════════════════════════════════════════════════════════
$rowPID = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(
            d.estado = 'perdido'
            OR EXISTS (
                SELECT 1 FROM alertas a
                WHERE a.mac_address = d.mac_address
                  AND a.tipo = 'fuera_de_sedes'
                  AND a.creada_en >= NOW() - INTERVAL 7 DAY
            )
        ) AS con_incidencia,
        SUM(d.estado = 'perdido') AS perdidos
    FROM dispositivos d
")->fetch_assoc();

$PID_total        = (int)$rowPID['total'];
$PID_detectadas   = (int)$rowPID['con_incidencia'];
$PID_sinIncidencia = $PID_total - $PID_detectadas;
$PID_porcentaje   = $PID_total > 0 ? round($PID_detectadas / $PID_total * 100, 1) : 0;

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 3 — Tasa de Monitoreo Continuo (TMC)
// Fórmula: por equipo → ubicaciones_recibidas / (ubicaciones + sin_ubicacion) × 100
//          global     → promedio de todos los equipos no-perdidos
// El cron/verificar_checkins.php inserta 'sin_ubicacion' cada hora para equipos
// que no reportaron, por lo que el denominador refleja slots reales esperados.
// ══════════════════════════════════════════════════════════════════════════════
$rowTMC = $conn->query("
    SELECT
        ROUND(AVG(tmc_equipo), 1)  AS tmc_global,
        COUNT(*)                   AS total_equipos
    FROM (
        SELECT
            d.mac_address,
            CASE
                WHEN COALESCE(g.total, 0) = 0 THEN 0
                ELSE ROUND(COALESCE(g.ubicaciones, 0) / g.total * 100, 1)
            END AS tmc_equipo
        FROM dispositivos d
        LEFT JOIN (
            SELECT
                mac_address,
                COUNT(*)                       AS total,
                SUM(tipo = 'ubicacion')        AS ubicaciones
            FROM registros_gps
            WHERE registrado_en >= NOW() - INTERVAL 7 DAY
            GROUP BY mac_address
        ) g ON g.mac_address = d.mac_address
        WHERE d.estado != 'perdido'
    ) t
")->fetch_assoc();

$resultTMC_porEquipo = $conn->query("
    SELECT
        COALESCE(d.hostname, d.mac_address)    AS nombre,
        COALESCE(g.ubicaciones, 0)             AS recibidos,
        COALESCE(g.total, 0)                   AS total_slots,
        CASE
            WHEN COALESCE(g.total, 0) = 0 THEN 0
            ELSE ROUND(COALESCE(g.ubicaciones, 0) / g.total * 100, 1)
        END AS tmc_equipo
    FROM dispositivos d
    LEFT JOIN (
        SELECT
            mac_address,
            COUNT(*)                    AS total,
            SUM(tipo = 'ubicacion')     AS ubicaciones
        FROM registros_gps
        WHERE registrado_en >= NOW() - INTERVAL 7 DAY
        GROUP BY mac_address
    ) g ON g.mac_address = d.mac_address
    WHERE d.estado != 'perdido'
    ORDER BY tmc_equipo ASC
    LIMIT 10
");

$TMC_porEquipo = [];
while ($row = $resultTMC_porEquipo->fetch_assoc()) {
    $TMC_porEquipo[] = [
        'nombre'      => $row['nombre'],
        'recibidos'   => (int)$row['recibidos'],
        'total_slots' => (int)$row['total_slots'],
        'tmc'         => (float)$row['tmc_equipo'],
    ];
}

// ══════════════════════════════════════════════════════════════════════════════
// INDICADOR 4 — Tiempo de Registro de Ubicación (TRU)
// Fórmula: promedio de tiempo_respuesta_ms de los últimos 7 días
// Mide: desde que el tracker envía el request hasta que queda registrado en BD
// ══════════════════════════════════════════════════════════════════════════════
$rowTRU = $conn->query("
    SELECT
        ROUND(AVG(tiempo_respuesta_ms), 2)  AS promedio_ms,
        ROUND(MIN(tiempo_respuesta_ms), 2)  AS minimo_ms,
        ROUND(MAX(tiempo_respuesta_ms), 2)  AS maximo_ms,
        COUNT(*)                            AS total_registros
    FROM registros_gps
    WHERE tipo = 'ubicacion'
      AND tiempo_respuesta_ms IS NOT NULL
      AND registrado_en >= NOW() - INTERVAL 7 DAY
")->fetch_assoc();

$TRU_promedio = $rowTRU['promedio_ms'] !== null ? (float)$rowTRU['promedio_ms'] : null;
$TRU_minimo   = $rowTRU['minimo_ms']   !== null ? (float)$rowTRU['minimo_ms']   : null;
$TRU_maximo   = $rowTRU['maximo_ms']   !== null ? (float)$rowTRU['maximo_ms']   : null;
$TRU_total    = (int)$rowTRU['total_registros'];

echo json_encode([
    'trazabilidad' => [
        'porcentaje' => $NT_porcentaje,
        'con_senal'  => $NT_conSenal,
        'total'      => $NT_total,
    ],
    'incidencias' => [
        'porcentaje'     => $PID_porcentaje,
        'detectadas'     => $PID_detectadas,
        'sin_incidencia' => $PID_sinIncidencia,
        'perdidos'       => (int)$rowPID['perdidos'],
        'total'          => $PID_total,
    ],
    'monitoreo_continuo' => [
        'porcentaje' => $rowTMC['tmc_global'] !== null ? (float)$rowTMC['tmc_global'] : 0,
        'por_equipo' => $TMC_porEquipo,
        'total'      => (int)$rowTMC['total_equipos'],
    ],
    'tiempo_registro' => [
        'promedio_ms' => $TRU_promedio,
        'minimo_ms'   => $TRU_minimo,
        'maximo_ms'   => $TRU_maximo,
        'total'       => $TRU_total,
    ],
]);
