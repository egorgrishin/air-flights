<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Airport
{
    public string $code;
    public ?string $city_code;
    public ?int $sort;
    public string $title;
}