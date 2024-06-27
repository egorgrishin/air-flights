<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Company
{
    public function __construct(
        public string $code,
        public string $title,
    )
    {
    }
}