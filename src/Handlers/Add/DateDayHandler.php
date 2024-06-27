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

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите день",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getCalendarButtons(),
                    $this->getMenuButtons(),
                ],
            ],
        ]);
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
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

    /**
     * Возвращает массив кнопок - календарь в виде клавиатуры
     */
    private function getCalendarButtons(): array
    {
        $tomorrow = new DateTime('tomorrow');
        $daysCount = cal_days_in_month(CAL_GREGORIAN, (int) $this->month, (int) $this->year);
        $buttons = [$this->getCalendarHeader()];
        $weekNum = 1;

        for ($day = 1; $day <= $daysCount; $day++) {
            $dt = DateTime::createFromFormat('Y-m-j H:i', "$this->year-$this->month-$day 00:00");
            // Номер дня недели
            $dayNum = (int) $dt->format('N');
            if (empty($buttons[$weekNum])) {
                $buttons[$weekNum] = [];
            }

            // Если это первый день и первая неделя, то на днях прошлого месяца ставим кресты
            if ($day === 1 && $weekNum === 1) {
                $this->addButtons(1, $dayNum, $weekNum, $buttons);
            }

            // Добавляем день в календарь
            $buttons[$weekNum][] = $this->getDayData($dt, $tomorrow, $day);

            // Если это последний день месяца, то на днях следующего месяца ставим кресты
            if ($day === $daysCount) {
                $this->addButtons($dayNum + 1, 8, $weekNum, $buttons);
            }

            // Если день - воскресенье, то увеличиваем номер неделеи
            if ($dayNum === 7) {
                $weekNum++;
            }
        }

        return $buttons;
    }

    /**
     * Возвращает массив кнопок - заголовок календаря
     */
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

    /**
     * Добавляет кресты в дни, попадающие в диапазон
     */
    private function addButtons(int $start, int $end, int $weekNum, array &$buttons): void
    {
        for ($j = $start; $j < $end; $j++) {
            $buttons[$weekNum][] = [
                'text'          => '❌',
                'callback_data' => self::SELF . ":$this->dep:$this->arr:$this->month:$this->year",
            ];
        }
    }

    /**
     * Возвращает день в виде кнопки для Telegram'а.
     * Если этот день недоступен - возвращает крест.
     * Если еще не прошел - возвращает сам день.
     */
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

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:$this->arr";
    }
}