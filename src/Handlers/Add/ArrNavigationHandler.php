<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class ArrNavigationHandler extends Add
{
    private const PREV = State::SelectDep->value;
    private const SELF = State::SelectArr->value;
    private const NEXT = State::SelectMonth->value;
    private AirportRepository $repository;
    private string $dep;
    private int $offset;
    private int $limit;

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
        return preg_match("/^$state:[A-Z]{3}:\d+$/", $dto->data) === 1;
    }

    /**
     * Обработка запроса
     */
    public function process(): void
    {
        $airports = $this->repository->get($this->offset, $this->limit, $this->dep);
        $airportsCount = $this->repository->count($this->dep);

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "● Укажите город прибытия 🛬",
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
        [, $this->dep, $offset] = explode(':', $dto->data);
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
                'callback_data' => self::NEXT . ":$this->dep:$airport->code",
            ],
        ], $airports);
    }

    /**
     * Возвращает кнопки для постраничной навигации
     */
    private function getNavigationButtons(int $airportsCount): array
    {
        $navButtons = [];
        if ($this->offset > 0) {
            $newStart = max(0, $this->offset - $this->limit);
            $navButtons[] = [
                'text'          => '⬅️',
                'callback_data' => self::SELF . ":$this->dep:$newStart",
            ];
        }
        $end = $this->offset + $this->limit;
        if ($end < $airportsCount) {
            $navButtons[] = [
                'text'          => '➡️',
                'callback_data' => self::SELF . ":$this->dep:$end",
            ];
        }
        return $navButtons;
    }

    /**
     * Возвращает callback-data для кнопки "Назад"
     */
    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":0";
    }
}