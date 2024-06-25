<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class DepNavigationHandler extends Add
{
    private const SELF = State::SelectDep->value;
    private const SELF_ANALOG = State::StartSubscription->value;
    private const NEXT = State::SelectArr->value;
    private AirportRepository $repository;
    private int $offset;
    private int $limit;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        $this->limit = 5;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
        return $dto->data === self::SELF_ANALOG
            || preg_match("/^$state:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->repository->getAll($this->offset, $this->limit);
        $airportsCount = $this->repository->getCount();

        $this->telegram->send(
            $this->method,
            $this->getMessageData($airports, $airportsCount),
        );
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === self::SELF_ANALOG ? self::SELF . ':0' : $dto->data;
        [, $offset] = explode(':', $data);
        $this->offset = (int) $offset;
    }

    private function getMessageData(array $airports, int $airportsCount): array
    {
        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getAirportButtons($airports),
                    $this->getNavigationButtons($airportsCount),
                    $this->getMenuButtons(),
                ],
            ],
        ];
    }

    /**
     * @param Airport[] $airports
     * @return array
     */
    private function getAirportButtons(array $airports): array
    {
        $buttons = [];
        foreach ($airports as $airport) {
            $buttons[] = [
                [
                    'text'          => $airport->title,
                    'callback_data' => self::NEXT . ":$airport->code:0",
                ],
            ];
        }
        return $buttons;
    }

    private function getNavigationButtons(int $airportsCount): array
    {
        $buttons = [];
        if ($this->offset > 0) {
            $newStart = max(0, $this->offset - $this->limit);
            $buttons[] = [
                'text'          => '<-',
                'callback_data' => self::SELF . ":$newStart",
            ];
        }
        $end = $this->offset + $this->limit;
        if ($end < $airportsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => self::SELF . ":$end",
            ];
        }
        return $buttons;
    }

    protected function getPrevCbData(): ?string
    {
        return null;
    }
}