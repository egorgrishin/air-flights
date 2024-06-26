<?php
declare(strict_types=1);

namespace App\Contracts;

interface HandlerContract
{
    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool;

    /**
     * Обработка запроса
     */
    public function process(): void;
}