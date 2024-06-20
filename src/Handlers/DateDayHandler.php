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
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->prevState = State::SelectMonth->value;
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
        $buttons = [];
        $tomorrow = new DateTime();

        for ($i = 0; $i < $daysCount; $i++) {
            $index = intdiv($i, 5);
            if (empty($buttons[$index])) {
                $buttons[$index] = [];
            }

            $day = $i + 1;
            $dt = DateTime::createFromFormat('Y-n-j', "$this->year-$this->month-$day");
            if ($dt < $tomorrow) {
                continue;
            }
            $buttons[$index][] = [
                'text'          => $day,
                'callback_data' => "$this->nextState:$this->dep:$this->arr:$this->month:$this->year:$day",
            ];
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