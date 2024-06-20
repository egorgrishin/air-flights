<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Price
{
    public string $companyCode;
    public int $subscriptionId;
    public ?float $price;

    public function __construct(
        string $companyCode,
        int    $subscriptionId,
        ?float $price,
    )
    {
        $this->companyCode = $companyCode;
        $this->subscriptionId = $subscriptionId;
        $this->price = $price;
    }

    public static function fromPdo(): callable
    {
        return function (string $companyCode, int $subscriptionId, ?float $price): Price {
            return new self($companyCode, $subscriptionId, $price);
        };
    }

    public function toArray(): array
    {
        return [
            'company_code'    => $this->companyCode,
            'subscription_id' => $this->subscriptionId,
            'price'           => $this->price,
        ];
    }
}