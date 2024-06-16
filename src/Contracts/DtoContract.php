<?php
declare(strict_types=1);

namespace App\Contracts;

interface DtoContract
{
    public function __construct(array $body);
}