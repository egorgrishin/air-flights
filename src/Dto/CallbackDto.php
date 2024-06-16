<?php
declare(strict_types=1);

namespace App\Dto;

use App\Contracts\DtoContract;

final readonly class CallbackDto implements DtoContract
{
    public int $fromId;
    public int $messageId;
    public string $data;

    public function __construct(array $body)
    {
        $this->fromId = $body['callback_query']['from']['id'];
        $this->messageId = $body['callback_query']['message']['message_id'];
        $this->data = $body['callback_query']['data'];
    }
}