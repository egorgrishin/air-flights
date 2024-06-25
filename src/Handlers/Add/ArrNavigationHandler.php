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
    private int $start;
    private int $end;

    public function __construct(DtoContract $dto)
    {
        $this->repository = new AirportRepository();
        parent::__construct($dto);
    }

    public static function validate(DtoContract $dto): bool
    {
        $state = self::SELF;
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
            $navButtons[] = [
                'text'          => '<-',
                'callback_data' => self::SELF . ":$this->dep:<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $navButtons[] = [
                'text'          => '->',
                'callback_data' => self::SELF . ":$this->dep:>:$this->end",
            ];
        }
        return $navButtons;
    }

    protected function getPrevCbData(): ?string
    {
        return self::PREV . ":>:0";
    }
}