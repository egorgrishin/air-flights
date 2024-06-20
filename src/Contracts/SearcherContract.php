<?php
declare(strict_types=1);

namespace App\Contracts;

use DateTime;

interface SearcherContract
{
    public function run(string $dep, string $arr, DateTime $dateTime): float;

    public function getCode(): string;
}