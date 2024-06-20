<?php
declare(strict_types=1);

namespace App\VO;

final readonly class Subscription
{
    public ?int    $id;
    public string  $chatId;
    public string  $depCode;
    public string  $arrCode;
    public string  $date;
    public ?bool   $isActive;
    public ?string $createdAt;

    public function __construct(
        string  $chatId,
        string  $depCode,
        string  $arrCode,
        string  $date,
        ?int    $id = null,
        ?bool   $isActive = null,
        ?string $createdAt = null,
    ) {
        $this->id = $id;
        $this->chatId = $chatId;
        $this->depCode = $depCode;
        $this->arrCode = $arrCode;
        $this->date = $date;
        $this->isActive = $isActive;
        $this->createdAt = $createdAt;
    }

    public static function fromPdo(): callable
    {
        return function ($id, $chatId, $dep, $arr, $date): Subscription {
            return new self($chatId, $dep, $arr, $date, $id);
        };
    }
}