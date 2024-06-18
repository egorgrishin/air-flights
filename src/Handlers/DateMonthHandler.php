<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handler;

final readonly class DateMonthHandler extends Handler
{
    private const MONTHS = [
        'Декабрь',
        'Февраль',
        'Март',
        'Апрель',
        'Май',
        'Июнь',
        'Июль',
        'Август',
        'Сентябрь',
        'Октябрь',
        'Ноябрь',
        'Декабрь',
    ];

    private string $dep;
    private string $arr;
    private string $prevState;
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->prevState = State::SelectArr->value;
        $this->nextState = State::SelectDay->value;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = State::SelectMonth->value;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите месяц",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getButtons(),
                    $this->getMenuButtons(),
                ],
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = explode(':', $dto->data);
        [, $this->dep, $this->arr] = $data;
    }

    private function getButtons(): array
    {
        $buttons = [];
        $originalMonth = (int) date('n');
        $originalYear = (int) date('Y');

        for ($i = 0; $i < 6; $i++) {
            $month = $originalMonth + $i;
            $monthIndex = $month - 1;
            $year = $originalYear + intdiv($monthIndex, 12);
            $buttons[] = [
                [
                    'text'          => self::MONTHS[$monthIndex % 12] . ", {$year}г.",
                    'callback_data' => "$this->nextState:$this->dep:$this->arr:$month:$year",
                ],
            ];
        }

        return $buttons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Назад',
                'callback_data' => "$this->prevState:$this->dep:>:0",
            ],
            [
                'text'          => 'Отменить',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }
}