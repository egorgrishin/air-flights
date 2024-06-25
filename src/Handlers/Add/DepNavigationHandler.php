<?php
declare(strict_types=1);

namespace App\Handlers\Add;

use App\Contracts\DtoContract;
use App\Enums\State;
use App\Repositories\AirportRepository;

final readonly class DepNavigationHandler extends Add
{
    private const SELF = State::SelectDep->value;
    private const SELF_ANALOG = State::StartSubscription->value;
    private const NEXT = State::SelectArr->value;
    private AirportRepository $repository;
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
        return $dto->data === self::SELF_ANALOG
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
        $data = $dto->data === self::SELF_ANALOG ? self::SELF . ':>:0' : $dto->data;
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
                    'callback_data' => self::NEXT . ":$airport->code:>:0",
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
                'callback_data' => self::SELF . ":<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => self::SELF . ":>:$this->end",
            ];
        }
        return $buttons;
    }

    protected function getPrevCbData(): ?string
    {
        return null;
    }
}