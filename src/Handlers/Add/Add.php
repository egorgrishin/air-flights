<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Enums\State;
use App\Handlers\Handler;

abstract readonly class Add extends Handler
{
    protected function getMenuButtons(): array
    {
        $prevData = $this->getPrevCbData();
        return [
            ...(!$prevData ? [] : [
                [
                    'text'          => 'Назад',
                    'callback_data' => $prevData,
                ],
            ]),
            [
                'text'          => 'Отменить',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }

    abstract protected function getPrevCbData(): ?string;
}