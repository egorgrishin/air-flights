<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;
use App\Repositories\AirportRepository;

final class DepNavigationHandler implements HandlerContract
{
    private array $airports;

    public function __construct()
    {
        $this->airports = (new AirportRepository())->getAll();
    }

    public static function validate(DtoContract $dto): bool
    {
        return $dto->data === 'Начать мониторинг'
            || preg_match('/^sel_dep:[<>]:\d+$/', $dto->data) === 1;
    }

    public function process(DtoContract $dto): void
    {
        $method = $this->getMethod($dto->data);
        $data = $this->getMessageData($dto->data);
        [$start, $end] = $this->getIndexes($data, count($this->airports));

        $airportButtons = $this->getAirportButtons($start, $end);
        $navButtons = $this->getNavigationButtons($start, $end);


        Telegram::send($method, [
            'chat_id'      => $dto->fromId,
            'message_id'   => $dto->messageId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$airportButtons,
                    $navButtons,
                ],
            ],
        ]);
    }

    private function getMethod(string $data): string
    {
        return $data === 'Начать мониторинг'
            ? 'sendMessage'
            : 'editMessageText';
    }

    private function getMessageData(string $data): string
    {
        return $data === 'Начать мониторинг'
            ? 'sel_dep:>:5'
            : $data;
    }

    private function getIndexes(string $data, int $airportsCount): array
    {
        [, $sign, $index] = explode(':', $data);
        $index = (int) $index;

        return $sign === '>'
            ? [$index, min($airportsCount, $index + 5)]
            : [max(0, $index - 5), $index];
    }

    private function getNavigationButtons(int $start, int $end): array
    {
        $navButtons = [];
        if ($start > 0) {
            $navButtons[] = [
                'text'          => '<-',
                'callback_data' => "sel_dep:<:$start",
            ];
        }
        if ($end < count($this->airports)) {
            $navButtons[] = [
                'text'          => '->',
                'callback_data' => "sel_dep:>:$end",
            ];
        }
        return $navButtons;
    }

    private function getAirportButtons(int $start, int $end): array
    {
        $airportButtons = [];
        $airports = array_slice($this->airports, $start, $end - $start);
        foreach ($airports as $airport) {
            $airportButtons[] = [
                [
                    'text'          => $airport->title,
                    'callback_data' => "sel_arr:$airport->code:>:0",
                ],
            ];
        }
        return $airportButtons;
    }
}