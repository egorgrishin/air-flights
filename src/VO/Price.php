<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Price
{
    public function __construct(
        public ?string $companyCode = null,
        public ?int    $subscriptionId = null,
        public ?float  $price = null,
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