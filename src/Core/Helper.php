<?php
declare(strict_types=1);

namespace App\Core;

final class Helper
{
    /**
     * Возвращает абсолютный путь к файлу
     */
    public static function basePath(string $path = null): string
    {
        return realpath(__DIR__ . '/../../' . self::preparePath($path));
    }

    /**
     * Подготавливает путь для метода basePath
     */
    private static function preparePath(?string $path): string
    {
        return $path === null ? '' : trim($path, '/');
    }
}