<?php
declare(strict_types=1);

namespace App;

use App\Contracts\DtoContract;
use App\Core\Telegram;
use App\Dto\CallbackDto;
use App\Enums\TelegramMethod;

abstract readonly class Handler
{
    protected Telegram $telegram;
    protected TelegramMethod $method;
    protected int $fromId;
    protected ?int $messageId;

    public function __construct(DtoContract $dto)
    {
        $this->telegram = new Telegram();
        $this->fromId = $dto->fromId;
        $this->parseDto($dto);

        if ($dto instanceof CallbackDto) {
            $this->telegram->send(TelegramMethod::SendAnswer, [
                'callbackQueryId' => $dto->callbackQueryId,
            ]);

            $this->method = TelegramMethod::Edit;
            $this->messageId = $dto->messageId;
        } else {
            $this->method = TelegramMethod::Send;
            $this->messageId = null;
        }

    }

    abstract public function process(): void;

    abstract protected function parseDto(DtoContract $dto): void;
}