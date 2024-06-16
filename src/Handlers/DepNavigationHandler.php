<?php
declare(strict_types=1);

namespace App\Handlers;

use App\Contracts\DtoContract;
use App\Contracts\HandlerContract;
use App\Core\Telegram;
use App\Repositories\AirportRepository;
use App\VO\Airport;

final class DepNavigationHandler implements HandlerContract
{
    public static function validate(DtoContract $dto): bool
    {
        return preg_match('/^sel_dep:[<>]:\d+$/', $dto->data) === 1;
    }

    public function process(DtoContract $dto): void
    {
        $airports = (new AirportRepository())->getAll();
        [$start, $end] = $this->getIndexes($dto->data, count($airports));

        $nav = [];
        if ($start > 0) {
            $nav[] = ['text' => '<-', 'callback_data' => 'sel_dep:<:' . $start];
        }
        if ($end < count($airports)) {
            $nav[] = ['text' => '->', 'callback_data' => 'sel_dep:>:' . $end];
        }

        $airports = array_slice($airports, $start, $end - $start);
        foreach ($airports as &$airport) {
            $airport = [
                [
                    'text'          => $airport->title,
                    'callback_data' => "sel_arr:$airport->code:>:0",
                ],
            ];
        }

        Telegram::send('editMessageText', [
            'chat_id'      => $dto->fromId,
            'message_id'   => $dto->messageId,
            'text'         => "Выберите аэропорт отправления",
            'reply_markup' => [
                'inline_keyboard' => [
                    ...$airports,
                    $nav,
                ],
                //                'one_time_keyboard' => true,
                //                'resize_keyboard'   => true,
            ],
        ]);
    }

    private function getIndexes(string $data, int $airportsCount): array
    {
        [, $sign, $index] = explode(':', $data);
        $index = (int) $index;

        return $sign === '>'
            ? [$index, min($airportsCount, $index + 5)]
            : [max(0, $index - 5), $index];
    }
}