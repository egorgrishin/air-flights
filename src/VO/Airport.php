<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Airport
{
    public function __construct(
        public ?string $code = null,
        public ?string $cityCode = null,
        public ?int    $sort = null,
        public ?string $title = null,
    ) {}
}