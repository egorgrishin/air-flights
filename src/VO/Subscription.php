<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Subscription
{
    public function __construct(
        public string  $chatId,
        public string  $date,
        public ?int    $id = null,
        public ?string $depCode = null,
        public ?string $arrCode = null,
        public ?bool   $isActive = null,
        public ?string $createdAt = null,
        public ?float  $minPrice = null,
        public ?string $depTitle = null,
        public ?string $arrTitle = null,
    )
    {
    }
}