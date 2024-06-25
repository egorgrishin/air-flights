<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;

final readonly class DateMonthHandler extends Add
{
    private const PREV = State::SelectArr->value;
    private const SELF = State::SelectMonth->value;
    private const NEXT = State::SelectDay->value;
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

    public function __construct(DtoContract $dto)
    {
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
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
                    'callback_data' => self::NEXT . ":$this->dep:$this->arr:$month:$year",
                ],
            ];
        }

        return $buttons;
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:>:0";
    }
}