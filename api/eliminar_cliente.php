<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/app/Controllers/ClienteController.php';
if ($_SERVER['REQUEST_METHOD'] !== 'POST') { http_response_code(405); die(json_encode(['error' => 'Método no permitido'])); }
ClienteController::delete(json_decode(file_get_contents('php://input'), true) ?? []);
