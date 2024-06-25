<?php
declare(strict_types=1);

namespace App\Contracts;

use DateTime;

interface SearcherContract
{
    /**
     * Возвращает код авиакомпании
     */
    public function getCode(): string;

    /**
     * Возвращает цену на авиабилет с указанными параметрами
     */
    public function getPrice(string $dep, string $arr, DateTime $dt): ?float;
}