<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handler;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class ArrNavigationHandler extends Handler
{
    private AirportRepository $repository;
    private string $dep;
    private int $start;
    private int $end;
    private string $prevState;
    private string $selfState;
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        $this->prevState = State::SelectDep->value;
        $this->selfState = State::SelectArr->value;
        $this->nextState = State::SelectMonth->value;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = State::SelectArr->value;
        return preg_match("/^$state:[A-Z]{3}:[<>]:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->getAirports();

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите аэропорт прибытия",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getAirportButtons($airports),
                    $this->getNavigationButtons(count($airports)),
                    $this->getMenuButtons(),
                ],
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
        [, $this->dep, $sign, $index] = explode(':', $dto->data);
        $this->start = (int) ($sign === '>' ? $index : ($index - 5));
        $this->end = $this->start + 5;
    }

    private function getAirports(): array
    {
        $airports = $this->repository->getAll();
        $airports = array_filter($airports, fn (Airport $airport) => $airport->code !== $this->dep);
        return array_values($airports);
    }

    private function getAirportButtons(array $airports): array
    {
        $buttons = [];
        $airports = array_slice($airports, max(0, $this->start), $this->end - $this->start);
        foreach ($airports as $airport) {
            $buttons[] = [
                [
                    'text'          => $airport->title,
                    'callback_data' => "$this->nextState:$this->dep:$airport->code",
                ],
            ];
        }
        return $buttons;
    }

    private function getNavigationButtons(int $airportsCount): array
    {
        $navButtons = [];
        if ($this->start > 0) {
            $navButtons[] = [
                'text'          => '<-',
                'callback_data' => "$this->selfState:$this->dep:<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $navButtons[] = [
                'text'          => '->',
                'callback_data' => "$this->selfState:$this->dep:>:$this->end",
            ];
        }
        return $navButtons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Назад',
                'callback_data' => "$this->prevState:>:0",
            ],
            [
                'text'          => 'Отменить',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }
}