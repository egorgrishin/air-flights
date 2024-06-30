<?php
declare(strict_types=1);

namespace App\Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use Redis as RedisClient;

final class Container
{
    private static ?Env                  $env    = null;
    private static ?PDO                  $pdo    = null;
    private static ?Logger               $logger = null;
    private static ?AMQPStreamConnection $rabbit = null;
    private static ?RedisClient          $redis  = null;

    /**
     * Возвращает объект PDO
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = DB::getPdo();
        }
        return self::$pdo;
    }

    /**
     * Возвращает объект Env
     */
    public static function env(): Env
    {
        if (self::$env === null) {
            self::$env = new Env();
        }
        return self::$env;
    }

    /**
     * Возвращает объект Logger
     */
    public static function logger(): Logger
    {
        if (self::$logger === null) {
            self::setLogger();
        }
        return self::$logger;
    }

    /**
     * Возвращает объект AMQPStreamConnection
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function rabbit(): AMQPStreamConnection
    {
        if (self::$rabbit === null) {
            self::$rabbit = Rabbit::getConnection();
        }
        return self::$rabbit;
    }

    /**
     * Возвращает объект Redis
     * @noinspection PhpUnhandledExceptionInspection
     */
    public static function redis(): RedisClient
    {
        if (self::$redis === null) {
            self::$redis = Redis::getRedis();
        }
        return self::$redis;
    }

    /**
     * Создает объект Logger
     */
    private static function setLogger(): void
    {
        $logger = new Logger('app');
        $handler = new StreamHandler(
            Helper::basePath('/var/app.log'),
            Level::Debug
        );
        $handler->setFormatter(new LineFormatter(
            allowInlineLineBreaks: true,
            ignoreEmptyContextAndExtra: true,
        ));
        $logger->pushHandler($handler);
        self::$logger = $logger;
    }

    public function __destruct()
    {
        echo('des');
    }
}
