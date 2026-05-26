<?php
header('Content-Type: application/json');
require_once dirname(__DIR__) . '/app/Models/Alerta.php';

$alertas   = Alerta::getRecientes(30);
$noLeidas  = array_reduce($alertas, fn($c, $a) => $c + ($a['leida'] ? 0 : 1), 0);

echo json_encode(['alertas' => $alertas, 'no_leidas' => $noLeidas]);
