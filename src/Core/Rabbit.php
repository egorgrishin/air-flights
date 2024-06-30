<?php
declare(strict_types=1);

namespace App\Core;

use Exception;
use PhpAmqpLib\Connection\AMQPStreamConnection;

final class Rabbit
{
    private static ?AMQPStreamConnection $connection = null;

    /**
     * Возвращает объект AMQPStreamConnection
     * @throws Exception
     */
    public static function getConnection(): AMQPStreamConnection
    {
        if (self::$connection === null) {
            self::connect();
        }
        return self::$connection;
    }

    /**
     * Подключается к RabbitMQ
     * @throws Exception
     */
    private static function connect(): void
    {
        $host = Container::env()->get('RABBIT_HOST');
        $port = Container::env()->get('RABBIT_PORT');
        $user = Container::env()->get('RABBIT_USER');
        $pass = Container::env()->get('RABBIT_PASS');
        self::$connection = new AMQPStreamConnection($host, $port, $user, $pass);
    }
}