<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class SelectDepHandler extends Add
{
    private const SELF        = State::SelectDep->value;
    private const SELF_ANALOG = State::StartSubscription->value;
    private const NEXT        = State::SelectArr->value;
    private AirportRepository $repository;
    private int               $offset;
    private int               $limit;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        $this->limit = 5;
        parent::__construct($dto);
    }

    /**
     * Проверяет, должен ли обработчик обрабатывать запрос
     */
    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return $dto->data === self::SELF_ANALOG
            || preg_match("/^$state:\d+$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $airports = $this->repository->get($this->offset, $this->limit);
        $airportsCount = $this->repository->count();

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "● Укажите город отправления 🛫",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getAirportButtons($airports),
                    $this->getNavigationButtons($airportsCount),
                    $this->getMenuButtons(),
                ],
            ],
        ]);
        $this->sendSuccessCallback();
    }

    /**
     * Сохраняет данные из DTO в свойства обработчика
     */
    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === self::SELF_ANALOG ? self::SELF . ':0' : $dto->data;
        [, $offset] = explode(':', $data);
        $this->offset = (int) $offset;
    }

    /**
     * Возвращает кнопки с аэропортами
     */
    private function getAirportButtons(array $airports): array
    {
        return array_map(fn (Airport $airport) => [
            [
                'text'          => $airport->title,
                'callback_data' => self::NEXT . ":$airport->code:0",
            ],
        ], $airports);
    }

    /**
     * Возвращает кнопки для постраничной навигации
     */
    private function getNavigationButtons(int $airportsCount): array
    {
        $buttons = [];
        if ($this->offset > 0) {
            $newStart = max(0, $this->offset - $this->limit);
            $buttons[] = [
                'text'          => '⬅️',
                'callback_data' => self::SELF . ":$newStart",
            ];
        }
        $end = $this->offset + $this->limit;
        if ($end < $airportsCount) {
            $buttons[] = [
                'text'          => '➡️',
                'callback_data' => self::SELF . ":$end",
            ];
        }
        return $buttons;
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return null;
    }
}