<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;

final readonly class StartHandler extends Handler
{
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === '/start';
    }

    public function process(): void
    {
        $text = <<<TEXT
        Привет!
        Я - бот Air Flights и я занимаюсь мониторингом цен на авиабилеты!
        Выберите команду из меню
        TEXT;

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'text'         => $text,
            'reply_markup' => [
                'keyboard'          => [
                    [['text' => State::StartSubscription->value]],
                    [['text' => State::SubscriptionsList->value]],
                ],
                'one_time_keyboard' => true,
                'resize_keyboard'   => true,
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
    }
}