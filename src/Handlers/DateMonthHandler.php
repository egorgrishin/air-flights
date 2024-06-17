<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
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

    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_date:[A-Z]{3}:[A-Z]{3}$/', $dto->data) === 1;
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
                    'callback_data' => "sel_day:$this->dep:$this->arr:$month:$year",
                ],
            ];
        }

        return $buttons;
    }
}