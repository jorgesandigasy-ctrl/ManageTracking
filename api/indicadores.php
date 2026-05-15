<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/app/Models/RegistroGPS.php';
require_once dirname(__DIR__) . '/app/Models/Encuesta.php';

$conn = Database::get();

// Indicador 1: Tiempo promedio de respuesta de la API (ms)
$tiempoPromedio = RegistroGPS::promedioTiempoRespuesta();

// Indicador 2: Nivel de trazabilidad — % de dispositivos con al menos 1 registro GPS
$row = $conn->query("
    SELECT
        (SELECT COUNT(*) FROM dispositivos) AS total_dispositivos,
        (SELECT COUNT(DISTINCT mac_address) FROM registros_gps) AS con_registros,
        (SELECT COUNT(*) FROM registros_gps) AS total_registros
")->fetch_assoc();

$totalDisp      = (int)$row['total_dispositivos'];
$conRegistros   = (int)$row['con_registros'];
$totalRegistros = (int)$row['total_registros'];
$trazabilidad   = $totalDisp > 0 ? round($conRegistros / $totalDisp * 100, 1) : 0;

// Indicador 3: Porcentaje de incidencias detectadas
// Incidencia = dispositivo con estado 'perdido' O sin señal en >24h (siendo activo antes)
$row2 = $conn->query("
    SELECT
        COUNT(*) AS total,
        SUM(estado = 'perdido') AS perdidos,
        SUM(estado = 'activo' AND ultima_vez < NOW() - INTERVAL 24 HOUR) AS sin_senal
    FROM dispositivos
")->fetch_assoc();

$incidencias        = (int)$row2['perdidos'] + (int)$row2['sin_senal'];
$pctIncidencias     = (int)$row2['total'] > 0 ? round($incidencias / (int)$row2['total'] * 100, 1) : 0;

// Indicador 4: Satisfacción del personal
$satisfaccion = Encuesta::resumen();

echo json_encode([
    'tiempo_promedio_ms'    => $tiempoPromedio,
    'trazabilidad'          => ['porcentaje' => $trazabilidad, 'con_registros' => $conRegistros, 'total' => $totalDisp, 'total_registros' => $totalRegistros],
    'incidencias'           => ['porcentaje' => $pctIncidencias, 'total' => $incidencias, 'perdidos' => (int)$row2['perdidos'], 'sin_senal' => (int)$row2['sin_senal']],
    'satisfaccion'          => $satisfaccion,
]);
