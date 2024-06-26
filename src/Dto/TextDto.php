<?php
declare(strict_types=1);

namespace App\Dto;

use App\Contracts\DtoContract;

final readonly class TextDto implements DtoContract
{
    public string $fromId;
    public string $data;

    public function __construct(array $body)
    {
        $this->fromId = (string) $body['message']['from']['id'];
        $this->data = $body['message']['text'];
    }
}