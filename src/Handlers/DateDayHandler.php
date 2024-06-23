<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handler;
use DateTime;

final readonly class DateDayHandler extends Handler
{
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;
    private string $prevState;
    private string $selfState;
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->prevState = State::SelectMonth->value;
        $this->selfState = State::SelectDay->value;
        $this->nextState = State::AcceptMonitoring->value;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = State::SelectDay->value;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите день",
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
        [
            ,
            $this->dep,
            $this->arr,
            $this->month,
            $this->year,
        ] = $data;
    }

    private function getButtons(): array
    {
        $daysCount = cal_days_in_month(CAL_GREGORIAN, (int) $this->month, (int) $this->year);
        $buttons = [
            [
                [
                    'text'          => 'Пн',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Вт',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Ср',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Чт',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Пт',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Сб',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Вс',
                    'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                ],
            ],
        ];
        $tomorrow = new DateTime('tomorrow');
        $weekNum = 1;

        for ($i = 0; $i < $daysCount; $i++) {
            $day = $i + 1;
            $dt = DateTime::createFromFormat('Y-n-j H:i', "$this->year-$this->month-$day 00:00");
            $dayNum = (int) $dt->format('N');

            if (empty($buttons[$weekNum])) {
                $buttons[$weekNum] = [];
            }
            if ($i === 0 && $weekNum === 1) {
                for ($j = 1; $j < $dayNum; $j++) {
                    $buttons[$weekNum][] = [
                        'text'          => '❌',
                        'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                    ];
                }
            }

            $cb = "$this->nextState:$this->dep:$this->arr:$this->month:$this->year:$day";
            if ($dt <= $tomorrow) {
                $day = '❌';
                $cb = "$this->selfState:$this->dep:$this->arr:$this->month:$this->year";
            }
            $buttons[$weekNum][] = [
                'text'          => $day,
                'callback_data' => $cb,
            ];
            if ($i === $daysCount - 1) {
                for ($j = $dayNum + 1; $j <= 7; $j++) {
                    $buttons[$weekNum][] = [
                        'text'          => '❌',
                        'callback_data' => "$this->selfState:$this->dep:$this->arr:$this->month:$this->year",
                    ];
                }
            }
            if ($dayNum === 7) {
                $weekNum++;
            }
        }

        return $buttons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Назад',
                'callback_data' => "$this->prevState:$this->dep:$this->arr",
            ],
            [
                'text'          => 'Отменить',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }
}