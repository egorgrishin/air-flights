<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;

final readonly class CancelHandler extends Handler
{
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === State::CancelMonitoring->value;
    }

    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => 'Отменено',
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
    }
}