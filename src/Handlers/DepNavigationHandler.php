<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Handler;
use App\Repositories\AirportRepository;

final readonly class DepNavigationHandler extends Handler
{
    private AirportRepository $repository;
    private int $start;
    private int $end;
    private string $selfState;
    private string $nextState;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        $this->selfState = State::SelectDep->value;
        $this->nextState = State::SelectArr->value;
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = State::SelectDep->value;
        return $dto->data === State::StartSubscription->value
            || preg_match("/^$state:[<>]:\d+$/", $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->repository->getAll();

        $this->telegram->send(
            $this->method,
            $this->getMessageData($airports),
        );
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === State::StartSubscription->value ? "$this->selfState:>:0" : $dto->data;
        [, $sign, $index] = explode(':', $data);
        $this->start = (int) ($sign === '>' ? $index : ($index - 5));
        $this->end = $this->start + 5;
    }

    private function getMessageData(array $airports): array
    {
        return [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$this->getAirportButtons($airports),
                    $this->getNavigationButtons(count($airports)),
                    $this->getMenuButtons(),
                ],
            ],
        ];
    }

    private function getAirportButtons(array $airports): array
    {
        $buttons = [];
        $airports = array_slice($airports, max(0, $this->start), $this->end - $this->start);
        foreach ($airports as $airport) {
            $buttons[] = [
                [
                    'text'          => $airport->title,
                    'callback_data' => "$this->nextState:$airport->code:>:0",
                ],
            ];
        }
        return $buttons;
    }

    private function getNavigationButtons(int $airportsCount): array
    {
        $buttons = [];
        if ($this->start > 0) {
            $buttons[] = [
                'text'          => '<-',
                'callback_data' => "$this->selfState:<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => "$this->selfState:>:$this->end",
            ];
        }
        return $buttons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Отменить',
                'callback_data' => State::CancelMonitoring->value,
            ],
        ];
    }
}