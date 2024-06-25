<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Airport
{
    public function __construct(
        public string  $code,
        public ?string $cityCode,
        public ?int    $sort,
        public string  $title,
    ) {}
}