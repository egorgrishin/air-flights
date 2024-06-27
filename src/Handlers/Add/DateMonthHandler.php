<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;

final readonly class DateMonthHandler extends Add
{
    private const PREV   = State::SelectArr->value;
    private const SELF   = State::SelectMonth->value;
    private const NEXT   = State::SelectDay->value;
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

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
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

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
        $data = explode(':', $dto->data);
        [, $this->dep, $this->arr] = $data;
    }

    /**
     * Возвращает массив кнопок - список месяцев
     */
    private function getButtons(): array
    {
        $buttons = [];
        $originalMonth = (int) date('n');
        $originalYear = (int) date('Y');

        for ($month = $originalMonth; $month < $originalMonth + 6; $month++) {
            $monthIndex = $month - 1;
            $year = $originalYear + intdiv($monthIndex, 12);
            $buttons[] = [
                [
                    'text'          => self::MONTHS[$monthIndex % 12] . ", $year",
                    'callback_data' => self::NEXT . ":$this->dep:$this->arr:$month:$year",
                ],
            ];
        }

        return $buttons;
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:0";
    }
}