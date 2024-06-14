<?php
declare(strict_types=1);

namespace App\Core;

final class Helper
{
    public static function basePath(string $path = null): string
    {
        return realpath(__DIR__ . '/../../' . self::preparePath($path));
    }

    public function appPath(string $path): string
    {
        return self::basePath('src');
    }

    private static function preparePath(?string $path): string
    {
        return $path === null ? '' : trim($path, '/');
    }
}