<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/app/Controllers/DispositivoController.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(json_encode(['error' => 'Método no permitido'])); }
$d = json_decode(file_get_contents('php://input'), true);
if (!$d) $d = $_POST;
DispositivoController::registrarUbicacion($d ?? []);
