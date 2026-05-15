<?php
require_once dirname(__DIR__) . '/Models/Encuesta.php';

class EncuestaController {
    public static function guardar(array $d): void {
        for ($i = 1; $i <= 5; $i++) {
            $v = intval($d["p$i"] ?? 0);
            if ($v < 1 || $v > 5) {
                http_response_code(400);
                echo json_encode(['error' => "Respuesta inválida en pregunta $i (debe ser 1-5)"]);
                return;
            }
        }
        $id = Encuesta::guardar(
            intval($d['p1']), intval($d['p2']), intval($d['p3']),
            intval($d['p4']), intval($d['p5'])
        );
        echo json_encode(['ok' => true, 'id' => $id]);
    }

    public static function resumen(): void {
        echo json_encode(Encuesta::resumen());
    }
}
