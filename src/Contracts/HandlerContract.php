<?php
declare(strict_types=1);

namespace App\Contracts;

use Psr\Http\Message\ResponseInterface;

interface HandlerContract
{
    public static function validate(DtoContract $dto): bool;

    public function process(DtoContract $dto): void;
}