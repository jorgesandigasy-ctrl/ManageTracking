<?php
define('AUTH_USER', getenv('AUTH_USER') ?: 'jorgeSandiga');
define('AUTH_PASS', getenv('AUTH_PASS') ?: 'jorge123');

define('DB_HOST', getenv('MYSQLHOST')     ?: 'localhost');
define('DB_USER', getenv('MYSQLUSER')     ?: 'root');
define('DB_PASS', getenv('MYSQLPASSWORD') ?: '');
define('DB_NAME', getenv('MYSQLDATABASE') ?: 'managetracking');
define('DB_PORT', (int)(getenv('MYSQLPORT') ?: 3306));

function conectarDB() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
    if ($conn->connect_error) {
        http_response_code(500);
        die(json_encode(['error' => 'Error de conexión a la base de datos']));
    }
    $conn->set_charset('utf8mb4');
    return $conn;
}
