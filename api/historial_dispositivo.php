<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/app/Controllers/DispositivoController.php';
$mac    = strtoupper(trim($_GET['mac']    ?? ''));
$limite = min(intval($_GET['limite']      ?? 50), 500);
if (!$mac) { http_response_code(400); die(json_encode(['error' => 'Se requiere el parámetro mac'])); }
DispositivoController::historial($mac, $limite);
