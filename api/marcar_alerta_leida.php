<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/app/Models/Alerta.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(); }
$d = json_decode(file_get_contents('php://input'), true);

if (!empty($d['all'])) {
    Alerta::marcarTodasLeidas();
    echo json_encode(['ok' => true]);
    exit;
}

$id = intval($d['id'] ?? 0);
if (!$id) { http_response_code(400); echo json_encode(['error' => 'ID requerido']); exit; }
Alerta::marcarLeida($id);
echo json_encode(['ok' => true]);
