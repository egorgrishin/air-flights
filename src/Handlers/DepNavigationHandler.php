<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Enums\TelegramMethod;
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
        return $dto->data === '/new'
            || preg_match('/^sel_dep:[<>]:\d+$/', $dto->data) === 1;
    }

    public function process(): void
    {
        $airports = $this->repository->getAll();
        $airportButtons = $this->getAirportButtons($airports);
        $navButtons = $this->getNavigationButtons(count($airports));

        $this->telegram->send(
            $this->method,
            $this->getMessageData([...$airportButtons, $navButtons]),
        );
    }

    protected function parseDto(DtoContract $dto): void
    {
        $data = $dto->data === '/new' ? 'sel_dep:>:5' : $dto->data;
        [, $sign, $index] = explode(':', $data);
        $this->start = (int) ($sign === '>' ? $index : ($index - 5));
        $this->end = $this->start + 5;
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

    private function getMessageData(array $buttons): array
    {
        $data = [
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => $buttons,
            ],
        ];
        if ($this->method === TelegramMethod::Edit) {
            $data['chat_id'] = $this->fromId;
            $data['message_id'] = $this->messageId;
        }
        return $data;
    }
}