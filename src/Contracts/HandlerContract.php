<?php
declare(strict_types=1);

namespace App\Contracts;

interface HandlerContract
{
    public static function validate(DtoContract $dto): bool;

    public function process(): void;
}