<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/app/Controllers/SedeController.php';
$clienteId = !empty($_GET['cliente_id']) ? intval($_GET['cliente_id']) : null;
SedeController::getAll($clienteId);
