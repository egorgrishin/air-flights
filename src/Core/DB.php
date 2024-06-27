<?php
declare(strict_types=1);

namespace App\Core;

use PDO;

final class DB
{
    private static ?PDO $instance = null;

    /**
     * Возвращает объект PDO
     */
    public static function getPdo(): PDO
    {
        if (self::$instance === null) {
            self::connect();
        }
        return self::$instance;
    }

    /**
     * Подключается к базе данных MySQL
     */
    private static function connect(): void
    {
        $host = Container::env()->get('DB_HOST');
        $database = Container::env()->get('DB_DATABASE');
        $user = Container::env()->get('DB_USERNAME');
        $pass = Container::env()->get('DB_PASSWORD');
        self::$instance = new PDO("mysql:host=$host;dbname=$database", $user, $pass);
    }
}