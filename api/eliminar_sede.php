<?php
header('Content-Type: application/json');
require_once '../config.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(json_encode(['error' => 'Método no permitido'])); }

$d  = json_decode(file_get_contents('php://input'), true);
$id = intval($d['id'] ?? 0);
if (!$id) { http_response_code(400); die(json_encode(['error' => 'ID requerido'])); }

$conn = conectarDB();
$stmt = $conn->prepare("DELETE FROM sedes WHERE id=?");
$stmt->bind_param('i', $id);
$stmt->execute();
$conn->close();
echo json_encode(['ok' => true]);
