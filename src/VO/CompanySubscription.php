<?php
declare(strict_types=1);

namespace App\VO;

final readonly class CompanySubscription
{
    public string $companyCode;
    public int    $subscriptionId;
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
        return function ($companyCode, $subscriptionId, $price): CompanySubscription {
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