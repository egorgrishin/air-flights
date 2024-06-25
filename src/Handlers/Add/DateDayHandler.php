<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use DateTime;

final readonly class DateDayHandler extends Add
{
    private const PREV = State::SelectMonth->value;
    private const SELF = State::SelectDay->value;
    private const NEXT = State::AcceptMonitoring->value;
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;

    public function __construct(DtoContract $dto)
    {
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
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
            $month,
            $this->year,
        ] = $data;
        $this->month = $this->formatNum($month);
    }

    private function formatNum(string $num): string
    {
        return (int) $num < 10 ? '0' . (int) $num : $num;
    }

    private function getButtons(): array
    {
        $daysCount = cal_days_in_month(CAL_GREGORIAN, (int) $this->month, (int) $this->year);
        $buttons = [
            $this->getCalendarHeader(),
        ];

        $tomorrow = new DateTime('tomorrow');
        $weekNum = 1;
        for ($i = 0; $i < $daysCount; $i++) {
            $day = $i + 1;
            $dt = DateTime::createFromFormat('Y-m-j H:i', "$this->year-$this->month-$day 00:00");
            $dayNum = (int) $dt->format('N');
            if (empty($buttons[$weekNum])) {
                $buttons[$weekNum] = [];
            }

            // Ставим кресты на днях из прошлого месяца
            if ($i === 0 && $weekNum === 1) {
                $this->addButtons(1, $dayNum, $weekNum, $buttons);
            }

            $buttons[$weekNum][] = $this->getDayData($dt, $tomorrow, $day);

            // Если это последний день месяца, то ставим кресты на днях следующего месяца
            if ($i === $daysCount - 1) {
                $this->addButtons($dayNum + 1, 8, $weekNum, $buttons);
            }

            if ($dayNum === 7) {
                $weekNum++;
            }
        }

        return $buttons;
    }

    private function getCalendarHeader(): array
    {
        $days = ['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'];
        $buttons = [];
        foreach ($days as $day) {
            $buttons[] = [
                'text'          => $day,
                'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
            ];
        }
        return $buttons;
    }

    private function addButtons(int $start, int $end, int $weekNum, array &$buttons): void
    {
        for ($j = $start; $end; $j++) {
            $buttons[$weekNum][] = [
                'text'          => '❌',
                'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
            ];
        }
    }

    private function getDayData(DateTime $dt, DateTime $tomorrow, int $day): array
    {
        return $dt <= $tomorrow ? [
            'text'          => '❌',
            'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
        ] : [
            'text'          => $day,
            'callback_data' => self::NEXT . ":$this->dep:$this->arr:$this->month:$this->year:$day",
        ];
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:$this->arr";
    }
}