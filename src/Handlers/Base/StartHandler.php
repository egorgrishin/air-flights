<?php
declare(strict_types=1);

namespace App\Handlers\Base;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handlers\Handler;

final readonly class StartHandler extends Handler
{
    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === '/start';
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $text = <<<TEXT
        Привет, путешественник! Я бот Air Flights✈️
        Я занимаюсь отслеживанием цен на авиабилеты!
        Приступим?) Для того, чтобы начать нажми "Start 🚀"
        TEXT;

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'text'         => $text,
            'reply_markup' => [
                'keyboard'          => [
                    [['text' => State::StartSubscription->value]],
                    [['text' => State::SubscriptionsList->value]],
                    [['text' => State::Instruction->value]],
                ],
                'one_time_keyboard' => true,
                'resize_keyboard'   => true,
            ],
        ]);
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
    }
}