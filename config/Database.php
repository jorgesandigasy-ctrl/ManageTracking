<?php
class Database {
    private static ?mysqli $instance = null;

    public static function get(): mysqli {
        if (self::$instance === null) {
            require_once dirname(__DIR__) . '/config.php';
            self::$instance = conectarDB();
        }
        return self::$instance;
    }
}
