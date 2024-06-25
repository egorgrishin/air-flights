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
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Вт',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Ср',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Чт',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Пт',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Сб',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                ],
                [
                    'text'          => 'Вс',
                    'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
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
                        'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                    ];
                }
            }

            $cb = self::NEXT . ":$this->dep:$this->arr:$this->month:$this->year:$day";
            if ($dt <= $tomorrow) {
                $day = '❌';
                $cb = self::SELF . ":$this->dep:$this->arr:$this->month:$this->year";
            }
            $buttons[$weekNum][] = [
                'text'          => $day,
                'callback_data' => $cb,
            ];
            if ($i === $daysCount - 1) {
                for ($j = $dayNum + 1; $j <= 7; $j++) {
                    $buttons[$weekNum][] = [
                        'text'          => '❌',
                        'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
                    ];
                }
            }
            if ($dayNum === 7) {
                $weekNum++;
            }
        }

        return $buttons;
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:$this->arr";
    }
}