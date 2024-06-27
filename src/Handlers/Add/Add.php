<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Enums\State;
use App\Handlers\Handler;

abstract readonly class Add extends Handler
{
    /**
     * Добавляет ноль перед номером дня или месяца, если они меньше 10
     */
    protected function formatNum(string $num): string
    {
        return (int) $num < 10 ? '0' . (int) $num : $num;
    }

    /**
     * Возвращает массив кнопок навигации
     */
    protected function getMenuButtons(): array
    {
        $prevData = $this->getPrevCbData();
        return [
            ...(!$prevData ? [] : [
                [
                    'text'          => 'Назад ⏪️',
                    'callback_data' => $prevData,
                ],
            ]),
            [
                'text'          => 'Отменить ❌️',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    abstract protected function getPrevCbData(): ?string;
}