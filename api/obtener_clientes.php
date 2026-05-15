<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
require_once dirname(__DIR__) . '/app/Controllers/ClienteController.php';
ClienteController::getAll();
