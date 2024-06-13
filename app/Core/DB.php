<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

class DB
{
    private static ?PDO $instance = null;

    public static function getInstance(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    private static function connect(): void
    {
        $host = Env::get('DB_HOST');
        $database = Env::get('DB_DATABASE');
        $user = Env::get('DB_USERNAME');
        $pass = Env::get('DB_PASSWORD');
        self::$instance = new PDO("mysql:host=$host;dbname=$database", $user, $pass);
    }
}