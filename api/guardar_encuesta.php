<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/app/Controllers/EncuestaController.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(json_encode(['error' => 'Método no permitido'])); }
EncuestaController::guardar(json_decode(file_get_contents('php://input'), true) ?? []);
