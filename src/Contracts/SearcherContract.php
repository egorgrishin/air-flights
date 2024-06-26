<?php
declare(strict_types=1);

namespace App\Contracts;

use App\Exceptions\SearcherParseError;
use App\Exceptions\SearcherResponseError;
use DateTime;
use GuzzleHttp\Exception\GuzzleException;

interface SearcherContract
{
    /**
     * Возвращает код авиакомпании
     */
    public function getCode(): string;

    /**
     * Возвращает цену на авиабилет с указанными параметрами
     * @throws GuzzleException|SearcherParseError|SearcherResponseError
     */
    public function getPrice(string $dep, string $arr, DateTime $dt): ?float;
}