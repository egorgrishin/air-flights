<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Handler;

final readonly class DateDayHandler extends Handler
{
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;

    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_day:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}$/', $dto->data) === 1;
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
                ],
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = explode(':', $dto->data);
        [
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

        for ($i = 0; $i < $daysCount; $i++) {
            $index = intdiv($i, 5);
            if (empty($buttons[$index])) {
                $buttons[$index] = [];
            }

            $day = $i + 1;
            $buttons[$index][] = [
                'text'          => $day,
                'callback_data' => "sel_acc:$this->dep:$this->arr:$this->month:$this->year:$day",
            ];
        }

        return $buttons;
    }
}