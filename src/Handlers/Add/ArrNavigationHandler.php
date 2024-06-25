<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;

final readonly class ArrNavigationHandler extends Add
{
    private const PREV = State::SelectDep->value;
    private const SELF = State::SelectArr->value;
    private const NEXT = State::SelectMonth->value;
    private AirportRepository $repository;
    private string $dep;
    private int $start;
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
        return preg_match("/^$state:[A-Z]{3}:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->repository->getAll($this->start, $this->limit, $this->dep);
        $airportsCount = $this->repository->getCount($this->dep);

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите аэропорт прибытия",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getAirportButtons($airports),
                    $this->getNavigationButtons($airportsCount),
                    $this->getMenuButtons(),
                ],
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
        [, $this->dep, $start] = explode(':', $dto->data);
        $this->start = (int) $start;
    }

    private function getAirportButtons(array $airports): array
    {
        $buttons = [];
        foreach ($airports as $airport) {
            $buttons[] = [
                [
                    'text'          => $airport->title,
                    'callback_data' => self::NEXT . ":$this->dep:$airport->code",
                ],
            ];
        }
        return $buttons;
    }

    private function getNavigationButtons(int $airportsCount): array
    {
        $navButtons = [];
        if ($this->start > 0) {
            $newStart = max(0, $this->start - $this->limit);
            $navButtons[] = [
                'text'          => '<-',
                'callback_data' => self::SELF . ":$this->dep:$newStart",
            ];
        }
        $end = $this->start + $this->limit;
        if ($end < $airportsCount) {
            $navButtons[] = [
                'text'          => '->',
                'callback_data' => self::SELF . ":$this->dep:$end",
            ];
        }
        return $navButtons;
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":0";
    }
}