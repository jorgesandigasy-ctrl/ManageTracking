<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/config/Database.php';

$conn = Database::get();

$estadoSQL = "CASE
    WHEN d.estado = 'perdido'                     THEN 'perdido'
    WHEN d.ultima_vez >= NOW() - INTERVAL 2 HOUR  THEN 'activo'
    ELSE 'inactivo'
END";

// Clientes con sus sedes y dispositivos
$clientes = $conn->query("SELECT id, nombre, ruc FROM clientes ORDER BY nombre")->fetch_all(MYSQLI_ASSOC);

foreach ($clientes as &$c) {
    $stmtS = $conn->prepare("SELECT id, nombre, ciudad FROM sedes WHERE cliente_id=? ORDER BY nombre");
    $stmtS->bind_param('i', $c['id']);
    $stmtS->execute();
    $sedes = $stmtS->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($sedes as &$s) {
        $stmtD = $conn->prepare("
            SELECT d.mac_address, d.hostname, d.nombre_usuario, d.apellido_usuario,
                   d.tipo, d.ultima_vez, d.ultima_sede_detectada_id,
                   sd.nombre AS sede_detectada_nombre,
                   $estadoSQL AS estado
            FROM dispositivos d
            LEFT JOIN sedes sd ON sd.id = d.ultima_sede_detectada_id
            WHERE d.ultima_sede_detectada_id = ?
            ORDER BY d.hostname
        ");
        $stmtD->bind_param('i', $s['id']);
        $stmtD->execute();
        $devs = $stmtD->get_result()->fetch_all(MYSQLI_ASSOC);

        $s['dispositivos'] = $devs;
        $s['total']        = count($devs);
        $s['activos']      = count(array_filter($devs, fn($d) => $d['estado'] === 'activo'));
    }
    unset($s);
    $c['sedes'] = $sedes;
}
unset($c);

// Dispositivos fuera del radio de todas las sedes (sin ubicación detectada)
$sinSede = $conn->query("
    SELECT d.mac_address, d.hostname, d.nombre_usuario, d.apellido_usuario,
           d.tipo, d.ultima_vez, d.ultima_sede_detectada_id,
           sd.nombre AS sede_detectada_nombre,
           $estadoSQL AS estado
    FROM dispositivos d
    LEFT JOIN sedes sd ON sd.id = d.ultima_sede_detectada_id
    WHERE d.ultima_sede_detectada_id IS NULL
    ORDER BY d.hostname
")->fetch_all(MYSQLI_ASSOC);

echo json_encode(['clientes' => $clientes, 'sin_sede' => $sinSede]);
