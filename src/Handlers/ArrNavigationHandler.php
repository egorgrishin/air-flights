<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Handler;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final readonly class ArrNavigationHandler extends Handler
{
    private AirportRepository $repository;
    private string $dep;
    private int $start;
    private int $end;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_arr:[A-Z]{3}:[<>]:\d+$/', $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->getAirports();
        $airportButtons = $this->getAirportButtons($airports);
        $navButtons = $this->getNavigationButtons(count($airports));

        $this->telegram->send($this->method, [
            'chat_id'      => $this->fromId,
            'message_id'   => $this->messageId,
            'text'         => "Выберите аэропорт прибытия",
            'reply_markup' => [
                'inline_keyboard' => [...$airportButtons, $navButtons],
            ],
        ]);
    }

    protected function parseDto(DtoContract $dto): void
    {
        [, $this->dep, $sign, $index] = explode(':', $dto->data);
        $this->start = $sign === '>' ? $index : ($index - 5);
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
                    'callback_data' => "sel_date:$this->dep:$airport->code",
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
                'callback_data' => "sel_arr:$this->dep:<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => "sel_arr:$this->dep:>:$this->end",
            ];
        }
        return $buttons;
    }
}