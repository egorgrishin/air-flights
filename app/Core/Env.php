<?php
declare(strict_types=1);

namespace App\Core;

class Env
{
    private static array $params;

    public static function get(string $key, mixed $default = null): mixed
    {
        return self::$params[$key] ?? $default;
    }

    public static function load(): void
    {
        $path = __DIR__ . '/../../.env';
        $file = fopen($path, 'r');
        while (($string = fgets($file)) !== false) {
            $string = trim($string);
            [$key, $value] = explode('=', $string, 2);
            self::$params[$key] = $value;
        }
    }
}