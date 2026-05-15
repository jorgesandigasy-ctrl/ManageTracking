<?php
header('Content-Type: application/json');
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(json_encode(['error' => 'Método no permitido'])); }

$datos = json_decode(file_get_contents('php://input'), true);

$nombre            = trim($datos['nombre'] ?? '');
$dispositivo_id    = trim($datos['dispositivo_id'] ?? '');
$tipo              = $datos['tipo'] ?? 'laptop';
$estado            = $datos['estado'] ?? 'activo';
$descripcion       = trim($datos['descripcion'] ?? '');
$sistema_operativo = trim($datos['sistema_operativo'] ?? '');
$ip                = trim($datos['ip'] ?? '');

if (!$nombre || !$dispositivo_id) {
    http_response_code(400);
    die(json_encode(['error' => 'Nombre e ID del equipo son requeridos']));
}

if (!preg_match('/^[a-zA-Z0-9\-_]+$/', $dispositivo_id)) {
    http_response_code(400);
    die(json_encode(['error' => 'El ID solo puede contener letras, números y guiones']));
}

$api_key = bin2hex(random_bytes(32));
$conn = conectarDB();

$stmt = $conn->prepare("INSERT INTO dispositivos (dispositivo_id, nombre, tipo, sistema_operativo, ip, descripcion, api_key, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
$stmt->bind_param('ssssssss', $dispositivo_id, $nombre, $tipo, $sistema_operativo, $ip, $descripcion, $api_key, $estado);

if (!$stmt->execute()) {
    $conn->close();
    http_response_code(409);
    die(json_encode(['error' => 'El ID del equipo ya existe']));
}

$conn->close();
echo json_encode(['ok' => true, 'api_key' => $api_key, 'dispositivo_id' => $dispositivo_id]);
