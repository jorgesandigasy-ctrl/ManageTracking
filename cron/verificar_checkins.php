<?php
/**
 * ManageTracking — Verificador de check-ins
 * ==========================================
 * Corre cada hora. Para cada dispositivo no-perdido registrado hace más de 1h,
 * comprueba si envió ubicación en los últimos 70 minutos (60 min de intervalo
 * + 10 min de tolerancia). Si no lo hizo, inserta un registro 'sin_ubicacion'
 * para que el TMC refleje el slot perdido.
 *
 * Configurar en:
 *   Windows Task Scheduler:
 *     Programa : php
 *     Argumentos: C:\laragon\www\managetracking\cron\verificar_checkins.php
 *     Disparador: cada 1 hora
 *
 *   Railway (cron job):
 *     Schedule : 5 * * * *   (a los :05 de cada hora)
 *     Command  : php /app/cron/verificar_checkins.php
 */

require_once dirname(__DIR__) . '/config/Database.php';
require_once dirname(__DIR__) . '/app/Models/RegistroGPS.php';

$conn = Database::get();

// Dispositivos no-perdidos registrados hace más de 1 hora
$result = $conn->query("
    SELECT mac_address
    FROM dispositivos
    WHERE estado != 'perdido'
      AND creado_en <= NOW() - INTERVAL 1 HOUR
");

$insertados = 0;
$revisados  = 0;

while ($row = $result->fetch_assoc()) {
    $mac = $row['mac_address'];
    $revisados++;

    // ¿Tiene algún registro (de cualquier tipo) en los últimos 70 minutos?
    $stmt = $conn->prepare("
        SELECT 1 FROM registros_gps
        WHERE mac_address = ?
          AND registrado_en >= NOW() - INTERVAL 70 MINUTE
        LIMIT 1
    ");
    $stmt->bind_param('s', $mac);
    $stmt->execute();
    $tieneReciente = $stmt->get_result()->num_rows > 0;
    $stmt->close();

    if (!$tieneReciente) {
        RegistroGPS::insertSinUbicacion($mac);
        $insertados++;
    }
}

$log = date('Y-m-d H:i:s') . " — verificar_checkins: revisados=$revisados, sin_ubicacion insertados=$insertados\n";
echo $log;

// Log opcional a archivo
$logPath = __DIR__ . '/verificar_checkins.log';
file_put_contents($logPath, $log, FILE_APPEND | LOCK_EX);
