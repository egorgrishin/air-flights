<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Handler;
use App\Repositories\AirportRepository;

final readonly class DepNavigationHandler extends Handler
{
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
        return $dto->data === 'Начать мониторинг'
            || preg_match('/^sel_dep:[<>]:\d+$/', $dto->data) === 1;
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
        $data = $dto->data === '/Начать мониторинг' ? 'sel_dep:>:0' : $dto->data;
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
                    'callback_data' => "sel_arr:$airport->code:>:0",
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
                'callback_data' => "sel_dep:<:$this->start",
            ];
        }
        if ($this->end < $airportsCount) {
            $buttons[] = [
                'text'          => '->',
                'callback_data' => "sel_dep:>:$this->end",
            ];
        }
        return $buttons;
    }

    private function getMenuButtons(): array
    {
        return [
            [
                'text'          => 'Отменить',
                'callback_data' => "sel_cancel",
            ],
        ];
    }
}