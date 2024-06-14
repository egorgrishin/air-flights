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
    private static ?Env $env = null;
    private static ?PDO $pdo = null;
    private static ?Logger $logger = null;

    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = DB::getPdo();
        }
        return self::$pdo;
    }

    public static function env(): Env
    {
        if (self::$env === null) {
            self::$env = new Env();
        }
        return self::$env;
    }

    public static function logger(): Logger
    {
        if (self::$logger === null) {
            self::setLogger();
        }
        return self::$logger;
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
}
