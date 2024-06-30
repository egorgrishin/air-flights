<?php
declare(strict_types=1);

namespace App\Core;

use Redis as RedisClient;
use RedisException;

final class Redis
{
    private static ?RedisClient $redis = null;

    /**
     * Возвращает объект Redis
     * @throws RedisException
     */
    public static function getRedis(): RedisClient
    {
        if (self::$redis === null) {
            self::connect();
        }
        return self::$redis;
    }

    /**
     * Подключается к Redis
     * @throws RedisException
     */
    private static function connect(): void
    {
        $host = Container::env()->get('REDIS_HOST');
        $port = (int) Container::env()->get('REDIS_PORT', 6379);
        self::$redis = new RedisClient();
        self::$redis->connect($host, $port);
    }
}