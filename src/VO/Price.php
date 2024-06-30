<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Price
{
    public function __construct(
        public string $companyCode,
        public int    $subscriptionId,
        public ?float $price,
    ) {}

    public function toArray(): array
    {
        return [
            'company_code'    => $this->companyCode,
            'subscription_id' => $this->subscriptionId,
            'price'           => $this->price,
        ];
    }
}