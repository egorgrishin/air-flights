<?php
declare(strict_types=1);

namespace App\Core;

use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Level;
use Monolog\Logger;
use PDO;

final class Container
{
    private static Env $env;
    private static PDO $pdo;
    private static Logger $logger;
    private static bool $isInitialized = false;

    public static function init(): void
    {
        if (self::$isInitialized) {
            return;
        }
        self::$env = new Env();
        self::$pdo = DB::getPdo();
        self::setLogger();
        self::$isInitialized = true;
    }

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

    public static function pdo(): PDO
    {
        return self::$pdo;
    }

    public static function env(): Env
    {
        return self::$env;
    }

    public static function logger(): Logger
    {
        return self::$logger;
    }
}
