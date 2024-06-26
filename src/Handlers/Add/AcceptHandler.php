<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class AcceptHandler extends Add
{
    private const PREV = State::SelectDay->value;
    private const SELF = State::AcceptMonitoring->value;
    private const NEXT = State::SuccessMonitoring->value;
    private AirportRepository $repository;
    private string $dep;
    private string $arr;
    private string $month;
    private string $year;
    private string $day;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        parent::__construct($dto);
    }

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return preg_match("/^$state:[A-Z]{3}:[A-Z]{3}:\d{1,2}:\d{4}:\d{1,2}$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $airports = $this->repository->getByCode([$this->dep, $this->arr]);
        $dep = $this->getAirportByCode($this->dep, $airports);
        $arr = $this->getAirportByCode($this->arr, $airports);

        $this->telegram->send(
            $this->method,
            $this->getMessageData($dep, $arr),
        );
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
        [
            ,
            $this->dep,
            $this->arr,
            $month,
            $this->year,
            $day,
        ] = explode(':', $dto->data);
        $this->month = $this->formatNum($month);
        $this->day = $this->formatNum($day);
    }

    /**
     * Возвращает данные для отправки в Telegram
     */
    private function getMessageData(Airport $dep, Airport $arr): array
    {
        $text = <<<TEXT
            ● Город отправления: $dep->title ($dep->code)
            ● Город прибытия: $arr->title ($arr->code)
            Дата вылета:  $this->day.$this->month.$this->year
        TEXT;

        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => $text,
            'reply_markup' => [
                'inline_keyboard' => [
                    $this->getSuccessButton(),
                    $this->getMenuButtons(),
                ],
            ],
        ];
    }

    /**
     * Возвращает аэропорт по его коду из массива аэропортов
     *
     * @param string $code
     * @param Airport[] $airports
     * @return Airport
     */
    private function getAirportByCode(string $code, array $airports): Airport
    {
        $airports = array_filter($airports, fn (Airport $airport) => $airport->code === $code);
        return array_values($airports)[0];
    }

    /**
     * Возвращает данные для кнопки подтверждения
     */
    private function getSuccessButton(): array
    {
        return [
            [
                'text'          => 'Подтвердить ✅️',
                'callback_data' => self::NEXT . ":$this->dep:$this->arr:$this->month:$this->year:$this->day",
            ],
        ];
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":$this->dep:$this->arr:$this->month:$this->year";
    }
}